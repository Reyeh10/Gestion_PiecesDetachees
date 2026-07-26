@extends('layouts.layoutMaster')

@section('content')

<div class="card shadow-sm border-0">

    <div class="card-header border-0">

        <h3 class="mb-1 fw-bold">
            Traçabilité par immatriculation
        </h3>

        <p class="text-muted mb-0">
            Recherchez toutes les pièces vendues pour un véhicule.
        </p>

    </div>

    <div class="card-body">

        {{-- ====================================================== --}}
        {{-- FORMULAIRE DE RECHERCHE                                --}}
        {{-- ====================================================== --}}

        <form
            method="GET"
            action="{{ route('vehicles.history') }}"
            class="row g-3 align-items-end mb-4"
        >

            <div class="col-lg-8 col-md-7">

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
                    value="{{ $plate }}"
                    class="form-control text-uppercase"
                    placeholder="Exemple : 200D77"
                    autocomplete="off"
                    required
                >

            </div>

            <div class="col-lg-4 col-md-5">

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
            {{-- INFORMATION DE RECHERCHE                           --}}
            {{-- ================================================== --}}

            <div class="alert alert-info">

                Résultats pour l’immatriculation :

                <strong>
                    {{ $plate }}
                </strong>

                @if($items->isNotEmpty())

                    <span class="ms-2">
                        — {{ $items->count() }}
                        {{ $items->count() > 1 ? 'pièces trouvées' : 'pièce trouvée' }}
                    </span>

                @endif

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
                                    trim((string) ($sale?->status ?? ''))
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
                                                {{ $sale?->status
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
                                    colspan="9"
                                    class="text-center py-5 text-muted"
                                >

                                    <i
                                        class="bx bx-search-alt"
                                        style="font-size: 42px;"
                                    ></i>

                                    <div class="mt-2">
                                        Aucune pièce trouvée pour cette immatriculation.
                                    </div>

                                    <div class="small mt-1">
                                        Vérifiez que le véhicule est associé à une vente.
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
