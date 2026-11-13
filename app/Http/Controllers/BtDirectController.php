<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SaisiePiecesCatalogue;
use App\Models\BonTransfert;
use App\Models\EquipementAtelier;
use App\Models\ExternalBonCommande;
use App\Services\AppAtelierApiService;
use App\Services\BtDirectService;
use App\Services\ConsommationAtelierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use RuntimeException;

/**
 * « + BT » (V6) : BT direct du Magasin, saisi comme un devis, pour tout usage
 * interne (consommation atelier, vente intersociété, équipe commerciale,
 * autre). Seule la consommation atelier est envoyée à l'Atelier.
 */
class BtDirectController extends Controller
{
    use SaisiePiecesCatalogue;

    public function __construct(protected BtDirectService $btDirects)
    {
    }

    public function create(Request $request)
    {
        // Liste des équipements (consommation atelier) remise à jour si la copie a plus d'une heure
        $derniere = EquipementAtelier::max('synchronise_le');
        if (! $derniere || now()->diffInMinutes($derniere) >= 60) {
            try {
                app(ConsommationAtelierService::class)->synchroniser();
            } catch (RuntimeException $e) {
                Log::info('Liste des équipements non actualisée : ' . $e->getMessage());
                session()->now('avertissement', "Liste des équipements non actualisée depuis l'Atelier : {$e->getMessage()}");
            }
        }

        $motif = array_key_exists($request->query('motif'), ExternalBonCommande::MOTIFS) ? $request->query('motif') : null;

        return view('bons-transfert.direct', $this->donneesFormulaire(null, $motif, $request->integer('equipement') ?: null));
    }

    public function store(Request $request)
    {
        $data = $this->validerFormulaire($request);
        $conso = $data['motif'] === ExternalBonCommande::MOTIF_CONSO;
        $equipement = $conso ? EquipementAtelier::query()->where('actif', true)->findOrFail($data['equipement_atelier_id']) : null;

        try {
            $bt = $this->btDirects->creer($data['motif'], $equipement, $data['beneficiaire'] ?? null, $data['lignes'], $data['demande_par'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if (! $conso) {
            return redirect()->route('bons-transfert.show', $bt)->with('success', "Bon de transfert {$bt->numero} créé ("
                . ExternalBonCommande::MOTIFS[$data['motif']] . " — {$data['beneficiaire']}).");
        }

        $erreur = app(AppAtelierApiService::class)->envoyerBonTransfert($bt->fresh(['bonCommande', 'lignes.product', 'lignes.depot']));

        return redirect()->route('bons-transfert.show', $bt)->with(
            $erreur ? 'error' : 'success',
            "Bon de transfert {$bt->numero} créé pour « {$equipement->nom} »."
                . ($erreur ? " Envoi à l'Atelier échoué : {$erreur} — utilisez « Renvoyer à l'Atelier »." : " Envoyé à l'Atelier.")
        );
    }

    /** « Actualiser » : recopie la liste des équipements tenue dans l'Atelier */
    public function synchroniserEquipements(ConsommationAtelierService $consommations)
    {
        try {
            $r = $consommations->synchroniser();
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', "Liste des équipements non actualisée : {$e->getMessage()}");
        }

        return back()->withInput()->with('success', "Liste des équipements actualisée depuis l'Atelier : {$r['ajoutes']} ajouté(s), {$r['mis_a_jour']} à jour"
            . ($r['desactives'] ? ", {$r['desactives']} retiré(s)" : '') . '.');
    }

    public function edit(BonTransfert $bonTransfert)
    {
        $this->verifierBtDirect($bonTransfert);

        return view('bons-transfert.direct', $this->donneesFormulaire($bonTransfert));
    }

    public function update(Request $request, BonTransfert $bonTransfert)
    {
        $this->verifierBtDirect($bonTransfert);
        $bc = $bonTransfert->bonCommande;
        $data = $this->validerFormulaire($request, $bc->estConsommationDirecte() ? ExternalBonCommande::MOTIF_CONSO : $bc->motif);

        try {
            $bt = $this->btDirects->modifier($bonTransfert, $data['beneficiaire'] ?? null, $data['lignes'], $data['demande_par'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($bc->estConsommationDirecte() && ! $bonTransfert->envoye_atelier_at) {
            app(AppAtelierApiService::class)->envoyerBonTransfert($bt);
        }

        return redirect()->route('bons-transfert.show', $bt)->with('success', "Bon de transfert {$bt->numero} modifié" . ($bt->fresh()->envoye_atelier_at ? ' et renvoyé à l\'Atelier.' : '.'));
    }

    public function destroy(Request $request, BonTransfert $bonTransfert)
    {
        $this->verifierBtDirect($bonTransfert);
        $numero = $bonTransfert->numero;
        $conso = $bonTransfert->bonCommande->estConsommationDirecte();
        $remettre = $request->boolean('remettre_en_stock');

        try {
            $this->btDirects->supprimer($bonTransfert, $remettre);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('bons-transfert.index')->with('success', "Bon de transfert {$numero} supprimé"
            . ($conso ? " (annulé aussi dans l'Atelier)." : '.')
            . ($remettre ? ' Les pièces ont été remises dans leurs dépôts.' : " Le stock n'a pas été modifié."));
    }

    /* ------------------------------------------------------------------ */

    private function verifierBtDirect(BonTransfert $bt): void
    {
        abort_unless($bt->bonCommande?->estBtDirect(), 404);
    }

    /** $motif : celui du BT modifié (non modifiable) ; null à la création */
    private function validerFormulaire(Request $request, ?string $motif = null): array
    {
        $motif ??= (string) $request->input('motif');
        $conso = $motif === ExternalBonCommande::MOTIF_CONSO;
        $creation = ! $request->route('bonTransfert');

        $data = $request->validate(array_filter([
            'motif'                 => $creation ? ['required', Rule::in(array_keys(ExternalBonCommande::MOTIFS))] : null,
            'equipement_atelier_id' => $creation && $conso ? ['required', 'integer', 'exists:equipements_atelier,id'] : null,
            'beneficiaire'          => $conso ? null : ['required', 'string', 'max:150'],
            'demande_par'           => ['nullable', 'string', 'max:150'],
        ]) + $this->reglesLignes(true), [
            'motif.required'                 => 'Choisissez le motif du BT.',
            'equipement_atelier_id.required' => "Choisissez l'équipement.",
            'beneficiaire.required'          => 'Indiquez le bénéficiaire (société, commercial, service…).',
        ] + $this->messagesLignes());

        return $data + ['motif' => $motif];
    }

    private function donneesFormulaire(?BonTransfert $bt, ?string $motif = null, ?int $equipementId = null): array
    {
        $bt?->load(['bonCommande', 'lignes.product', 'lignes.depot']);
        $bc = $bt?->bonCommande;

        return [
            'bt'             => $bt,
            'motif'          => $bc ? ($bc->estConsommationDirecte() ? ExternalBonCommande::MOTIF_CONSO : $bc->motif) : $motif,
            'motifs'         => ExternalBonCommande::MOTIFS,
            'equipements'    => EquipementAtelier::query()->where('actif', true)->orderBy('nom')->get(),
            'equipementId'   => $bc?->equipement_atelier_id ?? $equipementId,
            'prixModifiable' => true,
        ] + $this->donneesPieces($bt);
    }
}
