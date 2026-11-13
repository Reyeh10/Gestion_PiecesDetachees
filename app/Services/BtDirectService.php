<?php

namespace App\Services;

use App\Models\BonTransfert;
use App\Models\EquipementAtelier;
use App\Models\ExternalBonCommande;
use App\Services\Concerns\LignesBcInterne;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * « + BT » (V6) : BT direct du Magasin, saisi comme un devis, pour tout usage
 * interne. Un BC interne + son BT ; le stock sort tout de suite.
 *
 * - Consommation atelier : un équipement du garage (pont, compresseur…) ;
 *   BC « magasin-conso » n° CONSO-AAAA-NNNN, le BT part à l'Atelier qui
 *   l'enregistre comme BC consommation « reçu ».
 * - Autres motifs (vente intersociété, équipe commerciale, autre) : un
 *   bénéficiaire en texte libre ; BC « magasin-interne » n° INT-AAAA-NNNN,
 *   rien n'est envoyé à l'Atelier.
 *
 * Prix : proposé au prix catalogue, modifiable ligne par ligne.
 */
class BtDirectService
{
    use LignesBcInterne;

    public function __construct(
        protected BonTransfertService $bonsTransfert,
        protected AppAtelierApiService $atelier,
    ) {
    }

    /**
     * @param  array<int, array{product_id:int, depot_id:int, quantite:float, prix:?float}>  $lignes
     *
     * @throws RuntimeException (stock insuffisant, pièce inconnue…)
     */
    public function creer(string $motif, ?EquipementAtelier $equipement, ?string $beneficiaire, array $lignes, ?string $demandePar = null): BonTransfert
    {
        $conso = $motif === ExternalBonCommande::MOTIF_CONSO;

        if ($conso && ! $equipement) {
            throw new RuntimeException("Choisissez l'équipement.");
        }

        $lignes = $this->regrouper($lignes);

        if (! $lignes) {
            throw new RuntimeException('Ajoutez au moins une pièce.');
        }

        return DB::transaction(function () use ($motif, $conso, $equipement, $beneficiaire, $lignes, $demandePar) {
            $bc = ExternalBonCommande::create([
                'numero'                => $this->prochainNumeroBc($conso ? 'CONSO' : 'INT'),
                'source_system'         => $conso ? ExternalBonCommande::SOURCE_CONSO : ExternalBonCommande::SOURCE_INTERNE,
                'motif'                 => $motif,
                'equipement_atelier_id' => $conso ? $equipement->id : null,
                'equipement'            => $conso ? $equipement->nom : null,
                'beneficiaire'          => $conso ? null : trim((string) $beneficiaire),
                'demande_par'           => filled($demandePar) ? trim($demandePar) : null,
                'client_nom'            => $conso ? 'Consommation atelier' : trim((string) $beneficiaire),
                'statut'                => 'recu',
                'vu_at'                 => now(),
            ]);

            $this->ecrireLignes($bc, $lignes);

            return $this->bonsTransfert->creer($bc->fresh());
        });
    }

    /**
     * Modifie un BT direct (le motif ne change pas) : le stock n'est corrigé
     * que de la différence. Un BT de consommation déjà à l'Atelier n'est
     * gardé modifié que si l'Atelier l'accepte.
     *
     * @throws RuntimeException
     */
    public function modifier(BonTransfert $bt, ?string $beneficiaire, array $lignes, ?string $demandePar = null): BonTransfert
    {
        $lignes = $this->regrouper($lignes);

        if (! $lignes) {
            throw new RuntimeException('Le BT doit garder au moins une pièce (sinon supprimez-le).');
        }

        $dejaEnvoye = (bool) $bt->envoye_atelier_at;

        return DB::transaction(function () use ($bt, $beneficiaire, $lignes, $demandePar, $dejaEnvoye) {
            $bc = ExternalBonCommande::query()->lockForUpdate()->findOrFail($bt->external_bon_commande_id);
            $bc->update(['demande_par' => filled($demandePar) ? trim($demandePar) : null]
                + ($bc->estInterne() ? ['beneficiaire' => trim((string) $beneficiaire), 'client_nom' => trim((string) $beneficiaire)] : []));

            $bc->lignes()->delete();
            $this->ecrireLignes($bc, $lignes, $bt->id);

            $this->bonsTransfert->mettreAJour($bc->fresh());

            $bt = $bt->fresh(['bonCommande', 'lignes.product', 'lignes.depot']);

            if ($bc->estConsommationDirecte() && $dejaEnvoye && ($erreur = $this->atelier->envoyerBonTransfert($bt))) {
                // tout est annulé (stock compris) : l'Atelier garde l'ancienne version
                throw new RuntimeException("Modification annulée, l'Atelier l'a refusée : {$erreur}");
            }

            return $bt;
        });
    }

    /**
     * Supprime un BT direct (consommation : l'Atelier l'annule d'abord). Les
     * pièces reviennent dans leur dépôt si demandé.
     *
     * @throws RuntimeException
     */
    public function supprimer(BonTransfert $bt, bool $remettreEnStock): void
    {
        if ($bt->bonCommande?->estConsommationDirecte() && ($erreur = $this->atelier->annulerBonTransfertConsommation($bt))) {
            throw new RuntimeException("Suppression refusée : {$erreur}");
        }

        DB::transaction(function () use ($bt, $remettreEnStock) {
            $bcId = $bt->external_bon_commande_id;

            $this->bonsTransfert->supprimer($bt, $remettreEnStock);

            ExternalBonCommande::query()
                ->whereIn('source_system', [ExternalBonCommande::SOURCE_CONSO, ExternalBonCommande::SOURCE_INTERNE])
                ->whereKey($bcId)
                ->delete();
        });
    }
}
