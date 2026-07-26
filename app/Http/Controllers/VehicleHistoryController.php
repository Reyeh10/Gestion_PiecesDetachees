<?php

namespace App\Http\Controllers;

use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleHistoryController extends Controller
{
    /**
     * Afficher l’historique des pièces vendues
     * par immatriculation et par période.
     */
    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | Validation des filtres
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'plate' => [
                'nullable',
                'string',
                'max:50',
            ],

            'date_from' => [
                'nullable',
                'date',
            ],

            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ], [
            'date_from.date' => 'La date de début est invalide.',

            'date_to.date' => 'La date de fin est invalide.',

            'date_to.after_or_equal' =>
                'La date de fin doit être égale ou postérieure à la date de début.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Normalisation des filtres
        |--------------------------------------------------------------------------
        */

        $plate = $this->normalizePlate(
            (string) ($validated['plate'] ?? '')
        );

        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Résultats par défaut
        |--------------------------------------------------------------------------
        */

        $items = collect();

        $salesCount = 0;
        $piecesCount = 0;
        $totalQuantity = 0;
        $totalAmount = 0;

        /*
        |--------------------------------------------------------------------------
        | Lancer la recherche
        |--------------------------------------------------------------------------
        |
        | La recherche est lancée lorsqu’une immatriculation est fournie.
        |
        */

        if ($plate !== '') {
            $query = SaleItem::query()

                /*
                |--------------------------------------------------------------------------
                | Relations nécessaires
                |--------------------------------------------------------------------------
                */

                ->with([
                    'product.brand',
                    'product.model',
                    'sale.customer',
                    'sale.vehicle',
                    'sale.payments',
                ])

                /*
                |--------------------------------------------------------------------------
                | Filtrer par immatriculation
                |--------------------------------------------------------------------------
                |
                | sale_items.sale_id
                |          ↓
                | sales.vehicle_id
                |          ↓
                | vehicles.plate_number
                |
                */

                ->whereHas(
                    'sale.vehicle',
                    function (Builder $vehicleQuery) use ($plate): void {
                        $vehicleQuery->whereRaw(
                            "
                            UPPER(
                                REPLACE(
                                    REPLACE(
                                        REPLACE(
                                            TRIM(plate_number),
                                            '-',
                                            ''
                                        ),
                                        ' ',
                                        ''
                                    ),
                                    '.',
                                    ''
                                )
                            ) = ?
                            ",
                            [$plate]
                        );
                    }
                )

                /*
                |--------------------------------------------------------------------------
                | Garder uniquement les ventes
                |--------------------------------------------------------------------------
                */

                ->whereHas(
                    'sale',
                    function (Builder $saleQuery): void {
                        $saleQuery->where(
                            'document_type',
                            'sale'
                        );
                    }
                );

            /*
            |--------------------------------------------------------------------------
            | Filtrer par date de début
            |--------------------------------------------------------------------------
            */

            if ($dateFrom !== null) {
                $startDate = Carbon::parse($dateFrom)
                    ->startOfDay();

                $query->whereHas(
                    'sale',
                    function (Builder $saleQuery) use ($startDate): void {
                        $saleQuery->where(
                            'created_at',
                            '>=',
                            $startDate
                        );
                    }
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Filtrer par date de fin
            |--------------------------------------------------------------------------
            */

            if ($dateTo !== null) {
                $endDate = Carbon::parse($dateTo)
                    ->endOfDay();

                $query->whereHas(
                    'sale',
                    function (Builder $saleQuery) use ($endDate): void {
                        $saleQuery->where(
                            'created_at',
                            '<=',
                            $endDate
                        );
                    }
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Récupérer les résultats
            |--------------------------------------------------------------------------
            */

            $items = $query
                ->orderByDesc('sale_id')
                ->orderByDesc('id')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Statistiques
            |--------------------------------------------------------------------------
            */

            // Nombre de lignes de pièces.
            $piecesCount = $items->count();

            // Nombre total de quantités vendues.
            $totalQuantity = $items->sum(
                function (SaleItem $item): float {
                    return (float) $item->quantity;
                }
            );

            // Nombre de ventes/factures différentes.
            $salesCount = $items
                ->pluck('sale_id')
                ->filter()
                ->unique()
                ->count();

            // Montant total HT des pièces vendues.
            $totalAmount = $items->sum(
                function (SaleItem $item): float {
                    /*
                    * Utiliser le total de la ligne s’il est enregistré.
                    * Sinon : prix unitaire × quantité.
                    */
                    if ($item->total !== null) {
                        return (float) $item->total;
                    }

                    return (float) $item->price * (float) $item->quantity;
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Retourner la vue
        |--------------------------------------------------------------------------
        */

        return view(
            'vehicles.history',
            [
                'plate' => $plate,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'items' => $items,
                'salesCount' => $salesCount,
                'piecesCount' => $piecesCount,
                'totalQuantity' => $totalQuantity,
                'totalAmount' => $totalAmount,
            ]
        );
    }

    /**
     * Normaliser une immatriculation.
     *
     * Exemples :
     *
     * 200 d 77
     * 200-D-77
     * 200D77
     *
     * deviennent tous :
     *
     * 200D77
     */
    private function normalizePlate(string $plate): string
    {
        $plate = strtoupper(
            trim($plate)
        );

        return preg_replace(
            '/[^A-Z0-9]/',
            '',
            $plate
        ) ?? '';
    }
}
