<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryAdjustment;
use App\Models\StockMovement;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryAdjustmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = InventoryAdjustment::with([

            'product.brand',
            'product.model',
            'approver',
        ]);

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        if ($request->search) {

            $query->whereHas('product', function ($q) use ($request) {

                $q->where(
                    'designation',
                    'like',
                    '%' . $request->search . '%'
                )
                ->orWhere(
                    'reference',
                    'like',
                    '%' . $request->search . '%'
                );
            });
        }

        $adjustments = $query
            ->latest()
            ->paginate(15);

        return view(
            'inventory_adjustments.index',
            compact('adjustments')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $products = Product::with([

            'brand',
            'model',
            'family',
            'subfamily',
        ])
        ->orderBy('designation')
        ->get();

        return view(
            'inventory_adjustments.create',
            compact('products')
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

            'product_id' => 'required|exists:products,id',
            'new_qty' => 'required|integer|min:0',
            'reason' => 'required|string|max:1000',
        ]);

        try {

            DB::transaction(function () use ($request) {

                /*
                |--------------------------------------------------------------------------
                | PRODUIT
                |--------------------------------------------------------------------------
                */

                $product = Product::findOrFail(
                    $request->product_id
                );

                $oldQty = $product->quantity;

                $newQty = (int) $request->new_qty;

                /*
                |--------------------------------------------------------------------------
                | DIFFERENCE
                |--------------------------------------------------------------------------
                */

                $difference = abs(
                    $newQty - $oldQty
                );

                /*
                |--------------------------------------------------------------------------
                | AJUSTEMENT
                |--------------------------------------------------------------------------
                */

                $adjustment = InventoryAdjustment::create([

                    'product_id' => $product->id,
                    'old_qty' => $oldQty,
                    'new_qty' => $newQty,
                    'reason' => $request->reason,
                    'approved_by' => auth()->id(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | UPDATE PRODUIT
                |--------------------------------------------------------------------------
                */

                $product->update([

                    'quantity' => $newQty,
                ]);

                /*
                |--------------------------------------------------------------------------
                | MOUVEMENT STOCK
                |--------------------------------------------------------------------------
                */

                if ($difference > 0) {

                    StockMovement::create([

                        'product_id' => $product->id,
                        'type' =>
                            $newQty >= $oldQty
                                ? 'in'
                                : 'out',

                        'quantity' => $difference,
                        'source' => 'Ajustement inventaire',
                        'reference' =>
                            'ADJ-' . $adjustment->id,

                        'user_id' => auth()->id(),
                    ]);
                }
            });

            return redirect()
                ->route('inventory-adjustments.index')
                ->with(
                    'success',
                    'Ajustement enregistré avec succès.'
                );

        } catch (\Exception $e) {

            return back()
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
        InventoryAdjustment $inventoryAdjustment
    ) {
        $inventoryAdjustment->load([

            'product.brand',
            'product.model',
            'product.family',
            'product.subfamily',
            'approver',
        ]);

        return view(
            'inventory_adjustments.show',
            compact('inventoryAdjustment')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        InventoryAdjustment $inventoryAdjustment
    ) {
        return redirect()->route(
            'inventory-adjustments.index'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        InventoryAdjustment $inventoryAdjustment
    ) {
        return redirect()->route(
            'inventory-adjustments.index'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(
        InventoryAdjustment $inventoryAdjustment
    ) {
        $inventoryAdjustment->delete();

        return redirect()
            ->route('inventory-adjustments.index')
            ->with(
                'success',
                'Ajustement supprimé avec succès.'
            );
    }
}
