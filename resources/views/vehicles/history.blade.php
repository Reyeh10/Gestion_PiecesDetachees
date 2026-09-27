@extends('layouts.layoutMaster')

@section('content')

{{-- ====================================================== --}}
{{-- MISE EN PAGE COMPACTE ET ADAPTATIVE                     --}}
{{-- Les styles ci-dessous concernent uniquement cette page. --}}
{{-- ====================================================== --}}
<style>
    /* La largeur disponible tient compte de la barre latérale. */
    #vehicle-history-page {
        width: 100%; max-width: 100%; min-width: 0;
        container-type: inline-size;
        container-name: vehicle-history;
    }

    /* Réduire les espaces tout en conservant les titres et explications. */
    #vehicle-history-page > .card-header { padding: 14px 16px 10px; }
    #vehicle-history-page > .card-header h3 { font-size: 1.25rem; }
    #vehicle-history-page > .card-header p { font-size: .82rem; }
    #vehicle-history-page > .card-body { padding: 0 16px 16px; }
    #vehicle-history-page .alert { padding: 9px 12px; font-size: .82rem; margin-bottom: 12px !important; }

    /* Quatre filtres et un groupe de boutons, sans débordement à droite. */
    #vehicle-history-page .history-filters {
        display: grid;
        grid-template-columns: minmax(130px, 1.2fr) repeat(2, minmax(130px, 1fr)) minmax(135px, 1fr) auto;
        gap: 10px; align-items: end; margin: 8px 0 14px;
    }
    #vehicle-history-page .history-filter-field { min-width: 0; }
    #vehicle-history-page .form-label { font-size: .7rem; margin-bottom: 5px; }
    #vehicle-history-page .form-control,
    #vehicle-history-page .form-select {
        width: 100%; min-width: 0; height: 36px; min-height: 36px;
        padding-top: 6px; padding-bottom: 6px; font-size: .8rem;
    }
    #vehicle-history-page .history-filter-actions { display: flex; flex-wrap: nowrap; gap: 6px; }
    #vehicle-history-page .history-filter-actions .btn {
        display: inline-flex; align-items: center; justify-content: center;
        height: 36px; margin: 0; padding: 6px 9px; font-size: .78rem; white-space: nowrap;
    }

    /* Les quatre statistiques restent présentes, dans des cartes plus basses. */
    #vehicle-history-page .history-stats {
        display: grid; grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px; margin-bottom: 14px;
    }
    #vehicle-history-page .history-stat { min-width: 0; }
    #vehicle-history-page .history-stat .card-body { padding: 10px 12px; }
    #vehicle-history-page .history-stat .small { font-size: .72rem; }
    #vehicle-history-page .history-stat h3 { font-size: 1.1rem; overflow-wrap: anywhere; }
    #vehicle-history-page .history-stat h3 small { font-size: .7rem !important; }
    #vehicle-history-page .history-stat .d-flex { gap: 8px; }
    #vehicle-history-page .history-stat .d-flex > div:first-child { min-width: 0; }
    #vehicle-history-page .history-stat .avatar { width: 30px; height: 30px; flex: 0 0 30px; }
    #vehicle-history-page .history-stat .avatar i { font-size: 20px !important; }

    /* Toutes les colonnes restent visibles ; les textes longs passent à la ligne. */
    #vehicle-history-page .history-table-wrapper { width: 100%; min-width: 0; }
    #vehicle-history-page .history-table { table-layout: fixed; width: 100%; min-width: 0; }
    #vehicle-history-page .history-table th,
    #vehicle-history-page .history-table td {
        padding: 8px 5px; font-size: .74rem; line-height: 1.35;
        white-space: normal; overflow-wrap: anywhere; vertical-align: middle;
    }
    #vehicle-history-page .history-table th { font-size: .64rem; letter-spacing: 0; overflow-wrap: normal; word-break: normal; }
    #vehicle-history-page .history-table th:nth-child(1) { width: 8%; }
    #vehicle-history-page .history-table th:nth-child(2) { width: 9%; }
    #vehicle-history-page .history-table th:nth-child(3) { width: 10%; }
    #vehicle-history-page .history-table th:nth-child(4) { width: 9%; }
    #vehicle-history-page .history-table th:nth-child(5) { width: 15%; }
    #vehicle-history-page .history-table th:nth-child(6) { width: 10%; }
    #vehicle-history-page .history-table th:nth-child(7) { width: 7%; }
    #vehicle-history-page .history-table th:nth-child(8) { width: 9%; }
    #vehicle-history-page .history-table th:nth-child(9) { width: 9%; }
    #vehicle-history-page .history-table th:nth-child(10) { width: 8%; }
    #vehicle-history-page .history-table th:nth-child(11) { width: 6%; }
    #vehicle-history-page .history-table .badge { font-size: .65rem; padding: 4px 5px; max-width: 100%; white-space: normal; }
    #vehicle-history-page .history-invoice-button { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; padding: 0; }
    #vehicle-history-page .history-invoice-button i { margin: 0 !important; }
    #vehicle-history-page .history-table td[colspan] { padding: 20px 12px !important; }

    /* Si la zone centrale se rétrécit, garder les deux boutons côte à côte. */
    @container vehicle-history (max-width: 1050px) {
        #vehicle-history-page .history-filters { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        #vehicle-history-page .history-filter-buttons { grid-column: 1 / -1; }
        #vehicle-history-page .history-filter-actions { justify-content: flex-end; }
        #vehicle-history-page .history-filter-actions .btn { flex: 0 0 130px; }
    }

    /* Sous 620 px de largeur utile, répartir les filtres sur deux colonnes.
       Les deux boutons gardent une largeur identique et restent côte à côte. */
    @container vehicle-history (max-width: 620px) {
        #vehicle-history-page .history-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    /* Sous 380 px, chaque filtre occupe sa propre ligne pour rester lisible. */
    @container vehicle-history (max-width: 380px) {
        #vehicle-history-page .history-filters { grid-template-columns: minmax(0, 1fr); }
        #vehicle-history-page .history-filter-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        #vehicle-history-page .history-filter-actions .btn { width: 100%; padding: 6px; font-size: .72rem; }
    }

    /* Sur petit écran, chaque ligne devient une fiche avec les onze informations. */
    @container vehicle-history (max-width: 850px) {
        #vehicle-history-page .history-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        #vehicle-history-page .history-table thead { display: none; }
        #vehicle-history-page .history-table,
        #vehicle-history-page .history-table tbody { display: block; }
        #vehicle-history-page .history-table tr { display: block; border: 1px solid #dfe3e8; border-radius: 8px; padding: 6px; margin-bottom: 10px; }
        #vehicle-history-page .history-table td { display: grid; grid-template-columns: 115px minmax(0, 1fr); gap: 8px; width: 100%; border: 0; text-align: left !important; }
        #vehicle-history-page .history-table td::before { content: attr(data-label); font-weight: 600; }
        #vehicle-history-page .history-table td[colspan] { display: block; }
        #vehicle-history-page .history-table td[colspan]::before { content: none; }
        #vehicle-history-page .history-filter-actions .btn { flex: 0 0 130px; }
    }
</style>


<div id="vehicle-history-page" class="card shadow-sm border-0">

    <div class="card-header border-0">

        <h3 class="mb-1 fw-bold">

            Traçabilité par immatriculation

        </h3>

        <p class="text-muted mb-0">

            Recherchez toutes les pièces vendues pour un véhicule

            pendant une période donnée.

        </p>

    </div>

    <div class="card-body">

        {{-- ====================================================== --}}

        {{-- ERREURS DE VALIDATION                                  --}}

        {{-- ====================================================== --}}

        @if($errors->any())

            <div class="alert alert-danger">

                <ul class="mb-0">

                    @foreach($errors->all() as $error)

                        <li>

                            {{ $error }}

                        </li>

                    @endforeach

                </ul>

            </div>

        @endif

        {{-- ====================================================== --}}

        {{-- FORMULAIRE DE RECHERCHE                                --}}

        {{-- ====================================================== --}}

        <form

            method="GET"

            action="{{ route('vehicles.history') }}"

            class="history-filters"

        >

            {{-- IMMATRICULATION --}}

            <div class="history-filter-field">

                <label

                    for="plate"

                    class="form-label fw-semibold"

                >

                    Immatriculation

                </label>

                <input

                    type="text"

                    name="plate"

                    id="plate"

                    value="{{ old('plate', $plate) }}"

                    class="form-control text-uppercase"

                    placeholder="Exemple : 200D77"

                    autocomplete="off"

                    required

                >

            </div>

            {{-- DATE DE DÉBUT --}}

            <div class="history-filter-field">

                <label

                    for="date_from"

                    class="form-label fw-semibold"

                >

                    Date de début

                </label>

                <input

                    type="date"

                    name="date_from"

                    id="date_from"

                    value="{{ old('date_from', $dateFrom) }}"

                    class="form-control"

                >

            </div>

            {{-- DATE DE FIN --}}

            <div class="history-filter-field">

                <label

                    for="date_to"

                    class="form-label fw-semibold"

                >

                    Date de fin

                </label>

                <input

                    type="date"

                    name="date_to"

                    id="date_to"

                    value="{{ old('date_to', $dateTo) }}"

                    class="form-control"

                >

            </div>

            {{-- STATUT --}}

            <div class="history-filter-field">

                <label

                    for="status"

                    class="form-label fw-semibold"

                >

                    Statut

                </label>

                <select

                    name="status"

                    id="status"

                    class="form-select"

                >

                    <option value="">

                        Tous les statuts

                    </option>

                    <option

                        value="vendu"

                        {{ old('status', $statusFilter) === 'vendu'

                            ? 'selected'

                            : ''

                        }}

                    >

                        Vendue

                    </option>

                    <option

                        value="payé"

                        {{ old('status', $statusFilter) === 'payé'

                            ? 'selected'

                            : ''

                        }}

                    >

                        Payée

                    </option>

                    <option

                        value="annulé"

                        {{ old('status', $statusFilter) === 'annulé'

                            ? 'selected'

                            : ''

                        }}

                    >

                        Annulée

                    </option>

                </select>

            </div>

            {{-- BOUTONS --}}

            <div class="history-filter-buttons">

                <div class="history-filter-actions">

                    <button

                        type="submit"

                        class="btn btn-primary"

                    >

                        <i class="bx bx-search me-1"></i>

                        Rechercher

                    </button>

                    <a

                        href="{{ route('vehicles.history') }}"

                        class="btn btn-outline-secondary"

                    >

                        Réinitialiser

                    </a>

                </div>

            </div>

        </form>

        @if($plate !== '')

            {{-- ================================================== --}}

            {{-- RÉSUMÉ DE LA RECHERCHE                             --}}

            {{-- ================================================== --}}

            <div class="alert alert-info mb-3">

                <div class="d-flex flex-wrap align-items-center gap-2">

                    <span>

                        Résultats pour l’immatriculation :

                    </span>

                    <strong>

                        {{ $plate }}

                    </strong>

                    @if($dateFrom || $dateTo)

                        <span class="mx-1">

                            |

                        </span>

                        <span>

                            Période :

                        </span>

                        <strong>

                            @if($dateFrom)

                                {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}

                            @else

                                Début indéfini

                            @endif

                            au

                            @if($dateTo)

                                {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}

                            @else

                                Aujourd’hui

                            @endif

                        </strong>

                    @endif

                    @if($statusFilter)

                        <span class="mx-1">

                            |

                        </span>

                        <span>

                            Statut :

                        </span>

                        <strong>

                            @switch($statusFilter)

                                @case('payé')

                                    Payée

                                    @break

                                @case('vendu')

                                    Vendue

                                    @break

                                @case('annulé')

                                    Annulée

                                    @break

                                @default

                                    Tous

                            @endswitch

                        </strong>

                    @endif

                </div>

            </div>

            {{-- ================================================== --}}

            {{-- CARTES DES STATISTIQUES                            --}}

            {{-- ================================================== --}}

            <div class="history-stats">

                {{-- NOMBRE DE VENTES --}}

                <div class="history-stat">

                    <div class="card border shadow-none h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <div class="text-muted small mb-1">

                                        Nombre de ventes

                                    </div>

                                    <h3 class="mb-0 fw-bold">

                                        {{ $salesCount }}

                                    </h3>

                                </div>

                                <div class="avatar bg-label-primary rounded">

                                    <i class="bx bx-receipt fs-3"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- NOMBRE DE LIGNES DE PIÈCES --}}

                <div class="history-stat">

                    <div class="card border shadow-none h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <div class="text-muted small mb-1">

                                        Lignes de pièces

                                    </div>

                                    <h3 class="mb-0 fw-bold">

                                        {{ $piecesCount }}

                                    </h3>

                                </div>

                                <div class="avatar bg-label-info rounded">

                                    <i class="bx bx-package fs-3"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- QUANTITÉ TOTALE --}}

                <div class="history-stat">

                    <div class="card border shadow-none h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <div class="text-muted small mb-1">

                                        Quantité totale

                                    </div>

                                    <h3 class="mb-0 fw-bold">

                                        {{ number_format(

                                            (float) $totalQuantity,

                                            2,

                                            ',',

                                            ' '

                                        ) }}

                                    </h3>

                                </div>

                                <div class="avatar bg-label-success rounded">

                                    <i class="bx bx-calculator fs-3"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- MONTANT TOTAL DES PIÈCES --}}

                <div class="history-stat">

                    <div class="card border shadow-none h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                   <div class="text-muted small mb-1">

                                        Montant total des pièces

                                    </div>

                                    <!--div class="text-muted" style="font-size: 11px;">

                                        Hors ventes annulées

                                    </div-->

                                    <h3 class="mb-0 fw-bold">

                                        {{ number_format(

                                            (float) $totalAmount,

                                            2,

                                            ',',

                                            ' '

                                        ) }}

                                        <small class="fs-6">

                                            FDJ

                                        </small>

                                    </h3>

                                </div>

                                <div class="avatar bg-label-warning rounded">

                                    <i class="bx bx-money fs-3"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- ================================================== --}}

            {{-- TABLEAU                                            --}}

            {{-- ================================================== --}}

            <div class="history-table-wrapper">

                <table class="table table-bordered table-hover align-middle mb-0 history-table">

                    <thead class="table-light">

                        <tr>

                            <th class="">

                                Date

                            </th>

                            <th class="">

                                Facture

                            </th>

                            <th>

                                Client

                            </th>

                            <th>

                                Référence

                            </th>

                            <th>

                                Désignation

                            </th>

                            <th class="">

                                <abbr title="Immatriculation" style="text-decoration: none;">Immat.</abbr>

                            </th>

                            <th class="text-center ">

                                Quantité

                            </th>

                            <th class="text-end ">

                                Prix unitaire

                            </th>

                            <th class="text-end ">

                                Total ligne

                            </th>

                            <th class="text-center">

                                Statut

                            </th>

                            <th class="text-center">

                                Action

                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($items as $item)

                            @php

                                $sale = $item->sale;

                                $vehicle = $sale?->vehicle;

                                $customer = $sale?->customer;

                                $product = $item->product;

                                $status = strtolower(

                                    trim(

                                        (string) ($sale?->status ?? '')

                                    )

                                );

                            @endphp

                            <tr>

                                {{-- DATE --}}

                                <td data-label="Date" class="">

                                    {{ $sale?->created_at?->format('d/m/Y') ?? '-' }}

                                </td>

                                {{-- FACTURE --}}

                                <td data-label="Facture" class="fw-bold ">

                                    {{ $sale?->invoice_number ?? '-' }}

                                </td>

                                {{-- CLIENT --}}

                                <td data-label="Client">

                                    {{ $customer?->name ?? 'Vente comptoir' }}

                                </td>

                                {{-- RÉFÉRENCE --}}

                                <td data-label="Référence" class="">

                                    {{ $product?->reference ?? '-' }}

                                </td>

                                {{-- DÉSIGNATION --}}

                                <td data-label="Désignation">

                                    {{ $product?->designation ?? '-' }}

                                    @if($product?->brand?->name)

                                        <div class="small text-muted">

                                            {{ $product->brand->name }}

                                            @if($product?->model?->name)

                                                —

                                                {{ $product->model->name }}

                                            @endif

                                        </div>

                                    @endif

                                </td>

                                {{-- IMMATRICULATION --}}

                                <td data-label="Immatriculation" class="fw-bold ">

                                    {{ $vehicle?->plate_number ?? '-' }}

                                </td>

                                {{-- QUANTITÉ --}}

                                <td data-label="Quantité" class="text-center ">

                                    {{ number_format(

                                        (float) $item->quantity,

                                        2,

                                        ',',

                                        ' '

                                    ) }}

                                    {{ $product?->unit_label ?? 'Pièce' }}

                                </td>

                                {{-- PRIX UNITAIRE --}}

                                <td data-label="Prix unitaire" class="text-end ">

                                    {{ number_format(

                                        (float) $item->price,

                                        2,

                                        ',',

                                        ' '

                                    ) }}

                                    FDJ

                                </td>

                                {{-- TOTAL DE LA LIGNE --}}

                                <td data-label="Total ligne" class="text-end  fw-bold">

                                    {{ number_format(

                                        $item->total !== null

                                            ? (float) $item->total

                                            : (float) $item->price * (float) $item->quantity,

                                        2,

                                        ',',

                                        ' '

                                    ) }}

                                    FDJ

                                </td>

                                {{-- STATUT --}}

                                <td data-label="Statut" class="text-center">

                                    @switch($status)

                                        @case('cancelled')

                                        @case('annulé')

                                        @case('annule')

                                            <span class="badge bg-danger">

                                                Annulée

                                            </span>

                                            @break

                                        @case('payé')

                                        @case('paye')

                                        @case('paid')

                                            <span class="badge bg-success">

                                                Payée

                                            </span>

                                            @break

                                        @case('vendu')

                                        @case('sold')

                                            <span class="badge bg-primary">

                                                Vendue

                                            </span>

                                            @break

                                        @case('en_attente')

                                        @case('pending')

                                            <span class="badge bg-warning text-dark">

                                                En attente

                                            </span>

                                            @break

                                        @default

                                            <span class="badge bg-secondary">

                                                {{

                                                    $sale?->status

                                                        ? ucfirst($sale->status)

                                                        : 'Non défini'

                                                }}

                                            </span>

                                    @endswitch

                                </td>

                                {{-- ACTION --}}

                                <td data-label="Action" class="text-center">

                                    @if($sale)

                                        <a

                                            href="{{ route('sales.show', $sale) }}"

                                            class="btn btn-sm btn-outline-primary history-invoice-button" title="Voir facture" aria-label="Voir facture"

                                        >

                                            <i class="bx bx-show me-1"></i>

                                            <span class="visually-hidden">Voir facture</span>

                                        </a>

                                    @else

                                        <span class="text-muted">

                                            -

                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td

                                    colspan="11"

                                    class="text-center py-5 text-muted"

                                >

                                    <i

                                        class="bx bx-search-alt"

                                        style="font-size: 42px;"

                                    ></i>

                                    <div class="mt-2">

                                        Aucune vente trouvée pour cette immatriculation

                                        pendant la période sélectionnée.

                                    </div>

                                    <div class="small mt-1">

                                        Modifiez les dates ou vérifiez l’immatriculation.

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        @endif

    </div>

</div>

@endsection
