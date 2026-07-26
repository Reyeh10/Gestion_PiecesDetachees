<?php

namespace App\Http\Controllers;

use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleHistoryController extends Controller
{
    /**
     * Afficher l’historique des pièces vendues par immatriculation.
     */
    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | Normalisation de l’immatriculation recherchée
        |--------------------------------------------------------------------------
        |
        | Exemples :
        |
        | 200 d 77
        | 200-D-77
        | 200D77
        |
        | deviennent :
        |
        | 200D77
        |
        */

        $plate = $this->normalizePlate(
            (string) $request->query('plate', '')
        );

        /*
        |--------------------------------------------------------------------------
        | Collection vide par défaut
        |--------------------------------------------------------------------------
        */

        $items = collect();

        /*
        |--------------------------------------------------------------------------
        | Recherche de l’historique
        |--------------------------------------------------------------------------
        */

        if ($plate !== '') {
            $items = SaleItem::query()

                /*
                |--------------------------------------------------------------------------
                | Charger toutes les relations nécessaires
                |--------------------------------------------------------------------------
                |
                | Le véhicule est maintenant associé à la vente :
                |
                | sale_items.sale_id
                |          ↓
                | sales.vehicle_id
                |          ↓
                | vehicles.id
                |
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
                | Rechercher l’immatriculation à travers la vente
                |--------------------------------------------------------------------------
                */

                ->whereHas(
                    'sale.vehicle',
                    function (Builder $vehicleQuery) use ($plate): void {
                        /*
                         * Cette comparaison fonctionne même si la plaque
                         * enregistrée contient des espaces ou des tirets.
                         *
                         * Exemple :
                         *
                         * 200-D-77
                         * 200 D 77
                         * 200D77
                         */

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
                )

                /*
                |--------------------------------------------------------------------------
                | Trier par vente récente
                |--------------------------------------------------------------------------
                */

                ->orderByDesc('sale_id')
                ->orderByDesc('id')
                ->get();
        }

        return view(
            'vehicles.history',
            [
                'plate' => $plate,
                'items' => $items,
            ]
        );
    }

    /**
     * Normaliser une immatriculation.
     */
    private function normalizePlate(string $plate): string
    {
        $plate = strtoupper(trim($plate));

        return preg_replace(
            '/[^A-Z0-9]/',
            '',
            $plate
        ) ?? '';
    }
}
