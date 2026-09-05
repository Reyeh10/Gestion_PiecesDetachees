<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductDepotStock;
use App\Models\Proforma;
use App\Models\ProformaItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class ProformaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTE DES PROFORMAS
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | PARAMÈTRES DE RECHERCHE
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        $status = trim(
            (string) $request->input(
                'status',
                ''
            )
        );


        /*
        |--------------------------------------------------------------------------
        | REQUÊTE DE BASE
        |--------------------------------------------------------------------------
        */

        $query = Proforma::query()
            ->with([
                'customer',
                'vehicle',
                'creator',
                'sale',
            ]);


        /*
        |--------------------------------------------------------------------------
        | RECHERCHE
        |--------------------------------------------------------------------------
        |
        | Accepte :
        | PROFORMA-000001
        | PROFORMA000001
        | 000001
        | 1
        |
        | Et fonctionne même si proforma_number est NULL pour un ancien
        | enregistrement.
        |
        */

        if ($search !== '') {

            $query->where(
                function ($q) use ($search) {

                    $proformaId = null;

                    if (
                        preg_match(
                            '/^PROFORMA[-\s]?0*(\d+)$/i',
                            $search,
                            $matches
                        )
                    ) {
                        $proformaId = (int) $matches[1];

                    } elseif (
                        ctype_digit($search)
                    ) {
                        $proformaId = (int) $search;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | ID / NUMÉRO PROFORMA
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $proformaId !== null
                        && $proformaId > 0
                    ) {

                        $q->where(
                            'id',
                            $proformaId
                        );

                        $q->orWhere(
                            'proforma_number',
                            'like',
                            '%' . $search . '%'
                        );

                    } else {

                        $q->where(
                            'proforma_number',
                            'like',
                            '%' . $search . '%'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CLIENT
                    |--------------------------------------------------------------------------
                    */

                    $q->orWhereHas(
                        'customer',
                        function ($customerQuery) use ($search) {

                            $customerQuery
                                ->where(
                                    'name',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'phone',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    '%' . $search . '%'
                                );
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | VÉHICULE
                    |--------------------------------------------------------------------------
                    */

                    $q->orWhereHas(
                        'vehicle',
                        function ($vehicleQuery) use ($search) {

                            $vehicleQuery
                                ->where(
                                    'plate_number',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'vin',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'brand',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'model',
                                    'like',
                                    '%' . $search . '%'
                                );
                        }
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRE STATUT
        |--------------------------------------------------------------------------
        */

        if ($status !== '') {

            $query->where(
                'status',
                $status
            );
        }


        /*
        |--------------------------------------------------------------------------
        | LISTE
        |--------------------------------------------------------------------------
        */

        $proformas = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();


        return view(
            'proformas.index',
            compact(
                'proformas',
                'search',
                'status'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FORMULAIRE DE CRÉATION
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        /*
        |--------------------------------------------------------------------------
        | PRODUITS DISPONIBLES DANS AU MOINS UN DÉPÔT
        |--------------------------------------------------------------------------
        |
        | La source réelle du stock est product_depot_stocks.
        | Un produit peut être présent dans plusieurs dépôts.
        |
        */
        $products = Product::query()
            ->with([
                'brand',
                'model',
                'depotStocks' => function ($query) {
                    $query
                        ->where('quantity', '>', 0)
                        ->orderBy('depot_id');
                },
                'depotStocks.depot',
            ])
            ->whereHas(
                'depotStocks',
                function ($query) {
                    $query->where(
                        'quantity',
                        '>',
                        0
                    );
                }
            )
            ->where(
                'status',
                '!=',
                'vendu'
            )
            ->orderBy('designation')
            ->get();

        $customers = Customer::query()
            ->orderBy('name')
            ->get();

        return view(
            'proformas.create',
            compact(
                'products',
                'customers'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FORMULAIRE DE CRÉATION AVEC VÉHICULE
    |--------------------------------------------------------------------------
    */

    public function createWithVehicle(
        Vehicle $vehicle
    ): View|RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | PRODUITS DISPONIBLES DANS AU MOINS UN DÉPÔT
        |--------------------------------------------------------------------------
        */
        $products = Product::query()
            ->with([
                'brand',
                'model',
                'depotStocks' => function ($query) {
                    $query
                        ->where('quantity', '>', 0)
                        ->orderBy('depot_id');
                },
                'depotStocks.depot',
            ])
            ->whereHas(
                'depotStocks',
                function ($query) {
                    $query->where(
                        'quantity',
                        '>',
                        0
                    );
                }
            )
            ->where(
                'status',
                '!=',
                'vendu'
            )
            ->orderBy('designation')
            ->get();

        $customers = Customer::query()
            ->orderBy('name')
            ->get();

        return view(
            'proformas.create',
            [
                'products' =>
                    $products,

                'customers' =>
                    $customers,

                'selectedVehicle' =>
                    $vehicle,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VÉHICULES DU CLIENT
    |--------------------------------------------------------------------------
    */

    public function vehiclesByCustomer(
        Customer $customer
    ): JsonResponse {
        $vehicles = Vehicle::query()
            ->where('customer_id', $customer->id)
            ->orderBy('plate_number')
            ->get([
                'id',
                'customer_id',
                'plate_number',
                'brand',
                'model',
            ])
            ->map(function (Vehicle $vehicle): array {
                $description = trim(
                    implode(
                        ' ',
                        array_filter([
                            $vehicle->brand,
                            $vehicle->model,
                        ])
                    )
                );

                return [
                    'id' => $vehicle->id,
                    'customer_id' => $vehicle->customer_id,
                    'plate_number' => $vehicle->plate_number,
                    'brand' => $vehicle->brand,
                    'model' => $vehicle->model,
                    'label' => $description !== ''
                        ? $vehicle->plate_number . ' - ' . $description
                        : $vehicle->plate_number,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'vehicles' => $vehicles,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ENREGISTRER LE PROFORMA
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate(
            [
                'customer_id' => [
                    'required',
                    'integer',
                    'exists:customers,id',
                ],

                'vehicle_id' => [
                    'required',
                    'integer',

                    Rule::exists(
                        'vehicles',
                        'id'
                    )->where(
                        fn ($query) =>
                            $query->where(
                                'customer_id',
                                $request->input(
                                    'customer_id'
                                )
                            )
                    ),
                ],

                'discount' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:100',
                ],

                'items' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'items.*.product_id' => [
                    'required',
                    'integer',
                    'exists:products,id',
                ],

                'items.*.quantity' => [
                    'required',
                    'numeric',
                    'min:0.01',
                ],
            ],
            [
                'customer_id.required' =>
                    'Veuillez sélectionner un client.',

                'vehicle_id.required' =>
                    'Veuillez sélectionner le véhicule associé au client.',

                'vehicle_id.exists' =>
                    'Le véhicule sélectionné n’appartient pas au client choisi.',

                'items.required' =>
                    'Vous devez ajouter au moins un produit.',

                'items.min' =>
                    'Vous devez ajouter au moins un produit.',

                'items.*.product_id.required' =>
                    'Veuillez sélectionner un produit.',

                'items.*.product_id.exists' =>
                    'Le produit sélectionné est invalide.',

                'items.*.quantity.required' =>
                    'La quantité est obligatoire.',

                'items.*.quantity.numeric' =>
                    'La quantité doit être numérique.',

                'items.*.quantity.min' =>
                    'La quantité doit être supérieure à zéro.',
            ]
        );

        DB::beginTransaction();

        try {
            $vehicle = Vehicle::query()
                ->whereKey(
                    $validated['vehicle_id']
                )
                ->where(
                    'customer_id',
                    $validated['customer_id']
                )
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | AGRÉGER LES QUANTITÉS PAR PRODUIT
            |--------------------------------------------------------------------------
            |
            | Empêche de contourner le stock en ajoutant plusieurs fois
            | le même produit dans le proforma.
            |
            */
            $requestedByProduct = [];

            foreach ($validated['items'] as $itemData) {
                $productId =
                    (int) $itemData['product_id'];

                $requestedByProduct[$productId] =
                    ($requestedByProduct[$productId] ?? 0)
                    +
                    (float) $itemData['quantity'];
            }

            /*
            |--------------------------------------------------------------------------
            | VÉRIFIER LE STOCK TOTAL RÉEL DANS LES DÉPÔTS
            |--------------------------------------------------------------------------
            */
            foreach (
                $requestedByProduct
                as $productId => $requestedQuantity
            ) {
                $product = Product::query()
                    ->whereKey(
                        $productId
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $availableQuantity = round(
                    (float) ProductDepotStock::query()
                        ->where(
                            'product_id',
                            $product->id
                        )
                        ->where(
                            'quantity',
                            '>',
                            0
                        )
                        ->sum(
                            'quantity'
                        ),
                    2
                );

                $requestedQuantity = round(
                    (float) $requestedQuantity,
                    2
                );

                if (
                    $requestedQuantity
                    >
                    $availableQuantity
                ) {
                    throw new \RuntimeException(
                        'Stock insuffisant pour : '
                        . $product->reference
                        . ' - '
                        . $product->designation
                        . '. Disponible dans les dépôts : '
                        . number_format(
                            $availableQuantity,
                            2,
                            ',',
                            ' '
                        )
                        . ' '
                        . (
                            $product->unit_label
                            ?? 'Pièce'
                        )
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | CALCUL DES LIGNES
            |--------------------------------------------------------------------------
            */
            $subtotal =
                0.00;

            $validatedItems =
                [];

            foreach ($validated['items'] as $itemData) {
                $product = Product::query()
                    ->findOrFail(
                        $itemData['product_id']
                    );

                $quantity = round(
                    (float) $itemData['quantity'],
                    2
                );

                /*
                |--------------------------------------------------------------------------
                | LE PRIX VIENT TOUJOURS DE LA BASE
                |--------------------------------------------------------------------------
                */
                $price = round(
                    (float) $product->sale_price,
                    2
                );

                $lineTotal = round(
                    $quantity * $price,
                    2
                );

                $subtotal +=
                    $lineTotal;

                $validatedItems[] = [
                    'product_id' =>
                        $product->id,

                    'quantity' =>
                        $quantity,

                    'price' =>
                        $price,

                    'total' =>
                        $lineTotal,
                ];
            }

            $subtotal = round(
                $subtotal,
                2
            );

            $discountPercent = round(
                (float) (
                    $validated['discount']
                    ??
                    0
                ),
                2
            );

            $discountAmount = round(
                (
                    $subtotal
                    *
                    $discountPercent
                )
                /
                100,
                2
            );

            $taxable = max(
                0,
                round(
                    $subtotal
                    -
                    $discountAmount,
                    2
                )
            );

            $tva = round(
                $taxable
                *
                0.10,
                2
            );

            $total = round(
                $taxable
                +
                $tva,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | NUMÉRO DU PROFORMA
            |--------------------------------------------------------------------------
            */
            $nextId =
                ((int) Proforma::max('id'))
                +
                1;

            $proformaNumber =
                'PROFORMA-'
                . str_pad(
                    (string) $nextId,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

            /*
            |--------------------------------------------------------------------------
            | CRÉER LE PROFORMA
            |--------------------------------------------------------------------------
            |
            | Aucun mode de paiement n'est demandé ici.
            |
            */
            $proforma = Proforma::create([
                'proforma_number' =>
                    $proformaNumber,

                'customer_id' =>
                    $validated['customer_id'],

                'vehicle_id' =>
                    $vehicle->id,

                'created_by' =>
                    auth()->id(),

                'payment_type' =>
                    null,

                'subtotal' =>
                    $subtotal,

                'discount' =>
                    $discountPercent,

                'discount_amount' =>
                    $discountAmount,

                'tva' =>
                    $tva,

                'total' =>
                    $total,

                'status' =>
                    Proforma::STATUS_VALIDATED,
            ]);

            /*
            |--------------------------------------------------------------------------
            | ENREGISTRER LES PRODUITS
            |--------------------------------------------------------------------------
            |
            | Le proforma ne réserve pas et ne diminue pas le stock.
            |
            */
            foreach ($validatedItems as $item) {
                ProformaItem::create([
                    'proforma_id' =>
                        $proforma->id,

                    'product_id' =>
                        $item['product_id'],

                    'quantity' =>
                        $item['quantity'],

                    'price' =>
                        $item['price'],

                    'total' =>
                        $item['total'],
                ]);
            }

            DB::commit();

            return redirect()
                ->route(
                    'proformas.show',
                    $proforma
                )
                ->with(
                    'success',
                    'Le proforma a été créé avec succès.'
                );

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error(
                'Création proforma impossible.',
                [
                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),

                    'user_id' =>
                        auth()->id(),

                    'customer_id' =>
                        $validated['customer_id']
                        ??
                        null,

                    'vehicle_id' =>
                        $validated['vehicle_id']
                        ??
                        null,
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->environment('local')
                        ? 'ERREUR : '
                            . $e->getMessage()
                        : 'La création du proforma a échoué.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | AFFICHER LE PROFORMA
    |--------------------------------------------------------------------------
    */

    public function show(
        Proforma $proforma
    ): View {
        $proforma->load([
            'customer',
            'vehicle',
            'creator',
            'sale',
            'items.product',
        ]);

        return view(
            'proformas.show',
            compact('proforma')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

    public function download(
        Proforma $proforma
    ) {
        $proforma->load([
            'customer',
            'vehicle',
            'creator',
            'items.product',
        ]);

        $safeNumber = preg_replace(
            '/[^A-Za-z0-9\-_]/',
            '-',
            $proforma->proforma_number
        );

        return Pdf::loadView(
            'proformas.pdf',
            [
                'proforma' => $proforma,
                'isPdf' => true,
            ]
        )
            ->setPaper('a4', 'portrait')
            ->download($safeNumber . '.pdf');
    }

   /*
    |--------------------------------------------------------------------------
    | CONVERTIR LE PROFORMA EN VENTE
    |--------------------------------------------------------------------------
    */
    public function convertToSale(
        Proforma $proforma
    ): RedirectResponse {
        try {
            $sale = DB::transaction(
                function () use ($proforma) {
                    /*
                    |--------------------------------------------------------------------------
                    | VERROUILLER LE PROFORMA
                    |--------------------------------------------------------------------------
                    */
                    $locked = Proforma::query()
                        ->with([
                            'items.product',
                            'vehicle',
                        ])
                        ->lockForUpdate()
                        ->findOrFail(
                            $proforma->id
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | DÉJÀ CONVERTI
                    |--------------------------------------------------------------------------
                    */
                    if (
                        $locked->status
                        === Proforma::STATUS_CONVERTED
                    ) {
                        if (!$locked->sale_id) {
                            throw new \RuntimeException(
                                'Ce proforma est marqué comme converti, '
                                . 'mais aucune vente associée n’a été trouvée.'
                            );
                        }

                        return Sale::query()
                            ->findOrFail(
                                $locked->sale_id
                            );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | PROFORMA ANNULÉ
                    |--------------------------------------------------------------------------
                    */
                    if (
                        $locked->status
                        === Proforma::STATUS_CANCELLED
                    ) {
                        throw new \RuntimeException(
                            'Un proforma annulé ne peut pas être converti.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | VÉRIFIER LES ARTICLES
                    |--------------------------------------------------------------------------
                    */
                    if ($locked->items->isEmpty()) {
                        throw new \RuntimeException(
                            'Ce proforma ne contient aucun produit.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | PRÉPARER LES AFFECTATIONS PAR DÉPÔT
                    |--------------------------------------------------------------------------
                    |
                    | Un produit peut exister dans plusieurs dépôts.
                    |
                    | Exemple :
                    | HILAC   = 7
                    | BALBALA = 4
                    | Quantité proforma = 9
                    |
                    | La conversion créera :
                    | - une ligne de vente de 7 depuis HILAC
                    | - une ligne de vente de 2 depuis BALBALA
                    |
                    */
                    $allocations = [];

                    foreach ($locked->items as $item) {
                        $product = Product::query()
                            ->whereKey(
                                $item->product_id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();

                        $requestedQuantity = round(
                            (float) $item->quantity,
                            2
                        );

                        if ($requestedQuantity <= 0) {
                            throw new \RuntimeException(
                                'Quantité invalide pour le produit '
                                . $product->reference
                                . '.'
                            );
                        }

                        $depotStocks = ProductDepotStock::query()
                            ->with('depot')
                            ->where(
                                'product_id',
                                $product->id
                            )
                            ->where(
                                'quantity',
                                '>',
                                0
                            )
                            ->orderByDesc('quantity')
                            ->lockForUpdate()
                            ->get();

                        $availableQuantity = round(
                            (float) $depotStocks->sum(
                                'quantity'
                            ),
                            2
                        );

                        if (
                            $requestedQuantity
                            >
                            $availableQuantity
                        ) {
                            throw new \RuntimeException(
                                'Stock insuffisant pour : '
                                . $product->reference
                                . ' - '
                                . $product->designation
                                . '. Disponible dans les dépôts : '
                                . number_format(
                                    $availableQuantity,
                                    2,
                                    ',',
                                    ' '
                                )
                                . ' '
                                . (
                                    $product->unit_label
                                    ?? 'Pièce'
                                )
                            );
                        }

                        $remaining = $requestedQuantity;

                        foreach ($depotStocks as $depotStock) {
                            if ($remaining <= 0) {
                                break;
                            }

                            $availableInDepot = round(
                                (float) $depotStock->quantity,
                                2
                            );

                            if ($availableInDepot <= 0) {
                                continue;
                            }

                            $take = round(
                                min(
                                    $remaining,
                                    $availableInDepot
                                ),
                                2
                            );

                            if ($take <= 0) {
                                continue;
                            }

                            $allocations[] = [
                                'proforma_item' =>
                                    $item,

                                'product' =>
                                    $product,

                                'depot_stock' =>
                                    $depotStock,

                                'depot' =>
                                    $depotStock->depot,

                                'quantity' =>
                                    $take,

                                'price' =>
                                    round(
                                        (float) $item->price,
                                        2
                                    ),
                            ];

                            $remaining = round(
                                $remaining - $take,
                                2
                            );
                        }

                        if ($remaining > 0.00001) {
                            throw new \RuntimeException(
                                'Impossible de répartir complètement le stock '
                                . 'du produit '
                                . $product->reference
                                . ' entre les dépôts.'
                            );
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | NUMÉRO DE FACTURE
                    |--------------------------------------------------------------------------
                    */
                    $nextSaleId =
                        ((int) Sale::max('id'))
                        + 1;

                    $invoiceNumber =
                        'FACT-'
                        . date('Y')
                        . '-'
                        . str_pad(
                            (string) $nextSaleId,
                            4,
                            '0',
                            STR_PAD_LEFT
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | CRÉER LA VENTE
                    |--------------------------------------------------------------------------
                    |
                    | Le mode de paiement est NULL ici.
                    | Il sera demandé au moment du paiement de la facture.
                    |
                    */
                    $sale = Sale::create([
                        'customer_id' =>
                            $locked->customer_id,

                        'vehicle_id' =>
                            $locked->vehicle_id,

                        'user_id' =>
                            auth()->id(),

                        'payment_type' =>
                            null,

                        'subtotal' =>
                            $locked->subtotal,

                        'discount' =>
                            $locked->discount,

                        'discount_amount' =>
                            $locked->discount_amount,

                        'tva' =>
                            $locked->tva,

                        'total' =>
                            $locked->total,

                        'status' =>
                            'vendu',

                        'document_type' =>
                            'sale',

                        'invoice_number' =>
                            $invoiceNumber,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | CRÉER LES LIGNES DE VENTE + SORTIR LE STOCK
                    |--------------------------------------------------------------------------
                    */
                    foreach ($allocations as $allocation) {
                        /** @var Product $product */
                        $product =
                            $allocation['product'];

                        /** @var ProductDepotStock $depotStock */
                        $depotStock =
                            $allocation['depot_stock'];

                        $depot =
                            $allocation['depot'];

                        $quantity = round(
                            (float) $allocation['quantity'],
                            2
                        );

                        $price = round(
                            (float) $allocation['price'],
                            2
                        );

                        $lineTotal = round(
                            $quantity * $price,
                            2
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | LIGNE DE VENTE
                        |--------------------------------------------------------------------------
                        */
                        SaleItem::create([
                            'sale_id' =>
                                $sale->id,

                            'product_id' =>
                                $product->id,

                            'vehicle_id' =>
                                $locked->vehicle_id,

                            'depot_id' =>
                                $depotStock->depot_id,

                            'quantity' =>
                                $quantity,

                            'price' =>
                                $price,

                            'total' =>
                                $lineTotal,
                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | DIMINUER UNIQUEMENT LE BON DÉPÔT
                        |--------------------------------------------------------------------------
                        */
                        $depotStock->quantity = max(
                            0,
                            round(
                                (float) $depotStock->quantity
                                -
                                $quantity,
                                2
                            )
                        );

                        $depotStock->save();

                        /*
                        |--------------------------------------------------------------------------
                        | RECALCULER LE STOCK TOTAL DU PRODUIT
                        |--------------------------------------------------------------------------
                        */
                        $productTotal = round(
                            (float) ProductDepotStock::query()
                                ->where(
                                    'product_id',
                                    $product->id
                                )
                                ->sum(
                                    'quantity'
                                ),
                            2
                        );

                        $product->quantity = max(
                            0,
                            $productTotal
                        );

                        $product->status =
                            $product->quantity > 0
                                ? 'disponible'
                                : 'vendu';

                        $product->save();

                        /*
                        |--------------------------------------------------------------------------
                        | MOUVEMENT DE STOCK
                        |--------------------------------------------------------------------------
                        */
                        StockMovement::create([
                            'product_id' =>
                                $product->id,

                            'type' =>
                                'out',

                            'quantity' =>
                                $quantity,

                            'source' =>
                                'Conversion proforma'
                                . (
                                    $depot
                                        ? ' | Dépôt: '
                                            . $depot->name
                                        : ''
                                ),

                            'reference' =>
                                $invoiceNumber,

                            'user_id' =>
                                auth()->id(),
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | MARQUER LE PROFORMA COMME CONVERTI
                    |--------------------------------------------------------------------------
                    */
                    $locked->update([
                        'status' =>
                            Proforma::STATUS_CONVERTED,

                        'sale_id' =>
                            $sale->id,

                        'converted_at' =>
                            now(),

                        'converted_by' =>
                            auth()->id(),
                    ]);

                    $sale->refresh();

                    return $sale;
                }
            );

            /*
            |--------------------------------------------------------------------------
            | REDIRECTION VERS LA FACTURE
            |--------------------------------------------------------------------------
            */
            return redirect()
                ->route(
                    'sales.invoice',
                    $sale
                )
                ->with(
                    'success',
                    'Le proforma a été converti en vente avec succès.'
                );

        } catch (\RuntimeException $e) {
            return back()
                ->with(
                    'error',
                    $e->getMessage()
                );

        } catch (Throwable $e) {
            Log::error(
                'Conversion du proforma impossible.',
                [
                    'proforma_id' =>
                        $proforma->id,

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),

                    'user_id' =>
                        auth()->id(),
                ]
            );

            return back()
                ->with(
                    'error',
                    app()->environment('local')
                        ? 'ERREUR : '
                            . $e->getMessage()
                        : 'Une erreur est survenue pendant la conversion.'
                );
        }
    }
    /*
    |--------------------------------------------------------------------------
    | ANNULER
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Proforma $proforma
    ): RedirectResponse {
        if (
            $proforma->status === Proforma::STATUS_CONVERTED
        ) {
            return back()->with(
                'error',
                'Un proforma converti ne peut pas être annulé.'
            );
        }

        if (
            $proforma->status === Proforma::STATUS_CANCELLED
        ) {
            return back()->with(
                'info',
                'Ce proforma est déjà annulé.'
            );
        }

        $proforma->update([
            'status' => Proforma::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by' => auth()->id(),
        ]);

        return redirect()
            ->route('proformas.index')
            ->with(
                'success',
                'Le proforma a été annulé.'
            );
    }
}
