@extends('layouts.layoutMaster')

@section('content')

<div class="dashboard-page dp-dash-compact">

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
                    Sorties de pièces par destination et stock faible
                </p>

            </div>

        </div>


        <div class="row g-4">

            {{-- V6 : SORTIES DE PIÈCES PAR DESTINATION, MOIS PAR MOIS --}}
            <div class="col-12 col-xl-8 dashboard-col-sorties">

                <div class="card dashboard-panel h-100">

                    <div class="card-header dashboard-panel-header">

                        <div>

                            <h5 class="dashboard-panel-title">
                                Sorties de pièces par destination
                            </h5>

                            <small class="dashboard-helper">
                                Valeur HT par mois : factures clients directs et bons de transfert (Atelier, bus, consommation, usage interne)
                            </small>

                        </div>

                        <form method="GET" action="{{ url()->current() }}" class="m-0">
                            <select name="annee" class="form-select form-select-sm" onchange="this.form.submit()">
                                @for($a = now()->year; $a >= 2024; $a--)
                                    <option value="{{ $a }}" @selected($anneeSorties === $a)>{{ $a }}</option>
                                @endfor
                            </select>
                        </form>

                    </div>

                    <div class="card-body">


                        @php
                            $fmtSortie = fn ($v) => $v ? number_format($v, 0, ',', ' ') : '—';
                            $totalMoisSorties = array_fill(1, 12, 0.0);
                            foreach ($sortiesParDestination as $d) {
                                foreach ($d['mois'] as $m => $v) { $totalMoisSorties[$m] += $v; }
                            }
                        @endphp

                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0 sorties-table">
                                <thead>
                                    <tr>
                                        <th>Destination</th>
                                        @foreach($months as $m)
                                            <th class="text-end">{{ $m }}</th>
                                        @endforeach
                                        <th class="text-end">Total {{ $anneeSorties }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sortiesParDestination as $d)
                                        <tr>
                                            <td class="text-nowrap">
                                                <a href="{{ $d['lien'] }}">{{ $d['libelle'] }}</a>
                                            </td>
                                            @foreach($d['mois'] as $m => $v)
                                                <td class="text-end" title="{{ $d['nombre'][$m] }} document(s)">{{ $fmtSortie($v) }}</td>
                                            @endforeach
                                            <td class="text-end fw-semibold" title="{{ $d['nb'] }} document(s)">{{ $fmtSortie($d['total']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td>Total sorties</td>
                                        @foreach($totalMoisSorties as $v)
                                            <td class="text-end">{{ $fmtSortie($v) }}</td>
                                        @endforeach
                                        <td class="text-end">{{ $fmtSortie(array_sum($totalMoisSorties)) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>

                </div>

            </div>

            {{-- STOCK FAIBLE (comme DreamsPOS) --}}
            <div class="col-12 col-xl-4">

                <div class="card dashboard-panel h-100">

                    <div class="card-header dashboard-panel-header">

                        <div>

                            <h5 class="dashboard-panel-title">
                                Stock faible
                            </h5>

                            <small class="dashboard-helper">
                                Pièces à réapprovisionner en priorité
                            </small>

                        </div>

                        @if(Route::has('products.to-order'))
                            <a href="{{ route('products.to-order') }}" class="dashboard-voir-tout">Tout voir</a>
                        @endif

                    </div>

                    <div class="card-body p-0">

                        <ul class="dashboard-stock-faible">

                            @forelse($stockFaible ?? [] as $piece)

                                <li>
                                    <div class="min-w-0">
                                        <a href="{{ route('products.show', $piece) }}" class="dashboard-stock-ref">{{ $piece->reference }}</a>
                                        <span class="dashboard-stock-nom">{{ $piece->designation }}</span>
                                    </div>

                                    <div class="text-end">
                                        <span class="dashboard-stock-qte {{ (float) $piece->quantity <= 0 ? 'rupture' : '' }}">
                                            {{ rtrim(rtrim(number_format((float) $piece->quantity, 2, ',', ' '), '0'), ',') }}
                                        </span>
                                        <small>{{ (float) $piece->quantity <= 0 ? 'En rupture' : 'Min. ' . rtrim(rtrim(number_format((float) $piece->min_stock, 2, ',', ' '), '0'), ',') }}</small>
                                    </div>
                                </li>

                            @empty

                                <li class="dashboard-stock-vide">Aucune pièce en stock faible.</li>

                            @endforelse

                        </ul>

                    </div>

                </div>

            </div>

        </div>

    </section>

</div>

<style>
    .dashboard-voir-tout {
        color: var(--stcd-primary, #FE9F43);
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
    }

    .dashboard-stock-faible {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .dashboard-stock-faible li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 18px;
        border-bottom: 1px solid var(--stcd-border, #E6EAED);
    }

    .dashboard-stock-faible li:last-child { border-bottom: 0; }

    .dashboard-stock-ref {
        display: block;
        font-size: 13.5px;
        font-weight: 700;
    }

    .dashboard-stock-nom {
        display: block;
        overflow: hidden;
        color: var(--stcd-muted, #8a9198);
        font-size: 12.5px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dashboard-stock-qte {
        display: inline-block;
        min-width: 42px;
        padding: 3px 10px;
        color: #b45309;
        background: #fef3c7;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 800;
        text-align: center;
    }

    .dashboard-stock-qte.rupture {
        color: #b91c1c;
        background: #fee2e2;
    }

    .dashboard-stock-faible small {
        display: block;
        margin-top: 2px;
        color: var(--stcd-muted, #8a9198);
        font-size: 11.5px;
    }

    .dashboard-stock-vide {
        justify-content: center !important;
        color: var(--stcd-muted, #8a9198);
    }
</style>


<style>
    /* ==========================================================
       TABLEAU DE BORD SUR UN SEUL ÉCRAN (V6, ordinateur ≥ 1200 px)
       4 rangées sur une grille de 8 colonnes :
       1. stock (4) + pièces véhicules (4)
       2. valorisation (2 × 2) + activité commerciale (4)
       3. ventes par mois (3) + état du stock (2) + pièces vendues (3)
       4. top pièces, dernières ventes, derniers mouvements,
          stock faible (2 chacun)
       Les titres de section sont masqués : chaque carte dit déjà
       ce qu'elle montre. En dessous de 1200 px : disposition normale.
    ========================================================== */
    @media (min-width: 1200px) {
        .dp-dash-compact {
            display: grid;
            grid-template-columns: repeat(8, minmax(0, 1fr));
            gap: 14px;
        }

        .dp-dash-compact > .dashboard-main-header,
        .dp-dash-compact .dashboard-section-header { display: none !important; }

        .dp-dash-compact > .dashboard-section,
        .dp-dash-compact > .dashboard-section > .row { display: contents; }

        .dp-dash-compact > .dashboard-section > .row > [class*="col"] {
            grid-column: span 1;
            width: auto;
            max-width: none;
            margin: 0;
            padding: 0;
        }

        /* rangée 2 : valorisation, 2 colonnes chacune (gros montants) */
        .dp-dash-compact > .dashboard-section:nth-of-type(3) > .row > [class*="col"] { grid-column: span 2; }

        /* rangée 3 : graphiques */
        .dp-dash-compact > .dashboard-section:nth-of-type(5) > .row:nth-of-type(2) > :nth-child(1) { grid-column: span 3; }
        .dp-dash-compact > .dashboard-section:nth-of-type(5) > .row:nth-of-type(2) > :nth-child(2) { grid-column: span 2; }
        .dp-dash-compact > .dashboard-section:nth-of-type(5) > .row:nth-of-type(3) > :nth-child(1) { grid-column: span 3; }

        /* rangée 4 : listes (top pièces + 3 listes d'activité) */
        .dp-dash-compact > .dashboard-section:nth-of-type(5) > .row:nth-of-type(3) > :nth-child(2),
        .dp-dash-compact > .dashboard-section:nth-of-type(6) > .row > [class*="col"] { grid-column: span 2; }

        /* cartes chiffrées compactes */
        .dp-dash-compact .dashboard-card .card-body { padding: 12px 14px !important; }
        .dp-dash-compact .dashboard-label { margin: 0 0 2px !important; font-size: 12.5px !important; }
        .dp-dash-compact .dashboard-value {
            margin: 0 0 1px !important;
            font-size: clamp(1.05rem, 1.25vw, 1.4rem) !important;
            white-space: nowrap;
        }
        /* le texte d'aide des petites cartes est masqué (titre + chiffre suffisent) */
        .dp-dash-compact > .dashboard-section > .row > [class*="col"] > .dashboard-card .dashboard-helper,
        .dp-dash-compact > .dashboard-section > .row > [class*="col"] > a > .dashboard-card .dashboard-helper,
        .dp-dash-compact > .dashboard-section > .row > [class*="col"] > a .dashboard-helper { display: none !important; }
        .dp-dash-compact .dashboard-label {
            display: -webkit-box;
            overflow: hidden;
            line-height: 1.25;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .dp-dash-compact .dashboard-icon {
            flex-shrink: 0;
            width: 32px !important;
            height: 32px !important;
            font-size: 16px !important;
            border-radius: 9px !important;
        }

        .dp-dash-compact .dashboard-card .card-body > .d-flex { gap: 8px !important; }

        /* panneaux (graphiques, listes) : hauteur fixe, liste qui défile */
        .dp-dash-compact .dashboard-panel { height: 100%; }

        .dp-dash-compact .dashboard-panel .card-header { padding: 10px 14px !important; }
        .dp-dash-compact .dashboard-panel-title { margin: 0 !important; font-size: 14px !important; }
        .dp-dash-compact .dashboard-panel .card-header .dashboard-helper { display: none; }
        .dp-dash-compact .dashboard-mini-icon { width: 30px !important; height: 30px !important; font-size: 15px !important; }

        .dp-dash-compact .dashboard-panel .card-body { padding: 6px 10px !important; }

        .dp-dash-compact > .dashboard-section:nth-of-type(6) .dashboard-panel .card-body,
        .dp-dash-compact > .dashboard-section:nth-of-type(5) > .row:nth-of-type(3) > :nth-child(2) .card-body {
            max-height: 300px;
            overflow-y: auto;
            padding: 0 !important;
        }

        .dp-dash-compact .dashboard-table th,
        .dp-dash-compact .dashboard-table td {
            padding: 7px 10px !important;
            font-size: 12.5px !important;
        }

        .dp-dash-compact .dashboard-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .dp-dash-compact .dashboard-stock-faible li { padding: 7px 14px; }
        .dp-dash-compact .dashboard-stock-ref { font-size: 12.5px; }
        .dp-dash-compact .dashboard-stock-nom { font-size: 11.5px; }

        .dp-dash-compact .chart-wrapper { min-height: 0 !important; height: auto !important; }

        /* rangée 4 (V6) : sorties par destination (6 colonnes) + stock faible (2) */
        .dp-dash-compact > .dashboard-section:nth-of-type(6) > .row > .dashboard-col-sorties { grid-column: span 6; }
        .dp-dash-compact > .dashboard-section:nth-of-type(6) .dashboard-col-sorties .dashboard-panel .card-body {
            max-height: 300px;   /* même hauteur que « Stock faible » ; le tableau défile si besoin */
            overflow: auto;
            padding: 0 6px !important;
        }
        .dp-dash-compact .dashboard-col-sorties .table-responsive { overflow: visible; }
        .dp-dash-compact .dashboard-panel .table-responsive { overflow-x: hidden; }
    }
</style>

@endsection


{{-- ============================================================= --}}
{{-- STYLES --}}
{{-- ============================================================= --}}

@push('styles')
<style>
    .sorties-table { font-size: 12px; }
    .sorties-table > :not(caption) > * > * {
        padding: 3px 7px !important;
        line-height: 1.25;
        white-space: nowrap;
    }
    .sorties-table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8f9fa;
        font-size: 11.5px;
        text-transform: none;
    }
    .sorties-table tfoot td {
        position: sticky;
        bottom: 0;
        background: #fff;
        border-top: 2px solid #d9dee3;
    }
</style>
@endpush

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
                    height: 190,
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
                    height: 190
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
                    height: 190,
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
