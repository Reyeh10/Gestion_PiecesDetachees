@extends('layouts.layoutMaster')

@section('content')

<div class="card shadow-sm border-0">

    <div class="card-header border-0">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>
                <h3 class="mb-1 fw-bold">
                    Liste des véhicules
                </h3>

                <p class="text-muted mb-0">
                    Gestion et consultation des véhicules.
                </p>
            </div>

            <a
                href="{{ route('vehicles.create') }}"
                class="btn btn-primary"
            >
                <i class="bx bx-plus me-1"></i>
                Nouveau véhicule
            </a>

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

        {{-- RECHERCHE --}}
        <form
            method="GET"
            action="{{ route('vehicles.index') }}"
            class="row g-3 align-items-end mb-4"
        >

            <div class="col-md-8">

                <label class="form-label fw-semibold">
                    Recherche
                </label>

                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="Immatriculation, VIN, marque, modèle ou client"
                >

            </div>

            <div class="col-md-4">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="bx bx-search me-1"></i>
                    Rechercher
                </button>

                <a
                    href="{{ route('vehicles.index') }}"
                    class="btn btn-outline-secondary"
                >
                    Réinitialiser
                </a>

            </div>

        </form>

        {{-- TABLEAU --}}
        <div class="table-responsive">

            <table class="table table-bordered align-middle">

                <thead class="table-light">

                    <tr>
                        <th>Immatriculation</th>
                        <th>Client</th>
                        <th>Marque</th>
                        <th>Modèle</th>
                        <th>VIN</th>

                        <th class="text-center">
                            Historique
                        </th>

                        <th class="text-center">
                            Actions
                        </th>
                    </tr>

                </thead>

                <tbody>

                    @forelse($vehicles as $vehicle)

                        <tr>

                            <td>
                                <span class="badge bg-label-primary fs-6">
                                    {{ $vehicle->plate_number }}
                                </span>
                            </td>

                            <td>
                                {{ $vehicle->customer->name ?? 'Non renseigné' }}
                            </td>

                            <td>
                                {{ $vehicle->brand ?? '-' }}
                            </td>

                            <td>
                                {{ $vehicle->model ?? '-' }}
                            </td>

                            <td>
                                {{ $vehicle->vin ?? '-' }}
                            </td>

                            <td class="text-center">
                               {{ $vehicle->sales_count }}
                               {{ $vehicle->sales_count > 1 ? 'ventes' : 'vente' }}
                            </td>

                            <td class="text-center">

                                <div class="d-flex justify-content-center gap-1">

                                    {{-- VOIR --}}
                                    <a
                                        href="{{ route(
                                            'vehicles.show',
                                            $vehicle
                                        ) }}"
                                        class="btn btn-sm btn-info"
                                        title="Voir"
                                    >
                                        <i class="bx bx-show"></i>
                                    </a>

                                    {{-- MODIFIER --}}
                                    <a
                                        href="{{ route(
                                            'vehicles.edit',
                                            $vehicle
                                        ) }}"
                                        class="btn btn-sm btn-warning"
                                        title="Modifier"
                                    >
                                        <i class="bx bx-edit"></i>
                                    </a>

                                    {{-- SUPPRIMER : ADMIN UNIQUEMENT --}}
                                    @if(
                                        auth()->user()->role === 'admin'
                                    )

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-danger"
                                            title="Supprimer"
                                            onclick="confirmVehicleDelete(
                                                {{ $vehicle->id }},
                                                @js($vehicle->plate_number)
                                            )"
                                        >
                                            <i class="bx bx-trash"></i>
                                        </button>

                                        <form
                                            id="delete-vehicle-{{ $vehicle->id }}"
                                            action="{{ route(
                                                'vehicles.destroy',
                                                $vehicle
                                            ) }}"
                                            method="POST"
                                            class="d-none"
                                        >
                                            @csrf
                                            @method('DELETE')
                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="7"
                                class="text-center py-5 text-muted"
                            >
                                Aucun véhicule trouvé.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-3">
            {{ $vehicles->links() }}
        </div>

    </div>

</div>

<script>
    function confirmVehicleDelete(vehicleId, plateNumber)
    {
        Swal.fire({
            icon: 'warning',

            title: 'Supprimer le véhicule ?',

            html:
                'Vous êtes sur le point de supprimer le véhicule ' +
                '<strong>' +
                plateNumber +
                '</strong>.<br><br>' +
                'Cette opération est définitive.',

            showCancelButton: true,

            confirmButtonText: 'Oui, supprimer',

            cancelButtonText: 'Annuler',

            confirmButtonColor: '#dc3545',

            cancelButtonColor: '#6c757d',

            reverseButtons: true
        }).then(function (result) {

            if (result.isConfirmed) {
                document
                    .getElementById(
                        'delete-vehicle-' + vehicleId
                    )
                    .submit();
            }

        });
    }
</script>

@endsection
