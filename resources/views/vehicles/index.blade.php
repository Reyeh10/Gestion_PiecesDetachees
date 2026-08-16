@extends('layouts.layoutMaster')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | UTILISATEUR CONNECTÉ
    |--------------------------------------------------------------------------
    */

    $user = auth()->user();

    /*
    |--------------------------------------------------------------------------
    | DROITS
    |--------------------------------------------------------------------------
    */

    // Peut créer et modifier un véhicule
    $canManageVehicle = in_array($user->role, [
        'admin',
        'chef_magasinier',
        'magasinier',
    ]);

    // Seul l'administrateur peut supprimer
    $canDeleteVehicle = $user->role === 'admin';
@endphp


<div class="card shadow-sm border-0">

    {{-- ============================================================
        HEADER
    ============================================================ --}}
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


            {{-- ====================================================
                NOUVEAU VÉHICULE
                VENDEUR ET CAISSIER : INTERDIT
            ==================================================== --}}

            @if($canManageVehicle)

                <a
                    href="{{ route('vehicles.create') }}"
                    class="btn btn-primary"
                >

                    <i class="bx bx-plus me-1"></i>

                    Nouveau véhicule

                </a>

            @endif

        </div>

    </div>


    <div class="card-body">

        {{-- ============================================================
            MESSAGE SUCCESS
        ============================================================ --}}

        @if(session('success'))

            <div class="alert alert-success alert-dismissible fade show"
                 role="alert">

                <i class="bx bx-check-circle me-1"></i>

                {{ session('success') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Fermer"
                ></button>

            </div>

        @endif


        {{-- ============================================================
            MESSAGE ERREUR
        ============================================================ --}}

        @if(session('error'))

            <div class="alert alert-danger alert-dismissible fade show"
                 role="alert">

                <i class="bx bx-error-circle me-1"></i>

                {{ session('error') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Fermer"
                ></button>

            </div>

        @endif


        {{-- ============================================================
            RECHERCHE
        ============================================================ --}}

        <form
            method="GET"
            action="{{ route('vehicles.index') }}"
            class="row g-3 align-items-end mb-4"
        >

            <div class="col-lg-8 col-md-7">

                <label
                    for="vehicleSearch"
                    class="form-label fw-semibold"
                >
                    Recherche
                </label>

                <input
                    type="text"
                    id="vehicleSearch"
                    name="search"
                    value="{{ $search ?? request('search') }}"
                    class="form-control"
                    placeholder="Immatriculation, VIN, marque, modèle ou client"
                >

            </div>


            <div class="col-lg-4 col-md-5">

                <div class="d-flex flex-wrap gap-2">

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

                        <i class="bx bx-reset me-1"></i>

                        Réinitialiser

                    </a>

                </div>

            </div>

        </form>


        {{-- ============================================================
            TABLEAU
        ============================================================ --}}

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-light">

                    <tr>

                        <th>
                            Immatriculation
                        </th>

                        <th>
                            Client
                        </th>

                        <th>
                            Marque
                        </th>

                        <th>
                            Modèle
                        </th>

                        <th>
                            VIN
                        </th>

                        <th class="text-center">
                            Historique
                        </th>

                        <th
                            class="text-center"
                            style="min-width: 120px;"
                        >
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($vehicles as $vehicle)

                        <tr>

                            {{-- ========================================
                                IMMATRICULATION
                            ======================================== --}}
                            <td>

                                <span class="badge bg-label-primary fs-6">

                                    {{ $vehicle->plate_number ?? '-' }}

                                </span>

                            </td>


                            {{-- ========================================
                                CLIENT
                            ======================================== --}}
                            <td>

                                {{ $vehicle->customer->name ?? 'Non renseigné' }}

                            </td>


                            {{-- ========================================
                                MARQUE
                            ======================================== --}}
                            <td>

                                {{ $vehicle->brand ?? '-' }}

                            </td>


                            {{-- ========================================
                                MODÈLE
                            ======================================== --}}
                            <td>

                                {{ $vehicle->model ?? '-' }}

                            </td>


                            {{-- ========================================
                                VIN
                            ======================================== --}}
                            <td>

                                {{ $vehicle->vin ?? '-' }}

                            </td>


                            {{-- ========================================
                                HISTORIQUE
                            ======================================== --}}
                            <td class="text-center">

                                @php
                                    $salesCount = (int) ($vehicle->sales_count ?? 0);
                                @endphp

                                @if($salesCount > 0)

                                    <span class="badge bg-label-info">

                                        {{ $salesCount }}

                                        {{ $salesCount > 1
                                            ? 'ventes'
                                            : 'vente'
                                        }}

                                    </span>

                                @else

                                    <span class="text-muted">
                                        Aucune vente
                                    </span>

                                @endif

                            </td>


                            {{-- ========================================
                                ACTIONS
                            ======================================== --}}
                            <td class="text-center">

                                <div class="d-flex justify-content-center align-items-center gap-1">


                                    {{-- ====================================
                                        VOIR
                                        TOUS LES UTILISATEURS AUTORISÉS
                                    ==================================== --}}

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


                                    {{-- ====================================
                                        MODIFIER
                                        
                                        AUTORISÉ :
                                        - ADMIN
                                        - CHEF MAGASINIER
                                        - MAGASINIER

                                        INTERDIT :
                                        - VENDEUR
                                        - CAISSIER
                                    ==================================== --}}

                                    @if($canManageVehicle)

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

                                    @endif


                                    {{-- ====================================
                                        SUPPRIMER
                                        
                                        ADMIN UNIQUEMENT
                                    ==================================== --}}

                                    @if($canDeleteVehicle)

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-danger"
                                            title="Supprimer"
                                            onclick="confirmVehicleDelete(
                                                {{ $vehicle->id }},
                                                @js($vehicle->plate_number ?? '')
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
                                class="text-center py-5"
                            >

                                <div class="text-muted">

                                    <i class="bx bx-car fs-1 d-block mb-2"></i>

                                    Aucun véhicule trouvé.

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- ============================================================
            PAGINATION
        ============================================================ --}}

        @if(method_exists($vehicles, 'links'))

            <div class="mt-3">

                {{ $vehicles->links() }}

            </div>

        @endif

    </div>

</div>


{{-- ================================================================
    JAVASCRIPT
================================================================ --}}

<script>

    /*
    |--------------------------------------------------------------------------
    | CONFIRMATION SUPPRESSION
    |--------------------------------------------------------------------------
    */

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

                const form = document.getElementById(
                    'delete-vehicle-' + vehicleId
                );

                if (form) {

                    form.submit();

                }

            }

        });
    }

</script>

@endsection