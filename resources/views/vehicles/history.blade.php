@extends('layouts.layoutMaster')

@section('content')

<div class="card shadow-sm border-0">

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
            class="row g-3 align-items-end mb-4"
        >

            {{-- IMMATRICULATION --}}
            <div class="col-xl-4 col-lg-4 col-md-6">

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
            <div class="col-xl-2 col-lg-2 col-md-3">

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
            <div class="col-xl-2 col-lg-2 col-md-3">

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

            {{-- BOUTONS --}}
            <div class="col-xl-4 col-lg-4 col-md-12">

                <div class="d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-primary flex-grow-1"
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

                </div>

            </div>

            {{-- ================================================== --}}
            {{-- CARTES DES STATISTIQUES                            --}}
            {{-- ================================================== --}}

            <div class="row g-3 mb-4">

                {{-- NOMBRE DE VENTES --}}
                <div class="col-xl-3 col-lg-6 col-md-6">

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
                <div class="col-xl-3 col-lg-6 col-md-6">

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
                <div class="col-xl-3 col-lg-6 col-md-6">

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
                <div class="col-xl-3 col-lg-6 col-md-6">

                    <div class="card border shadow-none h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <div class="text-muted small mb-1">
                                        Montant total des pièces
                                    </div>

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

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th class="text-nowrap">
                                Date
                            </th>

                            <th class="text-nowrap">
                                Facture
                            </th>

                            <th>
                                Client
                            </th>

                            <th>
                                Référence
                            </th>

                            <th style="min-width: 220px;">
                                Désignation
                            </th>

                            <th class="text-nowrap">
                                Immatriculation
                            </th>

                            <th class="text-center text-nowrap">
                                Quantité
                            </th>

                            <th class="text-end text-nowrap">
                                Prix unitaire
                            </th>

                            <th class="text-end text-nowrap">
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
                                <td class="text-nowrap">

                                    {{ $sale?->created_at?->format('d/m/Y') ?? '-' }}

                                </td>

                                {{-- FACTURE --}}
                                <td class="fw-bold text-nowrap">

                                    {{ $sale?->invoice_number ?? '-' }}

                                </td>

                                {{-- CLIENT --}}
                                <td>

                                    {{ $customer?->name ?? 'Vente comptoir' }}

                                </td>

                                {{-- RÉFÉRENCE --}}
                                <td class="text-nowrap">

                                    {{ $product?->reference ?? '-' }}

                                </td>

                                {{-- DÉSIGNATION --}}
                                <td>

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
                                <td class="fw-bold text-nowrap">

                                    {{ $vehicle?->plate_number ?? '-' }}

                                </td>

                                {{-- QUANTITÉ --}}
                                <td class="text-center text-nowrap">

                                    {{ number_format(
                                        (float) $item->quantity,
                                        2,
                                        ',',
                                        ' '
                                    ) }}

                                    {{ $product?->unit_label ?? 'Pièce' }}

                                </td>

                                {{-- PRIX UNITAIRE --}}
                                <td class="text-end text-nowrap">

                                    {{ number_format(
                                        (float) $item->price,
                                        2,
                                        ',',
                                        ' '
                                    ) }}

                                    FDJ

                                </td>

                                {{-- TOTAL DE LA LIGNE --}}
                                <td class="text-end text-nowrap fw-bold">

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
                                <td class="text-center">

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
                                <td class="text-center">

                                    @if($sale)

                                        <a
                                            href="{{ route('sales.show', $sale) }}"
                                            class="btn btn-sm btn-outline-primary text-nowrap"
                                        >
                                            <i class="bx bx-show me-1"></i>
                                            Voir facture
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
