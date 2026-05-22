<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {

        /*
        |--------------------------------------------------------------------------
        | KPI PRODUITS
        |--------------------------------------------------------------------------
        */

        // STOCK ACTUEL DISPONIBLE
        $availableProducts =
            Product::sum('quantity');

        // TOTAL PIECES VENDUES
        $soldProducts =
            SaleItem::sum('quantity');

        // TOTAL GENERAL
        $totalProducts =
            $availableProducts + $soldProducts;

        // STOCK FAIBLE
        $lowStock =
            Product::whereColumn(
                    'quantity',
                    '<=',
                    'min_stock'
                )
                ->where('quantity', '>', 0)
                ->count();

        // RUPTURE STOCK
        $outOfStock =
            Product::where('quantity', '<=', 0)
                ->count();

        /*
        |--------------------------------------------------------------------------
        | VALEUR DU STOCK
        |--------------------------------------------------------------------------
        */

        $stockValue =
            Product::sum(
                DB::raw(
                    'quantity * purchase_price'
                )
            );

            /*
    |--------------------------------------------------------------------------
    | PRIX TOTAL STOCK
    |--------------------------------------------------------------------------
    */

    $totalStockPrice = Product::sum(

        DB::raw('quantity * sale_price')

    );

    /*
    |--------------------------------------------------------------------------
    | PRIX TOTAL PRODUITS VENDUS
    |--------------------------------------------------------------------------
    */

    $totalSoldPrice = Sale::whereIn('status', [

            'vendu',
            'payé',
            'partiel',
            'paid',
            'partial'

        ])

        ->where('document_type', 'sale')
        ->sum('total');

        /*
        |--------------------------------------------------------------------------
        | MONTANT TOTAL PIECES VENDUES
        |--------------------------------------------------------------------------
        */

        $totalSoldAmount =
            SaleItem::sum(
                DB::raw(
                    'quantity * price'
                )
            );

        /*
        |--------------------------------------------------------------------------
        | ENTREES / SORTIES
        |--------------------------------------------------------------------------
        */

        $totalEntriesAmount = 0;
        $totalOutputsAmount = 0;

        /*
        |--------------------------------------------------------------------------
        | SI stock_movements EXISTE
        |--------------------------------------------------------------------------
        */

        if (
            DB::getSchemaBuilder()->hasTable('stock_movements')
        ) {

            // ENTREES
            if (
                DB::getSchemaBuilder()->hasColumn(
                    'stock_movements',
                    'quantity'
                )
            ) {

                $totalEntriesAmount =
                    StockMovement::where('type', 'in')
                        ->sum('quantity');

                $totalOutputsAmount =
                    StockMovement::where('type', 'out')
                        ->sum('quantity');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | VENTES DU MOIS
        |--------------------------------------------------------------------------
        */

        $salesThisMonth =
            Sale::whereMonth(
                    'created_at',
                    now()->month
                )
                ->sum('total');

        /*
        |--------------------------------------------------------------------------
        | NOMBRE VENTES DU MOIS
        |--------------------------------------------------------------------------
        */

        $salesCountThisMonth =
            Sale::whereMonth(
                    'created_at',
                    now()->month
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | TOP PRODUITS VENDUS
        |--------------------------------------------------------------------------
        */

        $topProducts =
            SaleItem::select(
                    'product_id',
                    DB::raw(
                        'SUM(quantity) as total_qty'
                    )
                )
                ->with('product')
                ->groupBy('product_id')
                ->orderByDesc('total_qty')
                ->take(5)
                ->get();

        /*
        |--------------------------------------------------------------------------
        | DERNIERS MOUVEMENTS
        |--------------------------------------------------------------------------
        */

        $latestMovements =
            StockMovement::with('product')
                ->latest()
                ->take(5)
                ->get();

        /*
        |--------------------------------------------------------------------------
        | DERNIERES VENTES
        |--------------------------------------------------------------------------
        */

        $latestSales =
            Sale::with('customer')
                ->latest()
                ->take(5)
                ->get();

        /*
        |--------------------------------------------------------------------------
        | CHARTS
        |--------------------------------------------------------------------------
        */

        $months = [
            'Jan',
            'Fév',
            'Mar',
            'Avr',
            'Mai',
            'Juin',
            'Juil',
            'Août',
            'Sep',
            'Oct',
            'Nov',
            'Déc'
        ];

        /*
        |--------------------------------------------------------------------------
        | MONTANT VENTES PAR MOIS
        |--------------------------------------------------------------------------
        */

        $monthlySales = [];

        /*
        |--------------------------------------------------------------------------
        | QUANTITES VENDUES PAR MOIS
        |--------------------------------------------------------------------------
        */

        $monthlySold = [];

        for ($i = 1; $i <= 12; $i++) {

            // MONTANT VENTES
            $monthlySales[] =

                Sale::whereMonth(
                        'created_at',
                        $i
                    )
                    ->sum('total');

            // QUANTITES VENDUES
            $monthlySold[] =

                SaleItem::whereMonth(
                        'created_at',
                        $i
                    )
                    ->sum('quantity');
        }



        /*
        |--------------------------------------------------------------------------
        | DASHBOARD ADMIN
        |--------------------------------------------------------------------------
        */

        if (
            auth()->user()->role == 'admin'
        ) {

            return view(
                'dashboard.admindashboard',
                compact(
                    'totalProducts',
                    'availableProducts',
                    'soldProducts',
                    'lowStock',
                    'outOfStock',
                    'stockValue',
                    'totalSoldAmount',
                    'totalEntriesAmount',
                    'totalOutputsAmount',
                    'salesThisMonth',
                    'salesCountThisMonth',
                    'topProducts',
                    'latestMovements',
                    'latestSales',
                    'months',
                    'monthlySales',
                    'totalStockPrice',
                    'totalSoldPrice',
                    'monthlySold'
                )
            );
        }

        /*
|--------------------------------------------------------------------------
| DASHBOARD EMPLOYEE
|--------------------------------------------------------------------------
*/

        return view(
            'dashboard.employee',
            compact(
                'totalProducts',
                'availableProducts',
                'soldProducts',
                'lowStock',
                'outOfStock',
                'stockValue',
                'salesThisMonth',
                'salesCountThisMonth',
                'topProducts',
                'latestMovements',
                'latestSales',
                'months',
                'monthlySales',
                'monthlySold'
            )
        );
    }
}
