<?php

namespace App\Services;

use App\Models\EquipementAtelier;
use Illuminate\Support\Facades\DB;

/**
 * Consommation atelier (V6) : pièces prises par le garage pour un équipement
 * (pont élévateur, compresseur d'air…) et non pour un véhicule.
 *
 * Deux entrées :
 * - l'Atelier crée un BC « consommation » (Suivi des pièces) : il arrive dans
 *   les commandes garage et suit le circuit normal (BT, BT du reste…) ;
 * - le Magasin fait un BT direct « + BT », motif consommation atelier
 *   (voir BtDirectService).
 * Un BC / BT = un seul équipement. Ce service tient la copie locale de la
 * liste des équipements.
 */
class ConsommationAtelierService
{
    public function __construct(protected AppAtelierApiService $atelier)
    {
    }

    /**
     * Met à jour la copie locale de la liste des équipements depuis l'Atelier.
     * Un équipement retiré (ou désactivé) dans l'Atelier est désactivé ici.
     *
     * @return array{ajoutes:int, mis_a_jour:int, desactives:int}
     */
    public function synchroniser(): array
    {
        $liste = $this->atelier->listeEquipements();
        $maintenant = now();
        $vus = [];
        $ajoutes = 0;
        $majs = 0;

        DB::transaction(function () use ($liste, $maintenant, &$vus, &$ajoutes, &$majs) {
            foreach ($liste as $e) {
                $nom = trim((string) ($e['nom'] ?? ''));

                if (empty($e['id']) || $nom === '') {
                    continue;
                }

                $fiche = EquipementAtelier::firstOrNew(['atelier_id' => (int) $e['id']]);
                $fiche->fill(['nom' => $nom, 'actif' => true, 'synchronise_le' => $maintenant]);
                $fiche->exists ? $majs++ : $ajoutes++;
                $fiche->save();
                $vus[] = (int) $e['id'];
            }
        });

        $desactives = EquipementAtelier::query()->where('actif', true)
            ->whereNotIn('atelier_id', $vus)
            ->update(['actif' => false, 'synchronise_le' => $maintenant]);

        return ['ajoutes' => $ajoutes, 'mis_a_jour' => $majs, 'desactives' => $desactives];
    }
}
