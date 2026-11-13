<?php

namespace App\Services;

use App\Models\Depot;
use App\Models\InventoryAdjustment;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductDepotStock;
use App\Models\Rayon;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * V6 — Une seule règle pour tous les ajustements d'inventaire (saisie, import
 * Excel, fiche d'inventaire, correction) :
 *
 * 1. on ajuste UN produit dans UN dépôt ; les autres dépôts ne bougent jamais ;
 * 2. à la saisie on enregistre l'écart (compté − stock du dépôt à ce moment),
 *    au CUMP du produit ; l'ajustement est « À valider » ;
 * 3. à la validation (chef magasinier / admin) l'écart est appliqué au stock
 *    ACTUEL du dépôt (les ventes / BT faits entre-temps restent comptés),
 *    le total du produit est recalculé (somme des dépôts) et le mouvement créé ;
 * 4. celui qui a le droit de valider et qui saisit valide en même temps ;
 * 5. pas de modification ni de suppression : contre-ajustement (corriger()) ;
 * 6. un ajustement peut aussi changer le rayon / l'emplacement de la pièce
 *    dans le dépôt (nouveau_rayon_id / nouvel_emplacement_id) : comme la
 *    quantité, la nouvelle place n'est appliquée qu'à la validation.
 */
class AjustementStockService
{
    public static function peutValider(?User $user): bool
    {
        return $user !== null && ($user->isAdmin() || $user->hasPermission('valider_ajustements'));
    }

    /**
     * Crée un ajustement. $donnees :
     *   product_id, depot_id, motif, remarque, source,
     *   qte_comptee (quantité comptée)  OU  ecart (correction)  OU  rien,
     *   nouveau_rayon_id / nouvel_emplacement_id (facultatif : nouvelle place),
     *   corrige_id (facultatif).
     * Retourne null s'il n'y a ni écart ni changement de place.
     */
    public function creer(array $donnees, User $user): ?InventoryAdjustment
    {
        $motif = (string) ($donnees['motif'] ?? '');
        $remarque = trim((string) ($donnees['remarque'] ?? ''));

        if (! array_key_exists($motif, InventoryAdjustment::MOTIFS)) {
            throw new RuntimeException('Choisissez le motif de l\'ajustement.');
        }

        if ($motif === 'autre' && $remarque === '') {
            throw new RuntimeException('Motif « Autre » : précisez la raison dans la remarque.');
        }

        return DB::transaction(function () use ($donnees, $motif, $remarque, $user) {
            $product = Product::query()->lockForUpdate()->findOrFail($donnees['product_id']);
            $depot = Depot::query()->findOrFail($donnees['depot_id']);
            $stock = $this->stockDepot($product, $depot->id);

            $ancienne = round((float) ($stock?->quantity ?? 0), 2);

            if (array_key_exists('ecart', $donnees) && $donnees['ecart'] !== null) {
                $ecart = round((float) $donnees['ecart'], 2);
            } elseif (array_key_exists('qte_comptee', $donnees) && $donnees['qte_comptee'] !== null && $donnees['qte_comptee'] !== '') {
                $ecart = round(round((float) $donnees['qte_comptee'], 2) - $ancienne, 2);
            } else {
                $ecart = 0.0;
            }

            if (abs($ecart) < 0.01) {
                $ecart = 0.0;
            }

            // place actuelle dans CE dépôt (celle du produit seulement si la pièce n'y a pas encore de ligne et que son rayon est de ce dépôt)
            $rayonDuProduit = $product->rayon_id && Rayon::query()->whereKey($product->rayon_id)->value('depot_id') == $depot->id;
            $rayonAvant = $stock ? $stock->rayon_id : ($rayonDuProduit ? $product->rayon_id : null);
            $emplacementAvant = $stock ? $stock->location_id : ($rayonDuProduit ? $product->location_id : null);
            [$nouveauRayon, $nouvelEmplacement] = $this->nouvellePlace($donnees, $rayonAvant, $emplacementAvant, $depot);

            if ($ecart === 0.0 && $nouveauRayon === null && $nouvelEmplacement === null) {
                return null;
            }

            $enAttente = InventoryAdjustment::query()
                ->where('product_id', $product->id)
                ->where('depot_id', $depot->id)
                ->where('statut', InventoryAdjustment::EN_ATTENTE)
                ->first();

            if ($enAttente) {
                throw new RuntimeException("La pièce {$product->reference} a déjà un ajustement à valider dans ce dépôt (n° {$enAttente->id}) : validez-le ou annulez-le d'abord.");
            }

            if ($ancienne + $ecart < -0.00001) {
                throw new RuntimeException("Le stock de {$product->reference} dans ce dépôt est {$ancienne} : un écart de {$ecart} le rendrait négatif.");
            }

            $cump = round((float) ($product->purchase_price ?? 0), 4);

            $ajustement = InventoryAdjustment::create([
                'statut'       => InventoryAdjustment::EN_ATTENTE,
                'source'       => $donnees['source'] ?? 'manuel',
                'product_id'   => $product->id,
                'depot_id'     => $depot->id,
                'rayon_id'     => $rayonAvant,
                'location_id'  => $emplacementAvant,
                'nouveau_rayon_id'      => $nouveauRayon,
                'nouvel_emplacement_id' => $nouvelEmplacement,
                'old_qty'      => $ancienne,
                'new_qty'      => round($ancienne + $ecart, 2),
                'motif'        => $motif,
                'ecart'        => $ecart,
                'cump'         => $cump,
                'valeur_ecart' => round($ecart * $cump, 2),
                'reason'       => $this->texteRaison($motif, $remarque),
                'created_by'   => $user->id,
                'corrige_id'   => $donnees['corrige_id'] ?? null,
            ]);

            if (self::peutValider($user)) {
                $this->valider($ajustement, $user);
            }

            return $ajustement->fresh();
        });
    }

    /** Applique l'écart au stock actuel du dépôt. */
    public function valider(InventoryAdjustment $ajustement, User $user): InventoryAdjustment
    {
        if (! self::peutValider($user)) {
            throw new RuntimeException('Vous n\'avez pas le droit de valider les ajustements.');
        }

        return DB::transaction(function () use ($ajustement, $user) {
            $ajustement = InventoryAdjustment::query()->lockForUpdate()->findOrFail($ajustement->id);

            if (! $ajustement->estEnAttente()) {
                throw new RuntimeException("L'ajustement n° {$ajustement->id} n'est plus à valider ({$ajustement->libelleStatut()}).");
            }

            $product = Product::query()->lockForUpdate()->findOrFail($ajustement->product_id);
            $depot = Depot::query()->findOrFail($ajustement->depot_id);
            $stock = $this->stockDepot($product, $depot->id)
                ?? new ProductDepotStock([
                    'product_id'  => $product->id,
                    'depot_id'    => $depot->id,
                    'rayon_id'    => $ajustement->rayon_id,
                    'location_id' => $ajustement->location_id,
                    'quantity'    => 0,
                ]);

            $ecart = $ajustement->ecartQuantite();
            $ancienne = round((float) $stock->quantity, 2);
            $nouvelle = round($ancienne + $ecart, 2);

            if ($nouvelle < -0.00001) {
                throw new RuntimeException("Pièce {$product->reference} : le stock du dépôt est maintenant {$ancienne} (ventes / transferts depuis la saisie) ; l'écart de {$ecart} le rendrait négatif. Refusez l'ajustement et recomptez.");
            }

            $stock->quantity = $nouvelle;

            if ($ajustement->nouveau_rayon_id || $ajustement->nouvel_emplacement_id) {
                $this->appliquerPlace($stock, $ajustement);
            }

            $stock->save();

            $product->quantity = round((float) ProductDepotStock::query()->where('product_id', $product->id)->sum('quantity'), 2);

            // La fiche produit suit la place du dépôt si la pièce n'est que dans ce dépôt
            if (($ajustement->nouveau_rayon_id || $ajustement->nouvel_emplacement_id)
                && ! ProductDepotStock::query()->where('product_id', $product->id)->where('depot_id', '!=', $depot->id)->exists()) {
                $product->rayon_id = $stock->rayon_id;
                $product->location_id = $stock->location_id;
            }

            $product->save();

            $ajustement->update([
                'statut'       => InventoryAdjustment::VALIDE,
                'old_qty'      => $ancienne,
                'new_qty'      => $nouvelle,
                'approved_by'  => $user->id,
                'validated_at' => now(),
            ]);

            if (abs($ecart) < 0.01) {
                return $ajustement; // changement de place seulement : pas de mouvement de stock
            }

            StockMovement::create([
                'product_id' => $product->id,
                'type'       => $ecart > 0 ? 'in' : 'out',
                'quantity'   => abs($ecart),
                'source'     => 'Ajustement inventaire (' . $ajustement->libelleMotif() . ') | Dépôt: ' . $depot->name,
                'reference'  => 'ADJ-' . $ajustement->id,
                'user_id'    => $user->id,
            ]);

            return $ajustement;
        });
    }

    public function refuser(InventoryAdjustment $ajustement, User $user, string $motif): InventoryAdjustment
    {
        if (! self::peutValider($user)) {
            throw new RuntimeException('Vous n\'avez pas le droit de refuser les ajustements.');
        }

        $this->verifierEnAttente($ajustement);

        $ajustement->update([
            'statut'      => InventoryAdjustment::REFUSE,
            'refuse_par'  => $user->id,
            'refuse_le'   => now(),
            'motif_refus' => trim($motif),
        ]);

        return $ajustement;
    }

    /** Le créateur (ou un valideur) retire un ajustement pas encore validé. */
    public function annuler(InventoryAdjustment $ajustement, User $user): InventoryAdjustment
    {
        if ((int) $ajustement->created_by !== (int) $user->id && ! self::peutValider($user)) {
            throw new RuntimeException('Seul celui qui a saisi l\'ajustement, le chef magasinier ou l\'admin peut l\'annuler.');
        }

        $this->verifierEnAttente($ajustement);

        $ajustement->update(['statut' => InventoryAdjustment::ANNULE, 'refuse_par' => $user->id, 'refuse_le' => now()]);

        return $ajustement;
    }

    /**
     * Contre-ajustement d'un ajustement validé. $correction :
     *   qte_correcte  = la quantité qui aurait dû être enregistrée, OU
     *   ecart_correct = l'écart qui aurait dû être enregistré ;
     *   nouveau_rayon_id / nouvel_emplacement_id = la bonne place (facultatif) ;
     *   remarque.
     * L'écart de correction est appliqué au stock actuel (à la validation) ;
     * l'ajustement d'origine reste dans l'historique.
     */
    public function corriger(InventoryAdjustment $ajustement, array $correction, User $user): InventoryAdjustment
    {
        if (! $ajustement->estValide()) {
            throw new RuntimeException('Seul un ajustement validé se corrige. Un ajustement à valider s\'annule ou se refuse.');
        }

        if ($ajustement->depot_id === null) {
            throw new RuntimeException('Cet ancien ajustement n\'a pas de dépôt : faites un nouvel ajustement sur le bon dépôt.');
        }

        $qteCorrecte = $correction['qte_correcte'] ?? null;
        $ecartCorrect = $correction['ecart_correct'] ?? null;
        $remarque = trim((string) ($correction['remarque'] ?? ''));
        $details = [];

        if ($qteCorrecte !== null && $qteCorrecte !== '') {
            $qteCorrecte = round((float) $qteCorrecte, 2);
            $ecart = round($qteCorrecte - (float) $ajustement->new_qty, 2);
            $details[] = 'quantité ' . $this->nombre($ajustement->new_qty) . ' → ' . $this->nombre($qteCorrecte);
        } elseif ($ecartCorrect !== null && $ecartCorrect !== '') {
            $ecartCorrect = round((float) $ecartCorrect, 2);
            $ecart = round($ecartCorrect - $ajustement->ecartQuantite(), 2);
            $details[] = 'écart ' . $this->signe($ajustement->ecartQuantite()) . ' → ' . $this->signe($ecartCorrect);
        } else {
            $ecart = 0.0;
        }

        if (! empty($correction['nouveau_rayon_id']) || ! empty($correction['nouvel_emplacement_id'])) {
            $details[] = 'place';
        }

        $nouveau = $this->creer([
            'product_id'            => $ajustement->product_id,
            'depot_id'              => $ajustement->depot_id,
            'motif'                 => 'erreur_saisie',
            'remarque'              => "Correction de l'ajustement n° {$ajustement->id}" . ($details ? ' (' . implode(', ', $details) . ')' : '') . ($remarque !== '' ? ' — ' . $remarque : ''),
            'source'                => 'correction',
            'ecart'                 => abs($ecart) < 0.01 ? 0.0 : $ecart,
            'nouveau_rayon_id'      => $correction['nouveau_rayon_id'] ?? null,
            'nouvel_emplacement_id' => $correction['nouvel_emplacement_id'] ?? null,
            'corrige_id'            => $ajustement->id,
        ], $user);

        if (! $nouveau) {
            throw new RuntimeException('Rien à corriger : la quantité, l\'écart et la place saisis sont ceux déjà enregistrés.');
        }

        return $nouveau;
    }

    public static function nombreAValider(): int
    {
        return InventoryAdjustment::query()->where('statut', InventoryAdjustment::EN_ATTENTE)->count();
    }

    private function verifierEnAttente(InventoryAdjustment $ajustement): void
    {
        if (! $ajustement->fresh()->estEnAttente()) {
            throw new RuntimeException("L'ajustement n° {$ajustement->id} n'est plus à valider.");
        }
    }

    private function stockDepot(Product $product, int $depotId): ?ProductDepotStock
    {
        return ProductDepotStock::query()
            ->where('product_id', $product->id)
            ->where('depot_id', $depotId)
            ->lockForUpdate()
            ->first();
    }

    /**
     * Nouvelle place demandée, ramenée à ce qui change vraiment :
     * un emplacement choisi seul entraîne son rayon ; une valeur identique à
     * la place actuelle est ignorée. Retourne [rayon|null, emplacement|null].
     */
    private function nouvellePlace(array $donnees, $rayonAvant, $emplacementAvant, ?Depot $depot = null): array
    {
        $rayon = ! empty($donnees['nouveau_rayon_id']) ? (int) $donnees['nouveau_rayon_id'] : null;
        $emplacement = ! empty($donnees['nouvel_emplacement_id']) ? (int) $donnees['nouvel_emplacement_id'] : null;

        if ($emplacement !== null) {
            $rayonDeLEmplacement = Location::query()->whereKey($emplacement)->value('rayon_id');

            if ($rayon === null && $rayonDeLEmplacement) {
                $rayon = (int) $rayonDeLEmplacement;
            } elseif ($rayon !== null && $rayonDeLEmplacement && (int) $rayonDeLEmplacement !== $rayon) {
                throw new RuntimeException('L\'emplacement choisi n\'est pas dans le rayon choisi.');
            }
        }

        // V6 : un rayon appartient à un seul dépôt
        if ($rayon !== null && $depot !== null) {
            $choisi = Rayon::query()->with('depot')->find($rayon);

            if ($choisi && ! $choisi->estDuDepot($depot->id)) {
                throw new RuntimeException("Le rayon {$choisi->name} n'est pas dans le dépôt {$depot->name}"
                    . ($choisi->depot ? " (il est dans {$choisi->depot->name})." : ' (il n\'a pas de dépôt).'));
            }
        }

        if ($rayon !== null && $rayon === (int) $rayonAvant) {
            $rayon = null;
        }

        if ($emplacement !== null && $emplacement === (int) $emplacementAvant) {
            $emplacement = null;
        }

        return [$rayon, $emplacement];
    }

    /** Applique la place demandée au stock du dépôt (emplacement vidé s'il n'est pas dans le nouveau rayon). */
    private function appliquerPlace(ProductDepotStock $stock, InventoryAdjustment $ajustement): void
    {
        if ($ajustement->nouveau_rayon_id) {
            $stock->rayon_id = $ajustement->nouveau_rayon_id;
        }

        if ($ajustement->nouvel_emplacement_id) {
            $stock->location_id = $ajustement->nouvel_emplacement_id;
        } elseif ($stock->location_id && (int) Location::query()->whereKey($stock->location_id)->value('rayon_id') !== (int) $stock->rayon_id) {
            $stock->location_id = null;
        }
    }

    private function nombre($valeur): string
    {
        return rtrim(rtrim(number_format((float) $valeur, 2, '.', ''), '0'), '.');
    }

    private function signe($valeur): string
    {
        return ((float) $valeur > 0 ? '+' : '') . $this->nombre($valeur);
    }

    private function texteRaison(string $motif, string $remarque): string
    {
        $libelle = InventoryAdjustment::MOTIFS[$motif];

        return $remarque !== '' ? $libelle . ' — ' . $remarque : $libelle;
    }
}
