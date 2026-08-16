@extends('layouts.layoutMaster')

@section('content')

<div class="dashboard-page">

    {{-- ========================================================= --}}
    {{-- EN-TÊTE PRINCIPAL --}}
    {{-- ========================================================= --}}

    <div class="dashboard-main-header">

        <h3 class="dashboard-main-title">
            Tableau de bord
        </h3>

        <p class="dashboard-main-subtitle">
            Vue globale du magasin de pièces détachées STCD Motors
        </p>

    </div>


    {{-- ========================================================= --}}
    {{-- SECTION 1 : ÉTAT DU STOCK --}}
    {{-- ========================================================= --}}

    <section class="dashboard-section">

        <div class="dashboard-section-header">

            <div class="dashboard-section-icon bg-primary-light">
                <i class="bx bx-package"></i>
            </div>

            <div>

                <h5 class="dashboard-section-title">
                    État du stock
                </h5>

                <p class="dashboard-section-subtitle">
                    Suivi des quantités et de la disponibilité des pièces
                </p>

            </div>

        </div>


        <div class="row g-3 g-xl-4">

            {{-- TOTAL PIÈCES --}}
            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card dashboard-card border-primary-bottom h-100">

                    <div class="card-body">

                        <div class="d-flex align-items-center justify-content-between gap-3">

                            <div class="flex-grow-1 min-width-0">

                                <p class="dashboard-label">
                                    Total pièces
                                </p>

                                <h2 class="dashboard-value">
                                    {{ number_format($totalProducts, 0, ',', ' ') }}
                                </h2>

                                <small class="dashboard-helper">
                                    Pièces enregistrées
                                </small>

                            </div>

                            <div class="dashboard-icon bg-primary-light">
                                <i class="bx bx-package"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- PIÈCES DISPONIBLES --}}
            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card dashboard-card border-success-bottom h-100">

                    <div class="card-body">

                        <div class="d-flex align-items-center justify-content-between gap-3">

                            <div class="flex-grow-1 min-width-0">

                                <p class="dashboard-label">
                                    Pièces disponibles
                                </p>

                                <h2 class="dashboard-value">
                                    {{ number_format($availableProducts, 0, ',', ' ') }}
                                </h2>

                                <small class="dashboard-helper">
                                    Disponibles à la vente
                                </small>

                            </div>

                            <div class="dashboard-icon bg-success-light">
                                <i class="bx bx-check-circle"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- STOCK FAIBLE --}}
            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card dashboard-card border-warning-bottom h-100">

                    <div class="card-body">

                        <div class="d-flex align-items-center justify-content-between gap-3">

                            <div class="flex-grow-1 min-width-0">

                                <p class="dashboard-label">
                                    Stock faible
                                </p>

                                <h2 class="dashboard-value">
                                    {{ number_format($lowStock, 0, ',', ' ') }}
                                </h2>

                                <small class="dashboard-helper">
                                    Pièces à surveiller
                                </small>

                            </div>

                            <div class="dashboard-icon bg-warning-light">
                                <i class="bx bx-error"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- RUPTURE STOCK --}}
            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card dashboard-card border-danger-bottom h-100">

                    <div class="card-body">

                        <div class="d-flex align-items-center justify-content-between gap-3">

                            <div class="flex-grow-1 min-width-0">

                                <p class="dashboard-label">
                                    Rupture de stock
                                </p>

                                <h2 class="dashboard-value">
                                    {{ number_format($outOfStock, 0, ',', ' ') }}
                                </h2>

                                <small class="dashboard-helper">
                                    Pièces indisponibles
                                </small>

                            </div>

                            <div class="dashboard-icon bg-danger-light">
                                <i class="bx bx-block"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- ========================================================= --}}
    {{-- SECTION 2 : SUIVI DES PIÈCES VÉHICULES --}}
    {{-- ========================================================= --}}

    <section class="dashboard-section">

        <div class="dashboard-section-header">

            <div class="dashboard-section-icon bg-warning-light">
                <i class="bx bx-search-alt-2"></i>
            </div>

            <div>
                <h5 class="dashboard-section-title">
                    Suivi des pièces véhicules
                </h5>

                <p class="dashboard-section-subtitle">
                    Pièces en recherche, commandées, reçues et introuvables
                </p>
            </div>

        </div>

        <div class="row g-3 g-xl-4">

            <div class="col-12 col-sm-6 col-xl-3">
                <a
                    href="{{ route('vehicle-part-requests.index', ['status' => 'searching']) }}"
                    class="dashboard-card-link"
                >
                    <div class="card dashboard-card border-info-bottom h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between gap-3">

                                <div class="flex-grow-1 min-width-0">
                                    <p class="dashboard-label">
                                        En recherche
                                    </p>

                                    <h2 class="dashboard-value">
                                        {{ number_format($searchingPartRequests, 0, ',', ' ') }}
                                    </h2>

                                    <small class="dashboard-helper">
                                        Demandes actuellement en recherche
                                    </small>
                                </div>

                                <div class="dashboard-icon bg-info-light">
                                    <i class="bx bx-search-alt-2"></i>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>
            </div>


            <div class="col-12 col-sm-6 col-xl-3">
                <a
                    href="{{ route('vehicle-part-requests.ordered') }}"
                    class="dashboard-card-link"
                >
                    <div class="card dashboard-card border-primary-bottom h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between gap-3">

                                <div class="flex-grow-1 min-width-0">
                                    <p class="dashboard-label">
                                        Pièces commandées
                                    </p>

                                    <h2 class="dashboard-value">
                                        {{ number_format($orderedPartRequests, 0, ',', ' ') }}
                                    </h2>

                                    <small class="dashboard-helper">
                                        Commandes en attente de réception
                                    </small>
                                </div>

                                <div class="dashboard-icon bg-primary-light">
                                    <i class="bx bx-cart-download"></i>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>
            </div>


            <div class="col-12 col-sm-6 col-xl-3">
                <a
                    href="{{ route('vehicle-part-requests.received') }}"
                    class="dashboard-card-link"
                >
                    <div class="card dashboard-card border-success-bottom h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between gap-3">

                                <div class="flex-grow-1 min-width-0">
                                    <p class="dashboard-label">
                                        Pièces reçues
                                    </p>

                                    <h2 class="dashboard-value">
                                        {{ number_format($receivedPartRequests, 0, ',', ' ') }}
                                    </h2>

                                    <small class="dashboard-helper">
                                        Pièces déjà réceptionnées
                                    </small>
                                </div>

                                <div class="dashboard-icon bg-success-light">
                                    <i class="bx bx-package"></i>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>
            </div>


            <div class="col-12 col-sm-6 col-xl-3">
                <a
                    href="{{ route('vehicle-part-requests.not-found') }}"
                    class="dashboard-card-link"
                >
                    <div class="card dashboard-card border-danger-bottom h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between gap-3">

                                <div class="flex-grow-1 min-width-0">
                                    <p class="dashboard-label">
                                        Pièces introuvables
                                    </p>

                                    <h2 class="dashboard-value">
                                        {{ number_format($notFoundPartRequests, 0, ',', ' ') }}
                                    </h2>

                                    <small class="dashboard-helper">
                                        Pièces déclarées non trouvées
                                    </small>
                                </div>

                                <div class="dashboard-icon bg-danger-light">
                                    <i class="bx bx-x-circle"></i>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>
            </div>

        </div>

    </section>




    {{-- ========================================================= --}}
    {{-- SECTION 3 : VALORISATION DU STOCK --}}
    {{-- ========================================================= --}}

    <section class="dashboard-section">

        <div class="dashboard-section-header">

            <div class="dashboard-section-icon bg-info-light">
                <i class="bx bx-wallet"></i>
            </div>

            <div>

                <h5 class="dashboard-section-title">
                    Valorisation du stock
                </h5>

                <p class="dashboard-section-subtitle">
                    Valeur financière des pièces actuellement en stock
                </p>

            </div>

        </div>


        <div class="row g-3 g-xl-4">

            {{-- VALEUR PRIX DE REVIENT --}}
            <div class="col-12 col-lg-6">

                <div class="card dashboard-card border-info-bottom h-100">

                    <div class="card-body">

                        <div class="d-flex align-items-center justify-content-between gap-3">

                            <div class="flex-grow-1 min-width-0">

                                <p class="dashboard-label">
                                    Valeur du stock au prix de revient
                                </p>

                                <h2 class="dashboard-value dashboard-money">
                                    {{ number_format($stockValue, 2, ',', ' ') }}
                                </h2>

                                <small class="dashboard-helper">
                                    Valeur basée sur le coût des pièces
                                </small>

                            </div>

                            <div class="dashboard-icon bg-info-light">
                                <i class="bx bx-purchase-tag-alt"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- VALEUR PRIX DE VENTE --}}
            <div class="col-12 col-lg-6">

                <div class="card dashboard-card border-success-bottom h-100">

                    <div class="card-body">

                        <div class="d-flex align-items-center justify-content-between gap-3">

                            <div class="flex-grow-1 min-width-0">

                                <p class="dashboard-label">
                                    Valeur du stock au prix de vente
                                </p>

                                <h2 class="dashboard-value dashboard-money">
                                    {{ number_format($totalStockPrice, 2, ',', ' ') }}
                                </h2>

                                <small class="dashboard-helper">
                                    Valeur potentielle totale de vente
                                </small>

                            </div>

                            <div class="dashboard-icon bg-success-light">
                                <i class="bx bx-money"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- ========================================================= --}}
    {{-- SECTION 4 : ACTIVITÉ COMMERCIALE --}}
    {{-- ========================================================= --}}

    <section class="dashboard-section">

        <div class="dashboard-section-header">

            <div class="dashboard-section-icon bg-success-light">
                <i class="bx bx-cart"></i>
            </div>

            <div>

                <h5 class="dashboard-section-title">
                    Activité commerciale
                </h5>

                <p class="dashboard-section-subtitle">
                    Suivi des ventes, du chiffre d'affaires et des transactions
                </p>

            </div>

        </div>


        <div class="row g-3 g-xl-4">

            {{-- PIÈCES VENDUES --}}
            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card dashboard-card border-primary-bottom h-100">

                    <div class="card-body">

                        <p class="dashboard-label">
                            Pièces vendues
                        </p>

                        <h2 class="dashboard-value">
                            {{ number_format($soldProducts, 0, ',', ' ') }}
                        </h2>

                        <small class="dashboard-helper">
                            Quantité totale vendue
                        </small>

                    </div>

                </div>

            </div>


            {{-- PRODUITS VENDUS / CA --}}
            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card dashboard-card border-success-bottom h-100">

                    <div class="card-body">

                        <p class="dashboard-label">
                            Chiffre d'affaires total
                        </p>

                        <h2 class="dashboard-value dashboard-money-small">
                            {{ number_format($totalSoldPrice, 2, ',', ' ') }}
                        </h2>

                        <small class="dashboard-helper">
                            Total des ventes réalisées
                        </small>

                    </div>

                </div>

            </div>


            {{-- VENTES DU MOIS --}}
            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card dashboard-card border-info-bottom h-100">

                    <div class="card-body">

                        <p class="dashboard-label">
                            Ventes du mois
                        </p>

                        <h2 class="dashboard-value dashboard-money-small">
                            {{ number_format($salesThisMonth, 2, ',', ' ') }}
                        </h2>

                        <small class="dashboard-helper">
                            Ventes payées ou partielles
                        </small>

                    </div>

                </div>

            </div>


            {{-- NOMBRE DE VENTES --}}
            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card dashboard-card border-warning-bottom h-100">

                    <div class="card-body">

                        <p class="dashboard-label">
                            Nombre de ventes du mois
                        </p>

                        <h2 class="dashboard-value">
                            {{ number_format($salesCountThisMonth, 0, ',', ' ') }}
                        </h2>

                        <small class="dashboard-helper">
                            Transactions enregistrées
                        </small>

                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- ========================================================= --}}
    {{-- SECTION 5 : ANALYSES ET STATISTIQUES --}}
    {{-- ========================================================= --}}

    <section class="dashboard-section">

        <div class="dashboard-section-header">

            <div class="dashboard-section-icon bg-primary-light">
                <i class="bx bx-bar-chart-alt-2"></i>
            </div>

            <div>

                <h5 class="dashboard-section-title">
                    Analyses et statistiques
                </h5>

                <p class="dashboard-section-subtitle">
                    Analyse de l'évolution des ventes et de l'état du stock
                </p>

            </div>

        </div>


        {{-- PREMIÈRE LIGNE DE GRAPHIQUES --}}
        <div class="row g-4 mb-4">

            {{-- VENTES PAR MOIS --}}
            <div class="col-12 col-xl-8">

                <div class="card dashboard-panel h-100">

                    <div class="card-header dashboard-panel-header">

                        <div>

                            <h5 class="dashboard-panel-title">
                                Ventes par mois
                            </h5>

                            <small class="dashboard-helper">
                                Montant total des ventes pour l'année
                            </small>

                        </div>

                        <span class="badge bg-label-primary">
                            {{ date('Y') }}
                        </span>

                    </div>

                    <div class="card-body">

                        <div class="chart-wrapper">
                            <div id="monthlySalesChart"></div>
                        </div>

                    </div>

                </div>

            </div>


            {{-- ÉTAT DU STOCK --}}
            <div class="col-12 col-xl-4">

                <div class="card dashboard-panel h-100">

                    <div class="card-header dashboard-panel-header">

                        <div>

                            <h5 class="dashboard-panel-title">
                                État du stock
                            </h5>

                            <small class="dashboard-helper">
                                Disponible, faible et rupture
                            </small>

                        </div>

                    </div>

                    <div class="card-body">

                        <div class="chart-wrapper">
                            <div id="stockStatusChart"></div>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- DEUXIÈME LIGNE --}}
        <div class="row g-4">

            {{-- PIÈCES VENDUES PAR MOIS --}}
            <div class="col-12 col-xl-7">

                <div class="card dashboard-panel h-100">

                    <div class="card-header dashboard-panel-header">

                        <div>

                            <h5 class="dashboard-panel-title">
                                Nombre de pièces vendues par mois
                            </h5>

                            <small class="dashboard-helper">
                                Quantité totale de pièces vendues
                            </small>

                        </div>

                    </div>

                    <div class="card-body">

                        <div class="chart-wrapper">
                            <div id="monthlySoldChart"></div>
                        </div>

                    </div>

                </div>

            </div>


            {{-- TOP PIÈCES VENDUES --}}
            <div class="col-12 col-xl-5">

                <div class="card dashboard-panel h-100">

                    <div class="card-header dashboard-panel-header">

                        <div>

                            <h5 class="dashboard-panel-title">
                                Top pièces vendues
                            </h5>

                            <small class="dashboard-helper">
                                Produits les plus vendus
                            </small>

                        </div>

                    </div>

                    <div class="card-body p-0">

                        @forelse($topProducts as $item)

                            <div class="top-product-item">

                                <div class="top-product-info">

                                    <strong class="top-product-reference">
                                        {{ $item->product?->reference ?? '-' }}
                                    </strong>

                                    <div class="dashboard-helper">
                                        {{ $item->product?->designation ?? 'Produit supprimé' }}
                                    </div>

                                </div>

                                <span class="badge bg-label-primary">
                                    {{ $item->total_qty }} vendues
                                </span>

                            </div>

                        @empty

                            <div class="p-4 text-center">

                                <i class="bx bx-cart-alt empty-icon"></i>

                                <p class="text-muted mb-0">
                                    Aucune vente enregistrée.
                                </p>

                            </div>

                        @endforelse

                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- ========================================================= --}}
    {{-- SECTION 6 : ACTIVITÉS RÉCENTES --}}
    {{-- ========================================================= --}}

    <section class="dashboard-section mb-0">

        <div class="dashboard-section-header">

            <div class="dashboard-section-icon bg-warning-light">
                <i class="bx bx-history"></i>
            </div>

            <div>

                <h5 class="dashboard-section-title">
                    Activités récentes
                </h5>

                <p class="dashboard-section-subtitle">
                    Dernières ventes et derniers mouvements enregistrés
                </p>

            </div>

        </div>


        <div class="row g-4">

            {{-- DERNIÈRES VENTES --}}
            <div class="col-12 col-xl-6">

                <div class="card dashboard-panel h-100">

                    <div class="card-header dashboard-panel-header">

                        <div>

                            <h5 class="dashboard-panel-title">
                                Dernières ventes
                            </h5>

                            <small class="dashboard-helper">
                                Transactions commerciales récentes
                            </small>

                        </div>

                        <div class="dashboard-mini-icon bg-success-light">
                            <i class="bx bx-receipt"></i>
                        </div>

                    </div>

                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table table-hover align-middle dashboard-table mb-0">

                                <thead>

                                    <tr>

                                        <th>
                                            Facture
                                        </th>

                                        <th>
                                            Client
                                        </th>

                                        <th>
                                            Total
                                        </th>

                                        <th>
                                            Statut
                                        </th>

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

                                                {{ $sale->customer?->name ?? 'Vente comptoir' }}

                                            </td>

                                            <td class="text-nowrap">

                                                {{ number_format($sale->total, 2, ',', ' ') }}

                                            </td>

                                            <td>

                                                @if($sale->status == 'paid')

                                                    <span class="badge bg-success">
                                                        Payée
                                                    </span>

                                                @elseif($sale->status == 'partial')

                                                    <span class="badge bg-warning text-dark">
                                                        Partielle
                                                    </span>

                                                @elseif($sale->status == 'cancelled')

                                                    <span class="badge bg-danger">
                                                        Annulée
                                                    </span>

                                                @else

                                                    <span class="badge bg-secondary">
                                                        {{ ucfirst($sale->status) }}
                                                    </span>

                                                @endif

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td colspan="4"
                                                class="text-center text-muted py-4">

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


            {{-- MOUVEMENTS DE STOCK --}}
            <div class="col-12 col-xl-6">

                <div class="card dashboard-panel h-100">

                    <div class="card-header dashboard-panel-header">

                        <div>

                            <h5 class="dashboard-panel-title">
                                Derniers mouvements de stock
                            </h5>

                            <small class="dashboard-helper">
                                Entrées et sorties récentes
                            </small>

                        </div>

                        <div class="dashboard-mini-icon bg-info-light">
                            <i class="bx bx-transfer-alt"></i>
                        </div>

                    </div>

                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table table-hover align-middle dashboard-table mb-0">

                                <thead>

                                    <tr>

                                        <th>
                                            Produit
                                        </th>

                                        <th>
                                            Type
                                        </th>

                                        <th>
                                            Qté
                                        </th>

                                        <th>
                                            Date
                                        </th>

                                    </tr>

                                </thead>

                                <tbody>

                                    @forelse($latestMovements as $movement)

                                        <tr>

                                            <td>

                                                <strong>
                                                    {{ $movement->product?->reference ?? '-' }}
                                                </strong>

                                                <div class="dashboard-helper">
                                                    {{ $movement->product?->designation ?? '' }}
                                                </div>

                                            </td>

                                            <td>

                                                @if($movement->type == 'in')

                                                    <span class="badge bg-success">
                                                        Entrée
                                                    </span>

                                                @else

                                                    <span class="badge bg-danger">
                                                        Sortie
                                                    </span>

                                                @endif

                                            </td>

                                            <td>

                                                {{ $movement->quantity }}

                                            </td>

                                            <td class="text-nowrap">

                                                {{ $movement->created_at?->format('d/m/Y') }}

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td colspan="4"
                                                class="text-center text-muted py-4">

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

    </section>

</div>

@endsection


{{-- ============================================================= --}}
{{-- STYLES --}}
{{-- ============================================================= --}}

@push('styles')

<style>
    .dashboard-page {
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    .min-width-0 {
        min-width: 0;
    }

    .dashboard-main-header {
        margin-bottom: 32px;
    }

    .dashboard-main-title {
        margin: 0 0 6px;
        font-weight: 700;
        color: #566a7f;
    }

    .dashboard-main-subtitle {
        margin: 0;
        color: #a1acb8;
        font-size: 1rem;
    }

    .dashboard-section {
        width: 100%;
        margin-bottom: 44px;
    }

    .dashboard-section-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e7e7e8;
    }

    .dashboard-section-title {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 700;
        color: #566a7f;
    }

    .dashboard-section-subtitle {
        margin: 4px 0 0;
        font-size: 0.875rem;
        color: #a1acb8;
    }

    .dashboard-section-icon {
        width: 46px;
        height: 46px;
        min-width: 46px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 23px;
    }

    .dashboard-card-link {
        display: block;
        height: 100%;
        color: inherit;
        text-decoration: none;
    }

    .dashboard-card-link:hover,
    .dashboard-card-link:focus {
        color: inherit;
        text-decoration: none;
    }

    .dashboard-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(67, 89, 113, 0.08);
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(67, 89, 113, 0.14);
    }

    .dashboard-card .card-body {
        padding: 23px;
    }

    .dashboard-label {
        margin: 0 0 7px;
        color: #8592a3;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .dashboard-value {
        margin: 0 0 4px;
        font-size: 2rem;
        line-height: 1.15;
        font-weight: 700;
        color: #566a7f;
        overflow-wrap: anywhere;
    }

    .dashboard-money {
        font-size: 2rem;
    }

    .dashboard-money-small {
        font-size: 1.7rem;
    }

    .dashboard-helper {
        color: #a1acb8;
        font-size: 0.82rem;
    }

    .dashboard-icon {
        width: 56px;
        height: 56px;
        min-width: 56px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    .dashboard-mini-icon {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .bg-primary-light {
        background: rgba(105, 108, 255, 0.14);
        color: #696cff;
    }

    .bg-success-light {
        background: rgba(40, 199, 111, 0.14);
        color: #28c76f;
    }

    .bg-danger-light {
        background: rgba(255, 77, 79, 0.14);
        color: #ff4d4f;
    }

    .bg-warning-light {
        background: rgba(255, 159, 67, 0.14);
        color: #ff9f43;
    }

    .bg-info-light {
        background: rgba(0, 207, 232, 0.14);
        color: #00cfe8;
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

    .dashboard-panel {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(67, 89, 113, 0.08);
        overflow: hidden;
    }

    .dashboard-panel-header {
        background: #fff;
        border-bottom: 1px solid #f0f0f1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 20px 22px;
    }

    .dashboard-panel-title {
        margin: 0 0 3px;
        color: #566a7f;
        font-size: 1rem;
        font-weight: 700;
    }

    .chart-wrapper {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        overflow: hidden;
    }

    .chart-wrapper > div,
    .apexcharts-canvas,
    .apexcharts-svg {
        max-width: 100% !important;
    }

    .top-product-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 17px 22px;
        border-bottom: 1px solid #f1f1f2;
    }

    .top-product-item:last-child {
        border-bottom: 0;
    }

    .top-product-info {
        min-width: 0;
        flex: 1;
    }

    .top-product-reference {
        display: block;
        color: #566a7f;
        margin-bottom: 2px;
        overflow-wrap: anywhere;
    }

    .empty-icon {
        display: block;
        font-size: 38px;
        color: #d2d6dc;
        margin-bottom: 8px;
    }

    .dashboard-table {
        width: 100%;
    }

    .dashboard-table thead th {
        background: #f8f8f9;
        color: #697a8d;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        border-bottom: 1px solid #e7e7e8;
        padding: 14px 18px;
    }

    .dashboard-table tbody td {
        padding: 15px 18px;
        color: #566a7f;
        vertical-align: middle;
    }

    .dashboard-table tbody tr:last-child td {
        border-bottom: 0;
    }

    @media (max-width: 1199.98px) {
        .dashboard-money,
        .dashboard-money-small {
            font-size: 1.75rem;
        }
    }

    @media (max-width: 767.98px) {
        .dashboard-main-header {
            margin-bottom: 24px;
        }

        .dashboard-main-title {
            font-size: 1.6rem;
        }

        .dashboard-main-subtitle {
            font-size: 0.9rem;
        }

        .dashboard-section {
            margin-bottom: 30px;
        }

        .dashboard-section-header {
            align-items: flex-start;
            gap: 11px;
            margin-bottom: 16px;
        }

        .dashboard-section-icon {
            width: 40px;
            height: 40px;
            min-width: 40px;
            font-size: 20px;
        }

        .dashboard-card .card-body {
            padding: 18px;
        }

        .dashboard-value,
        .dashboard-money,
        .dashboard-money-small {
            font-size: 1.6rem;
        }

        .dashboard-icon {
            width: 48px;
            height: 48px;
            min-width: 48px;
            font-size: 23px;
        }

        .dashboard-panel-header {
            padding: 17px 18px;
        }

        .top-product-item {
            padding: 15px 18px;
        }

        .dashboard-table thead th,
        .dashboard-table tbody td {
            padding-left: 14px;
            padding-right: 14px;
        }
    }

    @media (max-width: 479.98px) {
        .dashboard-section-title {
            font-size: 1rem;
        }

        .dashboard-section-subtitle {
            font-size: 0.8rem;
        }

        .dashboard-card .card-body > .d-flex {
            align-items: flex-start !important;
        }

        .dashboard-value,
        .dashboard-money,
        .dashboard-money-small {
            font-size: 1.45rem;
        }

        .dashboard-icon {
            width: 44px;
            height: 44px;
            min-width: 44px;
            font-size: 21px;
        }

        .dashboard-panel-header {
            align-items: flex-start;
        }
    }
</style>

@endpush

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const months = @json($months);
    const monthlySales = @json($monthlySales);
    const monthlySold = @json($monthlySold ?? []);

    const moneyFormatter = new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });

    const monthlySalesElement =
        document.querySelector('#monthlySalesChart');

    if (monthlySalesElement) {
        const monthlySalesChart = new ApexCharts(
            monthlySalesElement,
            {
                chart: {
                    type: 'area',
                    height: 330,
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: false
                    }
                },

                series: [
                    {
                        name: 'Ventes',
                        data: monthlySales
                    }
                ],

                xaxis: {
                    categories: months
                },

                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return moneyFormatter.format(value);
                        }
                    }
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
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },

                grid: {
                    borderColor: '#e7e7e8',
                    strokeDashArray: 4
                },

                tooltip: {
                    y: {
                        formatter: function (value) {
                            return moneyFormatter.format(value);
                        }
                    }
                }
            }
        );

        monthlySalesChart.render();
    }

    const stockStatusElement =
        document.querySelector('#stockStatusChart');

    if (stockStatusElement) {
        const stockStatusChart = new ApexCharts(
            stockStatusElement,
            {
                chart: {
                    type: 'donut',
                    height: 320
                },

                series: [
                    Number(@json($availableProducts)),
                    Number(@json($lowStock)),
                    Number(@json($outOfStock))
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
                },

                dataLabels: {
                    enabled: true
                }
            }
        );

        stockStatusChart.render();
    }

    const monthlySoldElement =
        document.querySelector('#monthlySoldChart');

    if (monthlySoldElement) {
        const soldData = monthlySold.length
            ? monthlySold
            : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

        const monthlySoldChart = new ApexCharts(
            monthlySoldElement,
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
                        data: soldData
                    }
                ],

                xaxis: {
                    categories: months
                },

                yaxis: {
                    min: 0,
                    forceNiceScale: true
                },

                plotOptions: {
                    bar: {
                        borderRadius: 7,
                        columnWidth: '45%'
                    }
                },

                colors: ['#00cfe8'],

                dataLabels: {
                    enabled: false
                },

                grid: {
                    borderColor: '#e7e7e8',
                    strokeDashArray: 4
                }
            }
        );

        monthlySoldChart.render();
    }
});
</script>

@endpush
