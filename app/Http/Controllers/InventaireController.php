<?php

namespace App\Http\Controllers;

use App\Exports\InventaireExport;
use App\Models\Depot;
use App\Models\Inventaire;
use App\Models\InventaireLigne;
use App\Models\InventoryAdjustment;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductDepotStock;
use App\Models\Rayon;
use App\Models\StockMovement;
use App\Services\AjustementStockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Fiches d'inventaire (V6) :
 * 1. on choisit un dépôt et un ou plusieurs rayons (ou un emplacement) ;
 * 2. la fiche s'imprime (triée par emplacement, quantité attendue affichée) ;
 * 3. le comptage se saisit à l'écran ou par Excel (export, remplissage, import) ;
 * 4. après le récapitulatif des écarts, la validation crée les ajustements
 *    d'inventaire « À valider » : écart de quantité et / ou nouveau rayon /
 *    emplacement. Rien ne change (stock, place) avant la validation du chef
 *    magasinier / de l'admin. Une référence erronée est seulement signalée.
 */
class InventaireController extends Controller
{
    public function index()
    {
        $inventaires = Inventaire::with(['depot', 'creePar'])
            ->withCount('lignes')
            ->withCount(['lignes as comptees_count' => fn ($q) => $q->whereNotNull('qte_comptee')])
            ->latest('id')
            ->paginate(25);

        return view('inventaires.index', compact('inventaires'));
    }

    public function create()
    {
        $depots = Depot::query()->where('is_active', true)->orderBy('name')->get();

        // V6 : chaque rayon appartient à un dépôt ; « Non défini » = pièces du dépôt sans rayon
        $rayons = Rayon::query()->whereNotNull('depot_id')->orderBy('name')->get();
        $emplacements = Location::query()->whereIn('rayon_id', $rayons->pluck('id'))->orderBy('name')->get(['id', 'name', 'rayon_id']);
        $sansRayon = ProductDepotStock::query()->whereNull('rayon_id')->whereHas('product')
            ->selectRaw('depot_id, COUNT(*) as n')->groupBy('depot_id')->pluck('n', 'depot_id');

        return view('inventaires.create', compact('depots', 'rayons', 'emplacements', 'sansRayon'));
    }

    /** Rayons d'un dépôt, triés (V6). */
    private function rayonsDuDepot($depotId)
    {
        return Rayon::query()->duDepot($depotId)->orderBy('name')->get();
    }

    /** Emplacements des rayons d'un dépôt, regroupés par rayon : [rayon_id => [[id, nom], …]] (V6). */
    private function emplacementsParRayon($rayons): array
    {
        return Location::query()->whereIn('rayon_id', $rayons->pluck('id'))->orderBy('name')->get(['id', 'name', 'rayon_id'])
            ->groupBy('rayon_id')
            ->map(fn ($liste) => $liste->map(fn ($e) => [$e->id, $e->name])->values())
            ->all();
    }

    /** Crée la fiche : photo du stock du dépôt pour les rayons choisis. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'depot_id'      => ['required', 'integer', 'exists:depots,id'],
            'rayon_ids'     => ['nullable', 'array'],
            'rayon_ids.*'   => ['string', 'regex:/^(\d+|sans)$/'],
            'location_id'   => ['nullable', 'integer', 'exists:locations,id'],
            'avec_zero'     => ['nullable', 'boolean'],
            'compteur'      => ['nullable', 'string', 'max:150'],
        ], [
            'depot_id.required' => 'Choisissez le dépôt.',
        ]);

        // « sans » = pièces sans rayon (« Non défini ») de ce dépôt
        $choix = $validated['rayon_ids'] ?? [];
        $sansRayon = in_array('sans', $choix, true);
        $rayonIds = array_values(array_map('intval', array_filter($choix, fn ($v) => $v !== 'sans')));
        $locationId = $validated['location_id'] ?? null;

        // V6 : seulement des rayons / un emplacement de ce dépôt
        $rayonsDuDepot = $this->rayonsDuDepot($validated['depot_id'])->pluck('id')->all();

        if (array_diff($rayonIds, $rayonsDuDepot)) {
            return back()->withInput()->with('error', 'Un rayon choisi n\'est pas dans ce dépôt.');
        }

        if ($locationId && ! in_array((int) Location::query()->whereKey($locationId)->value('rayon_id'), $rayonsDuDepot, true)) {
            return back()->withInput()->with('error', 'L\'emplacement choisi n\'est pas dans ce dépôt.');
        }

        $stocks = ProductDepotStock::query()
            ->with(['product', 'rayon', 'location'])
            ->where('depot_id', $validated['depot_id'])
            ->when($rayonIds || $sansRayon, fn ($q) => $q->where(function ($q) use ($rayonIds, $sansRayon) {
                $q->when($rayonIds, fn ($q) => $q->whereIn('rayon_id', $rayonIds))
                    ->when($sansRayon, fn ($q) => $q->orWhereNull('rayon_id'));
            }))
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->when(empty($validated['avec_zero']), fn ($q) => $q->where('quantity', '>', 0))
            ->whereHas('product')
            ->get()
            ->sortBy(fn ($s) => sprintf('%s|%s|%s', $s->rayon?->name ?? 'zzz', $s->location?->name ?? 'zzz', $s->product->reference))
            ->values();

        if ($stocks->isEmpty()) {
            return back()->withInput()->with('error', 'Aucune pièce dans ce périmètre (dépôt / rayons / emplacement).');
        }

        $perimetre = $locationId
            ? 'Emplacement ' . Location::find($locationId)?->name
            : ($rayonIds || $sansRayon
                ? 'Rayon(s) ' . Rayon::whereIn('id', $rayonIds)->orderBy('name')->pluck('name')->when($sansRayon, fn ($c) => $c->push('Non défini'))->implode(', ')
                : 'Tout le dépôt');

        $inventaire = DB::transaction(function () use ($validated, $stocks, $perimetre) {
            [$annee, $sequence, $numero] = Inventaire::prochainNumero();

            $inventaire = Inventaire::create([
                'numero'    => $numero,
                'annee'     => $annee,
                'sequence'  => $sequence,
                'depot_id'  => $validated['depot_id'],
                'perimetre' => $perimetre,
                'statut'    => Inventaire::EN_COURS,
                'compteur'  => $validated['compteur'] ?? null,
                'cree_par'  => auth()->id(),
            ]);

            foreach ($stocks as $i => $stock) {
                $inventaire->lignes()->create([
                    'ordre'        => $i + 1,
                    'product_id'   => $stock->product_id,
                    'rayon_id'     => $stock->rayon_id,
                    'location_id'  => $stock->location_id,
                    'reference'    => $stock->product->reference,
                    'designation'  => $stock->product->designation,
                    'qte_attendue' => round((float) $stock->quantity, 2),
                ]);
            }

            return $inventaire;
        });

        return redirect()->route('inventaires.show', $inventaire)
            ->with('success', "Fiche {$inventaire->numero} créée : " . $stocks->count() . ' pièce(s). Imprimez-la pour le comptage.');
    }

    public function show(Inventaire $inventaire)
    {
        $inventaire->load(['depot', 'creePar', 'validePar', 'lignes.product', 'lignes.rayon', 'lignes.location', 'lignes.nouveauRayon', 'lignes.nouvelEmplacement', 'lignes.ajustement']);
        // V6 : seulement les rayons du dépôt de la fiche, et leurs emplacements
        $rayons = $this->rayonsDuDepot($inventaire->depot_id);
        $emplacementsParRayon = $this->emplacementsParRayon($rayons);
        $emplacements = Location::query()->with('rayon')->whereIn('rayon_id', $rayons->pluck('id'))->orderBy('name')->get();

        // Ajustements de la fiche qui attendent encore le chef magasinier / l'admin
        $ajustementsAValider = $inventaire->lignes->pluck('ajustement')->filter(fn ($a) => $a && $a->estEnAttente());
        $peutValiderAjustements = AjustementStockService::peutValider(auth()->user());

        return view('inventaires.show', compact('inventaire', 'rayons', 'emplacements', 'emplacementsParRayon', 'ajustementsAValider', 'peutValiderAjustements'));
    }

    public function pdf(Inventaire $inventaire)
    {
        return $this->genererPdf($inventaire)->download($inventaire->numero . '.pdf');
    }

    public function imprimer(Inventaire $inventaire)
    {
        return $this->genererPdf($inventaire)->stream($inventaire->numero . '.pdf');
    }

    /** Saisie du comptage à l'écran (quantités, rayon/emplacement, signalements). */
    public function saisir(Request $request, Inventaire $inventaire)
    {
        if (! $inventaire->estEnCours()) {
            return back()->with('error', 'Cette fiche est déjà validée : elle ne se modifie plus.');
        }

        $validated = $request->validate([
            'lignes'                          => ['required', 'array'],
            'lignes.*.qte_comptee'            => ['nullable', 'numeric', 'min:0'],
            'lignes.*.ecart'                  => ['nullable', 'numeric'],
            'lignes.*.nouveau_rayon_id'       => ['nullable', 'integer', 'exists:rayons,id'],
            'lignes.*.nouvel_emplacement_id'  => ['nullable', 'integer', 'exists:locations,id'],
            'lignes.*.reference_signalee'     => ['nullable', 'string', 'max:150'],
            'lignes.*.remarque'               => ['nullable', 'string', 'max:500'],
            'compteur'                        => ['nullable', 'string', 'max:150'],
        ]);

        $lignes = $inventaire->lignes()->get()->keyBy('id');

        // Écart saisi sans quantité : compté = attendu + écart
        foreach ($validated['lignes'] as $id => $saisie) {
            $ligne = $lignes->get((int) $id);
            $qte = $saisie['qte_comptee'] ?? null;
            $ecart = $saisie['ecart'] ?? null;

            if ($ligne && ($qte === null || $qte === '') && $ecart !== null && $ecart !== '') {
                $calculee = round((float) $ligne->qte_attendue + (float) $ecart, 2);

                if ($calculee < 0) {
                    return back()->withInput()->with('error', "Pièce {$ligne->reference} : attendu " . (float) $ligne->qte_attendue . ", un écart de {$ecart} donnerait une quantité négative.");
                }

                $validated['lignes'][$id]['qte_comptee'] = $calculee;
            }
        }

        // V6 : nouvelle place = un rayon de ce dépôt, et un emplacement de ce rayon
        if ($erreur = $this->erreurPlace($inventaire, $lignes, $validated['lignes'])) {
            return back()->withInput()->with('error', $erreur);
        }

        DB::transaction(function () use ($validated, $lignes, $inventaire) {
            foreach ($validated['lignes'] as $id => $saisie) {
                if ($ligne = $lignes->get((int) $id)) {
                    $this->appliquerSaisie($ligne, $saisie);
                }
            }

            if (array_key_exists('compteur', $validated)) {
                $inventaire->update(['compteur' => $validated['compteur']]);
            }
        });

        $comptees = $inventaire->lignes()->whereNotNull('qte_comptee')->count();

        return redirect()->route('inventaires.show', $inventaire)
            ->with('success', "Comptage enregistré : {$comptees} / {$lignes->count()} pièce(s) comptée(s).");
    }

    /** Pièce trouvée qui n'était pas sur la fiche (par sa référence). */
    public function ajouterLigne(Request $request, Inventaire $inventaire)
    {
        if (! $inventaire->estEnCours()) {
            return back()->with('error', 'Cette fiche est déjà validée.');
        }

        $validated = $request->validate([
            'reference'   => ['required', 'string', 'max:150'],
            'qte_comptee' => ['required', 'numeric', 'min:0'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
        ], [
            'location_id.required' => 'Indiquez l\'emplacement où la pièce a été trouvée.',
        ]);

        $emplacement = Location::with('rayon')->findOrFail($validated['location_id']);

        if (! $emplacement->rayon?->estDuDepot($inventaire->depot_id)) {
            return back()->withInput()->with('error', "L'emplacement {$emplacement->name} n'est pas dans le dépôt de la fiche.");
        }

        $product = Product::query()->where('reference', trim($validated['reference']))->first();

        if (! $product) {
            return back()->with('error', "Référence « {$validated['reference']} » inconnue au catalogue : notez-la en remarque sur la fiche.");
        }

        if ($inventaire->lignes()->where('product_id', $product->id)->exists()) {
            return back()->with('error', "La pièce {$product->reference} est déjà sur la fiche : saisissez sa quantité dans la liste.");
        }

        $stock = ProductDepotStock::query()->where('product_id', $product->id)->where('depot_id', $inventaire->depot_id)->first();

        $inventaire->lignes()->create([
            'ordre'        => (int) $inventaire->lignes()->max('ordre') + 1,
            'product_id'   => $product->id,
            'rayon_id'     => $stock?->rayon_id,
            'location_id'  => $stock?->location_id,
            'reference'    => $product->reference,
            'designation'  => $product->designation,
            'qte_attendue' => round((float) ($stock?->quantity ?? 0), 2),
            'qte_comptee'  => round((float) $validated['qte_comptee'], 2),
            'hors_liste'   => true,
            // place où la pièce a été trouvée : appliquée au stock à la validation
            'nouveau_rayon_id'      => $emplacement->rayon_id,
            'nouvel_emplacement_id' => $emplacement->id,
        ]);

        return redirect()->route('inventaires.show', $inventaire)
            ->with('success', "Pièce {$product->reference} ajoutée à la fiche (trouvée hors liste).");
    }

    public function exportExcel(Inventaire $inventaire)
    {
        $inventaire->load(['depot', 'lignes.rayon', 'lignes.location', 'lignes.nouveauRayon', 'lignes.nouvelEmplacement']);

        return Excel::download(new InventaireExport($inventaire), $inventaire->numero . '.xlsx');
    }

    /** Fiche Excel remplie : colonnes reconnues par leur titre (voir InventaireExport). */
    public function importExcel(Request $request, Inventaire $inventaire)
    {
        if (! $inventaire->estEnCours()) {
            return back()->with('error', 'Cette fiche est déjà validée.');
        }

        $request->validate([
            'fichier' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], ['fichier.required' => 'Choisissez le fichier Excel rempli.']);

        $feuille = Excel::toArray(new \stdClass(), $request->file('fichier'))[0] ?? [];

        if (count($feuille) < 2) {
            return back()->with('error', 'Le fichier est vide.');
        }

        $entetes = array_map(fn ($t) => mb_strtolower(trim((string) $t)), $feuille[0]);
        $col = fn (string $nom) => array_search($nom, $entetes, true);
        $iLigne = $col('n° ligne');
        $iQte = $col('quantité comptée');

        if ($iLigne === false || $iQte === false) {
            return back()->with('error', 'Ce fichier ne vient pas de la fiche : colonnes « N° ligne » et « Quantité comptée » introuvables.');
        }

        $iRayon = $col('nouveau rayon');
        $iEmpl = $col('nouvel emplacement');
        $iRef = $col('référence signalée');
        $iRem = $col('remarque');

        // V6 : rayons du dépôt de la fiche ; un emplacement se cherche dans son rayon
        $rayons = $this->rayonsDuDepot($inventaire->depot_id)->keyBy(fn ($r) => mb_strtolower(trim($r->name)));
        $emplacementsParRayon = Location::query()->whereIn('rayon_id', $rayons->pluck('id'))->get()
            ->groupBy('rayon_id')->map(fn ($liste) => $liste->keyBy(fn ($l) => mb_strtolower(trim($l->name))));
        $lignes = $inventaire->lignes()->get()->keyBy('id');
        $maj = 0;
        $inconnus = [];

        DB::transaction(function () use ($feuille, $lignes, $iLigne, $iQte, $iRayon, $iEmpl, $iRef, $iRem, $rayons, $emplacementsParRayon, &$maj, &$inconnus) {
            foreach (array_slice($feuille, 1) as $n => $row) {
                $ligne = $lignes->get((int) ($row[$iLigne] ?? 0));

                if (! $ligne) {
                    continue;
                }

                // une case vide n'efface pas ce qui a déjà été saisi à l'écran
                $saisie = [];
                $qte = trim((string) ($row[$iQte] ?? ''));

                if ($qte !== '') {
                    $saisie['qte_comptee'] = $qte;
                }

                $nomRayon = $iRayon !== false ? trim((string) ($row[$iRayon] ?? '')) : '';
                $nomEmpl = $iEmpl !== false ? trim((string) ($row[$iEmpl] ?? '')) : '';
                $rayonId = null;

                if ($nomRayon !== '') {
                    $rayon = $rayons->get(mb_strtolower($nomRayon));
                    $rayon ? $saisie['nouveau_rayon_id'] = $rayonId = $rayon->id : $inconnus[] = "rayon {$nomRayon} (pas dans ce dépôt)";
                }

                if ($nomEmpl !== '' && ($nomRayon === '' || $rayonId)) {
                    $dansRayon = $rayonId ?? $ligne->nouveau_rayon_id ?? $ligne->rayon_id;
                    $trouve = $dansRayon ? $emplacementsParRayon->get($dansRayon)?->get(mb_strtolower($nomEmpl)) : null;
                    $trouve ? $saisie['nouvel_emplacement_id'] = $trouve->id : $inconnus[] = "emplacement {$nomEmpl} (pas dans le rayon)";
                }

                foreach ([[$iRef, 'reference_signalee'], [$iRem, 'remarque']] as [$i, $champ]) {
                    $valeur = $i !== false ? trim((string) ($row[$i] ?? '')) : '';
                    if ($valeur !== '') {
                        $saisie[$champ] = $valeur;
                    }
                }

                if (! $saisie) {
                    continue;
                }

                if (isset($saisie['qte_comptee']) && ! is_numeric(str_replace(',', '.', $saisie['qte_comptee']))) {
                    $inconnus[] = 'quantité « ' . $saisie['qte_comptee'] . ' » (ligne ' . ($n + 2) . ')';
                    unset($saisie['qte_comptee']);
                }

                $this->appliquerSaisie($ligne, $saisie);
                $maj++;
            }
        });

        $message = "Fichier importé : {$maj} ligne(s) mise(s) à jour.";

        if ($inconnus) {
            $message .= ' Valeur(s) non reconnue(s), ignorée(s) : ' . implode(', ', array_unique($inconnus)) . '.';
        }

        return redirect()->route('inventaires.show', $inventaire)->with('success', $message);
    }

    /**
     * Validation de la fiche : un ajustement par pièce comptée avec écart ou
     * changée de place. Stock et place ne changent qu'à la validation de ces
     * ajustements (tout de suite si celui qui valide la fiche est chef / admin).
     */
    public function valider(Inventaire $inventaire)
    {
        if (! $inventaire->estEnCours()) {
            return back()->with('error', 'Cette fiche est déjà validée.');
        }

        try {
            $resultat = DB::transaction(function () use ($inventaire) {
            $inventaire = Inventaire::query()->lockForUpdate()->findOrFail($inventaire->id);
            $inventaire->load(['depot', 'lignes.product']);
            $ajustements = 0;
            $aValider = 0;
            $deplacements = 0;

            foreach ($inventaire->lignes as $ligne) {
                if (! $ligne->estComptee() && ! $ligne->changeDePlace()) {
                    continue;
                }

                // V6 : même règle que tous les ajustements — écart sur le stock
                // ACTUEL du dépôt et nouvelle place, « À valider », appliqués à
                // la validation du chef magasinier / de l'admin
                try {
                    $ajustement = app(AjustementStockService::class)->creer([
                        'product_id'            => $ligne->product_id,
                        'depot_id'              => $inventaire->depot_id,
                        'qte_comptee'           => $ligne->estComptee() ? (float) $ligne->qte_comptee : null,
                        'nouveau_rayon_id'      => $ligne->changeDePlace() ? $ligne->nouveau_rayon_id : null,
                        'nouvel_emplacement_id' => $ligne->changeDePlace() ? $ligne->nouvel_emplacement_id : null,
                        'motif'                 => 'inventaire',
                        'remarque'              => $inventaire->numero . ($ligne->remarque ? ' — ' . $ligne->remarque : ''),
                        'source'                => 'inventaire',
                    ], auth()->user());
                } catch (\RuntimeException $e) {
                    throw new \RuntimeException('Ligne ' . $ligne->ordre . ' (' . $ligne->reference . ') : ' . $e->getMessage(), 0, $e);
                }

                if ($ajustement) {
                    $ligne->update(['inventory_adjustment_id' => $ajustement->id]);
                    $ajustements++;
                    $ajustement->changeDePlace() && $deplacements++;
                    $ajustement->estEnAttente() && $aValider++;
                }
            }

            $inventaire->update([
                'statut'     => Inventaire::VALIDE,
                'valide_par' => auth()->id(),
                'valide_le'  => now(),
            ]);

            return [$ajustements, $deplacements, $aValider];
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', 'La fiche n\'a pas été validée (rien n\'a changé). ' . $e->getMessage());
        }

        [$ajustements, $deplacements, $aValider] = $resultat;

        return redirect()->route('inventaires.show', $inventaire)
            ->with('success', "Inventaire {$inventaire->numero} validé : {$ajustements} ajustement(s) créé(s), dont {$deplacements} changement(s) de rayon / emplacement."
                . ($aValider
                    ? " {$aValider} attendent la validation du chef magasinier / de l'admin : le stock et les places ne changent pas avant."
                    : ' Stock et places mis à jour.'));
    }

    /** V6 : refuse un rayon d'un autre dépôt ou un emplacement hors du rayon (null si tout va bien). */
    private function erreurPlace(Inventaire $inventaire, $lignes, array $saisies): ?string
    {
        $rayons = $this->rayonsDuDepot($inventaire->depot_id)->keyBy('id');

        foreach ($saisies as $id => $saisie) {
            $ligne = $lignes->get((int) $id);
            $rayonId = ! empty($saisie['nouveau_rayon_id']) ? (int) $saisie['nouveau_rayon_id'] : null;
            $emplacementId = ! empty($saisie['nouvel_emplacement_id']) ? (int) $saisie['nouvel_emplacement_id'] : null;

            if (! $ligne || ($rayonId === null && $emplacementId === null)) {
                continue;
            }

            if ($rayonId !== null && ! $rayons->has($rayonId)) {
                return "Ligne {$ligne->ordre} ({$ligne->reference}) : ce rayon n'est pas dans le dépôt {$inventaire->depot->name}.";
            }

            if ($emplacementId !== null) {
                $rayonDeLEmplacement = (int) Location::query()->whereKey($emplacementId)->value('rayon_id');

                // emplacement seul : il entraîne son rayon, qui doit être de ce dépôt
                if ($rayonId === null && ! $rayons->has($rayonDeLEmplacement)) {
                    return "Ligne {$ligne->ordre} ({$ligne->reference}) : cet emplacement n'est pas dans le dépôt {$inventaire->depot->name}.";
                }

                if ($rayonId !== null && $rayonDeLEmplacement !== $rayonId) {
                    return "Ligne {$ligne->ordre} ({$ligne->reference}) : l'emplacement choisi n'est pas dans le rayon {$rayons->get($rayonId)->name}.";
                }
            }
        }

        return null;
    }

    private function appliquerSaisie(InventaireLigne $ligne, array $saisie): void
    {
        $donnees = [];

        if (array_key_exists('qte_comptee', $saisie)) {
            $q = $saisie['qte_comptee'];
            $donnees['qte_comptee'] = ($q === null || $q === '') ? null : round((float) str_replace(',', '.', (string) $q), 2);
        }

        foreach (['nouveau_rayon_id', 'nouvel_emplacement_id'] as $champ) {
            if (array_key_exists($champ, $saisie)) {
                $donnees[$champ] = $saisie[$champ] ?: null;
            }
        }

        foreach (['reference_signalee', 'remarque'] as $champ) {
            if (array_key_exists($champ, $saisie)) {
                $valeur = trim((string) ($saisie[$champ] ?? ''));
                $donnees[$champ] = $valeur === '' ? null : $valeur;
            }
        }

        if ($donnees) {
            $ligne->update($donnees);
        }
    }

    private function genererPdf(Inventaire $inventaire)
    {
        $inventaire->load(['depot', 'creePar', 'lignes.rayon', 'lignes.location']);

        return Pdf::loadView('inventaires.pdf', compact('inventaire'))->setPaper('a4', 'portrait');
    }
}
