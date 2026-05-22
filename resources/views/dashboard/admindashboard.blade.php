@extends('layouts.layoutMaster')

@section('content')

<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold mb-1">Tableau de bord</h3>
            <p class="text-muted mb-0">
                Vue globale du magasin de pièces détachées STCD Motors
            </p>
        </div>
    </div>

    {{-- KPI --}}
    <div class="row g-4 mb-4">

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 dashboard-card border-primary-bottom">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Total pièces</p>
                        <h2 class="fw-bold mb-0">{{ $totalProducts }}</h2>
                    </div>
                    <div class="dashboard-icon bg-primary-light">
                        <i class="bx bx-package"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 dashboard-card border-success-bottom">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Pièces disponibles</p>
                        <h2 class="fw-bold mb-0">{{ $availableProducts }}</h2>
                    </div>
                    <div class="dashboard-icon bg-success-light">
                        <i class="bx bx-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 dashboard-card border-danger-bottom">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Pièces vendues</p>
                        <h2 class="fw-bold mb-0">{{ $soldProducts }}</h2>
                    </div>
                    <div class="dashboard-icon bg-danger-light">
                        <i class="bx bx-cart"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 dashboard-card border-warning-bottom">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Stock faible</p>
                        <h2 class="fw-bold mb-0">{{ $lowStock }}</h2>
                    </div>
                    <div class="dashboard-icon bg-warning-light">
                        <i class="bx bx-error"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4 mb-4">

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 dashboard-card border-danger-bottom">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Rupture stock</p>
                        <h2 class="fw-bold mb-0">{{ $outOfStock }}</h2>
                    </div>
                    <div class="dashboard-icon bg-danger-light">
                        <i class="bx bx-block"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 dashboard-card border-info-bottom">
                <div class="card-body">
                    <p class="text-muted mb-1">Valeur stock</p>
                    <h3 class="fw-bold mb-0">
                        {{ number_format($stockValue, 2) }}
                    </h3>
                    <small class="text-muted">Valeur basée sur prix achat</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 dashboard-card border-success-bottom">
                <div class="card-body">
                    <p class="text-muted mb-1">
                        Prix total stock
                    </p>
                    <h3 class="fw-bold mb-0">
                        {{ number_format($totalStockPrice, 2) }}
                    </h3>
                    <small class="text-muted">
                        Basé sur prix vente
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 dashboard-card border-primary-bottom">
                <div class="card-body">
                    <p class="text-muted mb-1">
                        Produits vendus
                    </p>
                    <h3 class="fw-bold mb-0">
                        {{ number_format($totalSoldPrice, 2) }}
                    </h3>
                    <small class="text-muted">
                        Total ventes réalisées
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 dashboard-card border-primary-bottom">
                <div class="card-body">
                    <p class="text-muted mb-1">Ventes du mois</p>
                    <h3 class="fw-bold mb-0">
                        {{ number_format($salesThisMonth, 2) }}
                    </h3>
                    <small class="text-muted">Total ventes payées/partielles</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 dashboard-card border-success-bottom">
                <div class="card-body">
                    <p class="text-muted mb-1">Nombre ventes mois</p>
                    <h3 class="fw-bold mb-0">
                        {{ $salesCountThisMonth }}
                    </h3>
                    <small class="text-muted">Transactions enregistrées</small>
                </div>
            </div>
        </div>

    </div>

    {{-- CHARTS --}}
    <div class="row g-4 mb-4">

        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-0">Ventes par mois</h5>
                        <small class="text-muted">Montant total des ventes</small>
                    </div>
                    <span class="badge bg-label-primary">{{ date('Y') }}</span>
                </div>
                <div class="card-body">
                    <div id="monthlySalesChart"></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="fw-bold mb-0">État du stock</h5>
                    <small class="text-muted">Disponible, faible, rupture</small>
                </div>
                <div class="card-body">
                    <div id="stockStatusChart"></div>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4 mb-4">

        <div class="col-md-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="fw-bold mb-0">Nombre de pièces vendues par mois</h5>
                    <small class="text-muted">Quantité totale vendue</small>
                </div>
                <div class="card-body">
                    <div id="monthlySoldChart"></div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="fw-bold mb-0">Top pièces vendues</h5>
                    <small class="text-muted">Les produits les plus vendus</small>
                </div>
                <div class="card-body">

                    @forelse($topProducts as $item)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                            <div>
                                <strong>
                                    {{ $item->product?->reference ?? '-' }}
                                </strong>
                                <div class="text-muted small">
                                    {{ $item->product?->designation ?? 'Produit supprimé' }}
                                </div>
                            </div>

                            <span class="badge bg-label-primary">
                                {{ $item->total_qty }} vendues
                            </span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucune vente enregistrée.</p>
                    @endforelse

                </div>
            </div>
        </div>

    </div>

    {{-- TABLES --}}
    <div class="row g-4">

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="fw-bold mb-0">Dernières ventes</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Facture</th>
                                <th>Client</th>
                                <th>Total</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestSales as $sale)
                                <tr>
                                    <td>
                                        <strong>{{ $sale->invoice_number }}</strong>
                                    </td>
                                    <td>
                                        {{ $sale->customer?->name ?? 'Vente comptoir' }}
                                    </td>
                                    <td>
                                        {{ number_format($sale->total, 2) }}
                                    </td>
                                    <td>
                                        @if($sale->status == 'paid')
                                            <span class="badge bg-success">Payée</span>
                                        @elseif($sale->status == 'partial')
                                            <span class="badge bg-warning text-dark">Partielle</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $sale->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Aucune vente récente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="fw-bold mb-0">Derniers mouvements de stock</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Produit</th>
                                <th>Type</th>
                                <th>Qté</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestMovements as $movement)
                                <tr>
                                    <td>
                                        {{ $movement->product?->reference ?? '-' }}
                                        <div class="text-muted small">
                                            {{ $movement->product?->designation ?? '' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($movement->type == 'in')
                                            <span class="badge bg-success">Entrée</span>
                                        @else
                                            <span class="badge bg-danger">Sortie</span>
                                        @endif
                                    </td>
                                    <td>{{ $movement->quantity }}</td>
                                    <td>{{ $movement->created_at?->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Aucun mouvement récent.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection

@section('page-style')
<style>
    .dashboard-card {
        border-radius: 16px;
        transition: all 0.25s ease;
        overflow: hidden;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12) !important;
    }

    .dashboard-icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    .bg-primary-light {
        background: rgba(105, 108, 255, 0.15);
        color: #696cff;
    }

    .bg-success-light {
        background: rgba(40, 199, 111, 0.15);
        color: #28c76f;
    }

    .bg-danger-light {
        background: rgba(255, 77, 79, 0.15);
        color: #ff4d4f;
    }

    .bg-warning-light {
        background: rgba(255, 159, 67, 0.15);
        color: #ff9f43;
    }

    .border-primary-bottom {
        border-bottom: 4px solid #696cff !important;
    }

    .border-success-bottom {
        border-bottom: 4px solid #28c76f !important;
    }

    .border-danger-bottom {
        border-bottom: 4px solid #ff4d4f !important;
    }

    .border-warning-bottom {
        border-bottom: 4px solid #ff9f43 !important;
    }

    .border-info-bottom {
        border-bottom: 4px solid #00cfe8 !important;
    }
</style>
@endsection

@section('page-script')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const months = @json($months);
    const monthlySales = @json($monthlySales);
   const monthlySold = @json($monthlySold ?? []);


    new ApexCharts(document.querySelector("#monthlySalesChart"), {
        chart: {
            type: 'area',
            height: 330,
            toolbar: {
                show: false
            }
        },
        series: [{
            name: 'Ventes',
            data: monthlySales
        }],
        xaxis: {
            categories: months
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        dataLabels: {
            enabled: false
        },
        colors: ['#696cff'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.35,
                opacityTo: 0.05
            }
        }
    }).render();

    new ApexCharts(document.querySelector("#stockStatusChart"), {
        chart: {
            type: 'donut',
            height: 320
        },
        series: [
            {{ $availableProducts }},
            {{ $lowStock }},
            {{ $outOfStock }}
        ],
        labels: [
            'Disponible',
            'Stock faible',
            'Rupture'
        ],
        colors: [
            '#28c76f',
            '#ff9f43',
            '#ff4d4f'
        ],
        legend: {
            position: 'bottom'
        }
    }).render();

    new ApexCharts(document.querySelector("#monthlySoldChart"), {
        chart: {
            type: 'bar',
            height: 320,
            toolbar: {
                show: false
            }
        },
        series: [{
            name: 'Pièces vendues',
         data: monthlySold.length ? monthlySold : [0,0,0,0,0,0,0,0,0,0,0,0]
        }],
        xaxis: {
            categories: months
        },
        plotOptions: {
            bar: {
                borderRadius: 8,
                columnWidth: '45%'
            }
        },
        colors: ['#00cfe8'],
        dataLabels: {
            enabled: false
        }
    }).render();

});
</script>

@endsection
