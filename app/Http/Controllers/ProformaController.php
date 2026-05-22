<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\StockMovement;

use Barryvdh\DomPDF\Facade\Pdf;
use NumberToWords\NumberToWords;

class ProformaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $proformas = Sale::with([
                'customer',
                'items.product'
            ])

            ->where('document_type', 'proforma')

            ->when($request->client, function ($query) use ($request) {

                $query->whereHas('customer', function ($q) use ($request) {

                    $q->where(
                        'name',
                        'like',
                        '%' . $request->client . '%'
                    );
                });
            })

            ->when($request->reference, function ($query) use ($request) {

                $query->whereHas('items.product', function ($q) use ($request) {

                    $q->where(
                        'reference',
                        'like',
                        '%' . $request->reference . '%'
                    );
                });
            })

            ->when($request->designation, function ($query) use ($request) {

                $query->whereHas('items.product', function ($q) use ($request) {

                    $q->where(
                        'designation',
                        'like',
                        '%' . $request->designation . '%'
                    );
                });
            })

            ->when($request->date, function ($query) use ($request) {

                $query->whereDate(
                    'created_at',
                    $request->date
                );
            })

            ->latest()

            ->paginate(10);

        return view(
            'proformas.index',
            [
                'sales' => $proformas
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $customers = Customer::orderBy('name')->get();

        $products = Product::with([
                'brand',
                'model'
            ])
            ->where('quantity', '>', 0)
            ->orderBy('designation')
            ->get();

        return view(
            'proformas.create',
            compact('customers', 'products')
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

            'items' => 'required|array|min:1',

            'items.*.product_id' =>
                'required|exists:products,id',

            'items.*.quantity' =>
                'required|numeric|min:0.01',

            'items.*.price' =>
                'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | CALCUL TOTAL
            |--------------------------------------------------------------------------
            */

           $subtotal = 0;

            $subtotal = 0;

            foreach ($request->items as $item) {

                $lineTotal = round(
                    $item['quantity'] *
                    $item['price']
                );

                $subtotal += $lineTotal;
            }

            /*
            |--------------------------------------------------------------------------
            | REMISE
            |--------------------------------------------------------------------------
            */

            $discountPercent =
                $request->discount ?? 0;

            $discount = round(
                ($subtotal * $discountPercent) / 100
            );

            /*
            |--------------------------------------------------------------------------
            | TVA
            |--------------------------------------------------------------------------
            */

            $taxable =
                $subtotal - $discount;

            $tva = round(
                $taxable * 0.10
            );

            /*
            |--------------------------------------------------------------------------
            | TOTAL FINAL
            |--------------------------------------------------------------------------
            */

            $total = round(
                $taxable + $tva
            );
            /*
            |--------------------------------------------------------------------------
            | CREATE PROFORMA
            |--------------------------------------------------------------------------
            */

            $sale = Sale::create([

                'customer_id' =>
                    $request->customer_id,

                'payment_type' => null,

                'status' =>
                    'proforma',

                'document_type' =>
                    'proforma',

                'subtotal' =>
                    $subtotal,

                'discount' =>
                    $discount,

                'tva' =>
                    $tva,

                'total' =>
                    $total,

                'invoice_number' =>

                    'PRO-' .

                    date('Y') .

                    '-' .

                    str_pad(
                        Sale::max('id') + 1,
                        5,
                        '0',
                        STR_PAD_LEFT
                    ),
            ]);

            /*
            |--------------------------------------------------------------------------
            | SAVE ITEMS
            |--------------------------------------------------------------------------
            */

            foreach ($request->items as $item) {

                SaleItem::create([

                    'sale_id' =>
                        $sale->id,

                    'product_id' =>
                        $item['product_id'],

                    'quantity' =>
                        $item['quantity'],

                    'price' =>
                        $item['price'],
                ]);
            }

            DB::commit();

            return redirect()

                ->route(
                    'proformas.show',
                    $sale->id
                )

                ->with(
                    'success',
                    'Proforma généré avec succès.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()

                ->withInput()

                ->withErrors([
                    'error' => $e->getMessage()
                ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(int $id)
    {
        $sale = Sale::with([

            'customer',

            'items.product.brand',

            'items.product.model',

        ])->findOrFail($id);

       $total = round($sale->total);
        $entier = $total;
        $centimes = 0;

        $numberToWords =
            new NumberToWords();

        $numberTransformer =
            $numberToWords
                ->getNumberTransformer('fr');

        $totalInWords = strtoupper(

            $numberTransformer->toWords(
                $entier
            )

        ) . ' FDJ';



        return view(
            'proformas.show',
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

    public function edit(int $id)
    {
        $sale = Sale::with('items')
            ->findOrFail($id);

        $customers =
            Customer::orderBy('name')->get();

        $products =
            Product::orderBy('designation')->get();

        return view(
            'proformas.edit',
            compact(
                'sale',
                'customers',
                'products'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        int $id
    ) {

        $sale = Sale::findOrFail($id);

        $request->validate([

            'items' => 'required|array|min:1',

            'items.*.product_id' =>
                'required|exists:products,id',

            'items.*.quantity' =>
                'required|numeric|min:0.01',

            'items.*.price' =>
                'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | DELETE OLD ITEMS
            |--------------------------------------------------------------------------
            */

            $sale->items()->delete();

            /*
            |--------------------------------------------------------------------------
            | RECALCUL TOTAL
            |--------------------------------------------------------------------------
            */

            $subtotal = 0;

            foreach ($request->items as $item) {

                $subtotal +=
                    $item['quantity'] *
                    $item['price'];
            }

            $discount =
                $request->discount ?? 0;

            $tva =
                ($subtotal - $discount) * 0.10;

            $total =
                $subtotal +
                $tva -
                $discount;

            /*
            |--------------------------------------------------------------------------
            | UPDATE PROFORMA
            |--------------------------------------------------------------------------
            */

            $sale->update([

                'customer_id' =>
                    $request->customer_id,

                'subtotal' =>
                    $subtotal,

                'discount' =>
                    $discount,

                'tva' =>
                    $tva,

                'total' =>
                    $total,
            ]);

            /*
            |--------------------------------------------------------------------------
            | SAVE ITEMS
            |--------------------------------------------------------------------------
            */

            foreach ($request->items as $item) {

                SaleItem::create([

                    'sale_id' =>
                        $sale->id,

                    'product_id' =>
                        $item['product_id'],

                    'quantity' =>
                        $item['quantity'],

                    'price' =>
                        $item['price'],
                ]);
            }

            DB::commit();

            return redirect()

                ->route(
                    'proformas.show',
                    $sale->id
                )

                ->with(
                    'success',
                    'Proforma modifié avec succès.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()

                ->withInput()

                ->withErrors([
                    'error' => $e->getMessage()
                ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CONVERT PROFORMA TO SALE
    |--------------------------------------------------------------------------
    */

   public function convertToSale(int $id)
{
    DB::beginTransaction();

    try {

        /*
        |--------------------------------------------------------------------------
        | GET PROFORMA
        |--------------------------------------------------------------------------
        */

       $proforma = Sale::with([
            'customer',
            'items.product',
        ])->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | VERIFY ITEMS
        |--------------------------------------------------------------------------
        */

       if ($proforma->items()->count() <= 0) {
        throw new \Exception(
            'Aucun produit trouvé dans ce proforma.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFY STOCK
        |--------------------------------------------------------------------------
        */

        foreach ($proforma->items as $item) {

            $product = Product::find($item->product_id);

            if (!$product) {

                throw new \Exception(
                    'Produit introuvable.'
                );
            }

            if ($item->quantity > $product->quantity) {

                throw new \Exception(

                    'Stock insuffisant pour : '

                    . $product->designation

                    . ' | Disponible : '

                    . $product->quantity
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | CONVERT PROFORMA TO SALE
        |--------------------------------------------------------------------------
        */

        $proforma->document_type = 'sale';

        $proforma->status = 'vendu';

        $proforma->payment_type = 'cash';

        $proforma->invoice_number =

            'INV-' .

            date('Y') .

            '-' .

            str_pad(
                $proforma->id,
                5,
                '0',
                STR_PAD_LEFT
            );

        $proforma->save();

        /*
        |--------------------------------------------------------------------------
        | UPDATE STOCK
        |--------------------------------------------------------------------------
        */

        foreach ($proforma->items as $item) {

            $product = Product::find($item->product_id);

            /*
            |--------------------------------------------------------------------------
            | DECREASE STOCK
            |--------------------------------------------------------------------------
            */

            $product->quantity =
                $product->quantity - $item->quantity;

            if ($product->quantity < 0) {

                $product->quantity = 0;
            }

            /*
            |--------------------------------------------------------------------------
            | PRODUCT STATUS
            |--------------------------------------------------------------------------
            */

            if ($product->quantity <= 0) {

                $product->status = 'vendu';

            } else {

                $product->status = 'disponible';
            }

            $product->save();

            /*
            |--------------------------------------------------------------------------
            | STOCK MOVEMENT
            |--------------------------------------------------------------------------
            */

            StockMovement::create([

                'product_id' =>
                    $product->id,

                'user_id' =>
                    auth()->id(),

                'type' =>
                    'out',

                'quantity' =>
                    $item->quantity,

                'source' =>
                    'Conversion Proforma',

                'reference' =>
                    $proforma->invoice_number,
            ]);
        }

        DB::commit();

        return redirect()

            ->route(
                'sales.show',
                ['sale' => $proforma->id]
            )

            ->with(
                'success',
                'Proforma converti en vente avec succès.'
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
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(int $id)
    {
        $sale = Sale::findOrFail($id);

        $sale->items()->delete();

        $sale->delete();

        return redirect()

            ->route('proformas.index')

            ->with(
                'success',
                'Proforma supprimé avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

    public function pdf(int $proforma)
    {
        $sale = Sale::with([

            'customer',

            'items.product.brand',

            'items.product.model',

        ])->findOrFail($proforma);

        $total = round($sale->total);
        $entier = $total;
        $centimes = 0;

        $numberToWords =
            new NumberToWords();

        $numberTransformer =
            $numberToWords
                ->getNumberTransformer('fr');

        $totalInWords = strtoupper(

            $numberTransformer->toWords(
                $entier
            )

        ) . ' FDJ';


        $pdf = Pdf::loadView(

            'proformas.proforma_pdf',

            compact(
                'sale',
                'totalInWords'
            )
        );

        return $pdf->download(

            'proforma_' .

            $sale->invoice_number .

            '.pdf'
        );
    }
}
