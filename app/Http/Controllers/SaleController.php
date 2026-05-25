<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\StockMovement;
use App\Models\Customer;
use App\Models\ProductDepotStock;

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
                    'depotStocks.depot'
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

            $customers = Customer::orderBy('name')->get();

            return view(
                'sales.create',
                compact('products', 'customers')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | STORE
        |--------------------------------------------------------------------------
        */

        public function store(Request $request)
        {
            $request->validate([

                'customer_id' => 'nullable|exists:customers,id',

                'payment_type' => 'required',

                'items' => 'required|array|min:1',

                'items.*.product_id' =>
                    'required|exists:products,id',

                'items.*.quantity' =>
                'required|numeric|min:0.01',
            ]);

            DB::beginTransaction();

            try {

                /*
                |--------------------------------------------------------------------------
                | CALCULS
                |--------------------------------------------------------------------------
                */

                $subtotal = 0;

                $validatedItems = [];

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
                    'INV-' .
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

                'items.product.brand',

                'items.product.model',

                'payments'
            ]);

            /*
            |--------------------------------------------------------------------------
            | TOTAL EN LETTRES
            |--------------------------------------------------------------------------
            */

            $numberToWords = new NumberToWords();

            $numberTransformer =
                $numberToWords->getNumberTransformer('fr');

           $total = round($sale->total);

            $entier = floor($total);
            $centimes = round(($total - $entier) * 100);
            $numberToWords = new NumberToWords();
            $numberTransformer = $numberToWords->getNumberTransformer('fr');
            $totalInWords = strtoupper($numberTransformer->toWords($entier)) . ' FDJ';

            if ($centimes > 0) {
                $totalInWords .= ' ET ' . strtoupper($numberTransformer->toWords($centimes)) . ' CENTIMES';
            }

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
            if ($sale->status === 'cancelled') {

                return back()->with(
                    'error',
                    'Impossible de payer une facture annulée.'
                );
            }
            
            $request->validate([

                'amount' =>
                    'required|numeric|min:1',

                'method' =>
                    'nullable|string|max:255',
            ]);

            Payment::create([

                'sale_id' =>
                    $sale->id,

                'amount' =>
                    $request->amount,

                'method' =>
                    $request->method,
            ]);

            /*
            |--------------------------------------------------------------------------
            | TOTAL PAYE
            |--------------------------------------------------------------------------
            */

            $paid =
                $sale->payments()->sum('amount');

           /*
|--------------------------------------------------------------------------
| TOTAL PAYE
|--------------------------------------------------------------------------
*/

$paid = $sale->payments()->sum('amount');

/*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            if ($paid >= $sale->total) {

                $sale->update([
                    'status' => 'payé'
                ]);

            } elseif ($paid > 0) {

                $sale->update([
                    'status' => 'partiel'
                ]);

            } else {

                $sale->update([
                    'status' => 'vendu'
                ]);
            }

            return back()->with(
                'success',
                'Paiement ajouté avec succès.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FACTURE PDF
        |--------------------------------------------------------------------------
        */

        public function invoice(Sale $sale)
        {
            $sale->load([

                'customer',

                'items.product.brand',

                'items.product.model',

                'payments'
            ]);

        /*
            |--------------------------------------------------------------------------
            | TOTAL EN LETTRES
            |--------------------------------------------------------------------------
            */

            $numberToWords = new NumberToWords();

            $numberTransformer =
                $numberToWords->getNumberTransformer('fr');

           $total = round($sale->total);

            $entier = floor($total);

            $centimes = round(($total - $entier) * 100);

            $numberToWords = new NumberToWords();

            $numberTransformer = $numberToWords->getNumberTransformer('fr');

            $totalInWords = strtoupper($numberTransformer->toWords($entier)) . ' FDJ';

            if ($centimes > 0) {
                $totalInWords .= ' ET ' . strtoupper($numberTransformer->toWords($centimes)) . ' CENTIMES';
            }

            /*
            |--------------------------------------------------------------------------
            | PDF
            |--------------------------------------------------------------------------
            */

            $pdf = Pdf::loadView(

                'sales.invoice_pdf',

                compact(
                    'sale',
                    'totalInWords'
                )

            );
                    return $pdf->download(

                        'facture_' .
                        $sale->invoice_number .
                        '.pdf'
                    );
        }
}
