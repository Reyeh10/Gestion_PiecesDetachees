<?php

namespace App\Services;

use App\Models\Demande;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\SupplierOrderSignature;
use App\Models\User;
use App\Models\VehiclePartRequest;
use App\Models\VehiclePartRequestHistory;
use App\Notifications\NewSupplierOrderNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * V7 — Circuit pièce spéciale → bon de commande → réception.
 *
 *  1. Demande : plusieurs pièces enregistrées ensemble (« En recherche »).
 *  2. Fournisseur saisi sur une pièce → elle passe « À commander » et le bon
 *     de commande de ce fournisseur se crée tout seul pour la demande (ou
 *     reçoit la pièce s'il n'est pas encore signé). Un BC par fournisseur.
 *  3. Signatures : chef magasinier (validé) puis admin (approuvé).
 *  4. Envoyé → les pièces passent « Commandée ».
 *  5. Réception depuis le BC, ligne par ligne (quantité + prix réel).
 *  6. Solder : le reste ne viendra pas → à re-commander ailleurs ou annulé.
 */
class CircuitAchatService
{
    /** Statuts de BC qui reçoivent encore des pièces (avant la signature du chef). */
    public const STATUTS_OUVERTS = [SupplierOrder::STATUS_DRAFT];

    public function __construct(
        private ReceptionPieceService $reception,
        private SignatureBonCommandeService $signatures,
    ) {
    }

    /* =====================================================================
     | 1. DEMANDE
     ===================================================================== */

    /**
     * @param  array  $entete  source, vehicle_id, depot_id, notes, external_bon_commande_id
     * @param  array  $lignes  product_id?, reference?, part_name?, quantity, unit?,
     *                         supplier_id?, prix?, description?, notes?, external_bon_commande_ligne_id?
     * @return array{demande: Demande, bons: Collection}
     */
    public function creerDemande(array $entete, array $lignes): array
    {
        return DB::transaction(function () use ($entete, $lignes) {
            $demande = Demande::create([
                'numero'                   => Demande::prochainNumero(),
                'source'                   => $entete['source'] ?? Demande::SOURCE_SAISIE,
                'vehicle_id'               => $entete['vehicle_id'] ?? null,
                'depot_id'                 => $entete['depot_id'] ?? null,
                'external_bon_commande_id' => $entete['external_bon_commande_id'] ?? null,
                'created_by'               => auth()->id(),
                'notes'                    => $entete['notes'] ?? null,
            ]);

            foreach ($lignes as $ligne) {
                $this->creerPiece($demande, $ligne);
            }

            return ['demande' => $demande, 'bons' => $this->genererBonsCommande($demande)];
        }, 3);
    }

    /** « Ajouter une pièce » : nouvelles lignes dans une demande existante. */
    public function ajouterPieces(Demande $demande, array $lignes): Collection
    {
        return DB::transaction(function () use ($demande, $lignes) {
            foreach ($lignes as $ligne) {
                $this->creerPiece($demande, $ligne);
            }

            return $this->genererBonsCommande($demande);
        }, 3);
    }

    public function creerPiece(Demande $demande, array $l): VehiclePartRequest
    {
        $product = ! empty($l['product_id']) ? Product::find($l['product_id']) : null;
        $prix = isset($l['prix']) && $l['prix'] !== '' && $l['prix'] !== null ? round((float) $l['prix'], 2) : null;

        $piece = VehiclePartRequest::create([
            'demande_id'                     => $demande->id,
            // même convention que les pièces spéciales : véhicule OU dépôt
            'vehicle_id'                     => $demande->vehicle_id,
            'depot_id'                       => $demande->vehicle_id ? null : $demande->depot_id,
            'product_id'                     => $product?->id,
            'supplier_id'                    => $l['supplier_id'] ?? null,
            'external_bon_commande_ligne_id' => $l['external_bon_commande_ligne_id'] ?? null,
            'created_by'                     => auth()->id(),
            'reference'                      => trim((string) ($l['reference'] ?? '')) ?: $product?->reference,
            'part_name'                      => trim((string) ($l['part_name'] ?? '')) ?: ($product?->designation ?? 'Pièce'),
            'description'                    => $l['description'] ?? null,
            'quantity'                       => round((float) $l['quantity'], 2),
            'received_quantity'              => 0,
            'unit'                           => ($l['unit'] ?? null) ?: ($product?->unit_label ?: 'Pièce'),
            'status'                         => VehiclePartRequest::STATUS_SEARCHING,
            'estimated_price'                => $prix,
            'purchase_price'                 => $prix,
            'requested_at'                   => now(),
            'search_started_at'              => now(),
            'notes'                          => $l['notes'] ?? null,
        ]);

        $this->historique($piece, null, VehiclePartRequest::STATUS_SEARCHING, 'Pièce enregistrée dans la demande ' . $demande->numero . '.');

        return $piece;
    }

    /** Pièce sans demande (ancienne saisie) : on lui crée sa demande. */
    public function assurerDemande(VehiclePartRequest $piece): Demande
    {
        if ($piece->demande_id) {
            return $piece->demande;
        }

        $demande = Demande::create([
            'numero'     => Demande::prochainNumero(),
            'source'     => Demande::SOURCE_SAISIE,
            'vehicle_id' => $piece->vehicle_id,
            'depot_id'   => $piece->depot_id,
            'created_by' => $piece->created_by,
        ]);

        $piece->update(['demande_id' => $demande->id]);

        return $demande;
    }

    /* =====================================================================
     | 2. FOURNISSEUR → BON DE COMMANDE AUTOMATIQUE
     ===================================================================== */

    /** Fournisseur (et prix indicatif) saisi sur une pièce : son BC se crée ou se complète. */
    public function definirFournisseur(VehiclePartRequest $piece, ?int $supplierId, ?float $prix = null): Collection
    {
        return DB::transaction(function () use ($piece, $supplierId, $prix) {
            $piece = VehiclePartRequest::query()->lockForUpdate()->findOrFail($piece->id);

            if (! in_array($piece->status, [VehiclePartRequest::STATUS_SEARCHING, VehiclePartRequest::STATUS_TO_ORDER, VehiclePartRequest::STATUS_NOT_FOUND], true)) {
                throw ValidationException::withMessages(['supplier_id' => 'Le fournisseur ne peut plus changer : la pièce est déjà commandée.']);
            }

            // Changement de fournisseur : la pièce quitte son BC pas encore signé
            if ((int) $piece->supplier_id !== (int) $supplierId) {
                $this->retirerDuBonCommande($piece, 'Fournisseur changé.');
            }

            $piece->supplier_id = $supplierId;

            if ($prix !== null) {
                $piece->estimated_price = round($prix, 2);
                $piece->purchase_price = round($prix, 2);
            }

            if ($piece->status === VehiclePartRequest::STATUS_NOT_FOUND && $supplierId) {
                $this->changerStatut($piece, VehiclePartRequest::STATUS_SEARCHING, 'Fournisseur trouvé.');
            }

            $piece->save();

            // Prix modifié alors que la pièce est déjà sur un BC pas encore signé
            $ligne = $this->ligneActive($piece);
            if ($ligne && $prix !== null && in_array($ligne->supplierOrder->status, self::STATUTS_OUVERTS, true)) {
                $ligne->update(['unit_price' => round($prix, 4), 'line_total' => round((float) $ligne->quantity_ordered * $prix, 2)]);
                $ligne->supplierOrder->recalculateTotals();
            }

            return $this->genererBonsCommande($this->assurerDemande($piece));
        }, 3);
    }

    /**
     * Crée ou complète les BC de la demande : chaque pièce qui a un fournisseur
     * et n'est sur aucun BC actif va sur le BC ouvert (pas encore signé par le
     * chef) de ce fournisseur, sinon sur un nouveau BC. Les BC déjà signés ne
     * changent pas : la pièce va alors sur un BC complémentaire.
     *
     * @return Collection<SupplierOrder> BC créés (nouveaux)
     */
    public function genererBonsCommande(Demande $demande): Collection
    {
        $pieces = $demande->pieces()
            ->whereNotNull('supplier_id')
            ->whereIn('status', [VehiclePartRequest::STATUS_SEARCHING, VehiclePartRequest::STATUS_TO_ORDER])
            ->whereDoesntHave('supplierOrderItems', fn ($q) => $q->whereHas(
                'supplierOrder',
                fn ($o) => $o->whereNotIn('status', SupplierOrder::STATUTS_INACTIFS)
            ))
            ->with('product')
            ->lockForUpdate()
            ->get();

        $nouveaux = collect();

        foreach ($pieces->groupBy('supplier_id') as $supplierId => $lot) {
            $bc = $demande->bonsCommande()
                ->where('supplier_id', $supplierId)
                ->whereIn('status', self::STATUTS_OUVERTS)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if (! $bc) {
                $bc = $this->nouveauBonCommande($demande, Supplier::findOrFail($supplierId), $lot);

                if (! $bc) {
                    continue; // pas de dépôt de réception : la demande le signale
                }

                $nouveaux->push($bc);
            }

            foreach ($lot as $piece) {
                $this->ajouterLigne($bc, $piece);

                if ($piece->status === VehiclePartRequest::STATUS_SEARCHING) {
                    $this->changerStatut($piece, VehiclePartRequest::STATUS_TO_ORDER, 'Fournisseur trouvé : ajoutée au bon de commande ' . $bc->order_number . '.');
                }

                $piece->order_reference = $bc->order_number;
                $piece->save();
            }

            $bc->recalculateTotals();
        }

        foreach ($nouveaux as $bc) {
            DB::afterCommit(fn () => $this->notifier(
                $bc,
                ['chef_magasinier', 'admin'],
                'Bon de commande à valider',
                'Le bon de commande ' . $bc->order_number . ' (demande ' . $demande->numero . ') attend la validation du chef magasinier.'
            ));
        }

        return $nouveaux;
    }

    private function nouveauBonCommande(Demande $demande, Supplier $supplier, Collection $lot): ?SupplierOrder
    {
        $depotId = $demande->depot_id ?? $lot->pluck('depot_id')->filter()->first();

        if (! $depotId) {
            return null;
        }

        return SupplierOrder::create([
            'order_number' => SupplierOrder::generateOrderNumber(),
            'demande_id'   => $demande->id,
            'supplier_id'  => $supplier->id,
            'depot_id'     => $depotId,
            'created_by'   => auth()->id() ?? $demande->created_by,
            'order_date'   => today(),
            'status'       => SupplierOrder::STATUS_DRAFT,
            'currency'     => strtoupper($supplier->currency ?: 'DJF'),
            'notes'        => 'Demande ' . $demande->numero . ($demande->notes ? ' — ' . $demande->notes : ''),
        ]);
    }

    private function ajouterLigne(SupplierOrder $bc, VehiclePartRequest $piece): SupplierOrderItem
    {
        $quantite = max(0, round((float) $piece->quantity - (float) ($piece->received_quantity ?? 0), 2));
        $prix = round((float) ($piece->purchase_price ?? $piece->estimated_price ?? 0), 4);

        return SupplierOrderItem::create([
            'supplier_order_id'       => $bc->id,
            'vehicle_part_request_id' => $piece->id,
            'product_id'              => $piece->product_id,
            'reference'               => $piece->reference ?? $piece->product?->reference,
            'description'             => $piece->part_name ?? $piece->product?->designation ?? 'Pièce',
            'unit'                    => $piece->unit ?? $piece->product?->unit_label,
            'quantity_ordered'        => $quantite,
            'quantity_received'       => 0,
            'unit_price'              => $prix,
            'line_total'              => round($quantite * $prix, 2),
            'notes'                   => $piece->notes,
        ]);
    }

    /**
     * La pièce quitte son BC pas encore envoyé (fournisseur changé, pièce annulée).
     * BC déjà signé par le chef : il revient « À valider » et les signatures
     * repartent de zéro. BC vide : annulé.
     */
    public function retirerDuBonCommande(VehiclePartRequest $piece, string $motif): void
    {
        $ligne = $this->ligneActive($piece);

        if (! $ligne) {
            return;
        }

        $bc = SupplierOrder::query()->lockForUpdate()->findOrFail($ligne->supplier_order_id);

        if (! in_array($bc->status, [SupplierOrder::STATUS_DRAFT, SupplierOrder::STATUS_VALIDATED, SupplierOrder::STATUS_REJECTED], true)) {
            throw ValidationException::withMessages([
                'supplier_id' => 'La pièce est sur le bon de commande ' . $bc->order_number . ' déjà approuvé : annulez-le ou soldez-le d\'abord.',
            ]);
        }

        $ligne->delete();

        if ($piece->order_reference === $bc->order_number) {
            $piece->order_reference = null;
        }

        if ($piece->status === VehiclePartRequest::STATUS_TO_ORDER) {
            $this->changerStatut($piece, VehiclePartRequest::STATUS_SEARCHING, $motif . ' Retirée du bon de commande ' . $bc->order_number . '.');
        }

        $piece->save();

        if (! $bc->items()->exists()) {
            $bc->update(['status' => SupplierOrder::STATUS_CANCELLED]);

            return;
        }

        if ($bc->status === SupplierOrder::STATUS_VALIDATED) {
            $this->signatures->effacer($bc, SupplierOrderSignature::TYPE_VALIDATED);
            $bc->update(['status' => SupplierOrder::STATUS_DRAFT, 'validated_by' => null, 'validated_at' => null]);
        }

        $bc->recalculateTotals();
    }

    /* =====================================================================
     | 3. ENVOI AU FOURNISSEUR
     ===================================================================== */

    public function envoyer(SupplierOrder $bc): void
    {
        DB::transaction(function () use ($bc) {
            $bc = SupplierOrder::query()->lockForUpdate()->findOrFail($bc->id);

            if ($bc->status !== SupplierOrder::STATUS_APPROVED) {
                throw ValidationException::withMessages(['status' => 'Seul un bon de commande approuvé (signé par l\'admin) peut être envoyé au fournisseur.']);
            }

            $bc->update(['status' => SupplierOrder::STATUS_SENT, 'sent_at' => now()]);

            foreach ($bc->items()->with('vehiclePartRequest')->get() as $ligne) {
                $piece = $ligne->vehiclePartRequest;

                if ($piece && in_array($piece->status, [VehiclePartRequest::STATUS_TO_ORDER, VehiclePartRequest::STATUS_SEARCHING], true)) {
                    $piece->ordered_at = now();
                    $piece->order_reference = $bc->order_number;
                    $this->changerStatut($piece, VehiclePartRequest::STATUS_ORDERED, 'Bon de commande ' . $bc->order_number . ' envoyé au fournisseur.');
                    $piece->save();
                }
            }
        }, 3);
    }

    /* =====================================================================
     | 4. RÉCEPTION DEPUIS LE BON DE COMMANDE
     ===================================================================== */

    /**
     * @param  array  $lignes  [item_id => ['quantite' => x, 'prix' => y]] (quantité vide = rien reçu)
     * @return int nombre de lignes réceptionnées
     */
    public function receptionner(SupplierOrder $bc, array $lignes, int $depotId, bool $surplus = false): int
    {
        return DB::transaction(function () use ($bc, $lignes, $depotId, $surplus) {
            $bc = SupplierOrder::query()->lockForUpdate()->findOrFail($bc->id);

            if (! in_array($bc->status, SupplierOrder::STATUTS_RECEPTION, true)) {
                throw ValidationException::withMessages(['status' => 'On ne réceptionne qu\'un bon de commande envoyé au fournisseur (statut actuel : ' . $bc->libelleStatut() . ').']);
            }

            $items = $bc->items()->with('vehiclePartRequest')->get()->keyBy('id');
            $faites = 0;

            foreach ($lignes as $itemId => $saisie) {
                $quantite = (float) str_replace(',', '.', (string) ($saisie['quantite'] ?? ''));

                if ($quantite <= 0) {
                    continue;
                }

                $item = $items->get((int) $itemId);

                if (! $item || ! $item->vehiclePartRequest) {
                    throw ValidationException::withMessages(['lignes' => 'Ligne #' . $itemId . ' introuvable dans ' . $bc->order_number . '.']);
                }

                $prix = isset($saisie['prix']) && $saisie['prix'] !== '' && $saisie['prix'] !== null
                    ? (float) str_replace(',', '.', (string) $saisie['prix'])
                    : null;

                $this->reception->recevoir($item->vehiclePartRequest, $quantite, $depotId, $prix, null, $surplus);
                $faites++;
            }

            if ($faites === 0) {
                throw ValidationException::withMessages(['lignes' => 'Indiquez la quantité reçue d\'au moins une ligne.']);
            }

            return $faites;
        }, 3);
    }

    /* =====================================================================
     | 5. SOLDER (le reste ne sera pas livré)
     ===================================================================== */

    /**
     * @param  array  $choix  [item_id => 'recommander' | 'annuler'] pour les lignes pas entièrement reçues
     */
    public function solder(SupplierOrder $bc, array $choix): void
    {
        DB::transaction(function () use ($bc, $choix) {
            $bc = SupplierOrder::query()->lockForUpdate()->findOrFail($bc->id);

            if (! in_array($bc->status, SupplierOrder::STATUTS_RECEPTION, true)) {
                throw ValidationException::withMessages(['status' => 'Seul un bon de commande envoyé (ou reçu en partie) peut être soldé.']);
            }

            foreach ($bc->items()->with('vehiclePartRequest.demande')->get() as $ligne) {
                $piece = $ligne->vehiclePartRequest;
                $reste = max(0, (float) $ligne->quantity_ordered - (float) $ligne->quantity_received);

                if (! $piece || $reste <= 0 || in_array($piece->status, [VehiclePartRequest::STATUS_RECEIVED, VehiclePartRequest::STATUS_CANCELLED], true)) {
                    continue;
                }

                $recommander = ($choix[$ligne->id] ?? 'annuler') === 'recommander';
                $recue = (float) ($piece->received_quantity ?? 0);
                $libelleReste = number_format($reste, 2, ',', ' ') . ' ' . $piece->unit;

                if ($recue > 0) {
                    // La partie reçue reste sur cette pièce, qui est terminée
                    $piece->quantity = $recue;
                    $this->changerStatut($piece, VehiclePartRequest::STATUS_RECEIVED, 'BC ' . $bc->order_number . ' soldé : ' . $libelleReste . ' non livré(s)'
                        . ($recommander ? ', à commander ailleurs.' : ', annulé(s).'));
                    $piece->save();

                    if ($recommander) {
                        $this->reCommander($piece, $reste, $bc);
                    }

                    continue;
                }

                if ($recommander) {
                    // Rien reçu : la même pièce repart en recherche, sans fournisseur
                    $piece->supplier_id = null;
                    $piece->order_reference = null;
                    $this->changerStatut($piece, VehiclePartRequest::STATUS_SEARCHING, 'BC ' . $bc->order_number . ' soldé sans livraison : à commander chez un autre fournisseur.');
                } else {
                    $piece->cancelled_at = now();
                    $this->changerStatut($piece, VehiclePartRequest::STATUS_CANCELLED, 'BC ' . $bc->order_number . ' soldé sans livraison : pièce annulée.');
                }

                $piece->save();
            }

            $bc->update(['status' => SupplierOrder::STATUS_CLOSED, 'closed_by' => auth()->id(), 'closed_at' => now()]);
        }, 3);
    }

    /** Le reste non livré devient une nouvelle pièce de la même demande, « En recherche ». */
    private function reCommander(VehiclePartRequest $piece, float $reste, SupplierOrder $bc): VehiclePartRequest
    {
        $demande = $this->assurerDemande($piece);

        $nouvelle = $piece->replicate(['received_quantity', 'received_at', 'ordered_at', 'order_reference', 'supplier_id', 'found_at', 'cancelled_at']);
        $nouvelle->demande_id = $demande->id;
        $nouvelle->quantity = $reste;
        $nouvelle->received_quantity = 0;
        $nouvelle->status = VehiclePartRequest::STATUS_SEARCHING;
        $nouvelle->requested_at = now();
        $nouvelle->search_started_at = now();
        $nouvelle->created_by = auth()->id() ?? $piece->created_by;
        $nouvelle->save();

        $this->historique($nouvelle, null, VehiclePartRequest::STATUS_SEARCHING, 'Reste non livré du BC ' . $bc->order_number . ' : à commander chez un autre fournisseur.');

        return $nouvelle;
    }

    /* =====================================================================
     | OUTILS
     ===================================================================== */

    public function ligneActive(VehiclePartRequest $piece): ?SupplierOrderItem
    {
        return SupplierOrderItem::query()
            ->with('supplierOrder')
            ->where('vehicle_part_request_id', $piece->id)
            ->whereHas('supplierOrder', fn ($q) => $q->whereNotIn('status', SupplierOrder::STATUTS_INACTIFS))
            ->latest('id')
            ->first();
    }

    /** Change le statut (sans enregistrer la pièce) et note l'historique. */
    public function changerStatut(VehiclePartRequest $piece, string $statut, string $commentaire): void
    {
        $ancien = $piece->status;

        if ($ancien === $statut) {
            return;
        }

        $piece->status = $statut;

        if ($statut === VehiclePartRequest::STATUS_TO_ORDER) {
            $piece->found_at ??= now();
        }

        $this->historique($piece, $ancien, $statut, $commentaire);
    }

    private function historique(VehiclePartRequest $piece, ?string $ancien, string $nouveau, string $commentaire): void
    {
        VehiclePartRequestHistory::create([
            'vehicle_part_request_id' => $piece->id,
            'old_status'              => $ancien,
            'new_status'              => $nouveau,
            'old_received_quantity'   => (float) ($piece->getOriginal('received_quantity') ?? 0),
            'new_received_quantity'   => (float) ($piece->received_quantity ?? 0),
            'comment'                 => $commentaire,
            'changed_by'              => auth()->id(),
            'changed_at'              => now(),
        ]);
    }

    /** Notification interne aux utilisateurs actifs des rôles donnés (jamais bloquante). */
    public function notifier(SupplierOrder $bc, array $roles, string $titre, string $message): void
    {
        rescue(function () use ($bc, $roles, $titre, $message) {
            $destinataires = User::query()->whereIn('role', $roles)->where('is_active', true)->get();

            if ($destinataires->isNotEmpty()) {
                Notification::send($destinataires, new NewSupplierOrderNotification($bc, $titre, $message));
            }
        }, null, true);
    }
}
