<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;

use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = StockMovement::with([

            'product.brand',
            'product.model',
            'user',

        ]);

        /*
        |--------------------------------------------------------------------------
        | RECHERCHE REFERENCE
        |--------------------------------------------------------------------------
        */

        if ($request->reference) {

            $query->where(

                'reference',
                'like',
                '%' . $request->reference . '%'

            );
        }

        /*
        |--------------------------------------------------------------------------
        | RECHERCHE DESIGNATION
        |--------------------------------------------------------------------------
        */

        if ($request->designation) {

            $query->whereHas('product', function ($q) use ($request) {

                $q->where(

                    'designation',
                    'like',
                    '%' . $request->designation . '%'

                );

            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRE TYPE
        |--------------------------------------------------------------------------
        */

        if ($request->type) {

            $query->where(

                'type',
                $request->type

            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRE DATE UNIQUE
        |--------------------------------------------------------------------------
        */

        if ($request->date) {

            $query->whereDate(

                'created_at',
                $request->date

            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRE DATE DEBUT
        |--------------------------------------------------------------------------
        */

        if ($request->date_from) {

            $query->whereDate(

                'created_at',
                '>=',
                $request->date_from

            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRE DATE FIN
        |--------------------------------------------------------------------------
        */

        if ($request->date_to) {

            $query->whereDate(

                'created_at',
                '<=',
                $request->date_to

            );
        }

        /*
        |--------------------------------------------------------------------------
        | ANCIENNE RECHERCHE GENERALE
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

        /*
        |--------------------------------------------------------------------------
        | RESULTATS
        |--------------------------------------------------------------------------
        */

        $movements = $query
            ->latest()
            ->paginate(20);

        return view(
            'stock_movements.index',

            compact('movements')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(StockMovement $stockMovement)
    {
        $stockMovement->load([
            'product.brand',
            'product.model',
            'product.family',
            'product.subfamily',
            'product.rayon',
            'product.location',
            'user',
        ]);

        return view(
            'stock_movements.show',
            compact('stockMovement')
        );
    }
        /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(StockMovement $stockMovement)
    {
        $stockMovement->load([
            'product.brand',
            'product.model',
            'user',
        ]);

        return view(
            'stock_movements.edit',
            compact('stockMovement')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, StockMovement $stockMovement)
    {
        $request->validate([

            'quantity' => 'required|numeric|min:0',
            'source' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
        ]);

        $stockMovement->update([

            'quantity' => $request->quantity,
            'source' => $request->source,
            'reference' => $request->reference,
        ]);

        return redirect()
            ->route('stock-movements.index')
            ->with(
                'success',
                'Mouvement modifié avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | ENTREES
    |--------------------------------------------------------------------------
    */

    public function entries()
    {
        $movements = StockMovement::with('product')
            ->where('type', 'in')
            ->latest()
            ->get();

        return view(
            'stock_movements.index',
            compact('movements')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SORTIES
    |--------------------------------------------------------------------------
    */

    public function exits()
    {
        $movements = StockMovement::with('product')
            ->where('type', 'out')
            ->latest()
            ->get();

        return view(
            'stock_movements.index',
            compact('movements')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(StockMovement $stockMovement)
    {
        $stockMovement->delete();

        return redirect()
            ->route('stock-movements.index')
            ->with(
                'success',
                'Mouvement supprimé avec succès.'
            );
    }
}
