@extends('layouts.layoutMaster')

@section('content')

<div class="row g-4">

    {{-- INFORMATIONS --}}
    <div class="col-12">

        <div class="card shadow-sm border-0">

            <div class="card-header border-0">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                    <div>
                        <h3 class="mb-1 fw-bold">
                            Véhicule
                            {{ $vehicle->plate_number }}
                        </h3>

                        <p class="text-muted mb-0">
                            Informations et historique du véhicule.
                        </p>
                    </div>

                    <div class="d-flex gap-2">

                        <a
                            href="{{ route(
                                'vehicles.index'
                            ) }}"
                            class="btn btn-outline-secondary"
                        >
                            <i class="bx bx-arrow-back me-1"></i>
                            Retour
                        </a>

                        <a
                            href="{{ route(
                                'vehicles.edit',
                                $vehicle
                            ) }}"
                            class="btn btn-warning"
                        >
                            <i class="bx bx-edit me-1"></i>
                            Modifier
                        </a>

                    </div>

                </div>

            </div>

            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="row g-4">

                    <div class="col-md-3">
                        <small class="text-muted d-block">
                            Immatriculation
                        </small>

                        <span class="badge bg-label-primary fs-5">
                            {{ $vehicle->plate_number }}
                        </span>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted d-block">
                            Client
                        </small>

                        <strong>
                            {{ $vehicle->customer->name ?? 'Non renseigné' }}
                        </strong>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted d-block">
                            Marque
                        </small>

                        <strong>
                            {{ $vehicle->brand ?? '-' }}
                        </strong>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted d-block">
                            Modèle
                        </small>

                        <strong>
                            {{ $vehicle->model ?? '-' }}
                        </strong>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted d-block">
                            Année
                        </small>

                        <strong>
                            {{ $vehicle->year ?? '-' }}
                        </strong>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted d-block">
                            Couleur
                        </small>

                        <strong>
                            {{ $vehicle->color ?? '-' }}
                        </strong>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted d-block">
                            VIN
                        </small>

                        <strong>
                            {{ $vehicle->vin ?? '-' }}
                        </strong>
                    </div>

                    @if($vehicle->notes)

                        <div class="col-12">

                            <small class="text-muted d-block">
                                Notes
                            </small>

                            <div class="border rounded p-3 bg-light">
                                {{ $vehicle->notes }}
                            </div>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

    {{-- HISTORIQUE --}}
    <div class="col-12">

        <div class="card shadow-sm border-0">

            <div class="card-header border-0">
                <h4 class="mb-0 fw-bold">
                    Historique des pièces
                </h4>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered align-middle">

                        <thead class="table-light">

                            <tr>
                                <th>Date</th>
                                <th>Facture</th>
                                <th>Client</th>
                                <th>Référence</th>
                                <th>Désignation</th>

                                <th class="text-center">
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

                            @forelse(
                                $vehicle->saleItems
                                as $item
                            )

                                <tr>

                                    <td>
                                        {{
                                            optional(
                                                $item->sale?->created_at
                                            )->format('d/m/Y')
                                        }}
                                    </td>

                                    <td class="fw-bold">
                                        {{
                                            $item->sale?->invoice_number
                                            ?? '-'
                                        }}
                                    </td>

                                    <td>
                                        {{
                                            $item->sale?->customer?->name
                                            ?? 'Vente comptoir'
                                        }}
                                    </td>

                                    <td>
                                        {{
                                            $item->product?->reference
                                            ?? '-'
                                        }}
                                    </td>

                                    <td>
                                        {{
                                            $item->product?->designation
                                            ?? '-'
                                        }}
                                    </td>

                                    <td class="text-center">
                                        {{ $item->quantity }}
                                    </td>

                                    <td class="text-center">

                                        @if(
                                            $item->sale?->status
                                            === 'cancelled'
                                        )

                                            <span class="badge bg-danger">
                                                Annulée
                                            </span>

                                        @elseif(
                                            $item->sale?->status
                                            === 'payé'
                                        )

                                            <span class="badge bg-success">
                                                Payée
                                            </span>

                                        @else

                                            <span class="badge bg-primary">
                                                {{
                                                    ucfirst(
                                                        $item->sale?->status
                                                        ?? '-'
                                                    )
                                                }}
                                            </span>

                                        @endif

                                    </td>

                                    <td class="text-center">

                                        @if($item->sale)

                                            <a
                                                href="{{ route(
                                                    'sales.show',
                                                    $item->sale
                                                ) }}"
                                                class="btn btn-sm btn-outline-primary"
                                            >
                                                Voir facture
                                            </a>

                                        @endif

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td
                                        colspan="8"
                                        class="text-center py-5 text-muted"
                                    >
                                        Aucune pièce enregistrée pour ce véhicule.
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
