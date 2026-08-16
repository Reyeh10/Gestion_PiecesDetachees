<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\StockMovement;
use App\Models\Customer;
use App\Models\ProductDepotStock;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

use Barryvdh\DomPDF\Facade\Pdf;
use NumberToWords\NumberToWords;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

    class SaleController extends Controller
    {


        /*
        |--------------------------------------------------------------------------
        | INDEX
        |--------------------------------------------------------------------------
        */

        public function index(Request $request)
        {
            $sales = Sale::with([

                    'customer',

                    'items.product.brand',

                    'items.product.model',

                    'payments',

                ])

                /*
                |--------------------------------------------------------------------------
                | AFFICHER UNIQUEMENT LES VENTES
                |--------------------------------------------------------------------------
                */
                ->where('document_type', 'sale')

                /*
                |--------------------------------------------------------------------------
                | EXCLURE LES PROFORMAS
                |--------------------------------------------------------------------------
                */
                ->where('invoice_number', 'NOT LIKE', 'PRO-%')

                /*
                |--------------------------------------------------------------------------
                | RECHERCHE CLIENT
                |--------------------------------------------------------------------------
                */
               ->when($request->client, function ($query) use ($request) {

                    $query->where(function ($q) use ($request) {

                        /*
                        |--------------------------------------------------------------------------
                        | SEARCH CLIENT
                        |--------------------------------------------------------------------------
                        */

                        $q->whereHas('customer', function ($customer) use ($request) {

                            $customer->where(
                                'name',
                                'like',
                                '%' . $request->client . '%'
                            );
                        })

                        /*
                        |--------------------------------------------------------------------------
                        | SEARCH FACTURE
                        |--------------------------------------------------------------------------
                        */

                        ->orWhere(
                            'invoice_number',
                            'like',
                            '%' . $request->client . '%'
                        );

                    });

                })

                /*
                |--------------------------------------------------------------------------
                | RECHERCHE REFERENCE
                |--------------------------------------------------------------------------
                */
                ->when($request->reference, function ($query) use ($request) {

                    $query->whereHas('items.product', function ($q) use ($request) {

                        $q->where(
                            'reference',
                            'like',
                            '%' . $request->reference . '%'
                        );

                    });

                })

                /*
                |--------------------------------------------------------------------------
                | RECHERCHE DESIGNATION
                |--------------------------------------------------------------------------
                */
                ->when($request->designation, function ($query) use ($request) {

                    $query->whereHas('items.product', function ($q) use ($request) {

                        $q->where(
                            'designation',
                            'like',
                            '%' . $request->designation . '%'
                        );

                    });

                })

                /*
                |--------------------------------------------------------------------------
                | RECHERCHE DATE
                |--------------------------------------------------------------------------
                */
                ->when($request->filled('date'), function ($query) use ($request) {

                        $query->whereDate(
                            'created_at',
                            '=',
                            $request->date
                        );

                    })

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
            | VIEW
            |--------------------------------------------------------------------------
            */

            return view(

                'sales.index',

                compact('sales')

            );
        }
        /*
        |--------------------------------------------------------------------------
        | CREATE
        |--------------------------------------------------------------------------
        */

        /**
     * Afficher le formulaire de création d'une vente.
     */
    public function create()
    {
        /*
        |--------------------------------------------------------------------------
        | PRODUITS DISPONIBLES
        |--------------------------------------------------------------------------
        */

        $products = Product::with([
                'brand',
                'model',
                'depotStocks.depot',
            ])
            ->where('quantity', '>', 0)
            ->where('status', '!=', 'vendu')
            ->orderBy('designation')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | CLIENTS
        |--------------------------------------------------------------------------
        */

        $customers = Customer::orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | VÉHICULES
        |--------------------------------------------------------------------------
        */

        $vehicles = Vehicle::with('customer')
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
        | VÉHICULES ASSOCIÉS AU CLIENT — AJAX
        |--------------------------------------------------------------------------
        */

        public function vehiclesByCustomer(Customer $customer): JsonResponse
        {
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
                        implode(' ', array_filter([
                            $vehicle->brand,
                            $vehicle->model,
                        ]))
                    );

                    return [
                        'id' => $vehicle->id,
                        'customer_id' => $vehicle->customer_id,
                        'plate_number' => $vehicle->plate_number,

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
        | STORE
        |--------------------------------------------------------------------------
        */

        public function store(Request $request)
        {
           $request->validate(
                [
                    'customer_id' => [
                        'nullable',
                        'exists:customers,id',
                    ],

                     'vehicle_id' => [
                        'required',
                        'integer',

                        Rule::exists('vehicles', 'id')
                            ->where(
                                fn ($query) => $query->where(
                                    'customer_id',
                                    $request->input('customer_id')
                                )
                            ),
                    ],

                    'payment_type' => [
                        'required',
                        'string',
                    ],

                    'items' => [
                        'required',
                        'array',
                        'min:1',
                    ],

                    'items.*.product_id' => [
                        'required',
                        'exists:products,id',
                    ],

                    'items.*.quantity' => [
                        'required',
                        'numeric',
                        'min:0.01',
                    ],


                ],
                [
                    'items.required' =>
                        'Vous devez ajouter au moins un produit.',

                    'items.*.product_id.required' =>
                        'Veuillez sélectionner un produit.',

                    'items.*.product_id.exists' =>
                        'Le produit sélectionné est invalide.',

                    'items.*.quantity.required' =>
                        'La quantité est obligatoire.',

                    'items.*.quantity.min' =>
                        'La quantité doit être supérieure à zéro.',

                    'items.*.vehicle_id.exists' =>
                        'Le véhicule sélectionné est invalide.',

                    'items.*.plate_number.max' =>
                        'L’immatriculation ne doit pas dépasser 50 caractères.',
                ]
            );

            DB::beginTransaction();

            try {

                /*
                |--------------------------------------------------------------------------
                | CALCULS
                |--------------------------------------------------------------------------
                */

                $subtotal = 0;

                $validatedItems = [];

                $vehicle = Vehicle::findOrFail(
                    $request->vehicle_id
                );

                $vehicle->customer_id =
                    $request->customer_id;

                $vehicle->save();

                foreach ($request->items as $item) {

                    /*
                    |--------------------------------------------------------------------------
                    | PRODUIT
                    |--------------------------------------------------------------------------
                    */

                    $product = Product::findOrFail(
                        $item['product_id']
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | RECHERCHE OU CRÉATION DU VÉHICULE
                    |--------------------------------------------------------------------------
                    */



                    /*
                    |--------------------------------------------------------------------------
                    | PRIX AUTO
                    |--------------------------------------------------------------------------
                    */

                    $price = $product->sale_price;



                    /*
                    |--------------------------------------------------------------------------
                    | STOCK DISPONIBLE
                    |--------------------------------------------------------------------------
                    */

                  $availableQty = $product->quantity;

                    /*
                    |--------------------------------------------------------------------------
                    | VERIFICATION STOCK
                    |--------------------------------------------------------------------------
                    */

                    if ($item['quantity'] > $availableQty) {

                        DB::rollBack();

                        return redirect()
                            ->back()
                            ->withInput()
                            ->with(
                                'error',
                                'Stock insuffisant pour : '
                                . $product->reference
                                . ' - '
                                . $product->designation
                                . ' | Disponible : '
                                . $availableQty
                            );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | TOTAL
                    |--------------------------------------------------------------------------
                    */

                   $lineTotal =
                     $item['quantity'] * $price;

                     $subtotal += $lineTotal;

                    /*
                    |--------------------------------------------------------------------------
                    | SAVE TEMP
                    |--------------------------------------------------------------------------
                    */

                    $validatedItems[] = [

                        'product' => $product,
                        //'vehicle' => $vehicle,

                        'quantity' => $item['quantity'],

                        'price' => $price,

                        'line_total' => $lineTotal,
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | REMISE %
                |--------------------------------------------------------------------------
                */

                $discountPercent = $request->discount ?? 0;

                /*
                |--------------------------------------------------------------------------
                | MONTANT REMISE
                |--------------------------------------------------------------------------
                */

                $discountAmount =
                    ($subtotal * $discountPercent) / 100;

                            /*
                            |--------------------------------------------------------------------------
                            | TVA
                            |--------------------------------------------------------------------------
                            */

                        $taxable = $subtotal - $discountAmount;
                             $tva = $taxable * 0.10;
                /*
                |--------------------------------------------------------------------------
                | TOTAL FINAL
                |--------------------------------------------------------------------------
                */

              $total = round($taxable + $tva);
                /*
                |--------------------------------------------------------------------------
                | STATUS INITIAL
                |--------------------------------------------------------------------------
                */

              $status = 'vendu';

                /*
                |--------------------------------------------------------------------------
                | FACTURE NUMBER
                |--------------------------------------------------------------------------
                */

                $nextId = Sale::max('id') + 1;

                $invoiceNumber =
                   'FACT-' .
                    date('Y') .
                    '-' .
                    str_pad(
                        $nextId,
                        4,
                        '0',
                        STR_PAD_LEFT
                    );

                /*
                |--------------------------------------------------------------------------
                | CREATE SALE
                |--------------------------------------------------------------------------
                */

                $sale = Sale::create([

                    'customer_id' =>
                        $request->customer_id,

                    'vehicle_id' =>
                        $vehicle->id,

                    'payment_type' =>
                        $request->payment_type,

                    'subtotal' =>
                        $subtotal,

                    /*
                    |--------------------------------------------------------------------------
                    | POURCENTAGE REMISE
                    |--------------------------------------------------------------------------
                    */

                    'discount' =>
                        $discountPercent,

                    /*
                    |--------------------------------------------------------------------------
                    | MONTANT REMISE
                    |--------------------------------------------------------------------------
                    */

                    'discount_amount' =>
                        $discountAmount,

                    'tva' =>
                        $tva,

                    'total' =>
                        $total,

                    'status' =>
                        $status,

                    'document_type' =>
                        'sale',

                    'invoice_number' =>
                        $invoiceNumber,
                ]);

                /*
                |--------------------------------------------------------------------------
                | CREATE ITEMS
                |--------------------------------------------------------------------------
                */

                foreach ($validatedItems as $item) {

                    $product = $item['product'];

                    /*
                    |--------------------------------------------------------------------------
                    | CREATE SALE ITEM
                    |--------------------------------------------------------------------------
                    */

                    SaleItem::create([

                        'sale_id' =>
                            $sale->id,

                        'product_id' =>
                            $product->id,

                        'quantity' =>
                            $item['quantity'],

                        'price' =>
                            $item['price'],
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | DIMINUER LE STOCK REEL
                    |--------------------------------------------------------------------------
                    */

                    $product->quantity =
                        $product->quantity - $item['quantity'];

                    $product->quantity =
                            max(0, $product->quantity);

                    /*
                    |--------------------------------------------------------------------------
                    | EVITER STOCK NEGATIF
                    |--------------------------------------------------------------------------
                    */

                    if ($product->quantity < 0) {

                        $product->quantity = 0;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS
                    |--------------------------------------------------------------------------
                    */

                    if ($product->quantity <= 0) {

                        $product->status = 'vendu';

                    } else {

                        $product->status = 'disponible';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | SAVE PRODUCT
                    |--------------------------------------------------------------------------
                    */

                    $product->save();

                    /*
                    |--------------------------------------------------------------------------
                    | MISE A JOUR STOCK DEPOT
                    |--------------------------------------------------------------------------
                    */

                    $depotStock = ProductDepotStock::where(

                        'product_id',
                        $product->id

                    )->first();

                    if ($depotStock) {

                        $depotStock->quantity =
                            $depotStock->quantity - $item['quantity'];

                        /*
                        |--------------------------------------------------------------------------
                        | EVITER NEGATIF
                        |--------------------------------------------------------------------------
                        */

                        if ($depotStock->quantity < 0) {

                            $depotStock->quantity = 0;
                        }

                        $depotStock->save();
                    }
                    /*
                    |--------------------------------------------------------------------------
                    | STOCK MOVEMENT
                    |--------------------------------------------------------------------------
                    */

                    StockMovement::create([

                        'product_id' =>
                            $product->id,

                        'type' =>
                            'out',

                        'quantity' =>
                            $item['quantity'],

                        'source' =>
                            'Vente',

                        'reference' =>
                            $sale->invoice_number,

                        'user_id' =>
                            auth()->id(),
                    ]);
                }

                DB::commit();

                return redirect()
                    ->route('sales.show', $sale)
                    ->with(
                        'success',
                        'Vente enregistrée avec succès.'
                    );

            } catch (\Exception $e) {

                DB::rollBack();

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

        public function show(Sale $sale)
        {
            $sale->load([

                'customer',

                'vehicle',

                'items.product.brand',

                'items.product.model',

                'payments',
            ]);

            /*
            |--------------------------------------------------------------------------
            | TOTAL EN LETTRES
            |--------------------------------------------------------------------------
            */

            $numberToWords = new NumberToWords();

                        $numberTransformer =
                            $numberToWords->getNumberTransformer('fr');

                    $totalRounded = (int) round(
                (float) $sale->total
            );

            $totalInWords =
                strtoupper(
                    $numberTransformer->toWords(
                        $totalRounded
                    )
                )
                . ' FDJ';
            /*
            |--------------------------------------------------------------------------
            | VIEW
            |--------------------------------------------------------------------------
            */

            return view(
                'sales.show',
                compact(
                    'sale',
                    'totalInWords'
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | EDIT
        |--------------------------------------------------------------------------
        */

        public function edit(Sale $sale)
        {
            return redirect()
                ->route('sales.show', $sale);
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        public function update(
            Request $request,
            Sale $sale
        ) {
            return redirect()
                ->route('sales.show', $sale);
        }

        /*
        |--------------------------------------------------------------------------
        | DESTROY
        |--------------------------------------------------------------------------
        */

        public function destroy(Sale $sale)
        {

            if(
                    !in_array(auth()->user()->role, [
                        'admin',
                        'chef_magasinier'
                    ])
                ){
                    abort(403);
                }
            DB::beginTransaction();

            try {

              foreach ($sale->items as $item) {

                    $product = $item->product;

                    /*
                    |--------------------------------------------------------------------------
                    | REMETTRE LE STOCK
                    |--------------------------------------------------------------------------
                    */

                    $product->quantity =
                        $product->quantity + $item->quantity;

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS PRODUIT
                    |--------------------------------------------------------------------------
                    */

                    if ($product->quantity > 0) {

                        $product->status = 'disponible';

                    } else {

                        $product->status = 'vendu';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | SAVE
                    |--------------------------------------------------------------------------
                    */

                    $product->save();

                    /*
                    |--------------------------------------------------------------------------
                    | MOUVEMENT STOCK
                    |--------------------------------------------------------------------------
                    */

                    StockMovement::create([

                        'product_id' =>
                            $item->product_id,

                        'type' =>
                            'in',

                        'quantity' =>
                            $item->quantity,

                        'source' =>
                            'Annulation vente',

                        'reference' =>
                            $sale->invoice_number,

                        'user_id' =>
                            auth()->id(),
                    ]);
                }
                $sale->delete();

                DB::commit();

                return redirect()
                    ->route('sales.index')
                    ->with(
                        'success',
                        'Vente supprimée avec succès.'
                    );

            } catch (\Exception $e) {

                DB::rollBack();

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

    public function cancel(Sale $sale)
    {
        /*
        |--------------------------------------------------------------------------
        | DEJA ANNULEE
        |--------------------------------------------------------------------------
        */

        if ($sale->status === 'cancelled') {

            return back()->with(
                'error',
                'Cette facture est déjà annulée.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | RETOUR STOCK
        |--------------------------------------------------------------------------
        */

        foreach ($sale->items as $item) {

            $product = $item->product;

            if ($product) {

                /*
                |--------------------------------------------------------------------------
                | RETOUR QUANTITE
                |--------------------------------------------------------------------------
                */

                $product->quantity += $item->quantity;

                /*
                |--------------------------------------------------------------------------
                | STATUS DISPONIBLE
                |--------------------------------------------------------------------------
                */

                $product->status = 'disponible';

                $product->save();

                /*
                |--------------------------------------------------------------------------
                | MOUVEMENT STOCK
                |--------------------------------------------------------------------------
                */

                StockMovement::create([

                    'product_id' => $product->id,

                    'type' => 'in',

                    'quantity' => $item->quantity,

                    'source' => 'Annulation facture',

                    'reference' => $sale->invoice_number,

                    'user_id' => auth()->id(),
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | STATUS FACTURE
        |--------------------------------------------------------------------------
        */

        $sale->status = 'cancelled';

        $sale->save();

        return redirect()
            ->route('sales.show', $sale)
            ->with(
                'success',
                'Facture annulée avec succès.'
            );
    }

        /*
        |--------------------------------------------------------------------------
        | ADD PAYMENT
        |--------------------------------------------------------------------------
        */

        public function addPayment(
            Request $request,
            Sale $sale
        ) {
            /*
            |--------------------------------------------------------------------------
            | INTERDIRE LE PAIEMENT D'UNE FACTURE ANNULÉE
            |--------------------------------------------------------------------------
            */
            if (
                in_array(
                    strtolower((string) $sale->status),
                    ['cancelled', 'annulé', 'annule'],
                    true
                )
            ) {
                return back()->with(
                    'error',
                    'Impossible de payer une facture annulée.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDATION
            |--------------------------------------------------------------------------
            */
            $request->validate(
                [
                    'amount' => [
                        'required',
                        'numeric',
                        'min:1',
                    ],

                    'method' => [
                        'nullable',
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
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | TOTAL FACTURE ARRONDI EN FDJ
            |--------------------------------------------------------------------------
            |
            | On compare toujours le paiement avec le total ARRONDI affiché.
            |
            */
            $invoiceTotal = (int) round(
                (float) $sale->total
            );

            /*
            |--------------------------------------------------------------------------
            | TOTAL DÉJÀ PAYÉ
            |--------------------------------------------------------------------------
            */
            $alreadyPaid = (int) round(
                (float) $sale->payments()->sum('amount')
            );

            /*
            |--------------------------------------------------------------------------
            | RESTE À PAYER AVANT LE NOUVEAU PAIEMENT
            |--------------------------------------------------------------------------
            */
            $remainingAmount = max(
                0,
                $invoiceTotal - $alreadyPaid
            );

            /*
            |--------------------------------------------------------------------------
            | FACTURE DÉJÀ PAYÉE
            |--------------------------------------------------------------------------
            */
            if ($remainingAmount <= 0) {

                if ($sale->status !== 'payé') {
                    $sale->update([
                        'status' => 'payé',
                    ]);
                }

                return back()->with(
                    'success',
                    'Cette facture est déjà entièrement payée.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | MONTANT SAISI ARRONDI EN FDJ
            |--------------------------------------------------------------------------
            */
            $paymentAmount = (int) round(
                (float) $request->amount
            );

            /*
            |--------------------------------------------------------------------------
            | INTERDIRE LE SURPAIEMENT
            |--------------------------------------------------------------------------
            */
            if ($paymentAmount > $remainingAmount) {

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

            /*
            |--------------------------------------------------------------------------
            | ENREGISTRER LE PAIEMENT
            |--------------------------------------------------------------------------
            */
            Payment::create([
                'sale_id' =>
                    $sale->id,

                'amount' =>
                    $paymentAmount,

                'method' =>
                    $request->method ?? 'Cash',
            ]);

            /*
            |--------------------------------------------------------------------------
            | RECALCULER LE TOTAL PAYÉ
            |--------------------------------------------------------------------------
            */
            $paid = (int) round(
                (float) $sale->payments()->sum('amount')
            );

            /*
            |--------------------------------------------------------------------------
            | RECALCULER LE RESTE
            |--------------------------------------------------------------------------
            */
            $remaining = max(
                0,
                $invoiceTotal - $paid
            );

            /*
            |--------------------------------------------------------------------------
            | METTRE À JOUR LE STATUT
            |--------------------------------------------------------------------------
            */
            if ($remaining <= 0) {

                $sale->update([
                    'status' => 'payé',
                ]);

            } elseif ($paid > 0) {

                $sale->update([
                    'status' => 'partiel',
                ]);

            } else {

                $sale->update([
                    'status' => 'vendu',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | MESSAGE
            |--------------------------------------------------------------------------
            */
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
        | AFFICHER LA FACTURE DANS LE NAVIGATEUR
        |--------------------------------------------------------------------------
        |
        | Cette méthode affiche la facture.
        | Elle ne télécharge PAS automatiquement le PDF.
        |
        */

        public function invoice(Sale $sale)
        {
            /*
            |--------------------------------------------------------------------------
            | CHARGER LES RELATIONS
            |--------------------------------------------------------------------------
            */

            $sale->load([
                'customer',
                'vehicle',
                'items.product.brand',
                'items.product.model',
                'payments',
            ]);


            /*
            |--------------------------------------------------------------------------
            | NUMÉRO DE FACTURE
            |--------------------------------------------------------------------------
            */

            $invoiceNumber =
                $sale->invoice_number
                ?: 'FACTURE-' . str_pad(
                    (string) $sale->id,
                    6,
                    '0',
                    STR_PAD_LEFT
                );


            /*
            |--------------------------------------------------------------------------
            | TOTAL EN LETTRES
            |--------------------------------------------------------------------------
            */

            $numberToWords = new NumberToWords();

                        $numberTransformer =
                            $numberToWords->getNumberTransformer('fr');


                    $totalRounded = (int) round(
                (float) $sale->total
            );

            $totalInWords =
                strtoupper(
                    $numberTransformer->toWords(
                        $totalRounded
                    )
                )
                . ' FDJ';


            /*
            |--------------------------------------------------------------------------
            | AFFICHER LA FACTURE
            |--------------------------------------------------------------------------
            |
            | IMPORTANT :
            |
            | Votre projet possède déjà :
            |
            | resources/views/sales/invoice_pdf.blade.php
            |
            | On utilise donc cette vue pour l'affichage navigateur.
            |
            */

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
        |
        | Cette méthode est appelée uniquement lorsque
        | l'utilisateur clique sur "Télécharger PDF".
        |
        */

        public function downloadInvoice(Sale $sale)
        {
            /*
            |--------------------------------------------------------------------------
            | CHARGER LES RELATIONS
            |--------------------------------------------------------------------------
            */

            $sale->load([
                'customer',
                'vehicle',
                'items.product.brand',
                'items.product.model',
                'payments',
            ]);


            /*
            |--------------------------------------------------------------------------
            | NUMÉRO DE FACTURE
            |--------------------------------------------------------------------------
            */

            $invoiceNumber =
                $sale->invoice_number
                ?: 'FACTURE-' . str_pad(
                    (string) $sale->id,
                    6,
                    '0',
                    STR_PAD_LEFT
                );


            /*
            |--------------------------------------------------------------------------
            | TOTAL EN LETTRES
            |--------------------------------------------------------------------------
            */

            $numberToWords =
                new NumberToWords();


            $numberTransformer =
                $numberToWords
                    ->getNumberTransformer(
                        'fr'
                    );


                        $totalRounded = (int) round(
                    (float) $sale->total
                );

                $totalInWords =
                    strtoupper(
                        $numberTransformer->toWords(
                            $totalRounded
                        )
                    )
                    . ' FDJ';


            /*
            |--------------------------------------------------------------------------
            | NOM DU FICHIER
            |--------------------------------------------------------------------------
            */

            $safeInvoiceNumber =
                preg_replace(
                    '/[^A-Za-z0-9\-_]/',
                    '-',
                    $invoiceNumber
                );


            /*
            |--------------------------------------------------------------------------
            | GÉNÉRER LE PDF
            |--------------------------------------------------------------------------
            */

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


            /*
            |--------------------------------------------------------------------------
            | TÉLÉCHARGEMENT
            |--------------------------------------------------------------------------
            */

            return $pdf->download(
                $safeInvoiceNumber . '.pdf'
            );
        }
}
