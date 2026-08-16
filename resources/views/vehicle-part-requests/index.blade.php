@extends('layouts.layoutMaster')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | CONTEXTE DE LA LISTE
    |--------------------------------------------------------------------------
    */

    $currentList = $currentList ?? 'all';

    $pageTitle = $pageTitle ?? 'Suivi des pièces des véhicules';

    $pageDescription = $pageDescription
        ?? 'Recherche, commande et réception des pièces';

    /*
    |--------------------------------------------------------------------------
    | ROUTE COURANTE POUR FILTRES / RESET
    |--------------------------------------------------------------------------
    |
    | Important :
    | - Toutes les pièces      -> route courante
    | - Pièces commandées      -> route courante
    | - Pièces reçues          -> route courante
    | - Pièces non trouvées    -> route courante
    |
    */

    $currentUrl = url()->current();
@endphp

<style>
    .vpr-index-page {
        width: 100%;
        padding: 22px 18px 45px;
    }

    .vpr-index-inner {
        width: 100%;
        max-width: 1600px;
        margin: 0 auto;
    }

    .vpr-index-card {
        overflow: hidden;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .08);
    }

    .vpr-index-header {
        padding: 24px 28px;
        background: #fff;
        border-bottom: 1px solid #edf0f4;
    }

    .vpr-index-title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .vpr-index-title-row h3 {
        margin: 0 0 5px;
        font-size: 26px;
        font-weight: 800;
        color: #334155;
    }

    .vpr-index-title-row p {
        margin: 0;
        color: #94a3b8;
    }

    .vpr-new-button {
        min-height: 44px;
        padding: 9px 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 9px;
        font-weight: 700;
    }

    .vpr-index-body {
        padding: 22px 28px 28px;
    }

    .vpr-filter-grid {
        display: grid;
        grid-template-columns:
            minmax(240px, 1.4fr)
            minmax(220px, 1fr)
            minmax(180px, .8fr)
            minmax(300px, 1fr);
        gap: 14px;
        align-items: end;
        margin-bottom: 22px;
    }

    .vpr-filter-grid .form-label {
        margin-bottom: 7px;
        font-size: 11px;
        font-weight: 800;
        color: #52657b;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .vpr-filter-grid .form-control,
    .vpr-filter-grid .form-select {
        min-height: 46px;
        border-radius: 9px;
        border-color: #d8dee8;
        box-shadow: none;
    }

    .vpr-filter-grid .form-control:focus,
    .vpr-filter-grid .form-select:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 3px rgba(105, 108, 255, .10);
    }

    .vpr-filter-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .vpr-filter-actions .btn {
        min-height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 9px;
        font-weight: 700;
    }

    .vpr-table-wrapper {
        width: 100%;
        overflow-x: auto;
        border: 1px solid #edf0f4;
        border-radius: 11px;
    }

    .vpr-table {
        width: 100%;
        min-width: 1250px;
        margin: 0;
    }

    .vpr-table thead th {
        padding: 13px 14px;
        white-space: nowrap;
        vertical-align: middle;
        background: #e8edf3;
        color: #52657b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .vpr-table tbody td {
        padding: 14px;
        vertical-align: middle;
        color: #52657b;
        font-size: 13px;
    }

    .vpr-table tbody tr:hover {
        background: #f8fafc;
    }

    .vehicle-number {
        font-weight: 800;
        color: #5b6df8;
    }

    .part-name {
        font-weight: 800;
        color: #475569;
    }

    .quantity-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 86px;
        padding: 5px 9px;
        border-radius: 6px;
        color: #475569;
        background: #f1f5f9;
        font-weight: 700;
        white-space: nowrap;
    }

    .date-cell {
        white-space: nowrap;
    }

    .vpr-status-badge {
        min-width: 92px;
        padding: 6px 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .vpr-actions {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 7px;
        white-space: nowrap;
    }

    .vpr-actions .btn {
        min-height: 34px;
        padding: 6px 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        border-radius: 7px;
        font-weight: 700;
    }

    .vpr-actions form {
        margin: 0;
    }

    .vpr-pagination {
        display: flex;
        justify-content: center;
        margin-top: 22px;
    }

    .vpr-pagination svg,
    .vpr-pagination nav svg {
        width: 18px !important;
        height: 18px !important;
        max-width: 18px !important;
        max-height: 18px !important;
    }

    @media (max-width: 1199.98px) {
        .vpr-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .vpr-index-page {
            padding: 14px 10px 32px;
        }

        .vpr-index-header,
        .vpr-index-body {
            padding: 16px;
        }

        .vpr-index-title-row {
            align-items: stretch;
            flex-direction: column;
        }

        .vpr-new-button {
            width: 100%;
        }

        .vpr-filter-grid {
            grid-template-columns: 1fr;
        }

        .vpr-filter-actions {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="vpr-index-page">
    <div class="vpr-index-inner">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Fermer"
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
                    aria-label="Fermer"
                ></button>
            </div>
        @endif

        <div class="vpr-index-card">

            <div class="vpr-index-header">
                <div class="vpr-index-title-row">

                    <div>
                        <h3>
                            {{ $pageTitle }}
                        </h3>

                        <p>
                            {{ $pageDescription }}
                        </p>
                    </div>

                    <a
                        href="{{ route('vehicle-part-requests.create') }}"
                        class="btn btn-primary vpr-new-button"
                    >
                        <i class="bx bx-plus"></i>
                        Nouvelle commande
                    </a>

                </div>
            </div>

            <div class="vpr-index-body">

                {{-- ====================================================
                    FILTRES
                ==================================================== --}}

                <form
                    method="GET"
                    action="{{ $currentUrl }}"
                    class="vpr-filter-grid"
                >

                    <div>
                        <label for="search" class="form-label">
                            Recherche
                        </label>

                        <input
                            type="text"
                            name="search"
                            id="search"
                            value="{{ request('search') }}"
                            class="form-control"
                            placeholder="Pièce, référence, VIN, immatriculation..."
                        >
                    </div>

                    <div>
                        <label for="vehicle_id" class="form-label">
                            Véhicule
                        </label>

                        <select
                            name="vehicle_id"
                            id="vehicle_id"
                            class="form-select"
                        >
                            <option value="">
                                Tous les véhicules
                            </option>

                            @foreach($vehicles as $vehicle)
                                <option
                                    value="{{ $vehicle->id }}"
                                    @selected(
                                        (string) request('vehicle_id')
                                        ===
                                        (string) $vehicle->id
                                    )
                                >
                                    {{ $vehicle->plate_number ?? $vehicle->vin ?? '-' }}
                                    -
                                    {{ $vehicle->brand ?? '' }}
                                    {{ $vehicle->model ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status" class="form-label">
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

                            @foreach($statuses as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(
                                        request('status') === $value
                                    )
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="vpr-filter-actions">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="bx bx-search"></i>
                            Rechercher
                        </button>

                        <a
                            href="{{ $currentUrl }}"
                            class="btn btn-secondary"
                        >
                            <i class="bx bx-reset"></i>
                            Réinitialiser
                        </a>

                    </div>
                </form>

                {{-- ====================================================
                    TABLEAU
                ==================================================== --}}

                <div class="vpr-table-wrapper">
                    <table class="table table-hover align-middle vpr-table">

                        <thead>
                            <tr>
                                <th>Véhicule</th>
                                <th>Pièce</th>
                                <th>Quantité</th>
                                <th>Demande</th>
                                <th>Commande</th>

                                @if($currentList === 'received')
                                    <th>Réception</th>
                                @endif

                                <th>Statut</th>
                                <th class="text-center">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($partRequests as $partRequest)

                                <tr>

                                    {{-- VÉHICULE --}}
                                    <td>
                                        @if($partRequest->vehicle)

                                            <div class="vehicle-number">
                                                {{ $partRequest->vehicle->plate_number ?? $partRequest->vehicle->vin ?? '-' }}
                                            </div>

                                            @if($partRequest->vehicle->customer)
                                                <small class="text-muted d-block">
                                                    Client :
                                                    {{ $partRequest->vehicle->customer->name }}
                                                </small>
                                            @endif

                                            @if(
                                                $partRequest->vehicle->brand
                                                ||
                                                $partRequest->vehicle->model
                                            )
                                                <small class="text-muted d-block">
                                                    {{ $partRequest->vehicle->brand ?? '' }}
                                                    {{ $partRequest->vehicle->model ?? '' }}
                                                </small>
                                            @endif

                                        @else

                                            <span class="text-danger">
                                                Véhicule supprimé
                                            </span>

                                        @endif
                                    </td>

                                    {{-- PIÈCE --}}
                                    <td>
                                        <div class="part-name">
                                            {{ $partRequest->part_name }}
                                        </div>

                                        @if($partRequest->reference)
                                            <small class="text-muted d-block">
                                                Réf. :
                                                {{ $partRequest->reference }}
                                            </small>
                                        @endif

                                        @if($partRequest->product)
                                            <small class="text-success d-block">
                                                <i class="bx bx-link me-1"></i>
                                                Produit catalogue lié
                                            </small>
                                        @endif
                                    </td>

                                    {{-- QUANTITÉ --}}
                                    <td>
                                        <span class="quantity-pill">
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

                                    {{-- DATE DEMANDE --}}
                                    <td class="date-cell">
                                        @if($partRequest->requested_at)
                                            {{ $partRequest->requested_at->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    {{-- DATE COMMANDE --}}
                                    <td class="date-cell">
                                        @if($partRequest->ordered_at)
                                            {{ $partRequest->ordered_at->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">
                                                Non commandée
                                            </span>
                                        @endif
                                    </td>

                                    {{-- DATE RÉCEPTION --}}
                                    @if($currentList === 'received')
                                        <td class="date-cell">
                                            @if($partRequest->received_at)
                                                {{ $partRequest->received_at->format('d/m/Y H:i') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endif

                                    {{-- STATUT --}}
                                    <td>
                                        <span
                                            class="
                                                badge
                                                bg-{{ $partRequest->status_badge }}
                                                vpr-status-badge
                                            "
                                        >
                                            {{ $partRequest->status_label }}
                                        </span>
                                    </td>

                                    {{-- ACTIONS --}}
                                    <td class="text-center">
                                        <div class="vpr-actions">

                                            <a
                                                href="{{
                                                    route(
                                                        'vehicle-part-requests.show',
                                                        $partRequest
                                                    )
                                                }}"
                                                class="btn btn-info btn-sm text-white"
                                                title="Voir"
                                            >
                                                <i class="bx bx-show"></i>
                                                Voir
                                            </a>

                                            @if(
                                                in_array(
                                                    auth()->user()->role,
                                                    [
                                                        'admin',
                                                        'chef_magasinier',
                                                        'magasinier'
                                                    ],
                                                    true
                                                )
                                            )
                                                <a
                                                    href="{{
                                                        route(
                                                            'vehicle-part-requests.edit',
                                                            $partRequest
                                                        )
                                                    }}"
                                                    class="btn btn-warning btn-sm"
                                                    title="Modifier"
                                                >
                                                    <i class="bx bx-edit"></i>
                                                    Modifier
                                                </a>
                                            @endif

                                            @if(auth()->user()->role === 'admin')
                                                <form
                                                    method="POST"
                                                    action="{{
                                                        route(
                                                            'vehicle-part-requests.destroy',
                                                            $partRequest
                                                        )
                                                    }}"
                                                    class="delete-part-request-form"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="btn btn-danger btn-sm"
                                                        title="Supprimer"
                                                    >
                                                        <i class="bx bx-trash"></i>
                                                        Supprimer
                                                    </button>
                                                </form>
                                            @endif

                                        </div>
                                    </td>
                                </tr>

                            @empty

                                <tr>
                                    <td
                                        colspan="{{ $currentList === 'received' ? 8 : 7 }}"
                                        class="text-center py-5"
                                    >
                                        <i
                                            class="
                                                bx
                                                bx-package
                                                fs-1
                                                text-muted
                                                d-block
                                                mb-2
                                            "
                                        ></i>

                                        <p class="text-muted mb-0">
                                            Aucune pièce trouvée.
                                        </p>
                                    </td>
                                </tr>

                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ====================================================
                    PAGINATION
                ==================================================== --}}

                @if(
                    method_exists($partRequests, 'links')
                    &&
                    $partRequests->hasPages()
                )
                    <div class="vpr-pagination">
                        {{
                            $partRequests
                                ->appends(request()->query())
                                ->links()
                        }}
                    </div>
                @endif

            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document
        .querySelectorAll('.delete-part-request-form')
        .forEach(function (form) {

            form.addEventListener(
                'submit',
                function (event) {

                    event.preventDefault();

                    if (typeof Swal === 'undefined') {
                        if (
                            window.confirm(
                                'Voulez-vous supprimer cette demande ?'
                            )
                        ) {
                            form.submit();
                        }

                        return;
                    }

                    Swal.fire({
                        title: 'Supprimer la demande ?',
                        text: 'Cette action est irréversible.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Oui, supprimer',
                        cancelButtonText: 'Annuler'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                }
            );
        });
});
</script>

@endsection