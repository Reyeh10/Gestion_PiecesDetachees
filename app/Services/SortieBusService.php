<?php

namespace App\Services;

use App\Models\BonTransfert;
use App\Models\BusAtelier;
use App\Models\Depot;
use App\Models\ExternalBonCommande;
use App\Models\Product;
use App\Models\ProductDepotStock;
use App\Services\Concerns\LignesBcInterne;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Sorties de pièces pour les bus leasing / bus du transport public (V6).
 *
 * Le Magasin choisit le bus et les pièces ; une sortie = un BC interne
 * (source « magasin-bus ») + son BT, créé par BonTransfertService (sortie de
 * stock, mouvement, PDF, numéro BT-AAAA-NNNN). Le BT part ensuite à
 * l'Atelier, qui l'enregistre comme BC bus « reçu » pour son récap mensuel.
 * Prix : prix de vente du catalogue (non modifiable).
 */
class SortieBusService
{
    use LignesBcInterne;

    public function __construct(
        protected BonTransfertService $bonsTransfert,
        protected AppAtelierApiService $atelier,
    ) {
    }

    /**
     * Met à jour la copie locale de la liste des bus depuis l'Atelier.
     * Un bus retiré de la liste de l'Atelier est désactivé (ses BT restent).
     *
     * @return array{ajoutes:int, mis_a_jour:int, desactives:int}
     */
    public function synchroniser(): array
    {
        $liste = $this->atelier->listeBus();
        $maintenant = now();
        $vus = [];
        $ajoutes = 0;
        $majs = 0;

        DB::transaction(function () use ($liste, $maintenant, &$vus, &$ajoutes, &$majs) {
            foreach ($liste as $bus) {
                $groupe = (string) ($bus['groupe'] ?? '');
                $cle = BusAtelier::normaliser($bus['immatriculation'] ?? '');

                if ($cle === '' || ! isset(BusAtelier::GROUPES[$groupe])) {
                    continue;
                }

                $fiche = BusAtelier::firstOrNew(['immatriculation_normalisee' => $cle]);
                $fiche->fill([
                    'atelier_id'      => $bus['id'] ?? null,
                    'groupe'          => $groupe,
                    'immatriculation' => trim((string) $bus['immatriculation']),
                    'marque'          => $bus['marque'] ?? null,
                    'modele'          => $bus['modele'] ?? null,
                    'vin'             => $bus['vin'] ?? null,
                    'actif'           => true,
                    'synchronise_le'  => $maintenant,
                ]);
                $fiche->exists ? $majs++ : $ajoutes++;
                $fiche->save();
                $vus[] = $cle;
            }
        });

        $desactives = BusAtelier::query()->where('actif', true)
            ->whereNotIn('immatriculation_normalisee', $vus)
            ->update(['actif' => false, 'synchronise_le' => $maintenant]);

        return ['ajoutes' => $ajoutes, 'mis_a_jour' => $majs, 'desactives' => $desactives];
    }

    /**
     * Crée la sortie d'un bus et son BT.
     *
     * @param  array<int, array{product_id:int, depot_id:int, quantite:float}>  $lignes
     *
     * @throws RuntimeException (stock insuffisant, pièce inconnue…)
     */
    public function creer(BusAtelier $bus, array $lignes): BonTransfert
    {
        $lignes = $this->regrouper($lignes);

        if (! $lignes) {
            throw new RuntimeException("Aucune pièce saisie pour le bus {$bus->immatriculation}.");
        }

        return DB::transaction(function () use ($bus, $lignes) {
            $bc = ExternalBonCommande::create([
                'numero'                   => $this->prochainNumeroBc('BUS'),
                'source_system'            => ExternalBonCommande::SOURCE_BUS,
                'bus_atelier_id'           => $bus->id,
                'groupe_bus'               => $bus->groupe,
                'vehicule_marque'          => $bus->marque,
                'vehicule_modele'          => $bus->modele,
                'vehicule_immatriculation' => $bus->immatriculation,
                'vehicule_vin'             => $bus->vin,
                'client_nom'               => $bus->groupe_label,
                'statut'                   => 'recu',
                'vu_at'                    => now(),
            ]);

            $this->ecrireLignes($bc, $lignes);

            return $this->bonsTransfert->creer($bc->fresh());
        });
    }

    /**
     * Modifie les pièces d'un BT bus : le stock n'est corrigé que de la
     * différence (BonTransfertService::mettreAJour). Si le BT était déjà à
     * l'Atelier, la modification n'est gardée que si l'Atelier l'accepte
     * (refus si le BT est déjà compté dans un récap).
     *
     * @throws RuntimeException
     */
    public function modifier(BonTransfert $bt, array $lignes): BonTransfert
    {
        $lignes = $this->regrouper($lignes);

        if (! $lignes) {
            throw new RuntimeException('Le BT doit garder au moins une pièce (sinon supprimez-le).');
        }

        $dejaEnvoye = (bool) $bt->envoye_atelier_at;

        return DB::transaction(function () use ($bt, $lignes, $dejaEnvoye) {
            $bc = ExternalBonCommande::query()->lockForUpdate()->findOrFail($bt->external_bon_commande_id);

            $bc->lignes()->delete();
            $this->ecrireLignes($bc, $lignes, $bt->id);

            $this->bonsTransfert->mettreAJour($bc->fresh());

            $bt = $bt->fresh(['bonCommande', 'lignes.product', 'lignes.depot']);

            if ($dejaEnvoye && ($erreur = $this->atelier->envoyerBonTransfert($bt))) {
                // tout est annulé (stock compris) : l'Atelier garde l'ancienne version
                throw new RuntimeException("Modification annulée, l'Atelier l'a refusée : {$erreur}");
            }

            return $bt;
        });
    }

    /**
     * Supprime un BT bus : l'Atelier doit d'abord l'annuler (refus s'il est
     * dans un récap). Les pièces reviennent dans leur dépôt si demandé.
     *
     * @throws RuntimeException
     */
    public function supprimer(BonTransfert $bt, bool $remettreEnStock): void
    {
        if ($erreur = $this->atelier->annulerBonTransfertBus($bt)) {
            throw new RuntimeException("Suppression refusée : {$erreur}");
        }

        DB::transaction(function () use ($bt, $remettreEnStock) {
            $bcId = $bt->external_bon_commande_id;

            $this->bonsTransfert->supprimer($bt, $remettreEnStock);

            ExternalBonCommande::query()->sortiesBus()->whereKey($bcId)->delete();
        });
    }

    /**
     * Contrôle d'un import Excel (plusieurs bus) : colonnes Immatriculation,
     * Référence, Quantité, Dépôt (facultatif : dépôt par défaut sinon).
     * Rien n'est enregistré. Le stock est vérifié pour le total du fichier.
     *
     * @return array{erreurs: string[], groupes: array}
     */
    public function analyserImport(array $feuille, Depot $depotParDefaut): array
    {
        $erreurs = [];

        if (count($feuille) < 2) {
            return ['erreurs' => ['Le fichier est vide.'], 'groupes' => []];
        }

        $entetes = array_map(fn ($t) => mb_strtolower(trim((string) $t)), array_shift($feuille));
        $col = fn (array $noms) => collect($noms)->map(fn ($n) => array_search($n, $entetes, true))->first(fn ($i) => $i !== false);

        $iImmat = $col(['immatriculation']);
        $iRef = $col(['référence', 'reference']);
        $iQte = $col(['quantité', 'quantite']);
        $iDepot = $col(['dépôt', 'depot']);

        if ($iImmat === null || $iRef === null || $iQte === null) {
            return ['erreurs' => ['Colonnes « Immatriculation », « Référence » et « Quantité » introuvables : utilisez le modèle Excel.'], 'groupes' => []];
        }

        $depots = Depot::query()->get()->keyBy(fn ($d) => mb_strtolower(trim($d->name)));
        $groupes = [];
        $besoins = []; // stock demandé par pièce + dépôt, tout le fichier

        foreach ($feuille as $n => $row) {
            $numLigne = $n + 2;
            $immat = trim((string) ($row[$iImmat] ?? ''));
            $ref = trim((string) ($row[$iRef] ?? ''));
            $qteBrute = trim((string) ($row[$iQte] ?? ''));

            if ($immat === '' && $ref === '' && $qteBrute === '') {
                continue;
            }

            $bus = BusAtelier::parImmatriculation($immat);
            if (! $bus || ! $bus->actif) {
                $erreurs[] = "Ligne {$numLigne} : bus « {$immat} » absent de la liste des bus (actualisez la liste depuis l'Atelier).";
                continue;
            }

            $product = $ref === '' ? null : Product::query()->where('reference', $ref)->first();
            if (! $product) {
                $erreurs[] = "Ligne {$numLigne} : référence « {$ref} » inconnue au catalogue.";
                continue;
            }

            $qte = (float) str_replace(',', '.', $qteBrute);
            if (! is_numeric(str_replace(',', '.', $qteBrute)) || $qte <= 0) {
                $erreurs[] = "Ligne {$numLigne} : quantité « {$qteBrute} » invalide.";
                continue;
            }

            $nomDepot = $iDepot !== null ? trim((string) ($row[$iDepot] ?? '')) : '';
            $depot = $nomDepot === '' ? $depotParDefaut : $depots->get(mb_strtolower($nomDepot));
            if (! $depot) {
                $erreurs[] = "Ligne {$numLigne} : dépôt « {$nomDepot} » inconnu.";
                continue;
            }

            $groupes[$bus->id] ??= ['bus' => $bus, 'lignes' => []];
            $groupes[$bus->id]['lignes'][] = [
                'ligne'       => $numLigne,
                'product_id'  => $product->id,
                'reference'   => $product->reference,
                'designation' => $product->designation,
                'depot_id'    => $depot->id,
                'depot'       => $depot->name,
                'quantite'    => round($qte, 2),
                'prix'        => round((float) $product->sale_price, 2),
            ];

            $k = $product->id.':'.$depot->id;
            $besoins[$k] = ['demande' => round(($besoins[$k]['demande'] ?? 0) + $qte, 2), 'ref' => $product->reference, 'depot' => $depot->name, 'product_id' => $product->id, 'depot_id' => $depot->id];
        }

        foreach ($besoins as $b) {
            $dispo = (float) ProductDepotStock::query()->where('product_id', $b['product_id'])->where('depot_id', $b['depot_id'])->value('quantity');
            if ($dispo < $b['demande']) {
                $erreurs[] = "Stock insuffisant : {$b['ref']} dans {$b['depot']} — disponible {$dispo}, demandé {$b['demande']} (total du fichier).";
            }
        }

        return ['erreurs' => $erreurs, 'groupes' => array_values($groupes)];
    }
}
