<?php

namespace App\Services;

use App\Models\ExternalBonCommandeLigne;
use App\Models\Product;
use App\Models\ProductDepotStock;
use App\Models\StockMovement;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\VehiclePartRequest;
use App\Models\VehiclePartRequestHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Réception d'une pièce spéciale : une seule logique, que la réception soit
 * faite depuis la pièce ou depuis son bon de commande (V7).
 *
 * En un enregistrement :
 * - la fiche produit est retrouvée (ou créée), son stock et son CUMP mis à jour ;
 * - le stock du dépôt de réception augmente, un mouvement d'entrée est noté ;
 * - la pièce passe à « Réception partielle » ou « Reçue » (historique) ;
 * - la ligne de son bon de commande actif reçoit la quantité (et le prix réel),
 *   le BC passe à « Réception partielle » ou « Reçu » ;
 * - pièce demandée par l'Atelier : la ligne du BC garage redevient disponible
 *   et l'Atelier est prévenu.
 */
class ReceptionPieceService
{
    /**
     * @param  float|null  $prixReel   prix d'achat de la facture fournisseur (null = prix prévu)
     * @param  bool        $surplus    autoriser plus que la quantité restante (admin seulement)
     * @return array{statut: string, reste: float}
     */
    public function recevoir(
        VehiclePartRequest $piece,
        float $quantite,
        int $depotId,
        ?float $prixReel = null,
        ?string $commentaire = null,
        bool $surplus = false
    ): array {
        return DB::transaction(function () use ($piece, $quantite, $depotId, $prixReel, $commentaire, $surplus) {
            $piece = VehiclePartRequest::query()->lockForUpdate()->findOrFail($piece->id);
            $nom = $piece->part_name ?: ($piece->reference ?: '#' . $piece->id);

            if (! in_array($piece->status, [VehiclePartRequest::STATUS_ORDERED, VehiclePartRequest::STATUS_PARTIAL_RECEIVED], true)) {
                throw ValidationException::withMessages([
                    'quantite' => "« {$nom} » ne peut pas être réceptionnée dans son statut actuel ({$piece->status_label}).",
                ]);
            }

            if ($quantite <= 0) {
                throw ValidationException::withMessages(['quantite' => "« {$nom} » : la quantité reçue doit être supérieure à zéro."]);
            }

            $commandee = (float) $piece->quantity;
            $dejaRecue = (float) ($piece->received_quantity ?? 0);
            $reste = max(0, $commandee - $dejaRecue);

            if ($quantite > $reste + 0.0001 && ! $surplus) {
                throw ValidationException::withMessages([
                    'quantite' => "« {$nom} » : vous ne pouvez recevoir que "
                        . number_format($reste, 2, ',', ' ') . ' ' . $piece->unit
                        . ' au maximum (seul l\'administrateur peut recevoir plus que commandé).',
                ]);
            }

            if ($prixReel !== null) {
                $piece->purchase_price = round($prixReel, 2);
            }

            $product = $this->produit($piece);
            $this->entrerEnStock($product, $piece, $quantite, $depotId);

            $nouvelleRecue = $dejaRecue + $quantite;

            // Surplus accepté (admin) : la quantité demandée suit ce qui est arrivé
            if ($nouvelleRecue > $commandee) {
                $piece->quantity = $nouvelleRecue;
                $commandee = $nouvelleRecue;
            }

            $ancienStatut = $piece->status;
            $nouveauStatut = $nouvelleRecue >= $commandee
                ? VehiclePartRequest::STATUS_RECEIVED
                : VehiclePartRequest::STATUS_PARTIAL_RECEIVED;

            $piece->product_id = $product->id;
            $piece->received_quantity = $nouvelleRecue;
            $piece->status = $nouveauStatut;
            $piece->received_at ??= now();
            $piece->save();

            $bc = $this->mettreAJourBonCommande($piece, $quantite, $prixReel);
            $this->prevenirAtelier($piece, $product, $depotId);

            VehiclePartRequestHistory::create([
                'vehicle_part_request_id' => $piece->id,
                'old_status'              => $ancienStatut,
                'new_status'              => $nouveauStatut,
                'old_received_quantity'   => $dejaRecue,
                'new_received_quantity'   => $nouvelleRecue,
                'comment'                 => $commentaire ?: 'Nouvelle réception'
                    . ($bc ? ' (BC ' . $bc->order_number . ')' : '') . ' : '
                    . number_format($quantite, 2, ',', ' ') . ' ' . $piece->unit
                    . '. Total reçu : ' . number_format($nouvelleRecue, 2, ',', ' ')
                    . ' / ' . number_format($commandee, 2, ',', ' ') . '.',
                'changed_by'              => Auth::id(),
                'changed_at'              => now(),
            ]);

            return ['statut' => $nouveauStatut, 'reste' => max(0, $commandee - $nouvelleRecue)];
        }, 3);
    }

    /** Fiche produit de la pièce : liée, même référence, ou créée (stock 0). */
    private function produit(VehiclePartRequest $piece): Product
    {
        $product = $piece->product_id ? Product::query()->lockForUpdate()->find($piece->product_id) : null;

        if (! $product && filled($piece->reference)) {
            $product = Product::query()->where('reference', $piece->reference)->lockForUpdate()->first();
        }

        if ($product) {
            return $product;
        }

        if (blank($piece->reference)) {
            throw ValidationException::withMessages([
                'quantite' => 'Impossible de créer automatiquement la pièce « ' . $piece->part_name . ' » : la référence est obligatoire.',
            ]);
        }

        $product = new Product();
        $product->reference = trim($piece->reference);
        $product->designation = $piece->part_name ?: $piece->reference;
        $product->quantity = 0;
        $product->initial_quantity = 0;
        $product->received_quantity = 0;

        if (filled($piece->unit)) {
            $product->unit_label = $piece->unit;
        }

        $product->purchase_price = round((float) ($piece->purchase_price ?? 0), 4);
        $product->cost_price = round($product->purchase_price * (float) ($product->coef_purchase ?? 0), 4);
        $product->sale_price = round($product->cost_price * (float) ($product->coef_sale ?? 0), 2);
        $product->status = 'disponible';
        $product->save();

        return $product;
    }

    /** Stock global + CUMP (prix d'achat moyen pondéré), stock du dépôt, mouvement d'entrée. */
    private function entrerEnStock(Product $product, VehiclePartRequest $piece, float $quantite, int $depotId): void
    {
        $stockAvant = (float) ($product->quantity ?? 0);
        $cumpAvant = (float) ($product->purchase_price ?? 0);
        $prixEntree = (float) ($piece->purchase_price ?? 0);
        $stockApres = $stockAvant + $quantite;

        $cump = $stockAvant > 0
            ? (($stockAvant * $cumpAvant) + ($quantite * $prixEntree)) / $stockApres
            : $prixEntree;
        $cump = round($cump, 4);
        $coutRevient = round($cump * (float) ($product->coef_purchase ?? 0), 4);

        $product->initial_quantity = (float) ($product->initial_quantity ?? 0) + $quantite;
        $product->received_quantity = (float) ($product->received_quantity ?? 0) + $quantite;
        $product->quantity = $stockApres;
        $product->purchase_price = $cump;
        $product->cost_price = $coutRevient;
        $product->sale_price = round($coutRevient * (float) ($product->coef_sale ?? 0), 2);
        $product->status = 'disponible';
        $product->save();

        $stockDepot = ProductDepotStock::query()
            ->where('product_id', $product->id)
            ->where('depot_id', $depotId)
            ->lockForUpdate()
            ->first()
            ?? new ProductDepotStock();

        $stockDepot->product_id = $product->id;
        $stockDepot->depot_id = $depotId;
        $stockDepot->quantity = (float) ($stockDepot->quantity ?? 0) + $quantite;
        $stockDepot->save();

        $mouvement = new StockMovement();
        $mouvement->product_id = $product->id;
        $mouvement->type = 'in';
        $mouvement->quantity = $quantite;
        $mouvement->source = 'Réception commande véhicule';
        $mouvement->reference = $piece->order_reference ?? $piece->reference;
        $mouvement->user_id = Auth::id();
        $mouvement->save();
    }

    /** Ligne du BC actif : quantité reçue (+ prix réel), puis statut du BC. */
    private function mettreAJourBonCommande(VehiclePartRequest $piece, float $quantite, ?float $prixReel): ?SupplierOrder
    {
        $ligne = SupplierOrderItem::query()
            ->where('vehicle_part_request_id', $piece->id)
            ->whereHas('supplierOrder', fn ($q) => $q->whereNotIn('status', SupplierOrder::STATUTS_INACTIFS))
            ->latest('id')
            ->lockForUpdate()
            ->first();

        if (! $ligne) {
            return null;
        }

        $ligne->quantity_received = (float) $ligne->quantity_received + $quantite;

        if ($prixReel !== null && abs($prixReel - (float) $ligne->unit_price) > 0.00001) {
            $ligne->unit_price = round($prixReel, 4);
            $ligne->line_total = round((float) $ligne->quantity_ordered * $prixReel, 2);
        }

        $ligne->save();

        $bc = SupplierOrder::query()->lockForUpdate()->findOrFail($ligne->supplier_order_id);
        $bc->recalculateTotals();

        // Le statut ne bouge que pour un BC envoyé (ancien circuit : BC encore en
        // signature, la quantité est notée sans sauter les signatures)
        if (in_array($bc->status, SupplierOrder::STATUTS_RECEPTION, true)) {
            $lignes = $bc->items()->get();
            $toutRecu = $lignes->every(fn ($l) => (float) $l->quantity_received >= (float) $l->quantity_ordered);
            $bc->update(['status' => $toutRecu ? SupplierOrder::STATUS_RECEIVED : SupplierOrder::STATUS_PARTIAL_RECEIVED]);
        }

        return $bc;
    }

    /** Pièce commandée pour un BC du garage : la ligne redevient disponible, l'Atelier est prévenu. */
    private function prevenirAtelier(VehiclePartRequest $piece, Product $product, int $depotId): void
    {
        if (! $piece->external_bon_commande_ligne_id) {
            return;
        }

        $ligneGarage = ExternalBonCommandeLigne::query()->lockForUpdate()->find($piece->external_bon_commande_ligne_id);

        if (! $ligneGarage || $ligneGarage->bon_transfert_id) {
            return;
        }

        $enStock = (float) ProductDepotStock::query()
            ->where('product_id', $product->id)
            ->where('depot_id', $depotId)
            ->value('quantity');

        $ligneGarage->update([
            'product_id'          => $product->id,
            'depot_id'            => $depotId,
            'quantite_disponible' => $enStock,
            'disponible'          => $enStock >= (float) $ligneGarage->quantite_demandee,
            'prix_unitaire'       => $product->sale_price,
        ]);

        DB::afterCommit(function () use ($ligneGarage) {
            rescue(fn () => app(AppAtelierApiService::class)->envoyerDisponibilite($ligneGarage->fresh()), null, false);
        });
    }
}
