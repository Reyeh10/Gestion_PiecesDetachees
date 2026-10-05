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
     * @throws RuntimeException si une pièce n'a plus assez de stock dans son dépôt
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

            [$annee, $sequence] = $this->prochainNumero($bc);

            $bt = BonTransfert::create([
                'numero'                   => sprintf('BT-%d-%04d', $annee, $sequence),
                'annee'                    => $annee,
                'sequence'                 => $sequence,
                'external_bon_commande_id' => $bc->id,
                'user_id'                  => auth()->id(),
                'total'                    => 0,
            ]);

            $total = 0.0;

            foreach ($bc->lignes->sortBy('position') as $ligne) {
                $total += $this->transfererLigne($bt, $ligne);
            }

            $bt->update(['total' => round($total, 2)]);

            $bc->update(['bon_transfert_id' => $bt->id]);

            return $bt;
        });
    }

    /**
     * Met le BT à jour après une modification du BC (devis modifié côté
     * Atelier, ou pièce/dépôt changé ici). Le numéro ne change pas et le
     * stock n'est corrigé que de la différence : quantité en plus -> sortie,
     * pièce retirée ou quantité en moins -> retour dans le stock du dépôt.
     *
     * @throws RuntimeException si le stock manque pour une quantité en plus
     */
    public function mettreAJour(ExternalBonCommande $bc): BonTransfert
    {
        return DB::transaction(function () use ($bc) {
            $bc = ExternalBonCommande::query()->lockForUpdate()->findOrFail($bc->id);

            if (! $bc->bon_transfert_id) {
                throw new RuntimeException("Aucun bon de transfert à mettre à jour pour {$bc->numero}.");
            }

            $bt = BonTransfert::query()->lockForUpdate()->findOrFail($bc->bon_transfert_id);

            $bc->load('lignes.product', 'lignes.depot');
            $bt->load('lignes');

            foreach ($bc->lignes as $ligne) {
                if (! $ligne->product_id || ! $ligne->depot_id) {
                    $designation = $ligne->designation ?: $ligne->reference ?: "ligne #{$ligne->position}";

                    throw new RuntimeException("Pièce ou dépôt manquant pour : {$designation}.");
                }
            }

            $cle = fn ($productId, $depotId) => $productId.':'.$depotId;

            $avant = $bt->lignes
                ->groupBy(fn ($l) => $cle($l->product_id, $l->depot_id))
                ->map(fn ($lignes) => round($lignes->sum(fn ($l) => (float) $l->quantite), 2));

            $apres = $bc->lignes
                ->groupBy(fn ($l) => $cle($l->product_id, $l->depot_id))
                ->map(fn ($lignes) => round($lignes->sum(fn ($l) => (float) $l->quantite_demandee), 2));

            foreach ($avant->keys()->merge($apres->keys())->unique() as $k) {
                $ecart = round(($apres[$k] ?? 0) - ($avant[$k] ?? 0), 2);

                if (abs($ecart) >= 0.01) {
                    [$productId, $depotId] = array_map('intval', explode(':', $k));
                    $this->ajusterStock($bt, $productId, $depotId, $ecart);
                }
            }

            // Une pièce déjà transférée garde son prix du BT ; une nouvelle
            // pièce prend le prix de vente actuel.
            $prixDuBt = $bt->lignes->groupBy('product_id')->map(fn ($l) => (float) $l->first()->prix_unitaire);

            $bt->lignes()->delete();

            $total = 0.0;

            foreach ($bc->lignes->sortBy('position') as $ligne) {
                $quantite = round((float) $ligne->quantite_demandee, 2);
                $prix = round($prixDuBt[$ligne->product_id] ?? (float) $ligne->product->sale_price, 2);
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

            // Enregistre explicitement le nouveau total du BT.
            $bt->update([
                'total' => round($total, 2),
            ]);

            return $bt->fresh('lignes');
        });
    }

    /** Sort (écart > 0) ou remet (écart < 0) la différence dans le stock du dépôt. */
    private function ajusterStock(BonTransfert $bt, int $productId, int $depotId, float $ecart): void
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
            'source'     => ($ecart > 0 ? 'Transfert garage (modification)' : 'Retour transfert garage (modification)')
                .' | Dépôt: '.$depotNom,
            'reference'  => $bt->numero,
            'user_id'    => auth()->id(),
        ]);
    }

    /** PDF du bon de transfert (téléchargement et envoi au garage). */
    public function pdf(BonTransfert $bt): \Barryvdh\DomPDF\PDF
    {
        $bt->loadMissing(['bonCommande', 'user', 'lignes.product', 'lignes.depot']);

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

        $prix = round((float) $product->sale_price, 2);
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
            'source'     => 'Transfert garage | Dépôt: '.$depotNom,
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
