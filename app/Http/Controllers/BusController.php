<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SaisiePiecesCatalogue;
use App\Models\BonTransfert;
use App\Models\BusAtelier;
use App\Models\Depot;
use App\Models\ExternalBonCommande;
use App\Services\AppAtelierApiService;
use App\Services\SortieBusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

/**
 * Bus leasing / bus du transport public (V6) : la sortie des pièces se fait
 * au Magasin (un bus à l'écran, plusieurs bus par Excel). Chaque sortie
 * donne un BT normal, envoyé à l'Atelier pour son récap mensuel.
 */
class BusController extends Controller
{
    use SaisiePiecesCatalogue;

    public function __construct(protected SortieBusService $sorties)
    {
    }

    public function index(Request $request)
    {
        // Liste mise à jour toute seule si la dernière copie a plus d'une heure
        $derniere = BusAtelier::max('synchronise_le');
        if (! $derniere || now()->diffInMinutes($derniere) >= 60) {
            try {
                $this->sorties->synchroniser();
            } catch (RuntimeException $e) {
                Log::info('Liste des bus non actualisée : ' . $e->getMessage());
                session()->flash('avertissement', "Liste des bus non actualisée depuis l'Atelier : {$e->getMessage()}");
            }
        }

        $groupe = $request->query('groupe');
        $groupe = isset(BusAtelier::GROUPES[$groupe]) ? $groupe : null;
        $mois = preg_match('/^\d{4}-\d{2}$/', (string) $request->query('mois')) ? $request->query('mois') : null;
        $recherche = trim((string) $request->query('search', ''));

        $bus = BusAtelier::query()
            ->where('actif', true)
            ->when($groupe, fn ($q) => $q->where('groupe', $groupe))
            ->withCount(['sorties as sorties_du_mois' => fn ($q) => $q->whereHas('bonTransfert', fn ($b) => $b->where('created_at', '>=', now()->startOfMonth()))])
            ->orderBy('groupe')->orderBy('immatriculation')
            ->get();

        $bts = BonTransfert::query()
            ->with(['bonCommande', 'user'])
            ->withCount('lignes')
            ->whereHas('bonCommande', fn ($q) => $q->sortiesBus()->when($groupe, fn ($g) => $g->where('groupe_bus', $groupe)))
            ->when($mois, fn ($q) => $q->whereYear('created_at', substr($mois, 0, 4))->whereMonth('created_at', substr($mois, 5, 2)))
            ->when($recherche !== '', function ($q) use ($recherche) {
                $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $recherche) . '%';
                $q->where(fn ($w) => $w->where('numero', 'like', $like)
                    ->orWhereHas('bonCommande', fn ($bc) => $bc->where('vehicule_immatriculation', 'like', $like))
                    ->orWhereHas('lignes.product', fn ($p) => $p->where('reference', 'like', $like)->orWhere('designation', 'like', $like)));
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('bus.index', [
            'bus'           => $bus,
            'bts'           => $bts,
            'groupe'        => $groupe,
            'mois'          => $mois,
            'recherche'     => $recherche,
            'synchroniseLe' => BusAtelier::max('synchronise_le'),
            'depots'        => Depot::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function synchroniser()
    {
        try {
            $r = $this->sorties->synchroniser();
        } catch (RuntimeException $e) {
            return back()->with('error', "Liste des bus non actualisée : {$e->getMessage()}");
        }

        return back()->with('success', "Liste des bus actualisée depuis l'Atelier : {$r['ajoutes']} ajouté(s), {$r['mis_a_jour']} à jour"
            . ($r['desactives'] ? ", {$r['desactives']} retiré(s) de la liste" : '') . '.');
    }

    /* ------------------------------------------------------------------
     | Un bus, à l'écran
     * ------------------------------------------------------------------ */

    public function create(Request $request)
    {
        return view('bus.form', $this->donneesFormulaire(null, $request->integer('bus') ?: null));
    }

    public function store(Request $request)
    {
        $data = $this->validerFormulaire($request);
        $bus = BusAtelier::query()->where('actif', true)->findOrFail($data['bus_atelier_id']);

        try {
            $bt = $this->sorties->creer($bus, $data['lignes']);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $erreur = app(AppAtelierApiService::class)->envoyerBonTransfert($bt->fresh(['bonCommande', 'lignes.product', 'lignes.depot']));

        return redirect()->route('bons-transfert.show', $bt)->with(
            $erreur ? 'error' : 'success',
            "Bon de transfert {$bt->numero} créé pour le bus {$bus->immatriculation}."
                . ($erreur ? " Envoi à l'Atelier échoué : {$erreur} — utilisez « Renvoyer à l'Atelier »." : " Envoyé à l'Atelier.")
        );
    }

    public function edit(BonTransfert $bonTransfert)
    {
        $this->verifierBtBus($bonTransfert);

        return view('bus.form', $this->donneesFormulaire($bonTransfert));
    }

    public function update(Request $request, BonTransfert $bonTransfert)
    {
        $this->verifierBtBus($bonTransfert);
        $data = $this->validerFormulaire($request, false);

        try {
            $bt = $this->sorties->modifier($bonTransfert, $data['lignes']);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if (! $bonTransfert->envoye_atelier_at) {
            app(AppAtelierApiService::class)->envoyerBonTransfert($bt);
        }

        return redirect()->route('bons-transfert.show', $bt)->with('success', "Bon de transfert {$bt->numero} modifié" . ($bt->fresh()->envoye_atelier_at ? ' et renvoyé à l\'Atelier.' : '.'));
    }

    public function destroy(Request $request, BonTransfert $bonTransfert)
    {
        $this->verifierBtBus($bonTransfert);
        $numero = $bonTransfert->numero;
        $remettre = $request->boolean('remettre_en_stock');

        try {
            $this->sorties->supprimer($bonTransfert, $remettre);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('bus.index')->with('success', "Bon de transfert {$numero} supprimé (annulé aussi dans l'Atelier)."
            . ($remettre ? ' Les pièces ont été remises dans leurs dépôts.' : " Le stock n'a pas été modifié."));
    }

    /* ------------------------------------------------------------------
     | Plusieurs bus, par Excel
     * ------------------------------------------------------------------ */

    public function modeleExcel()
    {
        $classeur = new Spreadsheet();
        $feuille = $classeur->getActiveSheet();
        $feuille->setTitle('Sorties bus');
        $feuille->fromArray(['Immatriculation', 'Référence', 'Quantité', 'Dépôt'], null, 'A1');
        $feuille->getStyle('A1:D1')->getFont()->setBold(true);

        $exemple = BusAtelier::query()->where('actif', true)->value('immatriculation') ?? '45 D 81';
        $feuille->fromArray([$exemple, 'REF-PIECE', 1, Depot::query()->where('is_active', true)->orderBy('name')->value('name')], null, 'A2');

        foreach (range('A', 'D') as $c) {
            $feuille->getColumnDimension($c)->setAutoSize(true);
        }

        $chemin = tempnam(sys_get_temp_dir(), 'bus') . '.xlsx';
        (new Xlsx($classeur))->save($chemin);

        return response()->download($chemin, 'modele-sorties-bus.xlsx')->deleteFileAfterSend();
    }

    public function apercuImport(Request $request)
    {
        $request->validate([
            'fichier'  => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            'depot_id' => ['required', 'integer', 'exists:depots,id'],
        ], ['fichier.required' => 'Choisissez le fichier Excel.']);

        $feuille = Excel::toArray(new \stdClass(), $request->file('fichier'))[0] ?? [];
        $analyse = $this->sorties->analyserImport($feuille, Depot::findOrFail($request->integer('depot_id')));

        // Sans erreur : la sortie est gardée en session jusqu'à la confirmation
        session(['bus_import' => $analyse['erreurs'] ? null : collect($analyse['groupes'])->map(fn ($g) => [
            'bus_atelier_id' => $g['bus']->id,
            'lignes'         => collect($g['lignes'])->map(fn ($l) => ['product_id' => $l['product_id'], 'depot_id' => $l['depot_id'], 'quantite' => $l['quantite']])->all(),
        ])->all()]);

        return view('bus.import', $analyse);
    }

    public function importer()
    {
        $groupes = session()->pull('bus_import');

        if (! $groupes) {
            return redirect()->route('bus.index')->with('error', "Rien à importer : refaites l'aperçu du fichier.");
        }

        $crees = [];
        $erreurs = [];
        $atelier = app(AppAtelierApiService::class);

        foreach ($groupes as $g) {
            $bus = BusAtelier::find($g['bus_atelier_id']);

            try {
                $bt = $this->sorties->creer($bus, $g['lignes']);
            } catch (RuntimeException $e) {
                $erreurs[] = "{$bus?->immatriculation} : {$e->getMessage()}";
                continue;
            }

            $crees[] = $bt->numero;

            if ($e = $atelier->envoyerBonTransfert($bt->fresh(['bonCommande', 'lignes.product', 'lignes.depot']))) {
                $erreurs[] = "{$bt->numero} créé mais pas envoyé à l'Atelier ({$e}) : renvoyez-le depuis le BT.";
            }
        }

        return redirect()->route('bus.index')
            ->with($crees ? 'success' : 'error', count($crees) . ' bon(s) de transfert créé(s)' . ($crees ? ' : ' . implode(', ', $crees) : '') . '.')
            ->with('erreurs_import', $erreurs);
    }

    /* ------------------------------------------------------------------ */

    private function verifierBtBus(BonTransfert $bt): void
    {
        abort_unless($bt->bonCommande?->estBus(), 404);
    }

    private function validerFormulaire(Request $request, bool $avecBus = true): array
    {
        return $request->validate(array_filter([
            'bus_atelier_id' => $avecBus ? ['required', 'integer', 'exists:bus_atelier,id'] : null,
        ]) + $this->reglesLignes(), ['bus_atelier_id.required' => 'Choisissez le bus.'] + $this->messagesLignes());
    }

    private function donneesFormulaire(?BonTransfert $bt, ?int $busId = null): array
    {
        $bt?->load(['bonCommande.bus', 'lignes.product', 'lignes.depot']);

        return [
            'bt'       => $bt,
            'busListe' => BusAtelier::query()->where('actif', true)->orderBy('groupe')->orderBy('immatriculation')->get(),
            'busId'    => $bt?->bonCommande?->bus_atelier_id ?? $busId,
        ] + $this->donneesPieces($bt);
    }
}
