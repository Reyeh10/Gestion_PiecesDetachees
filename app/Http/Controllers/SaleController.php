<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Depot;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductDepotStock;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use NumberToWords\NumberToWords;

class SaleController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
   public function index(Request $request)
    {
        $sales = Sale::query()
            ->with([
                'customer',
                'vehicle',
                'items.product.brand',
                'items.product.model',
                'items.depot',
                'payments',
            ])

            /*
            |--------------------------------------------------------------------------
            | UNIQUEMENT LES VENTES
            |--------------------------------------------------------------------------
            */
            ->where(
                'document_type',
                'sale'
            )

            /*
            |--------------------------------------------------------------------------
            | EXCLURE LES PROFORMAS
            |--------------------------------------------------------------------------
            */
            ->where(
                'invoice_number',
                'NOT LIKE',
                'PRO-%'
            )

            /*
            |--------------------------------------------------------------------------
            | RECHERCHE CLIENT / FACTURE
            |--------------------------------------------------------------------------
            */
            ->when(
                $request->filled('client'),
                function ($query) use ($request) {

                    $search = trim(
                        (string) $request->client
                    );

                    $query->where(
                        function ($q) use ($search) {

                            $q->whereHas(
                                'customer',
                                function ($customer) use ($search) {

                                    $customer->where(
                                        'name',
                                        'like',
                                        '%' . $search . '%'
                                    );
                                }
                            )
                            ->orWhere(
                                'invoice_number',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    );
                }
            )

            /*
            |--------------------------------------------------------------------------
            | RECHERCHE RÉFÉRENCE PRODUIT
            |--------------------------------------------------------------------------
            */
            ->when(
                $request->filled('reference'),
                function ($query) use ($request) {

                    $search = trim(
                        (string) $request->reference
                    );

                    $query->whereHas(
                        'items.product',
                        function ($q) use ($search) {

                            $q->where(
                                'reference',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    );
                }
            )

            /*
            |--------------------------------------------------------------------------
            | RECHERCHE DÉSIGNATION
            |--------------------------------------------------------------------------
            */
            ->when(
                $request->filled('designation'),
                function ($query) use ($request) {

                    $search = trim(
                        (string) $request->designation
                    );

                    $query->whereHas(
                        'items.product',
                        function ($q) use ($search) {

                            $q->where(
                                'designation',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    );
                }
            )

            /*
            |--------------------------------------------------------------------------
            | RECHERCHE PAR DATE
            |--------------------------------------------------------------------------
            */
            ->when(
                $request->filled('date'),
                function ($query) use ($request) {

                    $query->whereDate(
                        'created_at',
                        $request->date
                    );
                }
            )

            /*
            |--------------------------------------------------------------------------
            | TRI
            |--------------------------------------------------------------------------
            */
            ->latest()

            /*
            |--------------------------------------------------------------------------
            | PAGINATION
            |--------------------------------------------------------------------------
            */
            ->paginate(10);

        /*
        |--------------------------------------------------------------------------
        | CONSERVER LES FILTRES DANS LA PAGINATION
        |--------------------------------------------------------------------------
        |
        | Utilisation de appends() à la place de withQueryString()
        | pour éviter le faux avertissement Intelephense.
        |
        */
        $sales->appends(
            $request->query()
        );

        return view(
            'sales.index',
            compact('sales')
        );
    }
    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    |
    | La disponibilité provient de product_depot_stocks.
    | Un produit peut exister dans plusieurs dépôts.
    |
    */
    public function create()
    {
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
            ->orderBy('designation')
            ->get();

        $customers = Customer::query()
            ->orderBy('name')
            ->get();

        $vehicles = Vehicle::query()
            ->with('customer')
            ->orderBy('plate_number')
            ->get();

        return view(
            'sales.create',
            compact(
                'products',
                'customers',
                'vehicles'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VÉHICULES ASSOCIÉS AU CLIENT
    |--------------------------------------------------------------------------
    */
    public function vehiclesByCustomer(
        Customer $customer
    ): JsonResponse {
        $vehicles = Vehicle::query()
            ->where(
                'customer_id',
                $customer->id
            )
            ->orderBy('plate_number')
            ->get([
                'id',
                'customer_id',
                'plate_number',
                'brand',
                'model',
            ])
            ->map(
                function (Vehicle $vehicle): array {
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
                        'id' =>
                            $vehicle->id,

                        'customer_id' =>
                            $vehicle->customer_id,

                        'plate_number' =>
                            $vehicle->plate_number,

                        'label' =>
                            $description !== ''
                                ? $vehicle->plate_number
                                    . ' - '
                                    . $description
                                : $vehicle->plate_number,
                    ];
                }
            )
            ->values();

        return response()->json([
            'success' =>
                true,

            'vehicles' =>
                $vehicles,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    |
    | Règles :
    | - chaque ligne choisit son dépôt ;
    | - la quantité est contrôlée dans ce dépôt précis ;
    | - sale_items.depot_id mémorise le dépôt utilisé ;
    | - products.quantity est recalculé comme somme des dépôts ;
    | - le mode de paiement n'est PAS demandé ici.
    |
    */
    public function store(
        Request $request
    ): RedirectResponse {
        $request->validate(
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

                'items.*.depot_id' => [
                    'required',
                    'integer',
                    'exists:depots,id',
                ],

                'items.*.quantity' => [
                    'required',
                    'numeric',
                    'min:0.01',
                ],

                'discount' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:100',
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

                'items.*.product_id.required' =>
                    'Veuillez sélectionner un produit.',

                'items.*.product_id.exists' =>
                    'Le produit sélectionné est invalide.',

                'items.*.depot_id.required' =>
                    'Veuillez sélectionner un dépôt pour chaque produit.',

                'items.*.depot_id.exists' =>
                    'Le dépôt sélectionné est invalide.',

                'items.*.quantity.required' =>
                    'La quantité est obligatoire.',

                'items.*.quantity.numeric' =>
                    'La quantité doit être numérique.',

                'items.*.quantity.min' =>
                    'La quantité doit être supérieure à zéro.',

                'discount.max' =>
                    'La remise ne peut pas dépasser 100 %.',
            ]
        );

        DB::beginTransaction();

        try {
            $vehicle = Vehicle::query()
                ->where(
                    'id',
                    $request->vehicle_id
                )
                ->where(
                    'customer_id',
                    $request->customer_id
                )
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | AGRÉGER LES QUANTITÉS PAR PRODUIT + DÉPÔT
            |--------------------------------------------------------------------------
            |
            | Empêche un dépassement de stock lorsque le même couple
            | produit/dépôt est présent plusieurs fois dans le formulaire.
            |
            */
            $requestedByProductDepot = [];

            foreach ($request->items as $row) {
                $productId =
                    (int) $row['product_id'];

                $depotId =
                    (int) $row['depot_id'];

                $quantity =
                    round(
                        (float) $row['quantity'],
                        2
                    );

                $key =
                    $productId
                    . ':'
                    . $depotId;

                $requestedByProductDepot[$key] =
                    ($requestedByProductDepot[$key] ?? 0)
                    + $quantity;
            }

            /*
            |--------------------------------------------------------------------------
            | VERROUILLER ET CONTRÔLER LES STOCKS
            |--------------------------------------------------------------------------
            */
            $locked = [];

            foreach (
                $requestedByProductDepot
                as $key => $requestedQty
            ) {
                [$productId, $depotId] =
                    array_map(
                        'intval',
                        explode(
                            ':',
                            $key
                        )
                    );

                $product = Product::query()
                    ->where(
                        'id',
                        $productId
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $depot = Depot::query()
                    ->where(
                        'id',
                        $depotId
                    )
                    ->firstOrFail();

                $depotStock =
                    ProductDepotStock::query()
                        ->where(
                            'product_id',
                            $productId
                        )
                        ->where(
                            'depot_id',
                            $depotId
                        )
                        ->lockForUpdate()
                        ->first();

                if (!$depotStock) {
                    throw new \RuntimeException(
                        'Le produit '
                        . $product->reference
                        . ' n’est pas disponible dans le dépôt '
                        . $depot->name
                        . '.'
                    );
                }

                $available =
                    round(
                        (float) $depotStock->quantity,
                        2
                    );

                $requestedQty =
                    round(
                        (float) $requestedQty,
                        2
                    );

                if ($requestedQty > $available) {
                    throw new \RuntimeException(
                        'Stock insuffisant pour '
                        . $product->reference
                        . ' - '
                        . $product->designation
                        . ' dans le dépôt '
                        . $depot->name
                        . '. Disponible : '
                        . number_format(
                            $available,
                            2,
                            ',',
                            ' '
                        )
                        . '.'
                    );
                }

                $locked[$key] = [
                    'product' =>
                        $product,

                    'depot' =>
                        $depot,

                    'stock' =>
                        $depotStock,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | CALCULER LE SOUS-TOTAL
            |--------------------------------------------------------------------------
            */
            $subtotal =
                0.0;

            $validatedItems =
                [];

            foreach ($request->items as $row) {
                $productId =
                    (int) $row['product_id'];

                $depotId =
                    (int) $row['depot_id'];

                $quantity =
                    round(
                        (float) $row['quantity'],
                        2
                    );

                $key =
                    $productId
                    . ':'
                    . $depotId;

                $product =
                    $locked[$key]['product'];

                $depot =
                    $locked[$key]['depot'];

                $price =
                    round(
                        (float) $product->sale_price,
                        2
                    );

                $lineTotal =
                    round(
                        $quantity * $price,
                        2
                    );

                $subtotal +=
                    $lineTotal;

                $validatedItems[] = [
                    'product' =>
                        $product,

                    'depot' =>
                        $depot,

                    'quantity' =>
                        $quantity,

                    'price' =>
                        $price,
                ];
            }

            $subtotal =
                round(
                    $subtotal,
                    2
                );

            /*
            |--------------------------------------------------------------------------
            | REMISE / TVA / TOTAL
            |--------------------------------------------------------------------------
            */
            $discountPercent =
                min(
                    100,
                    max(
                        0,
                        (float) (
                            $request->discount
                            ?? 0
                        )
                    )
                );

            $discountAmount =
                round(
                    (
                        $subtotal
                        *
                        $discountPercent
                    )
                    /
                    100,
                    2
                );

            $taxable =
                max(
                    0,
                    $subtotal
                    -
                    $discountAmount
                );

            $tva =
                round(
                    $taxable * 0.10,
                    2
                );

            $total =
                (int) round(
                    $taxable + $tva
                );

            /*
            |--------------------------------------------------------------------------
            | NUMÉRO DE FACTURE
            |--------------------------------------------------------------------------
            */
            $nextId =
                ((int) Sale::max('id'))
                +
                1;

            $invoiceNumber =
                'FACT-'
                . date('Y')
                . '-'
                . str_pad(
                    (string) $nextId,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            /*
            |--------------------------------------------------------------------------
            | CRÉER LA VENTE
            |--------------------------------------------------------------------------
            |
            | payment_type = NULL.
            | Il sera renseigné lors du paiement.
            |
            */
            $sale = Sale::create([
                'customer_id' =>
                    $request->customer_id,

                'vehicle_id' =>
                    $vehicle->id,

                'user_id' =>
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
                    'vendu',

                'document_type' =>
                    'sale',

                'invoice_number' =>
                    $invoiceNumber,
            ]);

            /*
            |--------------------------------------------------------------------------
            | LIGNES + SORTIES DE STOCK
            |--------------------------------------------------------------------------
            */
            foreach ($validatedItems as $item) {
                $product =
                    $item['product'];

                $depot =
                    $item['depot'];

                $key =
                    $product->id
                    . ':'
                    . $depot->id;

                $depotStock =
                    $locked[$key]['stock'];

                SaleItem::create([
                    'sale_id' =>
                        $sale->id,

                    'product_id' =>
                        $product->id,

                    'depot_id' =>
                        $depot->id,

                    'quantity' =>
                        $item['quantity'],

                    'price' =>
                        $item['price'],
                ]);

                $depotStock->quantity =
                    max(
                        0,
                        round(
                            (float) $depotStock->quantity
                            -
                            (float) $item['quantity'],
                            2
                        )
                    );

                $depotStock->save();

                $this->syncProductQuantityFromDepots(
                    $product
                );

                StockMovement::create([
                    'product_id' =>
                        $product->id,

                    'type' =>
                        'out',

                    'quantity' =>
                        $item['quantity'],

                    'source' =>
                        'Vente | Dépôt: '
                        . $depot->name,

                    'reference' =>
                        $sale->invoice_number,

                    'user_id' =>
                        auth()->id(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route(
                    'sales.invoice',
                    $sale
                )
                ->with(
                    'success',
                    'Vente enregistrée avec succès.'
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show(
        Sale $sale
    ): RedirectResponse {
        return redirect()
            ->route(
                'sales.invoice',
                $sale
            );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(
        Sale $sale
    ): RedirectResponse {
        return redirect()
            ->route(
                'sales.invoice',
                $sale
            );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(
        Request $request,
        Sale $sale
    ): RedirectResponse {
        return redirect()
            ->route(
                'sales.invoice',
                $sale
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */
    public function destroy(
        Sale $sale
    ): RedirectResponse {
        if (
            !in_array(
                auth()->user()->role,
                [
                    'admin',
                    'chef_magasinier',
                ],
                true
            )
        ) {
            abort(403);
        }

        DB::beginTransaction();

        try {
            $sale->load([
                'items.product',
                'items.depot',
            ]);

            /*
            |--------------------------------------------------------------------------
            | NE PAS RESTAURER DEUX FOIS UNE VENTE DÉJÀ ANNULÉE
            |--------------------------------------------------------------------------
            */
            if (!$this->isCancelled($sale)) {
                foreach ($sale->items as $item) {
                    $this->restoreSaleItemToDepot(
                        $item,
                        $sale,
                        'Suppression vente'
                    );
                }
            }

            $sale->payments()->delete();
            $sale->items()->delete();
            $sale->delete();

            DB::commit();

            return redirect()
                ->route('sales.index')
                ->with(
                    'success',
                    'Vente supprimée avec succès.'
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CANCEL SALE
    |--------------------------------------------------------------------------
    */
    public function cancel(
        Sale $sale
    ): RedirectResponse {
        if ($this->isCancelled($sale)) {
            return back()->with(
                'error',
                'Cette facture est déjà annulée.'
            );
        }

        DB::beginTransaction();

        try {
            $sale->load([
                'items.product',
                'items.depot',
            ]);

            foreach ($sale->items as $item) {
                $this->restoreSaleItemToDepot(
                    $item,
                    $sale,
                    'Annulation facture'
                );
            }

            $sale->status =
                'cancelled';

            $sale->save();

            DB::commit();

            return redirect()
                ->route(
                    'sales.invoice',
                    $sale
                )
                ->with(
                    'success',
                    'Facture annulée avec succès. Les stocks ont été remis dans leurs dépôts d’origine.'
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ADD PAYMENT
    |--------------------------------------------------------------------------
    |
    | Le mode de paiement est demandé uniquement ici.
    |
    */
    public function addPayment(
        Request $request,
        Sale $sale
    ): RedirectResponse {
        if ($this->isCancelled($sale)) {
            return back()->with(
                'error',
                'Impossible de payer une facture annulée.'
            );
        }

        $request->validate(
            [
                'amount' => [
                    'required',
                    'numeric',
                    'min:1',
                ],

                'method' => [
                    'required',
                    'string',
                    'max:255',
                ],
            ],
            [
                'amount.required' =>
                    'Veuillez saisir le montant du paiement.',

                'amount.numeric' =>
                    'Le montant du paiement doit être un nombre.',

                'amount.min' =>
                    'Le montant du paiement doit être supérieur à zéro.',

                'method.required' =>
                    'Veuillez sélectionner un mode de paiement.',
            ]
        );

        $invoiceTotal =
            (int) round(
                (float) $sale->total
            );

        $alreadyPaid =
            (int) round(
                (float) $sale->payments()
                    ->sum('amount')
            );

        $remainingAmount =
            max(
                0,
                $invoiceTotal
                -
                $alreadyPaid
            );

        if ($remainingAmount <= 0) {
            if ($sale->status !== 'payé') {
                $sale->update([
                    'status' =>
                        'payé',
                ]);
            }

            return back()->with(
                'success',
                'Cette facture est déjà entièrement payée.'
            );
        }

        $paymentAmount =
            (int) round(
                (float) $request->amount
            );

        if (
            $paymentAmount
            >
            $remainingAmount
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Le montant saisi dépasse le reste à payer de '
                    . number_format(
                        $remainingAmount,
                        0,
                        ',',
                        ' '
                    )
                    . ' FDJ.'
                );
        }

        DB::transaction(
            function () use (
                $sale,
                $paymentAmount,
                $request,
                $invoiceTotal
            ) {
                $method =
                    trim(
                        (string) $request->method
                    );

                Payment::create([
                    'sale_id' =>
                        $sale->id,

                    'amount' =>
                        $paymentAmount,

                    'method' =>
                        $method,
                ]);

                /*
                |--------------------------------------------------------------------------
                | MÉMORISER LE MODE DE PAIEMENT SUR LA FACTURE
                |--------------------------------------------------------------------------
                */
                $sale->payment_type =
                    $method;

                $paid =
                    (int) round(
                        (float) $sale->payments()
                            ->sum('amount')
                    );

                $remaining =
                    max(
                        0,
                        $invoiceTotal
                        -
                        $paid
                    );

                if ($remaining <= 0) {
                    $sale->status =
                        'payé';
                } elseif ($paid > 0) {
                    $sale->status =
                        'partiel';
                } else {
                    $sale->status =
                        'vendu';
                }

                $sale->save();
            }
        );

        $paid =
            (int) round(
                (float) $sale->payments()
                    ->sum('amount')
            );

        $remaining =
            max(
                0,
                $invoiceTotal
                -
                $paid
            );

        if ($remaining <= 0) {
            return back()->with(
                'success',
                'Paiement enregistré. La facture est entièrement payée.'
            );
        }

        return back()->with(
            'success',
            'Paiement enregistré avec succès. Reste à payer : '
            . number_format(
                $remaining,
                0,
                ',',
                ' '
            )
            . ' FDJ.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AFFICHER LA FACTURE
    |--------------------------------------------------------------------------
    */
    public function invoice(
        Sale $sale
    ) {
        $sale->load([
            'customer',
            'vehicle',
            'user',
            'items.product.brand',
            'items.product.model',
            'items.depot',
            'payments',
        ]);

        $invoiceNumber =
            $sale->invoice_number
            ?:
            'FACTURE-'
            . str_pad(
                (string) $sale->id,
                6,
                '0',
                STR_PAD_LEFT
            );

        $totalInWords =
            $this->totalInWords(
                (float) $sale->total
            );

        return view(
            'sales.invoice',
            [
                'sale' =>
                    $sale,

                'invoiceNumber' =>
                    $invoiceNumber,

                'totalInWords' =>
                    $totalInWords,

                'isPdf' =>
                    false,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TÉLÉCHARGER LA FACTURE PDF
    |--------------------------------------------------------------------------
    */
    public function downloadInvoice(
        Sale $sale
    ) {
        $sale->load([
            'customer',
            'vehicle',
            'user',
            'items.product.brand',
            'items.product.model',
            'items.depot',
            'payments',
        ]);

        $invoiceNumber =
            $sale->invoice_number
            ?:
            'FACTURE-'
            . str_pad(
                (string) $sale->id,
                6,
                '0',
                STR_PAD_LEFT
            );

        $totalInWords =
            $this->totalInWords(
                (float) $sale->total
            );

        $safeInvoiceNumber =
            preg_replace(
                '/[^A-Za-z0-9\-_]/',
                '-',
                $invoiceNumber
            );

        $pdf = Pdf::loadView(
            'sales.invoice_pdf',
            [
                'sale' =>
                    $sale,

                'invoiceNumber' =>
                    $invoiceNumber,

                'totalInWords' =>
                    $totalInWords,

                'isPdf' =>
                    true,
            ]
        )
        ->setPaper(
            'a4',
            'portrait'
        );

        return $pdf->download(
            $safeInvoiceNumber
            . '.pdf'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SYNCHRONISER products.quantity
    |--------------------------------------------------------------------------
    */
    private function syncProductQuantityFromDepots(
        Product $product
    ): void {
        $total =
            round(
                (float) ProductDepotStock::query()
                    ->where(
                        'product_id',
                        $product->id
                    )
                    ->sum('quantity'),
                2
            );

        $product->quantity =
            max(
                0,
                $total
            );

        $product->status =
            $product->quantity > 0
                ? 'disponible'
                : 'vendu';

        if (
            $product->quantity
            <=
            0
        ) {
            $product->supply_status =
                'rupture';
        } elseif (
            $product->supply_status
            ===
            'rupture'
        ) {
            $product->supply_status =
                null;
        }

        $product->save();
    }

    /*
    |--------------------------------------------------------------------------
    | RESTAURER UNE LIGNE DE VENTE DANS SON DÉPÔT
    |--------------------------------------------------------------------------
    */
    private function restoreSaleItemToDepot(
        SaleItem $item,
        Sale $sale,
        string $source
    ): void {
        $product = Product::query()
            ->where(
                'id',
                $item->product_id
            )
            ->lockForUpdate()
            ->first();

        if (!$product) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | ANCIENNES VENTES SANS depot_id
        |--------------------------------------------------------------------------
        |
        | Impossible de deviner de quel dépôt elles provenaient.
        |
        */
        if (!$item->depot_id) {
            throw new \RuntimeException(
                'Impossible de restaurer automatiquement le produit '
                . (
                    $product->reference
                    ??
                    '#' . $product->id
                )
                . ' : cette ancienne ligne de vente ne contient pas de dépôt.'
            );
        }

        $depot = Depot::query()
            ->find(
                $item->depot_id
            );

        if (!$depot) {
            throw new \RuntimeException(
                'Le dépôt d’origine de la ligne de vente est introuvable.'
            );
        }

        $depotStock =
            ProductDepotStock::query()
                ->where(
                    'product_id',
                    $product->id
                )
                ->where(
                    'depot_id',
                    $depot->id
                )
                ->lockForUpdate()
                ->first();

        if (!$depotStock) {
            $depotStock =
                ProductDepotStock::create([
                    'product_id' =>
                        $product->id,

                    'depot_id' =>
                        $depot->id,

                    'quantity' =>
                        0,
                ]);
        }

        $depotStock->quantity =
            round(
                (float) $depotStock->quantity
                +
                (float) $item->quantity,
                2
            );

        $depotStock->save();

        $this->syncProductQuantityFromDepots(
            $product
        );

        StockMovement::create([
            'product_id' =>
                $product->id,

            'type' =>
                'in',

            'quantity' =>
                $item->quantity,

            'source' =>
                $source
                . ' | Dépôt: '
                . $depot->name,

            'reference' =>
                $sale->invoice_number,

            'user_id' =>
                auth()->id(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FACTURE ANNULÉE ?
    |--------------------------------------------------------------------------
    */
    private function isCancelled(
        Sale $sale
    ): bool {
        return in_array(
            strtolower(
                trim(
                    (string) $sale->status
                )
            ),
            [
                'cancelled',
                'annulé',
                'annule',
            ],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL EN LETTRES
    |--------------------------------------------------------------------------
    */
    private function totalInWords(
        float $total
    ): string {
        $numberToWords =
            new NumberToWords();

        $numberTransformer =
            $numberToWords
                ->getNumberTransformer(
                    'fr'
                );

        $totalRounded =
            (int) round(
                $total
            );

        return strtoupper(
            $numberTransformer->toWords(
                $totalRounded
            )
        )
        . ' FDJ';
    }
}
