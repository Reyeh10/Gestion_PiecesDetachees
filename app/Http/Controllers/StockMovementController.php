<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    |
    | Affiche tous les mouvements de stock.
    | La recherche "reference" concerne maintenant la référence PRODUIT.
    |
    */
    public function index(Request $request): View
    {
        $query = $this->baseQuery();

        /*
        |--------------------------------------------------------------------------
        | RECHERCHE RÉFÉRENCE PRODUIT
        |--------------------------------------------------------------------------
        */
        if ($request->filled('reference')) {
            $reference = trim((string) $request->input('reference'));

            $query->whereHas('product', function (Builder $productQuery) use ($reference) {
                $productQuery->where(
                    'reference',
                    'like',
                    '%' . $reference . '%'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | RECHERCHE DÉSIGNATION PRODUIT
        |--------------------------------------------------------------------------
        */
        if ($request->filled('designation')) {
            $designation = trim((string) $request->input('designation'));

            $query->whereHas('product', function (Builder $productQuery) use ($designation) {
                $productQuery->where(
                    'designation',
                    'like',
                    '%' . $designation . '%'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRE TYPE
        |--------------------------------------------------------------------------
        */
        if ($request->filled('type')) {
            $type = $request->input('type');

            if (in_array($type, ['in', 'out'], true)) {
                $query->where('type', $type);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRE DATE UNIQUE
        |--------------------------------------------------------------------------
        */
        if ($request->filled('date')) {
            $query->whereDate(
                'created_at',
                $request->input('date')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRE DATE DÉBUT
        |--------------------------------------------------------------------------
        */
        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->input('date_from')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRE DATE FIN
        |--------------------------------------------------------------------------
        */
        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->input('date_to')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | RECHERCHE GÉNÉRALE
        |--------------------------------------------------------------------------
        |
        | Recherche :
        | - référence produit
        | - désignation produit
        | - référence document/mouvement
        | - source
        |
        */
        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function (Builder $movementQuery) use ($search) {
                $movementQuery
                    ->where(
                        'reference',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'source',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhereHas('product', function (Builder $productQuery) use ($search) {
                        $productQuery
                            ->where(
                                'reference',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'designation',
                                'like',
                                '%' . $search . '%'
                            );
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | RÉSULTATS
        |--------------------------------------------------------------------------
        */
        $movements = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

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
    public function show(StockMovement $stockMovement): View
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
    public function edit(StockMovement $stockMovement): View
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
    public function update(
        Request $request,
        StockMovement $stockMovement
    ): RedirectResponse {
        $validated = $request->validate([
            'quantity' => [
                'required',
                'numeric',
                'min:0',
            ],

            'source' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | RÉFÉRENCE DU DOCUMENT / MOUVEMENT
            |--------------------------------------------------------------------------
            |
            | Attention :
            | ceci n'est PAS la référence produit.
            |
            | Exemples :
            | FACT-2026-0110
            | ADJ-16
            | TRANS-001
            |
            */
            'reference' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $stockMovement->update([
            'quantity' => $validated['quantity'],
            'source' => $validated['source'] ?? null,
            'reference' => $validated['reference'] ?? null,
        ]);

        return redirect()
            ->route('stock-movements.show', $stockMovement)
            ->with(
                'success',
                'Mouvement modifié avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | ENTRÉES
    |--------------------------------------------------------------------------
    */
    public function entries(Request $request): View
    {
        $query = $this->baseQuery()
            ->where('type', 'in');

        $this->applyCommonFilters(
            $query,
            $request
        );

        $movements = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
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
    public function exits(Request $request): View
    {
        $query = $this->baseQuery()
            ->where('type', 'out');

        $this->applyCommonFilters(
            $query,
            $request
        );

        $movements = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
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
    public function destroy(
        StockMovement $stockMovement
    ): RedirectResponse {
        $stockMovement->delete();

        return redirect()
            ->back()
            ->with(
                'success',
                'Mouvement supprimé avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY DE BASE
    |--------------------------------------------------------------------------
    */
    private function baseQuery(): Builder
    {
        return StockMovement::query()
            ->with([
                'product.brand',
                'product.model',
                'user',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FILTRES COMMUNS
    |--------------------------------------------------------------------------
    |
    | Utilisés par :
    | - Entrées
    | - Sorties
    |
    */
    private function applyCommonFilters(
        Builder $query,
        Request $request
    ): void {
        /*
        |--------------------------------------------------------------------------
        | RÉFÉRENCE PRODUIT
        |--------------------------------------------------------------------------
        */
        if ($request->filled('reference')) {
            $reference = trim((string) $request->input('reference'));

            $query->whereHas(
                'product',
                function (Builder $productQuery) use ($reference) {
                    $productQuery->where(
                        'reference',
                        'like',
                        '%' . $reference . '%'
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DÉSIGNATION PRODUIT
        |--------------------------------------------------------------------------
        */
        if ($request->filled('designation')) {
            $designation = trim((string) $request->input('designation'));

            $query->whereHas(
                'product',
                function (Builder $productQuery) use ($designation) {
                    $productQuery->where(
                        'designation',
                        'like',
                        '%' . $designation . '%'
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DATE UNIQUE
        |--------------------------------------------------------------------------
        */
        if ($request->filled('date')) {
            $query->whereDate(
                'created_at',
                $request->input('date')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DATE DÉBUT
        |--------------------------------------------------------------------------
        */
        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->input('date_from')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DATE FIN
        |--------------------------------------------------------------------------
        */
        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->input('date_to')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | RECHERCHE GÉNÉRALE
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(
                function (Builder $movementQuery) use ($search) {
                    $movementQuery
                        ->where(
                            'reference',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'source',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhereHas(
                            'product',
                            function (Builder $productQuery) use ($search) {
                                $productQuery
                                    ->where(
                                        'reference',
                                        'like',
                                        '%' . $search . '%'
                                    )
                                    ->orWhere(
                                        'designation',
                                        'like',
                                        '%' . $search . '%'
                                    );
                            }
                        );
                }
            );
        }
    }
}
