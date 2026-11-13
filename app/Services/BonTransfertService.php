<?php

namespace App\Services;

use App\Models\BonTransfert;
use App\Models\Depot;
use App\Models\ExternalBonCommande;
use App\Models\ExternalBonCommandeLigne;
use App\Models\Product;
use App\Models\ProductDepotStock;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Crée le bon de transfert (BT) d'un bon de commande garage : les pièces
 * sortent du stock de leur dépôt (comme une vente) mais sans TVA, sans
 * remise, sans paiement, et sans créer de client ni de véhicule.
 */
class BonTransfertService
{
    /**
     * Crée le PREMIER bon de transfert du BC (V6 : BT partiel).
     * Seules les pièces prêtes (identifiées, avec dépôt, disponibles) partent ;
     * les autres restent « en attente » sur le BC et partiront dans un
     * « BT du reste » (BT-…-R1) quand elles seront réapprovisionnées.
     *
     * @throws RuntimeException si aucune pièce n'est prête, ou si le stock manque
     */
    public function creer(ExternalBonCommande $bc): BonTransfert
    {
        return DB::transaction(function () use ($bc) {
            // Verrouille le BC : deux clics simultanés ne créent pas deux BT.
            $bc = ExternalBonCommande::query()->lockForUpdate()->findOrFail($bc->id);

            if ($bc->bon_transfert_id) {
                return BonTransfert::findOrFail($bc->bon_transfert_id);
            }

            $bc->load('lignes.product', 'lignes.depot');

            $pretes = $bc->lignesPretesAEnvoyer();

            if ($pretes->isEmpty()) {
                throw new RuntimeException("Aucune pièce de {$bc->numero} n'est prête à partir (identifiée, avec un dépôt et disponible).");
            }

            [$annee, $sequence] = $this->prochainNumero($bc);

            $bt = BonTransfert::create([
                'numero'                   => sprintf('BT-%d-%04d', $annee, $sequence),
                'annee'                    => $annee,
                'sequence'                 => $sequence,
                'reste'                    => 0,
                'external_bon_commande_id' => $bc->id,
                'user_id'                  => auth()->id(),
                'total'                    => 0,
            ]);

            $this->transfererLignes($bt, $pretes);

            $bc->update(['bon_transfert_id' => $bt->id]);

            return $bt;
        });
    }

    /**
     * Crée un « BT du reste » : les pièces restées en attente et maintenant
     * prêtes (réapprovisionnées). Numéro = BT d'origine + « -R1 », « -R2 »…
     *
     * @throws RuntimeException s'il n'y a pas de premier BT, ou aucune pièce prête
     */
    public function creerReste(ExternalBonCommande $bc): BonTransfert
    {
        return DB::transaction(function () use ($bc) {
            $bc = ExternalBonCommande::query()->lockForUpdate()->findOrFail($bc->id);

            if (! $bc->bon_transfert_id) {
                throw new RuntimeException("Créez d'abord le bon de transfert de {$bc->numero}.");
            }

            $bc->load('lignes.product', 'lignes.depot');

            $pretes = $bc->lignesPretesAEnvoyer();

            if ($pretes->isEmpty()) {
                throw new RuntimeException("Aucune pièce en attente de {$bc->numero} n'est encore prête à partir.");
            }

            $origine = BonTransfert::query()->lockForUpdate()->findOrFail($bc->bon_transfert_id);

            $reste = (int) BonTransfert::query()
                ->where('external_bon_commande_id', $bc->id)
                ->lockForUpdate()
                ->max('reste') + 1;

            $bt = BonTransfert::create([
                // toujours à partir de la base BT-AAAA-NNNN (même si le 1er BT a été supprimé)
                'numero'                   => sprintf('BT-%d-%04d', $origine->annee, $origine->sequence) . '-R' . $reste,
                'annee'                    => $origine->annee,
                'sequence'                 => $origine->sequence,
                'reste'                    => $reste,
                'parent_id'                => $origine->id,
                'external_bon_commande_id' => $bc->id,
                'user_id'                  => auth()->id(),
                'total'                    => 0,
            ]);

            $this->transfererLignes($bt, $pretes);

            return $bt;
        });
    }

    /** Transfère des pièces du BC dans le BT (sortie de stock) et les y rattache. */
    private function transfererLignes(BonTransfert $bt, $lignes): void
    {
        $total = 0.0;

        foreach ($lignes->sortBy('position') as $ligne) {
            $total += $this->transfererLigne($bt, $ligne);
            $ligne->update(['bon_transfert_id' => $bt->id]);
        }

        $bt->update(['total' => round($total, 2)]);
    }

    /**
     * Met les BT à jour après une modification du BC (devis modifié côté
     * Atelier, ou pièce/dépôt changé ici). V6 : chaque BT (le premier et les
     * BT du reste) est corrigé pour SES pièces ; les numéros ne changent pas
     * et le stock n'est corrigé que de la différence :
     * quantité en plus -> sortie, pièce retirée / quantité en moins -> retour.
     * Une pièce ajoutée au devis reste en attente (elle partira dans un BT du
     * reste). Un BT du reste qui n'a plus aucune pièce est supprimé.
     *
     * @return BonTransfert[] les BT modifiés (à renvoyer au garage)
     *
     * @throws RuntimeException si le stock manque pour une quantité en plus
     */
    public function mettreAJour(ExternalBonCommande $bc): array
    {
        return DB::transaction(function () use ($bc) {
            $bc = ExternalBonCommande::query()->lockForUpdate()->findOrFail($bc->id);

            if (! $bc->bon_transfert_id) {
                throw new RuntimeException("Aucun bon de transfert à mettre à jour pour {$bc->numero}.");
            }

            $bc->load('lignes.product', 'lignes.depot');
            $prixSaisis = $bc->estBtDirect(); // BT direct : prix saisi à l'écran

            $bts = BonTransfert::query()
                ->where('external_bon_commande_id', $bc->id)
                ->orderBy('reste')
                ->lockForUpdate()
                ->get()
                ->load('lignes');

            $cle = fn ($productId, $depotId) => $productId.':'.$depotId;
            $modifies = [];

            foreach ($bts as $bt) {
                $sesLignes = $bc->lignes->where('bon_transfert_id', $bt->id);

                foreach ($sesLignes as $ligne) {
                    if (! $ligne->product_id || ! $ligne->depot_id) {
                        $designation = $ligne->designation ?: $ligne->reference ?: "ligne #{$ligne->position}";

                        throw new RuntimeException("Pièce ou dépôt manquant pour : {$designation} (déjà dans {$bt->numero}).");
                    }
                }

                $avant = $bt->lignes
                    ->groupBy(fn ($l) => $cle($l->product_id, $l->depot_id))
                    ->map(fn ($l) => round($l->sum(fn ($x) => (float) $x->quantite), 2));

                $apres = $sesLignes
                    ->groupBy(fn ($l) => $cle($l->product_id, $l->depot_id))
                    ->map(fn ($l) => round($l->sum(fn ($x) => (float) $x->quantite_demandee), 2));

                $signatureAvant = $bt->lignes->map(fn ($l) => $l->position.'|'.$l->product_id.'|'.$l->depot_id.'|'.round((float) $l->quantite, 2).($prixSaisis ? '|'.round((float) $l->prix_unitaire, 2) : ''))->sort()->values()->all();
                $signatureApres = $sesLignes->map(fn ($l) => $l->position.'|'.$l->product_id.'|'.$l->depot_id.'|'.round((float) $l->quantite_demandee, 2).($prixSaisis ? '|'.round((float) $l->prix_unitaire, 2) : ''))->sort()->values()->all();

                if ($signatureAvant === $signatureApres) {
                    continue; // ce BT n'a pas bougé
                }

                foreach ($avant->keys()->merge($apres->keys())->unique() as $k) {
                    $ecart = round(($apres[$k] ?? 0) - ($avant[$k] ?? 0), 2);

                    if (abs($ecart) >= 0.01) {
                        [$productId, $depotId] = array_map('intval', explode(':', $k));
                        $this->ajusterStock($bt, $productId, $depotId, $ecart);
                    }
                }

                // Une pièce déjà transférée garde son prix du BT ; une autre
                // prend le prix de vente actuel.
                $prixDuBt = $bt->lignes->groupBy('product_id')->map(fn ($l) => (float) $l->first()->prix_unitaire);

                $bt->lignes()->delete();

                if ($sesLignes->isEmpty() && $bt->estReste()) {
                    $bt->delete(); // BT du reste vidé par la modification du devis
                    continue;
                }

                $total = 0.0;

                foreach ($sesLignes->sortBy('position') as $ligne) {
                    $quantite = round((float) $ligne->quantite_demandee, 2);
                    $prix = round($prixSaisis ? (float) $ligne->prix_unitaire : ($prixDuBt[$ligne->product_id] ?? (float) $ligne->product->sale_price), 2);
                    $totalLigne = round($quantite * $prix, 2);
                    $total += $totalLigne;

                    $bt->lignes()->create([
                        'position'           => $ligne->position,
                        'designation_garage' => $ligne->designation,
                        'product_id'         => $ligne->product_id,
                        'depot_id'           => $ligne->depot_id,
                        'quantite'           => $quantite,
                        'prix_unitaire'      => $prix,
                        'total'              => $totalLigne,
                    ]);
                }

                $bt->update(['total' => round($total, 2)]);
                $modifies[] = $bt->fresh('lignes');
            }

            return $modifies;
        });
    }

    /** Sort (écart > 0) ou remet (écart < 0) la différence dans le stock du dépôt. */
    private function ajusterStock(BonTransfert $bt, int $productId, int $depotId, float $ecart, ?string $libelleRetour = null): void
    {
        $product = Product::query()->lockForUpdate()->findOrFail($productId);
        $depotNom = Depot::query()->whereKey($depotId)->value('name') ?? "dépôt #{$depotId}";

        $stock = ProductDepotStock::query()
            ->where('product_id', $productId)
            ->where('depot_id', $depotId)
            ->lockForUpdate()
            ->first();

        if ($ecart > 0) {
            $disponible = $stock ? (float) $stock->quantity : 0.0;

            if ($disponible < $ecart) {
                throw new RuntimeException(
                    "Stock insuffisant pour {$product->reference} ({$product->designation}) dans {$depotNom} : "
                    ."disponible {$disponible}, demandé en plus {$ecart}."
                );
            }

            $stock->quantity = round($disponible - $ecart, 2);
        } else {
            $stock ??= new ProductDepotStock(['product_id' => $productId, 'depot_id' => $depotId, 'quantity' => 0]);
            $stock->quantity = round((float) $stock->quantity + abs($ecart), 2);
        }

        $stock->save();

        $this->synchroniserQuantiteProduit($product);

        StockMovement::create([
            'product_id' => $productId,
            'type'       => $ecart > 0 ? 'out' : 'in',
            'quantity'   => abs($ecart),
            'source'     => ($ecart > 0
                    ? 'Transfert garage (modification)'
                    : ($libelleRetour ? $libelleRetour.' '.$bt->numero : 'Retour transfert garage (modification)'))
                .' | Dépôt: '.$depotNom,
            'reference'  => $bt->numero,
            'user_id'    => auth()->id(),
        ]);
    }

    /**
     * Suppression manuelle d'un BT (administrateur), par exemple quand le
     * devis a été supprimé côté garage. Si les pièces sont revenues au
     * magasin, elles sont remises dans leur dépôt (mouvement « Suppression
     * BT … »). Le BC repasse « sans BT » : il peut être retraité ou supprimé.
     */
    public function supprimer(BonTransfert $bt, bool $remettreEnStock): void
    {
        DB::transaction(function () use ($bt, $remettreEnStock) {
            $bt = BonTransfert::query()->lockForUpdate()->findOrFail($bt->id);
            $bt->load('lignes');

            if ($remettreEnStock) {
                foreach ($bt->lignes as $ligne) {
                    if ($ligne->product_id && $ligne->depot_id && (float) $ligne->quantite > 0) {
                        $this->ajusterStock($bt, (int) $ligne->product_id, (int) $ligne->depot_id, -1 * (float) $ligne->quantite, 'Suppression BT');
                    }
                }
            }

            // Les pièces de ce BT repassent « en attente » sur le BC.
            ExternalBonCommandeLigne::query()
                ->where('bon_transfert_id', $bt->id)
                ->update(['bon_transfert_id' => null]);

            // Premier BT supprimé alors qu'il existe des BT du reste : le plus
            // ancien d'entre eux devient le BT principal du BC.
            $suivant = BonTransfert::query()
                ->where('external_bon_commande_id', $bt->external_bon_commande_id)
                ->whereKeyNot($bt->id)
                ->orderBy('reste')
                ->first();

            ExternalBonCommande::query()
                ->where('bon_transfert_id', $bt->id)
                ->update(['bon_transfert_id' => $suivant?->id]);

            $bt->delete();
        });
    }

    /** PDF du bon de transfert (téléchargement et envoi au garage). */
    public function pdf(BonTransfert $bt): \Barryvdh\DomPDF\PDF
    {
        $bt->loadMissing([
            'bonCommande',
            'user',
            'lignes.product.depotStocks.rayon',
            'lignes.product.depotStocks.location',
            'lignes.depot',
        ]);

        return Pdf::loadView('bons-transfert.pdf', ['bt' => $bt])->setPaper('a4', 'portrait');
    }

    public function nomFichierPdf(BonTransfert $bt): string
    {
        return preg_replace('/[^A-Za-z0-9\-_]/', '-', $bt->numero).'.pdf';
    }

    /**
     * Numérotation BT-AAAA-NNNN :
     * - tout premier BT : reprend le numéro de son BC (BC-2026-0055 -> BT-2026-0055) ;
     * - ensuite : dernier BT de l'année + 1 ;
     * - chaque nouvelle année repart à 0001.
     *
     * @return array{0:int,1:int} [année, séquence]
     */
    private function prochainNumero(ExternalBonCommande $bc): array
    {
        $annee = (int) now()->format('Y');

        $dernier = BonTransfert::query()
            ->where('annee', $annee)
            ->orderByDesc('sequence')
            ->lockForUpdate()
            ->first();

        if ($dernier) {
            return [$annee, $dernier->sequence + 1];
        }

        $aucunBtJusquIci = ! BonTransfert::query()->lockForUpdate()->exists();

        if ($aucunBtJusquIci && preg_match('/(\d+)\s*$/', (string) $bc->numero, $m) && (int) $m[1] > 0) {
            return [$annee, (int) $m[1]];
        }

        return [$annee, 1];
    }

    /** Crée la ligne du BT et sort la quantité du stock du dépôt. Renvoie le total de la ligne. */
    private function transfererLigne(BonTransfert $bt, ExternalBonCommandeLigne $ligne): float
    {
        $designation = $ligne->designation ?: $ligne->reference ?: "ligne #{$ligne->position}";

        if (! $ligne->product_id || ! $ligne->depot_id) {
            throw new RuntimeException("Pièce ou dépôt manquant pour : {$designation}.");
        }

        $product = Product::query()->lockForUpdate()->findOrFail($ligne->product_id);

        $stock = ProductDepotStock::query()
            ->where('product_id', $ligne->product_id)
            ->where('depot_id', $ligne->depot_id)
            ->lockForUpdate()
            ->first();

        $quantite = round((float) $ligne->quantite_demandee, 2);
        $disponible = $stock ? (float) $stock->quantity : 0.0;
        $depotNom = $ligne->depot->name ?? "dépôt #{$ligne->depot_id}";

        if ($disponible < $quantite) {
            throw new RuntimeException(
                "Stock insuffisant pour {$product->reference} ({$designation}) dans {$depotNom} : "
                ."disponible {$disponible}, demandé {$quantite}."
            );
        }

        $prix = round($bt->bonCommande?->estBtDirect() ? (float) $ligne->prix_unitaire : (float) $product->sale_price, 2);
        $totalLigne = round($quantite * $prix, 2);

        $bt->lignes()->create([
            'position'           => $ligne->position,
            'designation_garage' => $ligne->designation,
            'product_id'         => $product->id,
            'depot_id'           => $ligne->depot_id,
            'quantite'           => $quantite,
            'prix_unitaire'      => $prix,
            'total'              => $totalLigne,
        ]);

        $stock->quantity = max(0, round($disponible - $quantite, 2));
        $stock->save();

        $this->synchroniserQuantiteProduit($product);

        StockMovement::create([
            'product_id' => $product->id,
            'type'       => 'out',
            'quantity'   => $quantite,
            'source'     => (match (true) {
                    (bool) $bt->bonCommande?->estBus()          => 'Sortie bus '.$bt->bonCommande->vehicule_immatriculation,
                    (bool) $bt->bonCommande?->estConsommation() => 'Consommation atelier – '.$bt->bonCommande->equipement,
                    (bool) $bt->bonCommande?->estInterne()      => $bt->bonCommande->libelleMotif().' – '.$bt->bonCommande->beneficiaire,
                    default                                     => 'Transfert garage',
                }).' | Dépôt: '.$depotNom,
            'reference'  => $bt->numero,
            'user_id'    => auth()->id(),
        ]);

        return $totalLigne;
    }

    /** Même règle que pour une vente (SaleController::syncProductQuantityFromDepots). */
    private function synchroniserQuantiteProduit(Product $product): void
    {
        $product->quantity = max(0, round(
            (float) ProductDepotStock::query()->where('product_id', $product->id)->sum('quantity'),
            2
        ));

        $product->status = $product->quantity > 0 ? 'disponible' : 'vendu';

        if ($product->quantity <= 0) {
            $product->supply_status = 'rupture';
        } elseif ($product->supply_status === 'rupture') {
            $product->supply_status = null;
        }

        $product->save();
    }
}
