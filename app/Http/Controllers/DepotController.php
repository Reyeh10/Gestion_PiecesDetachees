<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Models\DepotTransfer;
use App\Models\ProductDepotStock;
use App\Models\SaleItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepotController extends Controller
{
    public function index(): View
    {
        $depots = Depot::query()
            ->latest()
            ->get();

        return view(
            'depots.index',
            compact('depots')
        );
    }

    public function create(): View
    {
        return view(
            'depots.create'
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated =
            $request->validate([
                'name' =>
                    'required|string|max:255',

                'code' =>
                    'nullable|string|max:50',

                'address' =>
                    'nullable|string|max:255',

                'is_active' =>
                    'nullable|boolean',
            ]);

        Depot::create([
            'name' =>
                $validated['name'],

            'code' =>
                $validated['code']
                ?? null,

            'address' =>
                $validated['address']
                ?? null,

            'is_active' =>
                $request->boolean(
                    'is_active'
                ),
        ]);

        return redirect()
            ->route('depots.index')
            ->with(
                'success',
                'Dépôt créé avec succès.'
            );
    }

    public function edit(
        Depot $depot
    ): View {
        return view(
            'depots.edit',
            compact('depot')
        );
    }

    public function update(
        Request $request,
        Depot $depot
    ): RedirectResponse {
        $validated =
            $request->validate([
                'name' =>
                    'required|string|max:255',

                'code' =>
                    'nullable|string|max:50',

                'address' =>
                    'nullable|string|max:255',

                'is_active' =>
                    'nullable|boolean',
            ]);

        $depot->update([
            'name' =>
                $validated['name'],

            'code' =>
                $validated['code']
                ?? null,

            'address' =>
                $validated['address']
                ?? null,

            'is_active' =>
                $request->boolean(
                    'is_active'
                ),
        ]);

        return redirect()
            ->route('depots.index')
            ->with(
                'success',
                'Dépôt modifié avec succès.'
            );
    }

    public function show(
        Depot $depot
    ): View {
        $stocks =
            ProductDepotStock::query()
                ->with([
                    'product.brand',
                    'product.model',
                ])
                ->where(
                    'depot_id',
                    $depot->id
                )
                ->latest()
                ->get();

        $totalProducts =
            $stocks
                ->filter(
                    fn ($stock) =>
                        (float) $stock->quantity
                        >
                        0
                )
                ->count();

        $totalQuantity =
            (float) $stocks
                ->sum(
                    'quantity'
                );

        $lowStocks =
            $stocks
                ->filter(
                    function ($stock) {
                        return
                            (float) $stock->quantity
                            >
                            0
                            &&
                            (float) $stock->quantity
                            <=
                            (float) (
                                $stock->product->min_stock
                                ??
                                0
                            );
                    }
                )
                ->count();

        $ruptures =
            $stocks
                ->filter(
                    fn ($stock) =>
                        (float) $stock->quantity
                        <=
                        0
                )
                ->count();

        $totalValue =
            (float) $stocks
                ->sum(
                    function ($stock) {
                        return
                            (float) $stock->quantity
                            *
                            (float) (
                                $stock->product->sale_price
                                ??
                                0
                            );
                    }
                );

        $transfers =
            DepotTransfer::query()
                ->with([
                    'product',
                    'sourceDepot',
                    'destinationDepot',
                    'user',
                ])
                ->where(
                    function ($query) use ($depot) {
                        $query
                            ->where(
                                'source_depot_id',
                                $depot->id
                            )
                            ->orWhere(
                                'destination_depot_id',
                                $depot->id
                            );
                    }
                )
                ->latest()
                ->take(20)
                ->get();

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

    public function destroy(
        Depot $depot
    ): RedirectResponse {
        $hasStock =
            ProductDepotStock::query()
                ->where(
                    'depot_id',
                    $depot->id
                )
                ->where(
                    'quantity',
                    '>',
                    0
                )
                ->exists();

        if ($hasStock) {
            return back()->with(
                'error',
                'Impossible de supprimer ce dépôt : il contient encore du stock.'
            );
        }

        $usedBySales =
            SaleItem::query()
                ->where(
                    'depot_id',
                    $depot->id
                )
                ->exists();

        if ($usedBySales) {
            return back()->with(
                'error',
                'Impossible de supprimer ce dépôt : il est utilisé dans l’historique des ventes.'
            );
        }

        $usedByTransfers =
            DepotTransfer::query()
                ->where(
                    'source_depot_id',
                    $depot->id
                )
                ->orWhere(
                    'destination_depot_id',
                    $depot->id
                )
                ->exists();

        if ($usedByTransfers) {
            return back()->with(
                'error',
                'Impossible de supprimer ce dépôt : il est utilisé dans l’historique des transferts.'
            );
        }

        ProductDepotStock::query()
            ->where(
                'depot_id',
                $depot->id
            )
            ->delete();

        $depot->delete();

        return redirect()
            ->route('depots.index')
            ->with(
                'success',
                'Dépôt supprimé avec succès.'
            );
    }
}
