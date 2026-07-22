<?php

namespace App\Http\Controllers;

use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleHistoryController extends Controller
{
    /**
     * Rechercher l’historique des pièces par immatriculation.
     */
    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | NORMALISER L’IMMATRICULATION
        |--------------------------------------------------------------------------
        |
        | Exemple :
        | 1000 d 45
        | 1000-d-45
        | 1000D45
        |
        | deviennent tous :
        | 1000D45
        |
        */

        $plate = strtoupper(
            preg_replace(
                '/[^A-Z0-9]/',
                '',
                trim(
                    (string) $request->input(
                        'plate',
                        ''
                    )
                )
            ) ?? ''
        );

        /*
        |--------------------------------------------------------------------------
        | COLLECTION VIDE PAR DÉFAUT
        |--------------------------------------------------------------------------
        */

        $items = collect();

        /*
        |--------------------------------------------------------------------------
        | RECHERCHE
        |--------------------------------------------------------------------------
        */

        if ($plate !== '') {
            $items = SaleItem::query()
                ->with([
                    'vehicle',
                    'product.brand',
                    'product.model',
                    'sale.customer',
                    'sale.payments',
                ])

                /*
                |--------------------------------------------------------------------------
                | RECHERCHER DANS LA TABLE VEHICLES
                |--------------------------------------------------------------------------
                |
                | sale_items.vehicle_id
                |          ↓
                | vehicles.id
                |          ↓
                | vehicles.plate_number
                |
                */

                ->whereHas(
                    'vehicle',
                    function ($vehicleQuery) use ($plate) {
                        $vehicleQuery->where(
                            'plate_number',
                            $plate
                        );
                    }
                )

                /*
                |--------------------------------------------------------------------------
                | UNIQUEMENT LES VENTES
                |--------------------------------------------------------------------------
                */

                ->whereHas(
                    'sale',
                    function ($saleQuery) {
                        $saleQuery->where(
                            'document_type',
                            'sale'
                        );
                    }
                )

                /*
                |--------------------------------------------------------------------------
                | PLUS RÉCENT EN PREMIER
                |--------------------------------------------------------------------------
                */

                ->latest('id')
                ->get();
        }

        return view(
            'vehicles.history',
            compact(
                'plate',
                'items'
            )
        );
    }
}
