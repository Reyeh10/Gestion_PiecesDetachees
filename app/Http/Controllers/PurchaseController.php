<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTE DES ACHATS
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $purchases = Purchase::with([
                'supplier',
                'items.product',
                'user'
            ])
            ->latest()
            ->paginate(20);

        return view(
            'purchases.index',
            compact('purchases')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FORMULAIRE GENERER ACHAT
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        /*
        |--------------------------------------------------------------------------
        | RECUPERER FOURNISSEURS
        |--------------------------------------------------------------------------
        */

        $suppliers = Supplier::orderBy('name')->get();

        /*
        |--------------------------------------------------------------------------
        | RECUPERER PRODUITS
        |--------------------------------------------------------------------------
        */

        $products = Product::with([
                'brand',
                'model',
                'suppliers'
            ])
            ->orderBy('designation')
            ->get();

        return view(
            'purchases.create',
            compact(
                'suppliers',
                'products'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX PRODUITS FOURNISSEUR
    |--------------------------------------------------------------------------
    */

    public function getSupplierProducts(Supplier $supplier)
    {
        $supplier->load([
            'products.brand',
            'products.model'
        ]);

        $products = $supplier->products->map(function ($product) {

            return [

                'id' => $product->id,

                'reference' => $product->reference,

                'designation' => $product->designation,

                'brand' => $product->brand->name ?? '',

                'model' => $product->model->name ?? '',

                'stock' => $product->quantity ?? 0,

                'purchase_price' =>
                    $product->pivot->purchase_price
                    ??
                    $product->purchase_price
                    ??
                    0,
            ];
        });

        return response()->json($products);
    }

    /*
    |--------------------------------------------------------------------------
    | ENREGISTRER ACHAT
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'supplier_id' =>
                'required|exists:suppliers,id',

            'items' =>
                'required|array|min:1',

            'items.*.product_id' =>
                'required|exists:products,id',

            'items.*.quantity' =>
                'required|numeric|min:1',

            'items.*.price' =>
                'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | SOUS TOTAL
            |--------------------------------------------------------------------------
            */

            $subtotal = 0;

            foreach ($request->items as $item) {

                $lineTotal =
                    $item['quantity']
                    *
                    $item['price'];

                $subtotal += $lineTotal;
            }

            /*
            |--------------------------------------------------------------------------
            | TVA
            |--------------------------------------------------------------------------
            */

            $vat = $subtotal * 0.10;

            /*
            |--------------------------------------------------------------------------
            | TOTAL
            |--------------------------------------------------------------------------
            */

            $grandTotal =
                $subtotal + $vat;

            /*
            |--------------------------------------------------------------------------
            | REFERENCE ACHAT
            |--------------------------------------------------------------------------
            */

            $nextId =
                Purchase::max('id') + 1;

            $purchaseReference =
                'PUR-' .
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
            | CREATE PURCHASE
            |--------------------------------------------------------------------------
            */

            $purchase = Purchase::create([

                'reference' =>
                    $purchaseReference,

                'supplier_id' =>
                    $request->supplier_id,

                'user_id' =>
                    auth()->id(),

                'subtotal' =>
                    $subtotal,

                'vat' =>
                    $vat,

                'total' =>
                    $grandTotal,

                'status' =>
                    'completed',
            ]);

            /*
            |--------------------------------------------------------------------------
            | ITEMS
            |--------------------------------------------------------------------------
            */

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
                | TOTAL LIGNE
                |--------------------------------------------------------------------------
                */

                $lineTotal =
                    $item['quantity']
                    *
                    $item['price'];

                /*
                |--------------------------------------------------------------------------
                | CREATE PURCHASE ITEM
                |--------------------------------------------------------------------------
                */

                PurchaseItem::create([

                    'purchase_id' =>
                        $purchase->id,

                    'product_id' =>
                        $product->id,

                    'quantity' =>
                        $item['quantity'],

                    'price' =>
                        $item['price'],

                    'total' =>
                        $lineTotal,
                ]);

                /*
                |--------------------------------------------------------------------------
                | UPDATE STOCK
                |--------------------------------------------------------------------------
                */

                $product->quantity +=
                    $item['quantity'];

                /*
                |--------------------------------------------------------------------------
                | STATUS
                |--------------------------------------------------------------------------
                */

                if (
                    $product->quantity <= 0
                ) {

                    $product->status =
                        'rupture';

                } elseif (

                    $product->quantity <=
                    $product->min_stock

                ) {

                    $product->status =
                        'stock_faible';

                } else {

                    $product->status =
                        'disponible';
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

                    'type' =>
                        'in',

                    'quantity' =>
                        $item['quantity'],

                    'source' =>
                        'Achat fournisseur',

                    'reference' =>
                        $purchaseReference,

                    'user_id' =>
                        auth()->id(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route(
                    'purchases.show',
                    $purchase->id
                )
                ->with(
                    'success',
                    'Achat enregistré avec succès.'
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
    | DETAILS ACHAT
    |--------------------------------------------------------------------------
    */

    public function show(Purchase $purchase)
    {
        $purchase->load([

            'supplier',
            'items.product.brand',
            'items.product.model',
            'user'
        ]);

        return view(
            'purchases.show',
            compact('purchase')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SUPPRIMER ACHAT
    |--------------------------------------------------------------------------
    */

    public function destroy(Purchase $purchase)
    {
        DB::beginTransaction();

        try {

            foreach ($purchase->items as $item) {

                $product =
                    $item->product;

                /*
                |--------------------------------------------------------------------------
                | RETIRER STOCK
                |--------------------------------------------------------------------------
                */

                $product->quantity -=
                    $item->quantity;

                if (
                    $product->quantity < 0
                ) {

                    $product->quantity = 0;
                }

                /*
                |--------------------------------------------------------------------------
                | STATUS
                |--------------------------------------------------------------------------
                */

                if (
                    $product->quantity <= 0
                ) {

                    $product->status =
                        'rupture';

                } elseif (

                    $product->quantity <=
                    $product->min_stock

                ) {

                    $product->status =
                        'stock_faible';

                } else {

                    $product->status =
                        'disponible';
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

                    'type' =>
                        'out',

                    'quantity' =>
                        $item->quantity,

                    'source' =>
                        'Annulation achat',

                    'reference' =>
                        $purchase->reference,

                    'user_id' =>
                        auth()->id(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | DELETE ITEMS
            |--------------------------------------------------------------------------
            */

            $purchase->items()->delete();

            /*
            |--------------------------------------------------------------------------
            | DELETE PURCHASE
            |--------------------------------------------------------------------------
            */

            $purchase->delete();

            DB::commit();

            return redirect()
                ->route('purchases.index')
                ->with(
                    'success',
                    'Achat supprimé avec succès.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }
}
