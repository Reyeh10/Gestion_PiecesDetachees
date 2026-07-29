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
     * par immatriculation, période et statut.
     */
    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | Validation
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

            'status' => [
                'nullable',
                'string',
                'in:vendu,payé,annulé',
            ],
        ], [
            'date_from.date' =>
                'La date de début est invalide.',

            'date_to.date' =>
                'La date de fin est invalide.',

            'date_to.after_or_equal' =>
                'La date de fin doit être égale ou postérieure à la date de début.',

            'status.in' =>
                'Le statut sélectionné est invalide.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Filtres
        |--------------------------------------------------------------------------
        */

        $plate = $this->normalizePlate(
            (string) ($validated['plate'] ?? '')
        );

        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;
        $statusFilter = $validated['status'] ?? null;

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
        | Recherche
        |--------------------------------------------------------------------------
        */

        if ($plate !== '') {
            $query = SaleItem::query()
                ->with([
                    'product.brand',
                    'product.model',
                    'sale.customer',
                    'sale.vehicle',
                    'sale.payments',
                ])

                /*
                |--------------------------------------------------------------------------
                | Immatriculation
                |--------------------------------------------------------------------------
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
                | Uniquement les ventes
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
            | Date de début
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
            | Date de fin
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
            | Filtre par statut
            |--------------------------------------------------------------------------
            */

            if ($statusFilter !== null) {
                $query->whereHas(
                    'sale',
                    function (Builder $saleQuery) use ($statusFilter): void {
                        $saleQuery->where(
                            'status',
                            $statusFilter
                        );
                    }
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Résultats
            |--------------------------------------------------------------------------
            */

            $items = $query
                ->orderByDesc('sale_id')
                ->orderByDesc('id')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Nombre de lignes
            |--------------------------------------------------------------------------
            */

            $piecesCount = $items->count();

            /*
            |--------------------------------------------------------------------------
            | Quantité totale
            |--------------------------------------------------------------------------
            */

            $totalQuantity = $items->sum(
                function (SaleItem $item): float {
                    return (float) $item->quantity;
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Nombre de ventes distinctes
            |--------------------------------------------------------------------------
            */

            $salesCount = $items
                ->pluck('sale_id')
                ->filter()
                ->unique()
                ->count();

            /*
            |--------------------------------------------------------------------------
            | Montant total hors ventes annulées
            |--------------------------------------------------------------------------
            |
            | Les lignes annulées peuvent rester visibles dans le tableau,
            | mais leur montant ne sera jamais ajouté au total.
            |
            */

            $totalAmount = $items
                ->filter(function (SaleItem $item): bool {
                    $status = $this->normalizeStatus(
                        (string) ($item->sale?->status ?? '')
                    );

                    return !in_array(
                        $status,
                        [
                            'annule',
                            'annulee',
                            'cancelled',
                            'canceled',
                        ],
                        true
                    );
                })
                ->sum(function (SaleItem $item): float {
                    if ($item->total !== null) {
                        return (float) $item->total;
                    }

                    return (float) $item->price
                        * (float) $item->quantity;
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Vue
        |--------------------------------------------------------------------------
        */

        return view(
            'vehicles.history',
            [
                'plate' => $plate,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'statusFilter' => $statusFilter,
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
     */
    private function normalizePlate(string $plate): string
    {
        return preg_replace(
            '/[^A-Z0-9]/',
            '',
            strtoupper(trim($plate))
        ) ?? '';
    }

    /**
     * Normaliser un statut pour permettre les comparaisons,
     * même avec des accents ou des majuscules.
     */
    private function normalizeStatus(string $status): string
    {
        $status = mb_strtolower(
            trim($status),
            'UTF-8'
        );

        return str_replace(
            ['é', 'è', 'ê', 'ë', 'à', 'â', 'ù', 'û', 'ô', 'î', 'ï'],
            ['e', 'e', 'e', 'e', 'a', 'a', 'u', 'u', 'o', 'i', 'i'],
            $status
        );
    }
}
