<?php

namespace App\Http\Controllers;

use App\Models\BonTransfert;
use App\Models\ExternalBonCommande;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\VehiclePartRequest;
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
        SaleItem::whereHas('sale', function ($q) {

            $q->whereNotIn(
                'status',
                ['cancelled']
            );

        })->sum('quantity');

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
        | SUIVI DES PIÈCES POUR LES VÉHICULES
        |--------------------------------------------------------------------------
        |
        | On compte ici le nombre de demandes de pièces dans chaque état.
        | On utilise count() plutôt que sum(quantity), car les demandes peuvent
        | avoir des unités différentes (Pièce, Litre, etc.).
        |
        */

        $searchingPartRequests =
            VehiclePartRequest::where(
                'status',
                VehiclePartRequest::STATUS_SEARCHING
            )->count();

        $orderedPartRequests =
            VehiclePartRequest::where(
                'status',
                VehiclePartRequest::STATUS_ORDERED
            )->count();

        $receivedPartRequests =
            VehiclePartRequest::where(
                'status',
                VehiclePartRequest::STATUS_RECEIVED
            )->count();

        $notFoundPartRequests =
            VehiclePartRequest::where(
                'status',
                VehiclePartRequest::STATUS_NOT_FOUND
            )->count();

        /*
        |--------------------------------------------------------------------------
        | VALEUR DU STOCK
        |--------------------------------------------------------------------------
        */

       /* $stockValue =
            Product::sum(
                DB::raw(
                    'quantity * purchase_price'
                )
            );*/

            $stockValue =
                Product::sum(
                    DB::raw(
                        'quantity * cost_price'
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
        SaleItem::whereHas('sale', function ($q) {

            $q->whereNotIn(
                'status',
                ['cancelled']
            );

        })->sum(
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
            Sale::whereNotIn(
                    'status',
                    ['cancelled']
                )
                ->whereMonth(
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
            Sale::whereNotIn(
                    'status',
                    ['cancelled']
                )
                ->whereMonth(
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
    SaleItem::whereHas('sale', function ($q) {

            $q->whereNotIn(
                'status',
                ['cancelled']
            );

        })

        ->select(
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

            ->whereNotIn(
                'status',
                ['cancelled']
            )

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

            Sale::whereNotIn(
                    'status',
                    ['cancelled']
                )

                ->whereMonth(
                    'created_at',
                    $i
                )

                ->sum('total');

            // QUANTITES VENDUES
           $monthlySold[] =

            SaleItem::whereHas('sale', function ($q) {
                    $q->whereNotIn(
                        'status',
                        ['cancelled']
                    );
                })
                ->whereMonth(
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

        // Pièces en stock faible ou en rupture (bloc « Stock faible » du
        // tableau de bord, comme DreamsPOS) : les plus urgentes d'abord.
        $stockFaible = Product::query()
            ->where(function ($q) {
                $q->where('quantity', '<=', 0)
                    ->orWhere(fn ($w) => $w->where('min_stock', '>', 0)->whereColumn('quantity', '<=', 'min_stock'));
            })
            ->orderBy('quantity')
            ->orderBy('designation')
            ->take(6)
            ->get(['id', 'reference', 'designation', 'quantity', 'min_stock', 'unit_type']);

        // V6 : valeur des pièces sorties par destination, mois par mois
        $anneeSorties = (int) request('annee', now()->year);
        $anneeSorties = $anneeSorties >= 2000 && $anneeSorties <= now()->year + 1 ? $anneeSorties : now()->year;
        $sortiesParDestination = $this->sortiesParDestination($anneeSorties);

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
                    'notFoundPartRequests',
                    'receivedPartRequests',
                    'orderedPartRequests',
                    'searchingPartRequests',
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
                    'monthlySold',
                    'stockFaible',
                    'anneeSorties',
                    'sortiesParDestination'
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

    /**
     * V6 : valeur des sorties de pièces par destination et par mois (année
     * donnée) : factures clients directs + bons de transfert selon leur type.
     *
     * @return array<string, array{libelle:string, lien:?string, mois:array<int,float>, nombre:array<int,int>, total:float, nb:int}>
     */
    private function sortiesParDestination(int $annee): array
    {
        $destinations = [
            'clients'    => ['libelle' => 'Clients directs (factures)', 'lien' => route('sales.index')],
            'garage'     => ['libelle' => 'Atelier – véhicules (BC garage)', 'lien' => route('bons-transfert.index', ['type' => 'garage'])],
            'bus'        => ['libelle' => 'Bus leasing / transport', 'lien' => route('bons-transfert.index', ['type' => 'bus'])],
            ExternalBonCommande::MOTIF_CONSO => ['libelle' => 'Consommation atelier (équipements)', 'lien' => route('bons-transfert.index', ['type' => ExternalBonCommande::MOTIF_CONSO])],
        ];
        foreach (ExternalBonCommande::MOTIFS as $motif => $libelle) {
            $destinations[$motif] ??= ['libelle' => $libelle, 'lien' => route('bons-transfert.index', ['type' => $motif])];
        }
        foreach ($destinations as &$d) {
            $d += ['mois' => array_fill(1, 12, 0.0), 'nombre' => array_fill(1, 12, 0), 'total' => 0.0, 'nb' => 0];
        }
        unset($d);

        $ajouter = function (string $cle, int $mois, float $valeur, int $nombre) use (&$destinations) {
            $destinations[$cle]['mois'][$mois] += $valeur;
            $destinations[$cle]['nombre'][$mois] += $nombre;
            $destinations[$cle]['total'] += $valeur;
            $destinations[$cle]['nb'] += $nombre;
        };

        Sale::query()->whereNotIn('status', ['cancelled'])->whereYear('created_at', $annee)
            ->selectRaw('MONTH(created_at) mois, SUM(total) valeur, COUNT(*) nombre')
            ->groupByRaw('MONTH(created_at)')->get()
            ->each(fn ($r) => $ajouter('clients', (int) $r->mois, (float) $r->valeur, (int) $r->nombre));

        $bc = (new ExternalBonCommande)->getTable();
        $bt = (new BonTransfert)->getTable();
        $conso = ExternalBonCommande::MOTIF_CONSO;

        BonTransfert::query()
            ->leftJoin($bc, "{$bc}.id", '=', "{$bt}.external_bon_commande_id")
            ->whereYear("{$bt}.created_at", $annee)
            ->selectRaw("MONTH({$bt}.created_at) mois, SUM({$bt}.total) valeur, COUNT(*) nombre, CASE
                WHEN {$bc}.source_system = ? THEN 'bus'
                WHEN {$bc}.source_system = ? OR {$bc}.equipement IS NOT NULL THEN '{$conso}'
                WHEN {$bc}.source_system = ? THEN {$bc}.motif
                ELSE 'garage' END destination", [ExternalBonCommande::SOURCE_BUS, ExternalBonCommande::SOURCE_CONSO, ExternalBonCommande::SOURCE_INTERNE])
            ->groupByRaw('mois, destination')->get()
            ->each(fn ($r) => $ajouter(isset($destinations[$r->destination]) ? $r->destination : 'autre', (int) $r->mois, (float) $r->valeur, (int) $r->nombre));

        return $destinations;
    }
}
