<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Models\ProductDepotStock;
use App\Models\DepotTransfer;
use Illuminate\Http\Request;

class DepotController extends Controller
{
    public function index()
    {
        $depots = Depot::latest()->get();

        return view('depots.index', compact('depots'));
    }

    public function create()
    {
        return view('depots.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        Depot::create([
            'name' => $request->name,
            'code' => $request->code,
            'address' => $request->address,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('depots.index')
            ->with('success', 'Dépôt créé avec succès.');
    }

    public function edit(Depot $depot)
    {
        return view('depots.edit', compact('depot'));
    }

    public function update(Request $request, Depot $depot)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $depot->update([
            'name' => $request->name,
            'code' => $request->code,
            'address' => $request->address,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('depots.index')
            ->with('success', 'Dépôt modifié avec succès.');
    }

    public function show(Depot $depot)
    {
        /*
        |--------------------------------------------------------------------------
        | STOCKS
        |--------------------------------------------------------------------------
        */

        $stocks = ProductDepotStock::with([

            'product.brand',
            'product.model',

        ])

        ->where('depot_id', $depot->id)

        ->latest()

        ->get();

        /*
        |--------------------------------------------------------------------------
        | KPI
        |--------------------------------------------------------------------------
        */

        $totalProducts = $stocks
            ->filter(function ($stock) {

                return $stock->quantity > 0;
            })
            ->count();

        $totalQuantity = $stocks->sum('quantity');

        $lowStocks = $stocks->filter(function ($stock) {

            return $stock->quantity > 0
                &&
                $stock->quantity <=
                ($stock->product->min_stock ?? 0);

        })->count();

        $ruptures = $stocks->filter(function ($stock) {

            return $stock->quantity <= 0;

        })->count();

        /*
        |--------------------------------------------------------------------------
        | VALEUR STOCK
        |--------------------------------------------------------------------------
        */

        $totalValue = $stocks->sum(function ($stock) {

            return $stock->quantity
                *
                ($stock->product->sale_price ?? 0);

        });

        /*
        |--------------------------------------------------------------------------
        | TRANSFERTS
        |--------------------------------------------------------------------------
        */

        $transfers = DepotTransfer::with([

            'product',
            'sourceDepot',
            'destinationDepot',
            'user',

        ])

        ->where(function ($query) use ($depot) {

            $query->where(
                'source_depot_id',
                $depot->id
            )

            ->orWhere(
                'destination_depot_id',
                $depot->id
            );

        })

        ->latest()

        ->take(20)

        ->get();

        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return view(

            'depots.show',

            compact(

                'depot',
                'stocks',
                'transfers',
                'totalProducts',
                'totalQuantity',
                'lowStocks',
                'ruptures',
                'totalValue'

            )
        );
    }

    public function destroy(Depot $depot)
    {
        $depot->delete();

        return redirect()
            ->route('depots.index')
            ->with('success', 'Dépôt supprimé avec succès.');
    }
}
