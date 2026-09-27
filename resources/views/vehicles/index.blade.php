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

{{-- Styles limités à cette page : tableau compact et recherche alignée. --}}
<style>
    #vehicles-page { width: 100%; max-width: 100%; min-width: 0; }
    #vehicles-page .card-header { padding: 16px 18px 12px; }
    #vehicles-page .card-body { padding: 0 18px 16px; }
    #vehicles-page h3 { font-size: 1.3rem; }
    #vehicles-page .card-header p { font-size: .85rem; }
    #vehicles-page .btn { font-size: .82rem; padding: 7px 11px; }
    #vehicles-page .vehicle-search {
        display: grid; grid-template-columns: minmax(0, 1fr) auto;
        align-items: end; gap: 12px; margin: 10px 0 16px;
    }
    #vehicles-page .vehicle-search-field { min-width: 0; }
    #vehicles-page .form-label { font-size: .75rem; margin-bottom: 5px; }
    #vehicles-page .form-control { height: 38px; min-height: 38px; font-size: .85rem; }
    #vehicles-page .vehicle-search-actions { display: flex; flex-wrap: nowrap; gap: 8px; }
    #vehicles-page .vehicle-search-actions .btn {
        display: inline-flex; align-items: center; justify-content: center;
        white-space: nowrap; height: 38px; margin: 0;
    }
    #vehicles-page .vehicle-table-wrapper { width: 100%; min-width: 0; }
    #vehicles-page .vehicle-table { width: 100%; min-width: 0; table-layout: fixed; margin: 0; }
    #vehicles-page .vehicle-table th,
    #vehicles-page .vehicle-table td {
        padding: 9px 7px; font-size: .8rem; line-height: 1.4;
        white-space: normal; overflow-wrap: anywhere; vertical-align: middle;
    }
    #vehicles-page .vehicle-table th { font-size: .68rem; letter-spacing: .02em; }
    #vehicles-page .vehicle-table th:nth-child(1) { width: 16%; }
    #vehicles-page .vehicle-table th:nth-child(2) { width: 17%; }
    #vehicles-page .vehicle-table th:nth-child(3) { width: 12%; }
    #vehicles-page .vehicle-table th:nth-child(4) { width: 12%; }
    #vehicles-page .vehicle-table th:nth-child(5) { width: 17%; }
    #vehicles-page .vehicle-table th:nth-child(6) { width: 12%; }
    #vehicles-page .vehicle-table th:nth-child(7) { width: 14%; }
    #vehicles-page .badge { max-width: 100%; white-space: normal; overflow-wrap: anywhere; font-size: .74rem; padding: 5px 7px; line-height: 1.35; }
    #vehicles-page .vehicle-row-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 4px; }
    #vehicles-page .vehicle-icon-button { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; min-width: 28px; padding: 0; flex: 0 0 28px; }
    #vehicles-page .vehicle-icon-button i { font-size: 16px; }
    #vehicles-page .pagination { flex-wrap: wrap; gap: 3px; }
    @media (max-width: 767.98px) {
        #vehicles-page .card-header { padding: 12px; }
        #vehicles-page .card-body { padding: 0 12px 12px; }
        #vehicles-page .vehicle-search { grid-template-columns: minmax(0, 1fr); gap: 8px; }
        #vehicles-page .vehicle-search-actions .btn { flex: 1; }
        #vehicles-page .vehicle-table thead { display: none; }
        #vehicles-page .vehicle-table, #vehicles-page .vehicle-table tbody { display: block; }
        #vehicles-page .vehicle-table tr { display: block; border: 1px solid #dfe3e8; border-radius: 8px; margin-bottom: 10px; padding: 6px; }
        #vehicles-page .vehicle-table td { display: grid; grid-template-columns: 110px minmax(0, 1fr); gap: 8px; width: 100%; border: 0; text-align: left !important; padding: 6px; }
        #vehicles-page .vehicle-table td::before { content: attr(data-label); font-weight: 600; font-size: .73rem; }
        #vehicles-page .vehicle-table td[colspan] { display: block; }
        #vehicles-page .vehicle-table td[colspan]::before { content: none; }
        #vehicles-page .vehicle-row-actions { justify-content: flex-start; }
    }
</style>

<div id="vehicles-page" class="card shadow-sm border-0">

    {{-- ============================================================*
    HEADER
    *    ============================================================ --}}

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

            {{-- ====================================================*
            NOUVEAU VÉHICULE
            VENDEUR ET CAISSIER : INTERDIT
            *            ==================================================== --}}

            @if($canManageVehicle)
                <a
                    href="{{ route('vehicles.create') }}"
                    data-bs-toggle="modal" data-bs-target="#createVehicleModal"
                    class="btn btn-primary"
                >
                    <i class="bx bx-plus me-1"></i>
                    Nouveau véhicule
                </a>
            @endif
        </div>
    </div>
    <div class="card-body">

        {{-- ============================================================*
        MESSAGE SUCCESS
        *        ============================================================ --}}

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

        {{-- ============================================================*
        MESSAGE ERREUR
        *        ============================================================ --}}

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

        {{-- ============================================================*
        RECHERCHE
        *        ============================================================ --}}

        <form
            method="GET"
            action="{{ route('vehicles.index') }}"
            class="vehicle-search"
        >
            <div class="vehicle-search-field">
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
            <div class="vehicle-search-buttons">
                <div class="vehicle-search-actions">
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

        {{-- ============================================================*
        TABLEAU
        *        ============================================================ --}}

        <div class="vehicle-table-wrapper">
            <table class="table table-bordered table-hover align-middle vehicle-table">
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

                        >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $vehicle)
                        <tr>

                            {{-- ========================================*
                            IMMATRICULATION
                            *                            ======================================== --}}


                            {{-- ========================================*
                            CLIENT
                            *                            ======================================== --}}


                            {{-- ========================================*
                            MARQUE
                            *                            ======================================== --}}


                            {{-- ========================================*
                            MODÈLE
                            *                            ======================================== --}}


                            {{-- ========================================*
                            VIN
                            *                            ======================================== --}}


                            {{-- ========================================*
                            HISTORIQUE
                            *                            ======================================== --}}


                            {{-- ========================================*
                            ACTIONS
                            *                            ======================================== --}}

                            <td data-label="Immatriculation">
                                <span class="badge bg-label-primary">
                                    {{ $vehicle->plate_number ?? '-' }}
                                </span>
                            </td>
                            <td data-label="Client">
                                {{ $vehicle->customer->name ?? 'Non renseigné' }}
                            </td>
                            <td data-label="Marque">
                                {{ $vehicle->brand ?? '-' }}
                            </td>
                            <td data-label="Modèle">
                                {{ $vehicle->model ?? '-' }}
                            </td>
                            <td data-label="VIN">
                                {{ $vehicle->vin ?? '-' }}
                            </td>
                            <td data-label="Historique" class="text-center">
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
                            <td data-label="Actions" class="text-center">
                                <div class="vehicle-row-actions">

                                    {{-- ====================================*
                                    VOIR
                                    TOUS LES UTILISATEURS AUTORISÉS
                                    *                                    ==================================== --}}

                                    <a
                                        href="{{ route(
                                            'vehicles.show',
                                            $vehicle
                                        ) }}"
                                        class="btn btn-sm btn-info vehicle-icon-button" aria-label="Voir"
                                        title="Voir"
                                    >
                                        <i class="bx bx-show"></i>
                                    </a>

                                    {{-- ====================================*
                                    MODIFIER
                                    AUTORISÉ :
                                    - ADMIN
                                    - CHEF MAGASINIER
                                    - MAGASINIER
                                    INTERDIT :
                                    - VENDEUR
                                    - CAISSIER
                                    *                                    ==================================== --}}

                                    @if($canManageVehicle)
                                        <a
                                            href="{{ route(
                                                'vehicles.edit',
                                                $vehicle
                                            ) }}"
                                            class="btn btn-sm btn-warning vehicle-icon-button" aria-label="Modifier"
                                            title="Modifier"
                                        >
                                            <i class="bx bx-edit"></i>
                                        </a>
                                    @endif

                                    {{-- ====================================*
                                    SUPPRIMER
                                    ADMIN UNIQUEMENT
                                    *                                    ==================================== --}}

                                    @if($canDeleteVehicle)
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-danger vehicle-icon-button" aria-label="Supprimer"
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

        {{-- ============================================================*
        PAGINATION
        *        ============================================================ --}}

        @if(method_exists($vehicles, 'links'))
            <div class="mt-3">
                {{ $vehicles->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ================================================================*
JAVASCRIPT
*================================================================ --}}

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
            text: 'Vous êtes sur le point de supprimer le véhicule ' + plateNumber + '. Cette opération est définitive.',
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

{{-- ============================================================
    POPUP DE CRÉATION D’UN VÉHICULE
    Le formulaire conserve les valeurs old() après une erreur.
============================================================ --}}

@if($canManageVehicle)
<div class="modal fade" id="createVehicleModal" tabindex="-1" aria-labelledby="createVehicleModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createVehicleModalTitle"><i class="bx bx-car me-2"></i>Nouveau véhicule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form action="{{ route('vehicles.store') }}" method="POST" id="createVehicleForm" style="display:flex;flex-direction:column;min-height:0;overflow:hidden">
                @csrf
                <input type="hidden" name="_vehicle_create_modal" value="1">
                <div class="modal-body">
                    @if(old('_vehicle_create_modal') && $errors->any())
                        <div class="alert alert-danger" role="alert">
                            <strong>Veuillez corriger les informations suivantes :</strong>
                            <ul class="mb-0 mt-1">
                                @foreach($errors->all() as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if(old('_vehicle_create_modal') && session('error'))
                        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                    @endif
                    {{-- Explicitement vide : ne pas reprendre le dernier véhicule du tableau. --}}
                    @include('vehicles._form', ['vehicle' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
window.addEventListener('load', function () {
    const modal = document.getElementById('createVehicleModal');
    if (!modal) return;
    // Select2 doit afficher sa liste dans la modale pour rester accessible.
    modal.addEventListener('shown.bs.modal', function () {
        if (window.jQuery && window.jQuery.fn.select2) {
            const select = window.jQuery(modal).find('select[name="customer_id"]');
            if (select.hasClass('select2-hidden-accessible')) select.select2('destroy');
            select.select2({ dropdownParent: window.jQuery(modal), width: '100%' });
        }
        modal.querySelector('[name="plate_number"]').focus();
    });
    // Réouvrir uniquement après un échec du formulaire de création du popup.
    @if(old('_vehicle_create_modal') && ($errors->any() || session('error')))
        if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modal).show();
        }
    @endif
});
</script>
@endif

@endsection
