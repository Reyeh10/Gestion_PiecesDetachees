<?php

namespace App\Http\Controllers;

use App\Exports\InventoryAdjustmentTemplateExport;
use App\Models\Depot;
use App\Models\InventoryAdjustment;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductDepotStock;
use App\Models\Rayon;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InventoryAdjustmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View
    {
        $query = InventoryAdjustment::query()
            ->with([
                'product.brand',
                'product.model',
                'product.rayon',
                'product.location',
                'depot',
                'rayon',
                'location',
                'approver',
            ]);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($productQuery) use ($search) {
                    $productQuery
                        ->where('reference', 'like', '%' . $search . '%')
                        ->orWhere('designation', 'like', '%' . $search . '%');
                })
                ->orWhereHas('depot', function ($depotQuery) use ($search) {
                    $depotQuery
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('code', 'like', '%' . $search . '%');
                })
                ->orWhereHas('rayon', function ($rayonQuery) use ($search) {
                    $rayonQuery->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('location', function ($locationQuery) use ($search) {
                    $locationQuery->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        $adjustments = $query
            ->latest()
            ->paginate(20);

        $adjustments->appends(
            $request->query()
        );

        return view(
            'inventory_adjustments.index',
            compact('adjustments')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE - AJUSTEMENT UNITAIRE
    |--------------------------------------------------------------------------
    */
    public function create(): View
    {
        $products = Product::query()
            ->with([
                'brand',
                'model',
                'rayon',
                'location',
            ])
            ->orderBy('designation')
            ->get();

        $depots = Depot::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'inventory_adjustments.create',
            compact(
                'products',
                'depots'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE - AJUSTEMENT UNITAIRE
    |--------------------------------------------------------------------------
    |
    | Ajustement manuel :
    | - old_qty = products.quantity (quantité disponible actuelle)
    | - new_qty = nouvelle quantité disponible
    | - initial_quantity n'est JAMAIS modifiée
    | - la localisation n'est pas modifiée
    |
    */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'product_id' => [
                    'required',
                    'exists:products,id',
                ],
                'depot_id' => [
                    'required',
                    'exists:depots,id',
                ],
                'new_qty' => [
                    'required',
                    'numeric',
                    'min:0',
                ],
                'reason' => [
                    'required',
                    'string',
                    'max:1000',
                ],
            ],
            [
                'product_id.required' =>
                    'Veuillez sélectionner un produit.',
                'depot_id.required' =>
                    'Veuillez sélectionner un dépôt.',
                'depot_id.exists' =>
                    'Le dépôt sélectionné est invalide.',
                'new_qty.required' =>
                    'Veuillez saisir la nouvelle quantité.',
                'new_qty.min' =>
                    'La quantité ne peut pas être négative.',
                'reason.required' =>
                    'Veuillez préciser la raison.',
            ]
        );

        try {
            DB::transaction(function () use ($request) {
                $product = Product::query()
                    ->where('id', $request->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $depot = Depot::query()
                    ->where('id', $request->depot_id)
                    ->where('is_active', true)
                    ->first();

                if (!$depot) {
                    throw new RuntimeException(
                        'Le dépôt sélectionné est introuvable ou désactivé.'
                    );
                }

                $oldQty = round(
                    (float) $product->quantity,
                    2
                );

                $newQty = round(
                    (float) $request->new_qty,
                    2
                );

                $difference = round(
                    $newQty - $oldQty,
                    2
                );

                $adjustment = InventoryAdjustment::create([
                    'product_id' => $product->id,
                    'depot_id' => $depot->id,
                    'rayon_id' => $product->rayon_id,
                    'location_id' => $product->location_id,
                    'old_qty' => $oldQty,
                    'new_qty' => $newQty,
                    'reason' => trim((string) $request->reason),
                    'approved_by' => auth()->id(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | REGLE METIER : UN PRODUIT = UN SEUL DEPOT COURANT
                |--------------------------------------------------------------------------
                |
                | On verrouille les anciennes lignes, puis on supprime
                | TOUTES les présences actuelles du produit dans les dépôts.
                | Ensuite on recrée uniquement le dépôt sélectionné.
                |
                */
                ProductDepotStock::query()
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->get();

                ProductDepotStock::query()
                    ->where('product_id', $product->id)
                    ->delete();

                ProductDepotStock::create([
                    'product_id' => $product->id,
                    'depot_id' => $depot->id,
                    'quantity' => $newQty,
                ]);

                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'quantity' => $newQty,
                        'updated_at' => now(),
                    ]);

                if (abs($difference) > 0.00001) {
                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => $difference > 0 ? 'in' : 'out',
                        'quantity' => abs($difference),
                        'source' =>
                            'Ajustement inventaire | Dépôt: '
                            . $depot->name,
                        'reference' => 'ADJ-' . $adjustment->id,
                        'user_id' => auth()->id(),
                    ]);
                }
            });

            return redirect()
                ->route('inventory-adjustments.index')
                ->with(
                    'success',
                    'Ajustement inventaire enregistré avec succès.'
                );
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Une erreur est survenue : '
                    . $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT EXCEL - FORMULAIRE
    |--------------------------------------------------------------------------
    |
    | L'utilisateur choisit d'abord le dépôt.
    |
    */
    public function importForm(): View
    {
        $depots = Depot::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'inventory_adjustments.import',
            compact('depots')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT EXCEL - MODELE
    |--------------------------------------------------------------------------
    */
    public function importTemplate(): BinaryFileResponse
    {
        return Excel::download(
            new InventoryAdjustmentTemplateExport(),
            'modele-ajustement-inventaire.xlsx'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT EXCEL - PREVISUALISATION
    |--------------------------------------------------------------------------
    |
    | Colonnes :
    | A Référence           obligatoire
    | B Quantité comptée    obligatoire
    | C Rayon               facultatif
    | D Emplacement         facultatif
    | E Raison              facultatif si raison globale
    |
    | Aucune donnée n'est modifiée ici.
    |
    */
    public function importPreview(Request $request): View|RedirectResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'depot_id' => [
                    'required',
                    'exists:depots,id',
                ],
                'file' => [
                    'required',
                    'file',
                    'mimes:xlsx,xls,csv',
                    'max:10240',
                ],
                'global_reason' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],
            ],
            [
                'depot_id.required' =>
                    'Veuillez sélectionner le dépôt avant l’import.',
                'depot_id.exists' =>
                    'Le dépôt sélectionné est invalide.',
                'file.required' =>
                    'Veuillez sélectionner le fichier Excel.',
                'file.mimes' =>
                    'Le fichier doit être XLSX, XLS ou CSV.',
            ]
        );

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $depot = Depot::query()
            ->where('id', $request->depot_id)
            ->where('is_active', true)
            ->first();

        if (!$depot) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Le dépôt sélectionné est désactivé ou introuvable.'
                );
        }

        try {
            $spreadsheet = IOFactory::load(
                $request->file('file')->getRealPath()
            );

            $sheetRows = $spreadsheet
                ->getActiveSheet()
                ->toArray(
                    null,
                    true,
                    true,
                    false
                );
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Impossible de lire le fichier Excel : '
                    . $e->getMessage()
                );
        }

        if (count($sheetRows) <= 1) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Le fichier ne contient aucune ligne de produit.'
                );
        }

        // Première ligne = en-têtes.
        array_shift($sheetRows);

        $globalReason = trim(
            (string) $request->input('global_reason', '')
        );

        $rows = [];
        $seenReferences = [];
        $excelLine = 1;

        foreach ($sheetRows as $sheetRow) {
            $excelLine++;

            $reference = $this->cellToString(
                $sheetRow[0] ?? null
            );

            $quantityRaw = $this->cellToString(
                $sheetRow[1] ?? null
            );

            $excelRayonName = $this->cellToString(
                $sheetRow[2] ?? null
            );

            $excelLocationName = $this->cellToString(
                $sheetRow[3] ?? null
            );

            $lineReason = $this->cellToString(
                $sheetRow[4] ?? null
            );

            if (
                $reference === ''
                && $quantityRaw === ''
                && $excelRayonName === ''
                && $excelLocationName === ''
                && $lineReason === ''
            ) {
                continue;
            }

            $error = null;
            $warnings = [];

            $product = null;
            $oldAvailableQty = 0.0;
            $newDepotQty = null;
            $difference = null;
            $type = null;

            $currentRayonName = null;
            $currentLocationName = null;

            $locationAction = 'Conserver la localisation actuelle';

            if ($reference === '') {
                $error = 'Référence vide.';
            }

            $referenceKey = mb_strtoupper(
                trim($reference),
                'UTF-8'
            );

            if (
                $error === null
                && isset($seenReferences[$referenceKey])
            ) {
                $error =
                    'Référence en double dans le fichier. '
                    . 'Première apparition à la ligne '
                    . $seenReferences[$referenceKey]
                    . '.';
            }

            if (
                $error === null
                && $referenceKey !== ''
            ) {
                $seenReferences[$referenceKey] = $excelLine;

                $product = Product::query()
                    ->with([
                        'brand',
                        'model',
                        'rayon',
                        'location.rayon',
                    ])
                    ->where('reference', $reference)
                    ->first();

                if (!$product) {
                    $error =
                        'Produit introuvable pour la référence '
                        . $reference
                        . '.';
                }
            }

            /*
            |--------------------------------------------------------------------------
            | QUANTITE
            |--------------------------------------------------------------------------
            */
            $normalizedNumber = $this->normalizeNumber(
                $quantityRaw
            );

            if (
                $error === null
                && $quantityRaw === ''
            ) {
                $error = 'Quantité comptée vide.';
            }

            if (
                $error === null
                && !is_numeric($normalizedNumber)
            ) {
                $error =
                    'Quantité non numérique : '
                    . $quantityRaw
                    . '.';
            }

            if (
                $error === null
                && (float) $normalizedNumber < 0
            ) {
                $error =
                    'La quantité comptée ne peut pas être négative.';
            }

            /*
            |--------------------------------------------------------------------------
            | LOCALISATION EXCEL
            |--------------------------------------------------------------------------
            |
            | Rayon et emplacement sont facultatifs.
            | Si emplacement est renseigné sans rayon : erreur.
            |
            */
            if (
                $error === null
                && $excelLocationName !== ''
                && $excelRayonName === ''
            ) {
                $error =
                    'Le rayon doit être renseigné lorsque l’emplacement est renseigné.';
            }

            /*
            |--------------------------------------------------------------------------
            | RAISON
            |--------------------------------------------------------------------------
            */
            $reason = trim(
                $lineReason !== ''
                    ? $lineReason
                    : $globalReason
            );

            if (
                $error === null
                && $reason === ''
            ) {
                $error =
                    'Aucune raison renseignée pour cette ligne.';
            }

            if ($product) {
                $currentRayonName =
                    $product->rayon?->name;

                $currentLocationName =
                    $product->location?->name;

                if ($excelRayonName !== '') {
                    if ($excelLocationName !== '') {
                        $locationAction =
                            'Mettre à jour le rayon et l’emplacement';
                    } else {
                        if (
                            $currentRayonName === null
                            || mb_strtolower(
                                trim($currentRayonName),
                                'UTF-8'
                            ) !== mb_strtolower(
                                trim($excelRayonName),
                                'UTF-8'
                            )
                        ) {
                            $locationAction =
                                'Mettre à jour le rayon ; emplacement vidé si incompatible';
                        } else {
                            $locationAction =
                                'Rayon déjà identique ; conserver l’emplacement actuel';
                        }
                    }
                }

               /*
                |--------------------------------------------------------------------------
                | QUANTITÉ DISPONIBLE AVANT INVENTAIRE
                |--------------------------------------------------------------------------
                |
                | IMPORTANT :
                | La quantité avant inventaire est products.quantity,
                | c'est-à-dire la quantité disponible actuelle affichée
                | dans la liste des produits.
                |
                | initial_quantity ne doit jamais être utilisée ici.
                |
                */

                $oldAvailableQty = round(
                    (float) $product->quantity,
                    2
                );

                if (is_numeric($normalizedNumber)) {
                    $newDepotQty = round(
                        (float) $normalizedNumber,
                        2
                    );

                    $difference = round(
                        $newDepotQty - $oldAvailableQty,
                        2
                    );

                    $type = $difference > 0
                        ? 'Entrée'
                        : (
                            $difference < 0
                                ? 'Sortie'
                                : 'Aucun écart'
                        );
                }
            }

            $rows[] = [
                'excel_line' => $excelLine,
                'product_id' => $product?->id,
                'reference' => $reference,
                'designation' => $product?->designation,
                'brand_name' => $product?->brand?->name,
                'model_name' => $product?->model?->name,

                'current_rayon_name' =>
                    $currentRayonName,

                'current_location_name' =>
                    $currentLocationName,

                'excel_rayon_name' =>
                    $excelRayonName,

                'excel_location_name' =>
                    $excelLocationName,

                'location_action' =>
                    $locationAction,

                'old_qty' =>
                    $oldAvailableQty,

                'new_qty' =>
                    $newDepotQty,

                'difference' =>
                    $difference,

                'type' =>
                    $type,

                'reason' =>
                    $reason,

                'warnings' =>
                    $warnings,

                'error' =>
                    $error,
            ];
        }

        if (count($rows) === 0) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Le fichier ne contient aucune ligne exploitable.'
                );
        }

        $validCount = collect($rows)
            ->whereNull('error')
            ->count();

        $errorCount = collect($rows)
            ->whereNotNull('error')
            ->count();

        $token = (string) Str::uuid();

        session()->put(
            'inventory_adjustment_imports.' . $token,
            [
                'depot_id' =>
                    $depot->id,

                'rows' =>
                    $rows,

                'created_at' =>
                    now()->timestamp,
            ]
        );

        return view(
            'inventory_adjustments.import_preview',
            compact(
                'token',
                'depot',
                'rows',
                'validCount',
                'errorCount'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT EXCEL - ENREGISTREMENT
    |--------------------------------------------------------------------------
    |
    | 1. La quantité disponible AVANT = products.quantity.
    | 2. La quantité comptée devient la nouvelle products.quantity.
    | 3. initial_quantity n'est JAMAIS modifiée.
    | 4. Le stock du dépôt sélectionné est synchronisé avec la quantité comptée.
    | 5. Rayon/location ne sont modifiés QUE si le fichier les fournit.
    |
    */
    public function importStore(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => [
                'required',
                'string',
            ],
        ]);

        $sessionKey =
            'inventory_adjustment_imports.'
            . $request->token;

        $payload = session()->get(
            $sessionKey
        );

        if (!$payload) {
            return redirect()
                ->route(
                    'inventory-adjustments.import'
                )
                ->with(
                    'error',
                    'La prévisualisation a expiré ou est introuvable.'
                );
        }

        $createdAt = (int) (
            $payload['created_at']
            ?? 0
        );

        if (
            $createdAt <= 0
            || now()->timestamp - $createdAt > 3600
        ) {
            session()->forget(
                $sessionKey
            );

            return redirect()
                ->route(
                    'inventory-adjustments.import'
                )
                ->with(
                    'error',
                    'La prévisualisation a expiré. Veuillez recommencer.'
                );
        }

        $rows =
            $payload['rows']
            ?? [];

        if (
            collect($rows)
                ->whereNotNull('error')
                ->isNotEmpty()
        ) {
            return redirect()
                ->route(
                    'inventory-adjustments.import'
                )
                ->with(
                    'error',
                    'L’import contient encore des erreurs.'
                );
        }

        $depot = Depot::query()
            ->where(
                'id',
                $payload['depot_id']
                    ?? null
            )
            ->where(
                'is_active',
                true
            )
            ->first();

        if (!$depot) {
            return redirect()
                ->route(
                    'inventory-adjustments.import'
                )
                ->with(
                    'error',
                    'Le dépôt sélectionné est introuvable ou désactivé.'
                );
        }

        try {
            DB::transaction(
                function () use (
                    $rows,
                    $depot
                ) {
                    foreach ($rows as $row) {
                        /*
                        |--------------------------------------------------------------------------
                        | PRODUIT EXISTANT
                        |--------------------------------------------------------------------------
                        */
                        $product = Product::query()
                            ->with([
                                'rayon',
                                'location.rayon',
                            ])
                            ->where(
                                'id',
                                $row['product_id']
                            )
                            ->lockForUpdate()
                            ->firstOrFail();

                        /*
                        |--------------------------------------------------------------------------
                        | STOCK DEPOT - DEPOT UNIQUE
                        |--------------------------------------------------------------------------
                        |
                        | REGLE METIER :
                        | Après cet ajustement, ce produit ne doit exister
                        | QUE dans le dépôt sélectionné pour cet import.
                        |
                        | Les anciens ajustements restent dans l'historique,
                        | mais les anciennes lignes product_depot_stocks
                        | sont supprimées.
                        |
                        */

                        /*
                        | Verrouiller toutes les présences actuelles
                        | du produit dans les dépôts.
                        */
                        ProductDepotStock::query()
                            ->where(
                                'product_id',
                                $product->id
                            )
                            ->lockForUpdate()
                            ->get();

                        /*
                        | Supprimer le produit de TOUS les dépôts,
                        | y compris une ancienne ligne du dépôt cible.
                        */
                        ProductDepotStock::query()
                            ->where(
                                'product_id',
                                $product->id
                            )
                            ->delete();

                        /*
                        | Recréer UNE SEULE ligne :
                        | le dernier dépôt sélectionné.
                        |
                        | La quantité réelle sera affectée plus bas
                        | après validation complète de la ligne Excel.
                        */
                        $depotStock =
                            ProductDepotStock::create([
                                'product_id' =>
                                    $product->id,

                                'depot_id' =>
                                    $depot->id,

                                'quantity' =>
                                    0,
                            ]);

                      /*
                        |--------------------------------------------------------------------------
                        | QUANTITÉ DISPONIBLE ACTUELLE
                        |--------------------------------------------------------------------------
                        */

                        $currentDepotQty = round(
                            (float) $product->quantity,
                            2
                        );

                        $previewDepotQty = round(
                            (float) $row['old_qty'],
                            2
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | PROTECTION CONTRE UNE MODIFICATION APRES LA PREVIEW
                        |--------------------------------------------------------------------------
                        */
                        if (
                            abs(
                                $currentDepotQty
                                - $previewDepotQty
                            ) > 0.00001
                        ) {
                            throw new RuntimeException(
                                'La quantité disponible du produit '
                                . $product->reference
                                . ' a changé depuis la prévisualisation. '
                                . 'Veuillez relancer l’import.'
                            );
                        }

                        $newDepotQty = round(
                            (float) $row['new_qty'],
                            2
                        );

                        $difference = round(
                            $newDepotQty
                            - $currentDepotQty,
                            2
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | LOCALISATION
                        |--------------------------------------------------------------------------
                        |
                        | Excel vide :
                        |   => conserver la localisation actuelle.
                        |
                        | Rayon fourni :
                        |   => créer/récupérer le rayon et le mettre à jour.
                        |
                        | Rayon + emplacement fournis :
                        |   => créer/récupérer les deux et mettre à jour les deux.
                        |
                        | Rayon fourni mais emplacement vide :
                        |   => si le rayon change et que l'ancien emplacement
                        |      appartient à un autre rayon, location_id = null.
                        |
                        */
                        $excelRayonName = trim(
                            (string) (
                                $row['excel_rayon_name']
                                ?? ''
                            )
                        );

                        $excelLocationName = trim(
                            (string) (
                                $row['excel_location_name']
                                ?? ''
                            )
                        );

                        $finalRayonId =
                            $product->rayon_id;

                        $finalLocationId =
                            $product->location_id;

                        if ($excelRayonName !== '') {
                            $rayon = Rayon::firstOrCreate([
                                'name' =>
                                    $excelRayonName,
                            ]);

                            $finalRayonId =
                                $rayon->id;

                            if ($excelLocationName !== '') {
                                $location =
                                    Location::firstOrCreate(
                                        [
                                            'name' =>
                                                $excelLocationName,

                                            'rayon_id' =>
                                                $rayon->id,
                                        ]
                                    );

                                $finalLocationId =
                                    $location->id;
                            } else {
                                /*
                                | Si l'ancien emplacement appartient à un autre
                                | rayon, il ne peut pas être conservé.
                                */
                                if (
                                    $product->location
                                    && (int) $product->location->rayon_id
                                        !== (int) $rayon->id
                                ) {
                                    $finalLocationId =
                                        null;
                                }
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | METTRE A JOUR LA LOCALISATION DU PRODUIT
                        |--------------------------------------------------------------------------
                        */
                        $product->rayon_id =
                            $finalRayonId;

                        $product->location_id =
                            $finalLocationId;

                        /*
                        |--------------------------------------------------------------------------
                        | HISTORIQUE AJUSTEMENT
                        |--------------------------------------------------------------------------
                        |
                        | On enregistre la localisation finale après application
                        | éventuelle des données Excel.
                        |
                        */
                        $adjustment =
                            InventoryAdjustment::create([
                                'product_id' =>
                                    $product->id,

                                'depot_id' =>
                                    $depot->id,

                                'rayon_id' =>
                                    $finalRayonId,

                                'location_id' =>
                                    $finalLocationId,

                                'old_qty' =>
                                    $currentDepotQty,

                                'new_qty' =>
                                    $newDepotQty,

                                'reason' =>
                                    trim(
                                        (string) $row['reason']
                                    ),

                                'approved_by' =>
                                    auth()->id(),
                            ]);

                        /*
                        |--------------------------------------------------------------------------
                        | NOUVELLE QUANTITE DU DEPOT
                        |--------------------------------------------------------------------------
                        */
                        $depotStock->quantity =
                            $newDepotQty;

                        $depotStock->save();

                        /*
                        |--------------------------------------------------------------------------
                        | QUANTITÉ DISPONIBLE APRÈS INVENTAIRE
                        |--------------------------------------------------------------------------
                        |
                        | IMPORTANT :
                        |
                        | - products.quantity = quantité disponible actuelle.
                        | - La quantité comptée devient la nouvelle quantité disponible.
                        | - initial_quantity ne doit JAMAIS être modifiée.
                        | - received_quantity ne doit JAMAIS être modifiée.
                        | - les quantités vendues ne doivent JAMAIS être modifiées.
                        |
                        */
                        $product->quantity =
                            $newDepotQty;

                        $product->save();

                        /*
                        |--------------------------------------------------------------------------
                        | MOUVEMENT STOCK
                        |--------------------------------------------------------------------------
                        */
                        if (
                            abs($difference)
                            > 0.00001
                        ) {
                            $finalRayon =
                                $finalRayonId
                                    ? Rayon::find(
                                        $finalRayonId
                                    )
                                    : null;

                            $finalLocation =
                                $finalLocationId
                                    ? Location::find(
                                        $finalLocationId
                                    )
                                    : null;

                            $source =
                                'Ajustement inventaire Excel'
                                . ' | Dépôt: '
                                . $depot->name;

                            if ($finalRayon) {
                                $source .=
                                    ' | Rayon: '
                                    . $finalRayon->name;
                            }

                            if ($finalLocation) {
                                $source .=
                                    ' | Emplacement: '
                                    . $finalLocation->name;
                            }

                            StockMovement::create([
                                'product_id' =>
                                    $product->id,

                                'type' =>
                                    $difference > 0
                                        ? 'in'
                                        : 'out',

                                'quantity' =>
                                    abs($difference),

                                'source' =>
                                    $source,

                                'reference' =>
                                    'ADJ-'
                                    . $adjustment->id,

                                'user_id' =>
                                    auth()->id(),
                            ]);
                        }
                    }
                }
            );

            session()->forget(
                $sessionKey
            );

            return redirect()
                ->route(
                    'inventory-adjustments.index'
                )
                ->with(
                    'success',
                    count($rows)
                    . ' ajustement(s) enregistré(s) avec succès dans le dépôt « '
                    . $depot->name
                    . ' ».'
                );
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route(
                    'inventory-adjustments.import'
                )
                ->with(
                    'error',
                    'L’import a été annulé. Aucune donnée n’a été modifiée. '
                    . $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show(
        InventoryAdjustment $inventoryAdjustment
    ): View {
        $inventoryAdjustment->load([
            'product.brand',
            'product.model',
            'product.rayon',
            'product.location',
            'depot',
            'rayon',
            'location',
            'approver',
        ]);

        return view(
            'inventory_adjustments.show',
            compact(
                'inventoryAdjustment'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    |
    | Seul l'administrateur doit pouvoir atteindre cette méthode via web.php.
    | Le produit de l'ajustement reste inchangé afin de préserver la traçabilité.
    |
    */
    public function edit(
        InventoryAdjustment $inventoryAdjustment
    ): View {
        $inventoryAdjustment->load([
            'product.brand',
            'product.model',
            'product.rayon',
            'product.location',
            'depot',
            'rayon',
            'location',
            'approver',
        ]);

        $products = Product::query()
            ->with([
                'brand',
                'model',
                'rayon',
                'location',
            ])
            ->orderBy('designation')
            ->get();

        $depots = Depot::query()
            ->orderBy('name')
            ->get();

        $rayons = Rayon::query()
            ->orderBy('name')
            ->get();

        $locations = Location::query()
            ->orderBy('name')
            ->get();

        return view(
            'inventory_adjustments.edit',
            compact(
                'inventoryAdjustment',
                'products',
                'depots',
                'rayons',
                'locations'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    |
    | La correction d'un ajustement met à jour l'effet de cet ajustement
    | sur le stock courant, sans toucher à initial_quantity.
    |
    */
    public function update(
        Request $request,
        InventoryAdjustment $inventoryAdjustment
    ): RedirectResponse {
        $request->validate(
            [
                'depot_id' => [
                    'required',
                    'exists:depots,id',
                ],
                'new_qty' => [
                    'required',
                    'numeric',
                    'min:0',
                ],
                'reason' => [
                    'required',
                    'string',
                    'max:1000',
                ],
                'rayon_id' => [
                    'nullable',
                    'exists:rayons,id',
                ],
                'location_id' => [
                    'nullable',
                    'exists:locations,id',
                ],
            ],
            [
                'depot_id.required' =>
                    'Veuillez sélectionner un dépôt.',
                'depot_id.exists' =>
                    'Le dépôt sélectionné est invalide.',
                'new_qty.required' =>
                    'Veuillez saisir la nouvelle quantité.',
                'new_qty.numeric' =>
                    'La nouvelle quantité doit être numérique.',
                'new_qty.min' =>
                    'La quantité ne peut pas être négative.',
                'reason.required' =>
                    'Veuillez préciser la raison.',
                'rayon_id.exists' =>
                    'Le rayon sélectionné est invalide.',
                'location_id.exists' =>
                    'L’emplacement sélectionné est invalide.',
            ]
        );

        try {
            DB::transaction(function () use (
                $request,
                $inventoryAdjustment
            ) {
                $adjustment = InventoryAdjustment::query()
                    ->where('id', $inventoryAdjustment->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $product = Product::query()
                    ->where('id', $adjustment->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $depot = Depot::query()
                    ->where('id', $request->depot_id)
                    ->firstOrFail();

                $historicalOldQty = round(
                    (float) $adjustment->old_qty,
                    2
                );

                $previousNewQty = round(
                    (float) $adjustment->new_qty,
                    2
                );

                $previousDifference = round(
                    $previousNewQty - $historicalOldQty,
                    2
                );

                $newQty = round(
                    (float) $request->new_qty,
                    2
                );

                $newDifference = round(
                    $newQty - $historicalOldQty,
                    2
                );

                $stockCorrection = round(
                    $newDifference - $previousDifference,
                    2
                );

                $currentProductQty = round(
                    (float) $product->quantity,
                    2
                );

                $correctedProductQty = round(
                    $currentProductQty + $stockCorrection,
                    2
                );

                if ($correctedProductQty < 0) {
                    throw new RuntimeException(
                        'La modification rendrait la quantité disponible '
                        . 'du produit négative.'
                    );
                }

                $finalRayonId = $adjustment->rayon_id;
                $finalLocationId = $adjustment->location_id;

                if ($request->exists('rayon_id')) {
                    $finalRayonId = $request->filled('rayon_id')
                        ? (int) $request->rayon_id
                        : null;
                }

                if ($request->exists('location_id')) {
                    $finalLocationId = $request->filled('location_id')
                        ? (int) $request->location_id
                        : null;
                }

                if ($finalLocationId !== null) {
                    $location = Location::query()
                        ->findOrFail($finalLocationId);

                    if (
                        $finalRayonId !== null
                        && (int) $location->rayon_id
                            !== (int) $finalRayonId
                    ) {
                        throw new RuntimeException(
                            'L’emplacement sélectionné n’appartient pas '
                            . 'au rayon sélectionné.'
                        );
                    }

                    if ($finalRayonId === null) {
                        $finalRayonId = $location->rayon_id;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | DEPOT UNIQUE APRES MODIFICATION
                |--------------------------------------------------------------------------
                */
                ProductDepotStock::query()
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->get();

                ProductDepotStock::query()
                    ->where('product_id', $product->id)
                    ->delete();

                ProductDepotStock::create([
                    'product_id' => $product->id,
                    'depot_id' => $depot->id,
                    'quantity' => $newQty,
                ]);

                /*
                | Dans cette règle métier, products.quantity correspond
                | exactement à la quantité du seul dépôt courant.
                */
                $product->quantity = $newQty;

                if (
                    $request->exists('rayon_id')
                    || $request->exists('location_id')
                ) {
                    $product->rayon_id = $finalRayonId;
                    $product->location_id = $finalLocationId;
                }

                $product->save();

                $adjustment->depot_id = $depot->id;
                $adjustment->new_qty = $newQty;
                $adjustment->reason =
                    trim((string) $request->reason);
                $adjustment->rayon_id = $finalRayonId;
                $adjustment->location_id = $finalLocationId;
                $adjustment->approved_by = auth()->id();
                $adjustment->save();

                $movementReference =
                    'ADJ-' . $adjustment->id;

                $movement = StockMovement::query()
                    ->where(
                        'reference',
                        $movementReference
                    )
                    ->first();

                if (abs($newDifference) > 0.00001) {
                    $source = 'Ajustement inventaire corrigé';

                    if ($adjustment->depot_id !== null) {
                        $depotName = Depot::query()
                            ->where(
                                'id',
                                $adjustment->depot_id
                            )
                            ->value('name');

                        if ($depotName) {
                            $source .=
                                ' | Dépôt: ' . $depotName;
                        }
                    }

                    $movementData = [
                        'product_id' => $product->id,
                        'type' => $newDifference > 0
                            ? 'in'
                            : 'out',
                        'quantity' => abs($newDifference),
                        'source' => $source,
                        'reference' => $movementReference,
                        'user_id' => auth()->id(),
                    ];

                    if ($movement) {
                        $movement->update(
                            $movementData
                        );
                    } else {
                        StockMovement::create(
                            $movementData
                        );
                    }
                } elseif ($movement) {
                    $movement->delete();
                }
            });

            return redirect()
                ->route(
                    'inventory-adjustments.index'
                )
                ->with(
                    'success',
                    'Ajustement inventaire modifié avec succès.'
                );
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'La modification a été annulée : '
                    . $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    |
    | La suppression annule l'effet quantitatif de l'ajustement
    | avant de supprimer son historique et son mouvement de stock.
    |
    */
    public function destroy(
        InventoryAdjustment $inventoryAdjustment
    ): RedirectResponse {
        try {
            DB::transaction(function () use (
                $inventoryAdjustment
            ) {
                $adjustment = InventoryAdjustment::query()
                    ->where('id', $inventoryAdjustment->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $product = Product::query()
                    ->where('id', $adjustment->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $difference = round(
                    (float) $adjustment->new_qty
                    - (float) $adjustment->old_qty,
                    2
                );

                $correctedProductQty = round(
                    (float) $product->quantity
                    - $difference,
                    2
                );

                if ($correctedProductQty < 0) {
                    throw new RuntimeException(
                        'La suppression rendrait la quantité disponible '
                        . 'du produit négative.'
                    );
                }

                if ($adjustment->depot_id !== null) {
                    $depotStock = ProductDepotStock::query()
                        ->where(
                            'product_id',
                            $product->id
                        )
                        ->where(
                            'depot_id',
                            $adjustment->depot_id
                        )
                        ->lockForUpdate()
                        ->first();

                    if ($depotStock) {
                        $correctedDepotQty = round(
                            (float) $depotStock->quantity
                            - $difference,
                            2
                        );

                        if ($correctedDepotQty < 0) {
                            throw new RuntimeException(
                                'La suppression rendrait le stock du dépôt négatif.'
                            );
                        }

                        $depotStock->quantity =
                            $correctedDepotQty;

                        $depotStock->save();
                    }
                }

                $product->quantity =
                    $correctedProductQty;

                $product->save();

                StockMovement::query()
                    ->where(
                        'reference',
                        'ADJ-' . $adjustment->id
                    )
                    ->delete();

                $adjustment->delete();
            });

            return redirect()
                ->route(
                    'inventory-adjustments.index'
                )
                ->with(
                    'success',
                    'Ajustement inventaire supprimé avec succès. '
                    . 'Son effet sur le stock a été annulé.'
                );
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route(
                    'inventory-adjustments.index'
                )
                ->with(
                    'error',
                    'La suppression a été annulée : '
                    . $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    /*
    |--------------------------------------------------------------------------
    | FORCE SINGLE DEPOT STOCK
    |--------------------------------------------------------------------------
    |
    | Helper réutilisable si nécessaire :
    | supprime toutes les anciennes présences du produit et conserve
    | uniquement le dépôt fourni.
    |
    */
    private function forceSingleDepotStock(
        int $productId,
        int $depotId,
        float $quantity
    ): void {
        ProductDepotStock::query()
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->get();

        ProductDepotStock::query()
            ->where('product_id', $productId)
            ->delete();

        ProductDepotStock::create([
            'product_id' => $productId,
            'depot_id' => $depotId,
            'quantity' => round($quantity, 2),
        ]);
    }


    private function cellToString(
        mixed $value
    ): string {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value
                ? '1'
                : '0';
        }

        return trim(
            (string) $value
        );
    }

    private function normalizeNumber(
        string $raw
    ): string {
        $value = trim(
            $raw
        );

        $value = str_replace(
            [
                "\u{00A0}",
                "\u{202F}",
                ' ',
            ],
            '',
            $value
        );

        $value = str_replace(
            ',',
            '.',
            $value
        );

        if (
            preg_match(
                '/^[+-]?\d+(?:\.\d+)?/',
                $value,
                $matches
            )
        ) {
            return $matches[0];
        }

        return $value;
    }
}
