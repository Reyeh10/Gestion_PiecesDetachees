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

            <div class="d-flex flex-wrap gap-2">

                {{-- Retour à la liste des véhicules --}}
                <a
                    href="{{ route('vehicles.index') }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="bx bx-arrow-back me-1"></i>
                    Retour
                </a>

                {{-- Ajouter une pièce manquante pour ce véhicule --}}
                <a
                    href="{{ route(
                        'vehicle-part-requests.create',
                        ['vehicle_id' => $vehicle->id]
                    ) }}"
                    class="btn btn-primary"
                >
                    <i class="bx bx-wrench me-1"></i>
                    Ajouter une pièce
                </a>

                {{-- Modifier le véhicule --}}
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

        {{-- ========================================================== --}}
        {{-- SUIVI DES PIÈCES DU VÉHICULE                               --}}
        {{-- ========================================================== --}}

        <div class="card border shadow-none mt-4">

            <div class="card-header bg-light">

                <div
                    class="d-flex flex-wrap
                        justify-content-between
                        align-items-center
                        gap-3"
                >

                    <div>
                        <h5 class="mb-1 fw-bold">
                            <i class="bx bx-wrench me-1"></i>
                            Suivi des pièces
                        </h5>

                        <p class="text-muted small mb-0">
                            Pièces recherchées, trouvées, commandées ou reçues
                            pour ce véhicule.
                        </p>
                    </div>

                    <a
                        href="{{ route(
                            'vehicle-part-requests.create',
                            ['vehicle_id' => $vehicle->id]
                        ) }}"
                        class="btn btn-primary btn-sm"
                    >
                        <i class="bx bx-plus me-1"></i>
                        Ajouter une pièce
                    </a>

                </div>

            </div>

            <div class="card-body">

                {{-- Résumé rapide des pièces --}}
                <div class="row g-3 mb-4">

                    {{-- Nombre total --}}
                    <div class="col-xl-3 col-md-6">

                        <div class="border rounded p-3 h-100">

                            <div
                                class="d-flex
                                    justify-content-between
                                    align-items-center"
                            >
                                <div>
                                    <div class="text-muted small">
                                        Total des demandes
                                    </div>

                                    <div class="fs-4 fw-bold mt-1">
                                        {{ $vehicle->partRequests->count() }}
                                    </div>
                                </div>

                                <div class="fs-2 text-primary">
                                    <i class="bx bx-package"></i>
                                </div>
                            </div>

                        </div>

                    </div>

                    {{-- En recherche --}}
                    <div class="col-xl-3 col-md-6">

                        <div class="border rounded p-3 h-100">

                            <div
                                class="d-flex
                                    justify-content-between
                                    align-items-center"
                            >
                                <div>
                                    <div class="text-muted small">
                                        En recherche
                                    </div>

                                    <div class="fs-4 fw-bold mt-1 text-warning">
                                        {{
                                            $vehicle->partRequests
                                                ->whereIn('status', [
                                                    'pending',
                                                    'searching',
                                                ])
                                                ->count()
                                        }}
                                    </div>
                                </div>

                                <div class="fs-2 text-warning">
                                    <i class="bx bx-search-alt"></i>
                                </div>
                            </div>

                        </div>

                    </div>

                    {{-- Commandées --}}
                    <div class="col-xl-3 col-md-6">

                        <div class="border rounded p-3 h-100">

                            <div
                                class="d-flex
                                    justify-content-between
                                    align-items-center"
                            >
                                <div>
                                    <div class="text-muted small">
                                        Commandées
                                    </div>

                                    <div class="fs-4 fw-bold mt-1 text-primary">
                                        {{
                                            $vehicle->partRequests
                                                ->where('status', 'ordered')
                                                ->count()
                                        }}
                                    </div>
                                </div>

                                <div class="fs-2 text-primary">
                                    <i class="bx bx-cart"></i>
                                </div>
                            </div>

                        </div>

                    </div>

                    {{-- Reçues --}}
                    <div class="col-xl-3 col-md-6">

                        <div class="border rounded p-3 h-100">

                            <div
                                class="d-flex
                                    justify-content-between
                                    align-items-center"
                            >
                                <div>
                                    <div class="text-muted small">
                                        Reçues
                                    </div>

                                    <div class="fs-4 fw-bold mt-1 text-success">
                                        {{
                                            $vehicle->partRequests
                                                ->where('status', 'received')
                                                ->count()
                                        }}
                                    </div>
                                </div>

                                <div class="fs-2 text-success">
                                    <i class="bx bx-check-circle"></i>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                {{-- Tableau des pièces --}}
                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-light">

                            <tr>
                                <th>
                                    Pièce
                                </th>

                                <th>
                                    Référence
                                </th>

                                <th class="text-center">
                                    Quantité
                                </th>

                                <th>
                                    Date de demande
                                </th>

                                <th>
                                    Date de commande
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

                            @forelse($vehicle->partRequests as $partRequest)

                                <tr>

                                    {{-- Nom de la pièce --}}
                                    <td>

                                        <div class="fw-semibold">
                                            {{ $partRequest->part_name }}
                                        </div>

                                        @if($partRequest->description)

                                            <small class="text-muted">
                                                {{
                                                    \Illuminate\Support\Str::limit(
                                                        $partRequest->description,
                                                        60
                                                    )
                                                }}
                                            </small>

                                        @endif

                                    </td>

                                    {{-- Référence --}}
                                    <td>

                                        @if($partRequest->reference)

                                            <span class="fw-semibold">
                                                {{ $partRequest->reference }}
                                            </span>

                                        @else

                                            <span class="text-muted">
                                                Non renseignée
                                            </span>

                                        @endif

                                    </td>

                                    {{-- Quantité --}}
                                    <td class="text-center">

                                        <span class="fw-semibold">

                                            {{
                                                number_format(
                                                    (float) $partRequest->quantity,
                                                    2,
                                                    ',',
                                                    ' '
                                                )
                                            }}

                                            {{ $partRequest->unit }}

                                        </span>

                                    </td>

                                    {{-- Date de demande --}}
                                    <td>

                                        @if($partRequest->requested_at)

                                            <div class="fw-semibold">
                                                {{
                                                    $partRequest
                                                        ->requested_at
                                                        ->format('d/m/Y')
                                                }}
                                            </div>

                                            <small class="text-muted">
                                                {{
                                                    $partRequest
                                                        ->requested_at
                                                        ->format('H:i')
                                                }}
                                            </small>

                                        @else

                                            <span class="text-muted">
                                                -
                                            </span>

                                        @endif

                                    </td>

                                    {{-- Date de commande --}}
                                    <td>

                                        @if($partRequest->ordered_at)

                                            <div class="fw-semibold">
                                                {{
                                                    $partRequest
                                                        ->ordered_at
                                                        ->format('d/m/Y')
                                                }}
                                            </div>

                                            <small class="text-muted">
                                                {{
                                                    $partRequest
                                                        ->ordered_at
                                                        ->format('H:i')
                                                }}
                                            </small>

                                        @else

                                            <span class="text-muted">
                                                Non commandée
                                            </span>

                                        @endif

                                    </td>

                                    {{-- Statut --}}
                                    <td class="text-center">

                                        @switch($partRequest->status)

                                            @case('pending')

                                                <span class="badge bg-secondary">
                                                    À RECHERCHER
                                                </span>

                                                @break

                                            @case('searching')

                                                <span class="badge bg-warning text-dark">
                                                    EN RECHERCHE
                                                </span>

                                                @break

                                            @case('found')

                                                <span class="badge bg-info">
                                                    TROUVÉE
                                                </span>

                                                @break

                                            @case('ordered')

                                                <span class="badge bg-primary">
                                                    COMMANDÉE
                                                </span>

                                                @break

                                            @case('received')

                                                <span class="badge bg-success">
                                                    REÇUE
                                                </span>

                                                @break

                                            @case('not_found')

                                                <span class="badge bg-danger">
                                                    NON TROUVÉE
                                                </span>

                                                @break

                                            @case('cancelled')

                                                <span class="badge bg-dark">
                                                    ANNULÉE
                                                </span>

                                                @break

                                            @default

                                                <span class="badge bg-secondary">
                                                    {{ strtoupper(
                                                        $partRequest->status
                                                    ) }}
                                                </span>

                                        @endswitch

                                    </td>

                                    {{-- Action --}}
                                    <td class="text-center">

                                        <a
                                            href="{{ route(
                                                'vehicle-part-requests.show',
                                                $partRequest
                                            ) }}"
                                            class="btn btn-sm btn-info"
                                            title="Voir le suivi de la pièce"
                                        >
                                            <i class="bx bx-show me-1"></i>
                                            Voir
                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="7"
                                        class="text-center py-5"
                                    >

                                        <div class="fs-1 text-muted mb-2">
                                            <i class="bx bx-package"></i>
                                        </div>

                                        <div class="fw-semibold">
                                            Aucune pièce enregistrée
                                        </div>

                                        <div class="text-muted small mb-3">
                                            Aucune pièce n’est actuellement recherchée
                                            pour ce véhicule.
                                        </div>

                                        <a
                                            href="{{ route(
                                                'vehicle-part-requests.create',
                                                ['vehicle_id' => $vehicle->id]
                                            ) }}"
                                            class="btn btn-primary btn-sm"
                                        >
                                            <i class="bx bx-plus me-1"></i>
                                            Ajouter la première pièce
                                        </a>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

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
