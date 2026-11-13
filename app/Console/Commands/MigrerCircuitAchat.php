<?php

namespace App\Console\Commands;

use App\Models\Demande;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\VehiclePartRequest;
use App\Services\CircuitAchatService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * V7 — Reprise des données existantes dans le nouveau circuit.
 *
 *   php artisan achats:migrer-circuit              (aperçu, rien n'est enregistré)
 *   php artisan achats:migrer-circuit --appliquer  (enregistre)
 *
 * 1. Chaque BC existant reçoit sa demande (avec les pièces de ses lignes).
 * 2. Pièce « Commandée » dont le BC n'est pas encore approuvé → « À commander ».
 * 3. Pièce « Commandée » sur aucun BC : regroupée par destination (véhicule ou
 *    dépôt) dans une demande ; avec fournisseur → « À commander » et BC créé
 *    (à valider par le chef), sans fournisseur → « En recherche ».
 * 4. Pièce déjà reçue : la quantité reçue est reportée sur la ligne de son BC.
 * 5. Toute autre pièce encore hors demande (reçue, en recherche, annulée…
 *    sans bon de commande) : une demande par véhicule (ou dépôt) et par jour
 *    de création ; une pièce en recherche avec fournisseur reçoit son BC
 *    (à valider). Plus aucune pièce ne reste hors d'une demande.
 * Les vieux BC en attente de signature ne sont pas touchés (à approuver ou annuler à la main).
 */
class MigrerCircuitAchat extends Command
{
    protected $signature = 'achats:migrer-circuit {--appliquer : enregistrer (sinon simple aperçu)}';

    protected $description = 'V7 : range les pièces spéciales et les bons de commande existants dans le circuit demande → BC → réception';

    public function handle(CircuitAchatService $circuit): int
    {
        $appliquer = (bool) $this->option('appliquer');
        $this->info($appliquer ? 'Reprise du circuit — ENREGISTREMENT' : 'Reprise du circuit — APERÇU (rien n\'est enregistré ; --appliquer pour enregistrer)');

        DB::beginTransaction();

        try {
            $stats = $this->reprendre($circuit);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Arrêt, rien n\'a été enregistré : ' . $e->getMessage());

            return self::FAILURE;
        }

        $appliquer ? DB::commit() : DB::rollBack();

        $this->table(['Étape', 'Nombre'], [
            ['BC existants rattachés à une demande', $stats['bc_demandes']],
            ['Pièces « Commandée » → « À commander » (BC pas encore approuvé)', $stats['a_commander']],
            ['Pièces commandées sans BC regroupées en demande', $stats['orphelines']],
            ['   dont avec fournisseur → BC créé (à valider)', $stats['orphelines_bc']],
            ['   dont sans fournisseur → « En recherche »', $stats['orphelines_recherche']],
            ['   BC créés pour elles', $stats['bc_crees']],
            ['Lignes de BC complétées avec la quantité déjà reçue', $stats['lignes_recues']],
            ['Autres pièces hors demande regroupées (véhicule + jour)', $stats['restantes']],
            ['   demandes créées pour elles', $stats['restantes_demandes']],
            ['   BC créés pour elles (en recherche avec fournisseur)', $stats['restantes_bc']],
            ['Pièces encore hors demande (doit être 0)', $stats['hors_demande']],
        ]);

        $this->line($appliquer ? '<info>Enregistré.</info>' : '<comment>Aperçu seulement : relancez avec --appliquer pour enregistrer.</comment>');

        return self::SUCCESS;
    }

    private function reprendre(CircuitAchatService $circuit): array
    {
        $stats = array_fill_keys(['bc_demandes', 'a_commander', 'orphelines', 'orphelines_bc', 'orphelines_recherche', 'bc_crees', 'lignes_recues', 'restantes', 'restantes_demandes', 'restantes_bc'], 0);

        // 1. Une demande par BC existant (pièces de ses lignes)
        SupplierOrder::query()->whereNull('demande_id')->with('items.vehiclePartRequest')->orderBy('id')->each(function (SupplierOrder $bc) use (&$stats) {
            $pieces = $bc->items->pluck('vehiclePartRequest')->filter();
            $premiere = $pieces->first();

            $demande = $pieces->pluck('demande_id')->filter()->first()
                ? Demande::find($pieces->pluck('demande_id')->filter()->first())
                : Demande::create([
                    'numero'     => Demande::prochainNumero(),
                    'source'     => $premiere?->external_bon_commande_ligne_id ? Demande::SOURCE_ATELIER : Demande::SOURCE_SAISIE,
                    'vehicle_id' => $premiere?->vehicle_id,
                    'depot_id'   => $bc->depot_id,
                    'created_by' => $bc->created_by,
                    'notes'      => 'Reprise du ' . $bc->order_number,
                ]);

            $bc->update(['demande_id' => $demande->id]);
            VehiclePartRequest::query()->whereIn('id', $pieces->pluck('id'))->whereNull('demande_id')->update(['demande_id' => $demande->id]);
            $stats['bc_demandes']++;
        });

        // 2. « Commandée » mais BC pas encore approuvé → « À commander »
        $nonApprouves = [SupplierOrder::STATUS_DRAFT, SupplierOrder::STATUS_VALIDATED, SupplierOrder::STATUS_REJECTED];

        VehiclePartRequest::query()
            ->where('status', VehiclePartRequest::STATUS_ORDERED)
            ->whereHas('supplierOrderItems', fn ($q) => $q->whereHas('supplierOrder', fn ($o) => $o->whereIn('status', $nonApprouves)))
            ->each(function (VehiclePartRequest $p) use ($circuit, &$stats) {
                $circuit->changerStatut($p, VehiclePartRequest::STATUS_TO_ORDER, 'Reprise V7 : bon de commande pas encore approuvé.');
                $p->save();
                $stats['a_commander']++;
            });

        // 3. « Commandée » sur aucun BC actif : une demande par destination
        $orphelines = VehiclePartRequest::query()
            ->where('status', VehiclePartRequest::STATUS_ORDERED)
            ->whereDoesntHave('supplierOrderItems', fn ($q) => $q->whereHas('supplierOrder', fn ($o) => $o->whereNotIn('status', SupplierOrder::STATUTS_INACTIFS)))
            ->orderBy('id')
            ->get();

        foreach ($orphelines->groupBy(fn ($p) => $p->vehicle_id ? 'v' . $p->vehicle_id : 'd' . $p->depot_id) as $lot) {
            $premiere = $lot->first();
            $demande = $premiere->demande_id ? Demande::find($premiere->demande_id) : Demande::create([
                'numero'     => Demande::prochainNumero(),
                'source'     => Demande::SOURCE_SAISIE,
                'vehicle_id' => $premiere->vehicle_id,
                'depot_id'   => $premiere->depot_id,
                'created_by' => $premiere->created_by,
                'notes'      => 'Reprise V7 : pièces « Commandées » sans bon de commande',
            ]);

            foreach ($lot as $p) {
                $p->demande_id = $demande->id;
                $p->order_reference = null;
                $circuit->changerStatut($p, VehiclePartRequest::STATUS_SEARCHING, 'Reprise V7 : « Commandée » sans bon de commande.');
                $p->save();
                $stats['orphelines']++;
                $p->supplier_id ? $stats['orphelines_bc']++ : $stats['orphelines_recherche']++;
            }

            $stats['bc_crees'] += $circuit->genererBonsCommande($demande)->count();
        }

        // 4. Pièce reçue : quantité reçue reportée sur la ligne de son BC
        SupplierOrderItem::query()
            ->whereHas('vehiclePartRequest', fn ($q) => $q->where('received_quantity', '>', 0))
            ->whereHas('supplierOrder', fn ($o) => $o->whereNotIn('status', SupplierOrder::STATUTS_INACTIFS))
            ->with('vehiclePartRequest')
            ->each(function (SupplierOrderItem $l) use (&$stats) {
                $recu = min((float) $l->quantity_ordered, (float) $l->vehiclePartRequest->received_quantity);

                if ((float) $l->quantity_received < $recu) {
                    $l->update(['quantity_received' => $recu]);
                    $stats['lignes_recues']++;
                }
            });

        // 5. Pièces encore hors demande : une demande par véhicule (ou dépôt) et par jour
        $restantes = VehiclePartRequest::query()->whereNull('demande_id')->orderBy('id')->get();

        $lots = $restantes->groupBy(fn ($p) => ($p->vehicle_id ? 'v' . $p->vehicle_id : 'd' . $p->depot_id)
            . '|' . ($p->requested_at ?? $p->created_at)?->format('Y-m-d'));

        foreach ($lots as $lot) {
            $premiere = $lot->first();
            $jour = ($premiere->requested_at ?? $premiere->created_at)?->format('d/m/Y');

            $demande = Demande::create([
                'numero'     => Demande::prochainNumero(),
                'source'     => $premiere->external_bon_commande_ligne_id ? Demande::SOURCE_ATELIER : Demande::SOURCE_SAISIE,
                'vehicle_id' => $premiere->vehicle_id,
                'depot_id'   => $premiere->depot_id,
                'created_by' => $premiere->created_by,
                'notes'      => 'Reprise : pièces sans bon de commande' . ($jour ? ' du ' . $jour : ''),
            ]);

            // garder la date d'origine pour que la liste reste dans l'ordre
            if ($date = $premiere->requested_at ?? $premiere->created_at) {
                $demande->forceFill(['created_at' => $date])->saveQuietly();
            }

            VehiclePartRequest::query()->whereIn('id', $lot->pluck('id'))->update(['demande_id' => $demande->id]);
            $stats['restantes'] += $lot->count();
            $stats['restantes_demandes']++;
            $stats['restantes_bc'] += $circuit->genererBonsCommande($demande)->count();
        }

        $stats['hors_demande'] = VehiclePartRequest::query()->whereNull('demande_id')->count();

        return $stats;
    }
}
