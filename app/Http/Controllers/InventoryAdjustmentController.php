<?php

namespace App\Http\Controllers;

use App\Exports\InventoryAdjustmentTemplateExport;
use App\Models\Depot;
use App\Models\InventoryAdjustment;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductDepotStock;
use App\Models\Rayon;
use App\Services\AjustementStockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Ajustements d'inventaire (V6).
 *
 * Toutes les saisies (écran, import Excel, fiche d'inventaire, correction)
 * passent par AjustementStockService : un produit dans un dépôt, écart
 * « À valider », appliqué au stock à la validation du chef magasinier / de
 * l'admin. Plus de modification ni de suppression : « Corriger » crée un
 * contre-ajustement.
 */
class InventoryAdjustmentController extends Controller
{
    public function __construct(private AjustementStockService $ajustements)
    {
    }

    /*
    |--------------------------------------------------------------------------
    | LISTE
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View
    {
        $query = InventoryAdjustment::query()
            ->with(['product', 'depot', 'rayon', 'location', 'nouveauRayon', 'nouvelEmplacement', 'approver', 'creator', 'refuseur']);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->whereHas('product', fn ($p) => $p->where('reference', 'like', "%{$search}%")->orWhere('designation', 'like', "%{$search}%"))
                    ->orWhere('reason', 'like', "%{$search}%");

                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        foreach (['statut', 'motif', 'source', 'depot_id'] as $filtre) {
            if ($request->filled($filtre)) {
                $query->where($filtre, $request->input($filtre));
            }
        }

        if ($request->filled('du')) {
            $query->whereDate('created_at', '>=', $request->du);
        }

        if ($request->filled('au')) {
            $query->whereDate('created_at', '<=', $request->au);
        }

        // valeur des écarts validés du filtre (sorties négatives)
        $totaux = (clone $query)->where('statut', InventoryAdjustment::VALIDE)
            ->selectRaw('SUM(CASE WHEN valeur_ecart > 0 THEN valeur_ecart ELSE 0 END) entrees, SUM(CASE WHEN valeur_ecart < 0 THEN valeur_ecart ELSE 0 END) sorties')
            ->reorder()
            ->first();

        $adjustments = $query->latest('id')->paginate(25)->withQueryString();
        $nombreAValider = AjustementStockService::nombreAValider();
        $peutValider = AjustementStockService::peutValider($request->user());
        $depots = Depot::query()->orderBy('name')->get();

        return view('inventory_adjustments.index', compact('adjustments', 'nombreAValider', 'peutValider', 'depots', 'totaux'));
    }

    /*
    |--------------------------------------------------------------------------
    | SAISIE À L'ÉCRAN
    |--------------------------------------------------------------------------
    */
    public function create(): View
    {
        $depots = Depot::query()->where('is_active', true)->orderBy('name')->get();

        $products = Product::query()
            ->with(['brand', 'model', 'rayon', 'location'])
            ->orderBy('designation')
            ->get();

        // [depot_id => [product_id => quantité]] pour le JavaScript du formulaire
        $depotStocks = ProductDepotStock::query()
            ->select(['depot_id', 'product_id', 'quantity'])
            ->get()
            ->groupBy('depot_id')
            ->map(fn ($stocks) => $stocks->keyBy('product_id')->map(fn ($s) => round((float) $s->quantity, 2)));

        return view('inventory_adjustments.create', compact('products', 'depots', 'depotStocks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'depot_id'   => ['required', Rule::exists('depots', 'id')->where('is_active', true)],
            'product_id' => ['required', 'exists:products,id'],
            'new_qty'    => ['required', 'numeric', 'min:0'],
            'motif'      => ['required', Rule::in(array_keys(InventoryAdjustment::MOTIFS))],
            'reason'     => ['nullable', 'string', 'max:1000'],
        ], [
            'depot_id.required'   => 'Veuillez sélectionner un dépôt.',
            'depot_id.exists'     => 'Le dépôt sélectionné est introuvable ou désactivé.',
            'product_id.required' => 'Veuillez sélectionner un produit.',
            'new_qty.required'    => 'Veuillez saisir la quantité comptée.',
            'new_qty.min'         => 'La quantité ne peut pas être négative.',
            'motif.required'      => 'Choisissez le motif de l\'ajustement.',
        ]);

        try {
            $ajustement = $this->ajustements->creer([
                'product_id'  => (int) $request->product_id,
                'depot_id'    => (int) $request->depot_id,
                'qte_comptee' => (float) $request->new_qty,
                'motif'       => $request->motif,
                'remarque'    => (string) $request->reason,
                'source'      => 'manuel',
            ], $request->user());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if (! $ajustement) {
            return back()->withInput()->with('error', 'La quantité comptée est égale au stock du dépôt : aucun ajustement à faire.');
        }

        return redirect()->route('inventory-adjustments.show', $ajustement)->with('success', $this->messageCree($ajustement));
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT EXCEL
    |--------------------------------------------------------------------------
    */
    public function importForm(): View
    {
        $depots = Depot::query()->where('is_active', true)->orderBy('name')->get();

        return view('inventory_adjustments.import', compact('depots'));
    }

    public function importTemplate(): BinaryFileResponse
    {
        return Excel::download(new InventoryAdjustmentTemplateExport(), 'modele-ajustement-inventaire.xlsx');
    }

    /**
     * Prévisualisation (rien n'est enregistré).
     * Colonnes : A Référence, B Quantité comptée, C Rayon, D Emplacement, E Remarque.
     * L'écart se calcule sur le stock DU DÉPÔT choisi ; une ligne sans écart est ignorée.
     */
    public function importPreview(Request $request): View|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'depot_id'      => ['required', Rule::exists('depots', 'id')->where('is_active', true)],
            'file'          => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'motif'         => ['required', Rule::in(array_keys(InventoryAdjustment::MOTIFS))],
            'global_reason' => ['nullable', 'string', 'max:1000'],
        ], [
            'depot_id.required' => 'Veuillez sélectionner le dépôt avant l’import.',
            'depot_id.exists'   => 'Le dépôt sélectionné est désactivé ou introuvable.',
            'file.required'     => 'Veuillez sélectionner le fichier Excel.',
            'file.mimes'        => 'Le fichier doit être XLSX, XLS ou CSV.',
            'motif.required'    => 'Choisissez le motif des ajustements.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $depot = Depot::findOrFail($request->depot_id);
        $motif = (string) $request->motif;

        try {
            $sheetRows = IOFactory::load($request->file('file')->getRealPath())->getActiveSheet()->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Impossible de lire le fichier Excel : ' . $e->getMessage());
        }

        if (count($sheetRows) <= 1) {
            return back()->withInput()->with('error', 'Le fichier ne contient aucune ligne de produit.');
        }

        array_shift($sheetRows); // en-têtes

        $globalReason = trim((string) $request->input('global_reason', ''));

        $enAttente = InventoryAdjustment::query()
            ->where('depot_id', $depot->id)
            ->where('statut', InventoryAdjustment::EN_ATTENTE)
            ->pluck('id', 'product_id');

        $rows = [];
        $seenReferences = [];
        $excelLine = 1;

        foreach ($sheetRows as $sheetRow) {
            $excelLine++;

            $reference = $this->cellToString($sheetRow[0] ?? null);
            $quantityRaw = $this->cellToString($sheetRow[1] ?? null);
            $excelRayonName = $this->cellToString($sheetRow[2] ?? null);
            $excelLocationName = $this->cellToString($sheetRow[3] ?? null);
            $lineReason = $this->cellToString($sheetRow[4] ?? null);

            if ($reference === '' && $quantityRaw === '' && $excelRayonName === '' && $excelLocationName === '' && $lineReason === '') {
                continue;
            }

            $error = null;
            $product = null;
            $stock = null;
            $oldQty = 0.0;
            $newQty = null;
            $difference = null;
            $type = null;
            $changePlace = false;
            $locationAction = 'Conserver la localisation actuelle';
            $referenceKey = mb_strtoupper($reference, 'UTF-8');

            if ($reference === '') {
                $error = 'Référence vide.';
            } elseif (isset($seenReferences[$referenceKey])) {
                $error = 'Référence en double dans le fichier. Première apparition à la ligne ' . $seenReferences[$referenceKey] . '.';
            } else {
                $seenReferences[$referenceKey] = $excelLine;
                $product = Product::query()->with(['brand', 'model'])->where('reference', $reference)->first();

                if (! $product) {
                    $error = 'Produit introuvable pour la référence ' . $reference . '.';
                }
            }

            $normalizedNumber = $this->normalizeNumber($quantityRaw);

            if ($error === null && $quantityRaw === '') {
                $error = 'Quantité comptée vide.';
            } elseif ($error === null && ! is_numeric($normalizedNumber)) {
                $error = 'Quantité non numérique : ' . $quantityRaw . '.';
            } elseif ($error === null && (float) $normalizedNumber < 0) {
                $error = 'La quantité comptée ne peut pas être négative.';
            }

            // « Non défini » dans l'Excel = pas de changement de place
            if (Rayon::estNomVide($excelRayonName)) {
                $excelRayonName = '';
            }

            if (Rayon::estNomVide($excelLocationName)) {
                $excelLocationName = '';
            }

            if ($error === null && $excelLocationName !== '' && $excelRayonName === '') {
                $error = 'Le rayon doit être renseigné lorsque l’emplacement est renseigné.';
            }

            // V6 : un rayon appartient à un seul dépôt
            if ($error === null && $excelRayonName !== '') {
                $rayonExcel = Rayon::query()->with('depot')->where('name', $excelRayonName)->first();

                if ($rayonExcel && $rayonExcel->depot_id !== null && ! $rayonExcel->estDuDepot($depot->id)) {
                    $error = "Le rayon {$rayonExcel->name} est dans le dépôt " . ($rayonExcel->depot?->name ?? '?') . ", pas dans {$depot->name}.";
                }
            }

            $remarque = $lineReason !== '' ? $lineReason : $globalReason;

            if ($error === null && $motif === 'autre' && $remarque === '') {
                $error = 'Motif « Autre » : remarque obligatoire (colonne E ou remarque globale).';
            }

            if ($product) {
                $stock = ProductDepotStock::query()->with(['rayon', 'location'])
                    ->where('product_id', $product->id)->where('depot_id', $depot->id)->first();

                $oldQty = round((float) ($stock?->quantity ?? 0), 2);

                if ($excelRayonName !== '') {
                    $locationAction = $excelLocationName !== ''
                        ? 'Mettre à jour le rayon et l’emplacement'
                        : (mb_strtolower(trim((string) $stock?->rayon?->name), 'UTF-8') === mb_strtolower($excelRayonName, 'UTF-8')
                            ? 'Rayon déjà identique ; conserver l’emplacement actuel'
                            : 'Mettre à jour le rayon ; emplacement vidé si incompatible');
                }

                if (is_numeric($normalizedNumber)) {
                    $newQty = round((float) $normalizedNumber, 2);
                    $difference = round($newQty - $oldQty, 2);
                    $type = $difference > 0 ? 'Entrée' : ($difference < 0 ? 'Sortie' : 'Aucun écart');
                }

                $memeNom = fn ($a, $b) => mb_strtolower(trim((string) $a), 'UTF-8') === mb_strtolower(trim((string) $b), 'UTF-8');
                $changePlace = $excelRayonName !== ''
                    && (! $memeNom($stock?->rayon?->name, $excelRayonName)
                        || ($excelLocationName !== '' && ! $memeNom($stock?->location?->name, $excelLocationName)));

                if ($changePlace && $difference !== null && abs($difference) < 0.01) {
                    $type = 'Place seulement';
                }

                if ($error === null && $difference !== null && (abs($difference) >= 0.01 || $changePlace) && $enAttente->has($product->id)) {
                    $error = 'Un ajustement de cette pièce est déjà à valider dans ce dépôt (n° ' . $enAttente[$product->id] . ') : validez-le ou annulez-le d’abord.';
                }
            }

            $rows[] = [
                'excel_line'            => $excelLine,
                'product_id'            => $product?->id,
                'reference'             => $reference,
                'designation'           => $product?->designation,
                'brand_name'            => $product?->brand?->name,
                'model_name'            => $product?->model?->name,
                'current_rayon_name'    => $stock?->rayon?->name,
                'current_location_name' => $stock?->location?->name,
                'excel_rayon_name'      => $excelRayonName,
                'excel_location_name'   => $excelLocationName,
                'location_action'       => $locationAction,
                'change_place'          => $changePlace,
                'old_qty'               => $oldQty,
                'new_qty'               => $newQty,
                'difference'            => $difference,
                'valeur'                => $difference !== null && $product ? round($difference * (float) $product->purchase_price, 2) : null,
                'type'                  => $type,
                'reason'                => $remarque,
                'error'                 => $error,
            ];
        }

        if (count($rows) === 0) {
            return back()->withInput()->with('error', 'Le fichier ne contient aucune ligne exploitable.');
        }

        $collection = collect($rows);
        $validCount = $collection->whereNull('error')->count();
        $errorCount = $collection->whereNotNull('error')->count();
        $ecartCount = $collection->whereNull('error')->filter(fn ($r) => abs((float) $r['difference']) >= 0.01 || ! empty($r['change_place']))->count();

        $token = (string) Str::uuid();

        session()->put('inventory_adjustment_imports.' . $token, [
            'depot_id'   => $depot->id,
            'motif'      => $motif,
            'rows'       => $rows,
            'created_at' => now()->timestamp,
        ]);

        return view('inventory_adjustments.import_preview', compact('token', 'depot', 'rows', 'validCount', 'errorCount', 'ecartCount', 'motif'));
    }

    /**
     * Enregistrement : rayon / emplacement mis à jour sur le stock du dépôt,
     * un ajustement « À valider » par ligne avec écart. Les autres dépôts ne
     * sont jamais touchés.
     */
    public function importStore(Request $request): RedirectResponse
    {
        $request->validate(['token' => ['required', 'string']]);

        $sessionKey = 'inventory_adjustment_imports.' . $request->token;
        $payload = session()->get($sessionKey);

        if (! $payload || now()->timestamp - (int) ($payload['created_at'] ?? 0) > 3600) {
            session()->forget($sessionKey);

            return redirect()->route('inventory-adjustments.import')->with('error', 'La prévisualisation a expiré. Veuillez recommencer.');
        }

        $rows = $payload['rows'] ?? [];

        if (collect($rows)->whereNotNull('error')->isNotEmpty()) {
            return redirect()->route('inventory-adjustments.import')->with('error', 'L’import contient encore des erreurs.');
        }

        $depot = Depot::query()->where('id', $payload['depot_id'] ?? null)->where('is_active', true)->first();

        if (! $depot) {
            return redirect()->route('inventory-adjustments.import')->with('error', 'Le dépôt sélectionné est introuvable ou désactivé.');
        }

        $user = $request->user();

        try {
            [$crees, $valides, $deplaces] = DB::transaction(function () use ($rows, $depot, $payload, $user) {
                $crees = 0;
                $valides = 0;
                $deplaces = 0;

                foreach ($rows as $row) {
                    $product = Product::query()->lockForUpdate()->findOrFail($row['product_id']);

                    $stock = ProductDepotStock::query()
                        ->where('product_id', $product->id)
                        ->where('depot_id', $depot->id)
                        ->lockForUpdate()
                        ->first();

                    $actuel = round((float) ($stock?->quantity ?? 0), 2);

                    if (abs($actuel - (float) $row['old_qty']) > 0.00001) {
                        throw new RuntimeException("Le stock de {$product->reference} dans ce dépôt a changé depuis la prévisualisation ({$row['old_qty']} → {$actuel}). Relancez l’import.");
                    }

                    [$rayonId, $emplacementId] = $this->placeExcel($row, $depot);

                    $ajustement = $this->ajustements->creer([
                        'product_id'            => $product->id,
                        'depot_id'              => $depot->id,
                        'qte_comptee'           => (float) $row['new_qty'],
                        'nouveau_rayon_id'      => $rayonId,
                        'nouvel_emplacement_id' => $emplacementId,
                        'motif'                 => $payload['motif'] ?? 'inventaire',
                        'remarque'              => (string) $row['reason'],
                        'source'                => 'import',
                    ], $user);

                    if ($ajustement) {
                        $crees++;
                        $ajustement->estValide() && $valides++;
                        $ajustement->changeDePlace() && $deplaces++;
                    }
                }

                return [$crees, $valides, $deplaces];
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('inventory-adjustments.import')
                ->with('error', 'L’import a été annulé. Aucune donnée n’a été modifiée. ' . $e->getMessage());
        }

        session()->forget($sessionKey);

        $message = "Dépôt « {$depot->name} » : {$crees} ajustement(s) " . ($valides === $crees ? 'enregistré(s) et validé(s)' : 'enregistré(s), à valider par le chef magasinier / l’admin')
            . ($deplaces ? ", dont {$deplaces} changement(s) de rayon / emplacement" : '')
            . '. ' . (count($rows) - $crees) . ' ligne(s) sans écart ni changement de place ignorée(s).';

        return redirect()->route('inventory-adjustments.index', $valides === $crees ? [] : ['statut' => InventoryAdjustment::EN_ATTENTE])
            ->with('success', $message);
    }

    /*
    |--------------------------------------------------------------------------
    | FICHE
    |--------------------------------------------------------------------------
    */
    public function show(Request $request, InventoryAdjustment $inventoryAdjustment): View
    {
        $inventoryAdjustment->load(['product', 'depot', 'rayon', 'location', 'nouveauRayon', 'nouvelEmplacement', 'approver', 'creator', 'refuseur', 'corrige', 'corrections.creator']);

        $peutValider = AjustementStockService::peutValider($request->user());
        $peutAnnuler = $inventoryAdjustment->estEnAttente()
            && ($peutValider || (int) $inventoryAdjustment->created_by === (int) $request->user()->id);
        $peutCorriger = $inventoryAdjustment->estValide()
            && $inventoryAdjustment->depot_id !== null
            && $request->user()->hasPermission('gerer_ajustements');

        return view('inventory_adjustments.show', [
            'ajustement'   => $inventoryAdjustment,
            'peutValider'  => $peutValider,
            'peutAnnuler'  => $peutAnnuler,
            'peutCorriger' => $peutCorriger,
            // V6 : seulement les rayons du dépôt de l'ajustement ; emplacements groupés par rayon (id)
            'rayons'       => $rayons = $peutCorriger
                ? Rayon::query()->duDepot($inventoryAdjustment->depot_id)->orderBy('name')->get(['id', 'name'])
                : collect(),
            'emplacementsParRayon' => $peutCorriger
                ? Location::query()->whereIn('rayon_id', $rayons->pluck('id'))->orderBy('name')->get(['id', 'name', 'rayon_id'])
                    ->groupBy('rayon_id')->map(fn ($liste) => $liste->map(fn ($e) => [$e->id, $e->name])->values())->all()
                : [],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATION / REFUS / ANNULATION
    |--------------------------------------------------------------------------
    */
    public function valider(Request $request, InventoryAdjustment $inventoryAdjustment): RedirectResponse
    {
        try {
            $this->ajustements->valider($inventoryAdjustment, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Ajustement n° {$inventoryAdjustment->id} validé : le stock du dépôt est mis à jour.");
    }

    /** Validation de plusieurs ajustements cochés dans la liste. */
    public function validerLot(Request $request): RedirectResponse
    {
        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']], ['ids.required' => 'Cochez au moins un ajustement.']);

        $valides = 0;
        $erreurs = [];

        foreach (InventoryAdjustment::query()->whereIn('id', $request->ids)->orderBy('id')->get() as $ajustement) {
            try {
                $this->ajustements->valider($ajustement, $request->user());
                $valides++;
            } catch (RuntimeException $e) {
                $erreurs[] = $e->getMessage();
            }
        }

        $retour = back()->with('success', "{$valides} ajustement(s) validé(s).");

        return $erreurs ? $retour->with('error', count($erreurs) . ' non validé(s) : ' . implode(' | ', array_slice($erreurs, 0, 5))) : $retour;
    }

    public function refuser(Request $request, InventoryAdjustment $inventoryAdjustment): RedirectResponse
    {
        $request->validate(['motif_refus' => ['required', 'string', 'max:500']], ['motif_refus.required' => 'Indiquez pourquoi l’ajustement est refusé.']);

        try {
            $this->ajustements->refuser($inventoryAdjustment, $request->user(), $request->motif_refus);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Ajustement n° {$inventoryAdjustment->id} refusé : le stock n’a pas changé.");
    }

    public function annuler(Request $request, InventoryAdjustment $inventoryAdjustment): RedirectResponse
    {
        try {
            $this->ajustements->annuler($inventoryAdjustment, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Ajustement n° {$inventoryAdjustment->id} annulé : le stock n’a pas changé.");
    }

    /*
    |--------------------------------------------------------------------------
    | CORRECTION (contre-ajustement)
    |--------------------------------------------------------------------------
    */
    public function corriger(Request $request, InventoryAdjustment $inventoryAdjustment): RedirectResponse
    {
        $donnees = $request->validate([
            'qte_correcte'          => ['nullable', 'numeric', 'min:0'],
            'ecart_correct'         => ['nullable', 'numeric'],
            'nouveau_rayon_id'      => ['nullable', 'integer', 'exists:rayons,id'],
            'nouvel_emplacement_id' => ['nullable', 'integer', 'exists:locations,id'],
            'remarque'              => ['nullable', 'string', 'max:500'],
        ]);

        if (($donnees['qte_correcte'] ?? null) === null && ($donnees['ecart_correct'] ?? null) === null
            && empty($donnees['nouveau_rayon_id']) && empty($donnees['nouvel_emplacement_id'])) {
            return back()->withInput()->with('error', 'Saisissez la quantité correcte, l’écart correct ou la bonne place.');
        }

        try {
            $correction = $this->ajustements->corriger($inventoryAdjustment, $donnees, $request->user());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('inventory-adjustments.show', $correction)->with('success', $this->messageCree($correction));
    }

    /*
    |--------------------------------------------------------------------------
    | OUTILS
    |--------------------------------------------------------------------------
    */
    private function messageCree(InventoryAdjustment $ajustement): string
    {
        return $ajustement->estValide()
            ? "Ajustement n° {$ajustement->id} enregistré et validé : le stock du dépôt est mis à jour."
            : "Ajustement n° {$ajustement->id} enregistré : il attend la validation du chef magasinier / de l’admin (le stock ne change pas avant).";
    }

    /**
     * Rayon / emplacement fournis par l'Excel → [rayon_id, emplacement_id]
     * demandés pour l'ajustement (créés s'ils n'existent pas). La place n'est
     * appliquée au stock du dépôt qu'à la validation de l'ajustement ; un
     * emplacement qui n'est pas dans le nouveau rayon est alors vidé.
     */
    private function placeExcel(array $row, Depot $depot): array
    {
        $rayonNom = trim((string) ($row['excel_rayon_name'] ?? ''));
        $emplacementNom = trim((string) ($row['excel_location_name'] ?? ''));

        if ($rayonNom === '' || empty($row['change_place'])) {
            return [null, null];
        }

        // V6 : rayon de ce dépôt (créé dans ce dépôt s'il n'existe pas)
        [$rayon, $erreur] = Rayon::pourDepot($rayonNom, $depot->id);

        if ($erreur) {
            throw new RuntimeException("Ligne {$row['excel_line']} ({$row['reference']}) : {$erreur}.");
        }

        return [$rayon?->id, Location::pourRayon($emplacementNom, $rayon)?->id];
    }

    private function cellToString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return trim((string) $value);
    }

    private function normalizeNumber(string $raw): string
    {
        $value = str_replace(["\u{00A0}", "\u{202F}", ' '], '', trim($raw));
        $value = str_replace(',', '.', $value);

        if (preg_match('/^[+-]?\d+(?:\.\d+)?/', $value, $matches)) {
            return $matches[0];
        }

        return $value;
    }
}
