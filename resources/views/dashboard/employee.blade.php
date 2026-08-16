@extends('layouts.layoutMaster')

@section('content')

<div class="dashboard-page">

    <div class="dashboard-container">


        {{-- ============================================================
             EN-TÊTE PRINCIPAL
        ============================================================ --}}

        <div class="dashboard-main-header">

            <div>
                <h2 class="dashboard-main-title">
                    Tableau de bord
                </h2>

                <p class="dashboard-main-subtitle">
                    Vue globale du magasin de pièces détachées STCD Motors
                </p>
            </div>

        </div>



        {{-- ============================================================
             SECTION 1 : VUE D'ENSEMBLE
        ============================================================ --}}

        <div class="dashboard-section">

            <div class="dashboard-section-header">

                <div class="dashboard-section-icon bg-primary-soft">
                    <i class="bx bx-grid-alt"></i>
                </div>

                <div>
                    <h4 class="dashboard-section-title">
                        Vue d'ensemble
                    </h4>

                    <p class="dashboard-section-description">
                        Principaux indicateurs du magasin
                    </p>
                </div>

            </div>


            <div class="row g-4">


                {{-- TOTAL PIÈCES --}}
                <div class="col-xl-4 col-lg-4 col-md-6">

                    <div class="
                        card
                        border-0
                        shadow-sm
                        h-100
                        dashboard-card
                        border-primary-bottom
                    ">

                        <div class="
                            card-body
                            d-flex
                            align-items-center
                            justify-content-between
                        ">

                            <div>

                                <p class="dashboard-kpi-label">
                                    Total pièces
                                </p>

                                <h2 class="dashboard-kpi-value">
                                    {{ $totalProducts }}
                                </h2>

                                <small class="dashboard-kpi-description">
                                    Références enregistrées
                                </small>

                            </div>


                            <div class="dashboard-icon bg-primary-light">

                                <i class="bx bx-package"></i>

                            </div>

                        </div>

                    </div>

                </div>



                {{-- PIÈCES DISPONIBLES --}}
                <div class="col-xl-4 col-lg-4 col-md-6">

                    <div class="
                        card
                        border-0
                        shadow-sm
                        h-100
                        dashboard-card
                        border-success-bottom
                    ">

                        <div class="
                            card-body
                            d-flex
                            align-items-center
                            justify-content-between
                        ">

                            <div>

                                <p class="dashboard-kpi-label">
                                    Pièces disponibles
                                </p>

                                <h2 class="dashboard-kpi-value">
                                    {{ $availableProducts }}
                                </h2>

                                <small class="dashboard-kpi-description">
                                    Stock disponible
                                </small>

                            </div>


                            <div class="dashboard-icon bg-success-light">

                                <i class="bx bx-check-circle"></i>

                            </div>

                        </div>

                    </div>

                </div>



                {{-- PIÈCES VENDUES --}}
                <div class="col-xl-4 col-lg-4 col-md-6">

                    <div class="
                        card
                        border-0
                        shadow-sm
                        h-100
                        dashboard-card
                        border-info-bottom
                    ">

                        <div class="
                            card-body
                            d-flex
                            align-items-center
                            justify-content-between
                        ">

                            <div>

                                <p class="dashboard-kpi-label">
                                    Pièces vendues
                                </p>

                                <h2 class="dashboard-kpi-value">
                                    {{ $soldProducts }}
                                </h2>

                                <small class="dashboard-kpi-description">
                                    Quantité totale vendue
                                </small>

                            </div>


                            <div class="dashboard-icon bg-info-light">

                                <i class="bx bx-cart"></i>

                            </div>

                        </div>

                    </div>

                </div>



                {{-- STOCK FAIBLE --}}
                <div class="col-xl-4 col-lg-4 col-md-6">

                    <div class="
                        card
                        border-0
                        shadow-sm
                        h-100
                        dashboard-card
                        border-warning-bottom
                    ">

                        <div class="
                            card-body
                            d-flex
                            align-items-center
                            justify-content-between
                        ">

                            <div>

                                <p class="dashboard-kpi-label">
                                    Stock faible
                                </p>

                                <h2 class="dashboard-kpi-value">
                                    {{ $lowStock }}
                                </h2>

                                <small class="dashboard-kpi-description">
                                    Pièces à surveiller
                                </small>

                            </div>


                            <div class="dashboard-icon bg-warning-light">

                                <i class="bx bx-error"></i>

                            </div>

                        </div>

                    </div>

                </div>



                {{-- RUPTURE STOCK --}}
                <div class="col-xl-4 col-lg-4 col-md-6">

                    <div class="
                        card
                        border-0
                        shadow-sm
                        h-100
                        dashboard-card
                        border-danger-bottom
                    ">

                        <div class="
                            card-body
                            d-flex
                            align-items-center
                            justify-content-between
                        ">

                            <div>

                                <p class="dashboard-kpi-label">
                                    Rupture de stock
                                </p>

                                <h2 class="dashboard-kpi-value">
                                    {{ $outOfStock }}
                                </h2>

                                <small class="dashboard-kpi-description">
                                    Pièces indisponibles
                                </small>

                            </div>


                            <div class="dashboard-icon bg-danger-light">

                                <i class="bx bx-block"></i>

                            </div>

                        </div>

                    </div>

                </div>



                {{-- VENTES DU MOIS --}}
                <div class="col-xl-4 col-lg-4 col-md-6">

                    <div class="
                        card
                        border-0
                        shadow-sm
                        h-100
                        dashboard-card
                        border-purple-bottom
                    ">

                        <div class="
                            card-body
                            d-flex
                            align-items-center
                            justify-content-between
                        ">

                            <div>

                                <p class="dashboard-kpi-label">
                                    Ventes du mois
                                </p>

                                <h2 class="dashboard-kpi-value">
                                    {{ $salesCountThisMonth }}
                                </h2>

                                <small class="dashboard-kpi-description">
                                    Transactions enregistrées
                                </small>

                            </div>


                            <div class="dashboard-icon bg-purple-light">

                                <i class="bx bx-receipt"></i>

                            </div>

                        </div>

                    </div>

                </div>


            </div>

        </div>



        {{-- ============================================================
             SECTION 2 : GESTION DU STOCK
        ============================================================ --}}

        <div class="dashboard-section">

            <div class="dashboard-section-header">

                <div class="dashboard-section-icon bg-success-soft">
                    <i class="bx bx-cube"></i>
                </div>

                <div>
                    <h4 class="dashboard-section-title">
                        Gestion du stock
                    </h4>

                    <p class="dashboard-section-description">
                        Suivi des entrées, sorties et niveaux de stock
                    </p>
                </div>

            </div>


            <div class="row g-4">


                {{-- DERNIERS MOUVEMENTS --}}
                <div class="col-xl-8 col-lg-7">

                    <div class="card border-0 shadow-sm h-100 dashboard-content-card">

                        <div class="card-header dashboard-card-header">

                            <div>

                                <h5 class="dashboard-card-title">
                                    Derniers mouvements de stock
                                </h5>

                                <p class="dashboard-card-subtitle">
                                    Dernières entrées et sorties enregistrées
                                </p>

                            </div>

                            <div class="dashboard-card-header-icon">
                                <i class="bx bx-transfer-alt"></i>
                            </div>

                        </div>


                        <div class="card-body table-responsive">

                            <table class="table table-hover align-middle dashboard-table">

                                <thead>

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

                                                <strong>
                                                    {{
                                                        $movement->product?->reference
                                                        ?? '-'
                                                    }}
                                                </strong>

                                                <div class="text-muted small">

                                                    {{
                                                        $movement->product?->designation
                                                        ?? ''
                                                    }}

                                                </div>

                                            </td>


                                            <td>

                                                @if($movement->type == 'in')

                                                    <span class="badge bg-success">

                                                        <i class="bx bx-down-arrow-alt me-1"></i>

                                                        Entrée

                                                    </span>

                                                @else

                                                    <span class="badge bg-danger">

                                                        <i class="bx bx-up-arrow-alt me-1"></i>

                                                        Sortie

                                                    </span>

                                                @endif

                                            </td>


                                            <td>

                                                <strong>
                                                    {{ $movement->quantity }}
                                                </strong>

                                            </td>


                                            <td>

                                                {{
                                                    $movement->created_at
                                                        ?->format('d/m/Y')
                                                }}

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td
                                                colspan="4"
                                                class="text-center text-muted py-5"
                                            >

                                                <i class="
                                                    bx
                                                    bx-transfer
                                                    fs-1
                                                    d-block
                                                    mb-2
                                                "></i>

                                                Aucun mouvement récent.

                                            </td>

                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>



                {{-- ÉTAT DU STOCK --}}
                <div class="col-xl-4 col-lg-5">

                    <div class="card border-0 shadow-sm h-100 dashboard-content-card">

                        <div class="card-header dashboard-card-header">

                            <div>

                                <h5 class="dashboard-card-title">
                                    État du stock
                                </h5>

                                <p class="dashboard-card-subtitle">
                                    Disponible, faible et rupture
                                </p>

                            </div>

                            <div class="dashboard-card-header-icon">
                                <i class="bx bx-doughnut-chart"></i>
                            </div>

                        </div>


                        <div class="
                            card-body
                            d-flex
                            align-items-center
                            justify-content-center
                        ">

                            <div id="stockStatusChart"></div>

                        </div>

                    </div>

                </div>


            </div>

        </div>



        {{-- ============================================================
             SECTION 3 : ANALYSE DES VENTES
        ============================================================ --}}

        <div class="dashboard-section">

            <div class="dashboard-section-header">

                <div class="dashboard-section-icon bg-info-soft">
                    <i class="bx bx-line-chart"></i>
                </div>

                <div>

                    <h4 class="dashboard-section-title">
                        Analyse des ventes
                    </h4>

                    <p class="dashboard-section-description">
                        Évolution des ventes et produits les plus vendus
                    </p>

                </div>

            </div>


            <div class="row g-4">


                {{-- PIÈCES VENDUES PAR MOIS --}}
                <div class="col-xl-8">

                    <div class="card border-0 shadow-sm h-100 dashboard-content-card">

                        <div class="card-header dashboard-card-header">

                            <div>

                                <h5 class="dashboard-card-title">
                                    Pièces vendues par mois
                                </h5>

                                <p class="dashboard-card-subtitle">
                                    Quantité totale vendue chaque mois
                                </p>

                            </div>


                            <div class="dashboard-card-header-icon">

                                <i class="bx bx-bar-chart-alt-2"></i>

                            </div>

                        </div>


                        <div class="card-body">

                            <div id="monthlySoldChart"></div>

                        </div>

                    </div>

                </div>



                {{-- TOP PIÈCES --}}
                <div class="col-xl-4">

                    <div class="card border-0 shadow-sm h-100 dashboard-content-card">

                        <div class="card-header dashboard-card-header">

                            <div>

                                <h5 class="dashboard-card-title">
                                    Top pièces vendues
                                </h5>

                                <p class="dashboard-card-subtitle">
                                    Produits les plus demandés
                                </p>

                            </div>


                            <div class="dashboard-card-header-icon">

                                <i class="bx bx-trophy"></i>

                            </div>

                        </div>


                        <div class="card-body">

                            @forelse($topProducts as $item)

                                <div class="
                                    top-product-row
                                    d-flex
                                    justify-content-between
                                    align-items-center
                                ">

                                    <div>

                                        <strong>

                                            {{
                                                $item->product?->reference
                                                ?? '-'
                                            }}

                                        </strong>

                                        <div class="text-muted small">

                                            {{
                                                $item->product?->designation
                                                ?? 'Produit supprimé'
                                            }}

                                        </div>

                                    </div>


                                    <span class="badge bg-label-primary">

                                        {{ $item->total_qty }}

                                        vendues

                                    </span>

                                </div>

                            @empty

                                <div class="dashboard-empty-state">

                                    <i class="bx bx-cart"></i>

                                    <p>
                                        Aucune vente enregistrée.
                                    </p>

                                </div>

                            @endforelse

                        </div>

                    </div>

                </div>


            </div>

        </div>



        {{-- ============================================================
             SECTION 4 : ACTIVITÉ COMMERCIALE
        ============================================================ --}}

        <div class="dashboard-section">

            <div class="dashboard-section-header">

                <div class="dashboard-section-icon bg-purple-soft">
                    <i class="bx bx-store"></i>
                </div>

                <div>

                    <h4 class="dashboard-section-title">
                        Activité commerciale
                    </h4>

                    <p class="dashboard-section-description">
                        Suivi des transactions récemment enregistrées
                    </p>

                </div>

            </div>


            <div class="row">

                <div class="col-12">

                    <div class="card border-0 shadow-sm dashboard-content-card">

                        <div class="card-header dashboard-card-header">

                            <div>

                                <h5 class="dashboard-card-title">
                                    Dernières ventes
                                </h5>

                                <p class="dashboard-card-subtitle">
                                    Transactions les plus récentes
                                </p>

                            </div>


                            <div class="dashboard-card-header-icon">

                                <i class="bx bx-receipt"></i>

                            </div>

                        </div>


                        <div class="card-body table-responsive">

                            <table class="
                                table
                                table-hover
                                align-middle
                                dashboard-table
                            ">

                                <thead>

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

                                                <strong>
                                                    {{ $sale->invoice_number }}
                                                </strong>

                                            </td>


                                            <td>

                                                {{
                                                    $sale->customer?->name
                                                    ?? 'Vente comptoir'
                                                }}

                                            </td>


                                            <td>

                                                <strong>
                                                    {{
                                                        number_format(
                                                            $sale->total,
                                                            2,
                                                            ',',
                                                            ' '
                                                        )
                                                    }}
                                                </strong>

                                            </td>


                                            <td>

                                                @if($sale->status == 'paid')

                                                    <span class="badge bg-success">
                                                        Payée
                                                    </span>

                                                @elseif($sale->status == 'partial')

                                                    <span class="
                                                        badge
                                                        bg-warning
                                                        text-dark
                                                    ">
                                                        Partielle
                                                    </span>

                                                @else

                                                    <span class="badge bg-secondary">

                                                        {{ $sale->status }}

                                                    </span>

                                                @endif

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td
                                                colspan="4"
                                                class="
                                                    text-center
                                                    text-muted
                                                    py-5
                                                "
                                            >

                                                <i class="
                                                    bx
                                                    bx-receipt
                                                    fs-1
                                                    d-block
                                                    mb-2
                                                "></i>

                                                Aucune vente récente.

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


    </div>

</div>

@endsection



{{-- ================================================================
     STYLE
================================================================ --}}

@section('page-style')

<style>

    /* ============================================================
       PAGE
    ============================================================ */

    .dashboard-page {
        width: 100%;
        padding: 22px 18px 50px;
    }

    .dashboard-container {
        width: 100%;
        max-width: 1600px;
        margin: 0 auto;
    }


    /* ============================================================
       HEADER PRINCIPAL
    ============================================================ */

    .dashboard-main-header {
        margin-bottom: 28px;
    }

    .dashboard-main-title {
        margin: 0 0 5px;

        font-size: clamp(25px, 2vw, 32px);
        font-weight: 800;

        letter-spacing: -0.6px;

        color: #334155;
    }

    .dashboard-main-subtitle {
        margin: 0;

        font-size: 14px;

        color: #94a3b8;
    }


    /* ============================================================
       SECTIONS
    ============================================================ */

    .dashboard-section {
        margin-bottom: 35px;
    }

    .dashboard-section-header {
        display: flex;
        align-items: center;

        gap: 12px;

        margin-bottom: 18px;

        padding-bottom: 12px;

        border-bottom: 1px solid #e7ebf0;
    }

    .dashboard-section-icon {
        width: 42px;
        height: 42px;

        display: flex;
        align-items: center;
        justify-content: center;

        flex-shrink: 0;

        border-radius: 11px;

        font-size: 22px;
    }

    .dashboard-section-title {
        margin: 0 0 2px;

        font-size: 18px;
        font-weight: 800;

        color: #334155;
    }

    .dashboard-section-description {
        margin: 0;

        font-size: 12px;

        color: #94a3b8;
    }


    /* ============================================================
       COULEURS TITRES SECTIONS
    ============================================================ */

    .bg-primary-soft {
        color: #696cff;
        background: rgba(105,108,255,.12);
    }

    .bg-success-soft {
        color: #28c76f;
        background: rgba(40,199,111,.12);
    }

    .bg-info-soft {
        color: #00a7c4;
        background: rgba(0,207,232,.12);
    }

    .bg-purple-soft {
        color: #8b5cf6;
        background: rgba(139,92,246,.12);
    }


    /* ============================================================
       KPI
    ============================================================ */

    .dashboard-card {
        min-height: 145px;

        overflow: hidden;

        border-radius: 16px;

        transition:
            transform .25s ease,
            box-shadow .25s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-3px);

        box-shadow:
            0 12px 28px
            rgba(15,23,42,.12) !important;
    }

    .dashboard-card .card-body {
        padding: 1.4rem;
    }

    .dashboard-kpi-label {
        margin: 0 0 4px;

        font-size: 14px;
        font-weight: 600;

        color: #94a3b8;
    }

    .dashboard-kpi-value {
        margin: 0 0 4px;

        font-size: 30px;
        font-weight: 800;

        color: #334155;
    }

    .dashboard-kpi-description {
        font-size: 11px;

        color: #94a3b8;
    }


    /* ============================================================
       ICÔNES KPI
    ============================================================ */

    .dashboard-icon {
        width: 64px;
        height: 64px;

        display: flex;
        align-items: center;
        justify-content: center;

        flex-shrink: 0;

        border-radius: 17px;

        font-size: 29px;
    }

    .bg-primary-light {
        color: #696cff;
        background: rgba(105,108,255,.14);
    }

    .bg-success-light {
        color: #28c76f;
        background: rgba(40,199,111,.14);
    }

    .bg-danger-light {
        color: #ea5455;
        background: rgba(234,84,85,.14);
    }

    .bg-warning-light {
        color: #ff9f43;
        background: rgba(255,159,67,.14);
    }

    .bg-info-light {
        color: #00cfe8;
        background: rgba(0,207,232,.14);
    }

    .bg-purple-light {
        color: #8b5cf6;
        background: rgba(139,92,246,.14);
    }


    /* ============================================================
       BORDURES KPI
    ============================================================ */

    .border-primary-bottom {
        border-bottom: 4px solid #696cff !important;
    }

    .border-success-bottom {
        border-bottom: 4px solid #28c76f !important;
    }

    .border-danger-bottom {
        border-bottom: 4px solid #ea5455 !important;
    }

    .border-warning-bottom {
        border-bottom: 4px solid #ff9f43 !important;
    }

    .border-info-bottom {
        border-bottom: 4px solid #00cfe8 !important;
    }

    .border-purple-bottom {
        border-bottom: 4px solid #8b5cf6 !important;
    }


    /* ============================================================
       CARTES CONTENU
    ============================================================ */

    .dashboard-content-card {
        overflow: hidden;

        border-radius: 16px !important;
    }

    .dashboard-card-header {
        min-height: 78px;

        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 15px;

        padding: 18px 20px;

        background: #ffffff !important;

        border-bottom: 1px solid #edf0f4 !important;
    }

    .dashboard-card-title {
        margin: 0 0 4px;

        font-size: 16px;
        font-weight: 800;

        color: #334155;
    }

    .dashboard-card-subtitle {
        margin: 0;

        font-size: 12px;

        color: #94a3b8;
    }

    .dashboard-card-header-icon {
        width: 40px;
        height: 40px;

        display: flex;
        align-items: center;
        justify-content: center;

        flex-shrink: 0;

        border-radius: 10px;

        font-size: 21px;

        color: #696cff;

        background: rgba(105,108,255,.10);
    }


    /* ============================================================
       TABLEAUX
    ============================================================ */

    .dashboard-table {
        margin-bottom: 0;
    }

    .dashboard-table thead th {
        padding: 12px 14px;

        border-bottom: 1px solid #dbe1e8;

        font-size: 11px;
        font-weight: 800;

        letter-spacing: .05em;

        text-transform: uppercase;

        white-space: nowrap;

        color: #52657b;

        background: #e8edf3;
    }

    .dashboard-table tbody td {
        padding: 13px 14px;

        vertical-align: middle;

        color: #52657b;
    }

    .dashboard-table tbody tr:hover {
        background: #f8fafc;
    }


    /* ============================================================
       TOP PRODUITS
    ============================================================ */

    .top-product-row {
        padding: 14px 0;

        border-bottom: 1px solid #edf0f4;
    }

    .top-product-row:first-child {
        padding-top: 0;
    }

    .top-product-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }


    /* ============================================================
       ÉTAT VIDE
    ============================================================ */

    .dashboard-empty-state {
        padding: 30px 15px;

        text-align: center;

        color: #94a3b8;
    }

    .dashboard-empty-state i {
        display: block;

        margin-bottom: 8px;

        font-size: 40px;
    }

    .dashboard-empty-state p {
        margin: 0;
    }


    /* ============================================================
       GRAPHIQUES
    ============================================================ */

    #stockStatusChart {
        width: 100%;
        max-width: 330px;
        margin: auto;
    }

    #monthlySoldChart {
        width: 100%;
    }


    /* ============================================================
       RESPONSIVE
    ============================================================ */

    @media(max-width: 991.98px) {

        .dashboard-page {
            padding: 18px 12px 40px;
        }

        .dashboard-section {
            margin-bottom: 28px;
        }

    }


    @media(max-width: 767.98px) {

        .dashboard-page {
            padding: 15px 8px 30px;
        }

        .dashboard-section-header {
            align-items: flex-start;
        }

        .dashboard-card {
            min-height: 130px;
        }

        .dashboard-icon {
            width: 56px;
            height: 56px;

            font-size: 25px;
        }

        .dashboard-card-header {
            padding: 15px;
        }

    }

</style>

@endsection



{{-- ================================================================
     SCRIPTS
================================================================ --}}

@section('page-script')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const months = @json($months);

    const monthlySold = @json($monthlySold);


    /*
    |--------------------------------------------------------------------------
    | ÉTAT DU STOCK
    |--------------------------------------------------------------------------
    */

    const stockChartElement =
        document.querySelector('#stockStatusChart');


    if (stockChartElement) {

        new ApexCharts(
            stockChartElement,
            {

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
                    '#ea5455'
                ],

                legend: {
                    position: 'bottom'
                },

                dataLabels: {
                    enabled: true
                },

                stroke: {
                    width: 2
                },

                responsive: [

                    {
                        breakpoint: 768,

                        options: {

                            chart: {
                                height: 280
                            },

                            legend: {
                                position: 'bottom'
                            }

                        }

                    }

                ]

            }

        ).render();

    }



    /*
    |--------------------------------------------------------------------------
    | VENTES PAR MOIS
    |--------------------------------------------------------------------------
    */

    const monthlyChartElement =
        document.querySelector('#monthlySoldChart');


    if (monthlyChartElement) {

        new ApexCharts(
            monthlyChartElement,
            {

                chart: {

                    type: 'bar',

                    height: 320,

                    toolbar: {
                        show: false
                    }

                },

                series: [

                    {
                        name: 'Pièces vendues',
                        data: monthlySold
                    }

                ],

                xaxis: {
                    categories: months
                },

                plotOptions: {

                    bar: {

                        borderRadius: 7,

                        columnWidth: '45%'

                    }

                },

                colors: [
                    '#00cfe8'
                ],

                dataLabels: {
                    enabled: false
                },

                grid: {

                    borderColor: '#edf0f4',

                    strokeDashArray: 4

                },

                yaxis: {

                    labels: {

                        formatter: function (value) {
                            return Math.round(value);
                        }

                    }

                }

            }

        ).render();

    }

});

</script>

@endsection
