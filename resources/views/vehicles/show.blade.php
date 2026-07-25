@extends('layouts.layoutMaster')

@section('content')

<div class="card shadow-sm border-0">

    <div class="card-header border-0">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>
                <h3 class="mb-1 fw-bold">
                    Détails du véhicule
                </h3>

                <p class="text-muted mb-0">
                    Informations et historique des ventes.
                </p>
            </div>

            <div class="d-flex gap-2">

                <a
                    href="{{ route('vehicles.index') }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="bx bx-arrow-back me-1"></i>
                    Retour
                </a>

                <a
                    href="{{ route('vehicles.edit', $vehicle) }}"
                    class="btn btn-warning"
                >
                    <i class="bx bx-edit me-1"></i>
                    Modifier
                </a>

            </div>

        </div>

    </div>

    <div class="card-body">

        {{-- ALERTES --}}
        @if(session('success'))

            <div class="alert alert-success alert-dismissible fade show">

                {{ session('success') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        @endif

        @if(session('error'))

            <div class="alert alert-danger alert-dismissible fade show">

                {{ session('error') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        @endif

        {{-- INFORMATIONS DU VÉHICULE --}}
        <div class="row g-4">

            <div class="col-lg-8">

                <div class="card border shadow-none h-100">

                    <div class="card-header bg-light">
                        <h5 class="mb-0 fw-bold">
                            Informations du véhicule
                        </h5>
                    </div>

                    <div class="card-body">

                        <div class="row g-4">

                            <div class="col-md-6">
                                <div class="text-muted small">
                                    Immatriculation
                                </div>

                                <div class="mt-1">
                                    <span class="badge bg-label-primary fs-5">
                                        {{ $vehicle->plate_number }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">
                                    Client
                                </div>

                                <div class="fw-semibold mt-1">
                                    {{ $vehicle->customer->name ?? 'Non renseigné' }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">
                                    Marque
                                </div>

                                <div class="fw-semibold mt-1">
                                    {{ $vehicle->brand ?? '-' }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">
                                    Modèle
                                </div>

                                <div class="fw-semibold mt-1">
                                    {{ $vehicle->model ?? '-' }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">
                                    VIN
                                </div>

                                <div class="fw-semibold mt-1">
                                    {{ $vehicle->vin ?? '-' }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">
                                    Année
                                </div>

                                <div class="fw-semibold mt-1">
                                    {{ $vehicle->year ?? '-' }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">
                                    Couleur
                                </div>

                                <div class="fw-semibold mt-1">
                                    {{ $vehicle->color ?? '-' }}
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="text-muted small">
                                    Notes
                                </div>

                                <div class="fw-semibold mt-1">
                                    {{ $vehicle->notes ?? 'Aucune note.' }}
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- RÉSUMÉ --}}
            <div class="col-lg-4">

                <div class="card border shadow-none h-100">

                    <div class="card-header bg-light">
                        <h5 class="mb-0 fw-bold">
                            Résumé
                        </h5>
                    </div>

                    <div class="card-body">

                        <div class="d-flex justify-content-between mb-3">

                            <span class="text-muted">
                                Nombre de ventes
                            </span>

                            <strong>
                                {{ $vehicle->sales->count() }}
                            </strong>

                        </div>

                        <div class="d-flex justify-content-between mb-3">

                            <span class="text-muted">
                                Total des ventes
                            </span>

                            <strong>
                                {{ number_format(
                                    round($vehicle->sales->sum('total')),
                                    0,
                                    ',',
                                    ' '
                                ) }}
                                FDJ
                            </strong>

                        </div>

                        <div class="d-flex justify-content-between">

                            <span class="text-muted">
                                Dernière vente
                            </span>

                            <strong>
                                {{ optional(
                                    $vehicle->sales->first()?->created_at
                                )->format('d/m/Y') ?? '-' }}
                            </strong>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- HISTORIQUE DES VENTES --}}
        <div class="card border shadow-none mt-4">

            <div class="card-header bg-light">

                <h5 class="mb-0 fw-bold">
                    Historique des ventes
                </h5>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered align-middle">

                        <thead class="table-light">

                            <tr>
                                <th>Facture</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th class="text-end">
                                    Montant
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

                            @forelse($vehicle->sales as $sale)

                                <tr>

                                    <td class="fw-semibold">
                                        {{ $sale->invoice_number }}
                                    </td>

                                    <td>
                                        {{ $sale->created_at->format('d/m/Y') }}
                                    </td>

                                    <td>
                                        {{ $sale->customer->name ?? '-' }}
                                    </td>

                                    <td class="text-end fw-semibold">

                                        {{ number_format(
                                            round($sale->total),
                                            0,
                                            ',',
                                            ' '
                                        ) }}
                                        FDJ

                                    </td>

                                    <td class="text-center">

                                        @if(in_array($sale->status, ['paid', 'payé']))

                                            <span class="badge bg-success">
                                                PAYÉ
                                            </span>

                                        @elseif(in_array($sale->status, ['partial', 'partiel']))

                                            <span class="badge bg-warning">
                                                PARTIEL
                                            </span>

                                        @elseif($sale->status === 'vendu')

                                            <span class="badge bg-primary">
                                                VENDU
                                            </span>

                                        @elseif($sale->status === 'cancelled')

                                            <span class="badge bg-danger">
                                                ANNULÉ
                                            </span>

                                        @else

                                            <span class="badge bg-secondary">
                                                {{ strtoupper($sale->status) }}
                                            </span>

                                        @endif

                                    </td>

                                    <td class="text-center">

                                        <a
                                            href="{{ route('sales.show', $sale) }}"
                                            class="btn btn-sm btn-info"
                                            title="Voir la facture"
                                        >
                                            <i class="bx bx-show"></i>
                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="6"
                                        class="text-center text-muted py-5"
                                    >
                                        Aucune vente enregistrée pour ce véhicule.
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
