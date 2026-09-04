@extends('layouts.layoutMaster')

@section('title', 'Modifier un ajustement inventaire')

@section('content')

@php
    $product = $inventoryAdjustment->product;

    $reference = $product?->reference ?? '-';
    $designation = $product?->designation ?? 'Produit supprimé';

    $depotName = $inventoryAdjustment->depot?->name ?? 'Non renseigné';
    $currentRayonId = old('rayon_id', $inventoryAdjustment->rayon_id);
    $currentLocationId = old('location_id', $inventoryAdjustment->location_id);

    $oldQty = (float) ($inventoryAdjustment->old_qty ?? 0);
    $newQty = old('new_qty', $inventoryAdjustment->new_qty ?? 0);

    $unit = $product?->unit_label
        ?? $product?->unit_type
        ?? 'Pièce';
@endphp

<div class="container-fluid">

    {{-- ============================================================
        HEADER
    ============================================================ --}}
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="bx bx-edit me-2"></i>
                Modifier l'ajustement #{{ $inventoryAdjustment->id }}
            </h4>

            <div class="text-muted">
                Correction d'un ajustement d'inventaire existant
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a
                href="{{ route('inventory-adjustments.show', $inventoryAdjustment->id) }}"
                class="btn btn-info"
            >
                <i class="bx bx-show me-1"></i>
                Voir
            </a>

            <a
                href="{{ route('inventory-adjustments.index') }}"
                class="btn btn-secondary"
            >
                <i class="bx bx-arrow-back me-1"></i>
                Retour
            </a>
        </div>
    </div>

    {{-- ============================================================
        MESSAGES
    ============================================================ --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
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

    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-2">
                <i class="bx bx-error-circle me-1"></i>
                Veuillez corriger les erreurs suivantes :
            </div>

            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ============================================================
        AVERTISSEMENT
    ============================================================ --}}
    <div class="alert alert-warning d-flex align-items-start gap-2">
        <i class="bx bx-info-circle fs-4 mt-1"></i>

        <div>
            <div class="fw-bold mb-1">
                Modification d'un ajustement existant
            </div>

            <div>
                La quantité avant ajustement reste une donnée historique.
                Vous pouvez corriger la quantité après ajustement, la raison,
                le rayon et l'emplacement.
            </div>
        </div>
    </div>

    {{-- ============================================================
        FORMULAIRE
    ============================================================ --}}
    <form
        method="POST"
        action="{{ route('inventory-adjustments.update', $inventoryAdjustment->id) }}"
        id="inventory-adjustment-edit-form"
    >
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- ====================================================
                INFORMATIONS PRODUIT
            ==================================================== --}}
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-package me-2"></i>
                            Produit
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">
                                    Référence
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="{{ $reference }}"
                                    readonly
                                >
                            </div>

                            <div class="col-12 col-md-8">
                                <label class="form-label fw-semibold">
                                    Désignation
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="{{ $designation }}"
                                    readonly
                                >
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">
                                    Dépôt
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="{{ $depotName }}"
                                    readonly
                                >
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">
                                    Marque
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="{{ $product?->brand?->name ?? '-' }}"
                                    readonly
                                >
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">
                                    Modèle
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="{{ $product?->model?->name ?? '-' }}"
                                    readonly
                                >
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- ====================================================
                QUANTITES
            ==================================================== --}}
            <div class="col-12 col-xl-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-calculator me-2"></i>
                            Quantités
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-12 col-md-6">
                                <label
                                    for="old_qty"
                                    class="form-label fw-semibold"
                                >
                                    Quantité avant
                                </label>

                                <div class="input-group">
                                    <input
                                        type="number"
                                        id="old_qty"
                                        class="form-control"
                                        value="{{ number_format($oldQty, 2, '.', '') }}"
                                        step="0.01"
                                        readonly
                                    >

                                    <span class="input-group-text">
                                        {{ $unit }}
                                    </span>
                                </div>

                                <div class="form-text">
                                    Valeur historique non modifiable.
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label
                                    for="new_qty"
                                    class="form-label fw-semibold"
                                >
                                    Nouvelle quantité
                                    <span class="text-danger">*</span>
                                </label>

                                <div class="input-group">
                                    <input
                                        type="number"
                                        id="new_qty"
                                        name="new_qty"
                                        class="form-control @error('new_qty') is-invalid @enderror"
                                        value="{{ $newQty }}"
                                        min="0"
                                        step="0.01"
                                        required
                                    >

                                    <span class="input-group-text">
                                        {{ $unit }}
                                    </span>

                                    @error('new_qty')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-3 rounded bg-light border">
                                    <div class="d-flex justify-content-between gap-3">
                                        <span class="fw-semibold">
                                            Nouvelle différence :
                                        </span>

                                        <span
                                            id="difference-preview"
                                            class="fw-bold"
                                        >
                                            -
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- ====================================================
                LOCALISATION
            ==================================================== --}}
            <div class="col-12 col-xl-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-map me-2"></i>
                            Localisation
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-12">
                                <label
                                    for="rayon_id"
                                    class="form-label fw-semibold"
                                >
                                    Rayon
                                </label>

                                <select
                                    id="rayon_id"
                                    name="rayon_id"
                                    class="form-select @error('rayon_id') is-invalid @enderror"
                                >
                                    <option value="">
                                        -- Aucun rayon --
                                    </option>

                                    @foreach($rayons as $rayon)
                                        <option
                                            value="{{ $rayon->id }}"
                                            @selected((string) $currentRayonId === (string) $rayon->id)
                                        >
                                            {{ $rayon->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('rayon_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label
                                    for="location_id"
                                    class="form-label fw-semibold"
                                >
                                    Emplacement
                                </label>

                                <select
                                    id="location_id"
                                    name="location_id"
                                    class="form-select @error('location_id') is-invalid @enderror"
                                >
                                    <option value="">
                                        -- Aucun emplacement --
                                    </option>

                                    @foreach($locations as $location)
                                        <option
                                            value="{{ $location->id }}"
                                            data-rayon-id="{{ $location->rayon_id }}"
                                            @selected((string) $currentLocationId === (string) $location->id)
                                        >
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('location_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                                <div class="form-text">
                                    Les emplacements sont filtrés selon le rayon sélectionné.
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- ====================================================
                RAISON
            ==================================================== --}}
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-message-square-detail me-2"></i>
                            Justification
                        </h5>
                    </div>

                    <div class="card-body">

                        <label
                            for="reason"
                            class="form-label fw-semibold"
                        >
                            Raison de l'ajustement
                            <span class="text-danger">*</span>
                        </label>

                        <textarea
                            id="reason"
                            name="reason"
                            rows="4"
                            maxlength="1000"
                            class="form-control @error('reason') is-invalid @enderror"
                            required
                        >{{ old('reason', $inventoryAdjustment->reason) }}</textarea>

                        @error('reason')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text">
                            Indiquez clairement pourquoi cet ajustement est corrigé.
                        </div>

                    </div>
                </div>
            </div>

            {{-- ====================================================
                INFORMATIONS HISTORIQUES
            ==================================================== --}}
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-history me-2"></i>
                            Informations historiques
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-12 col-md-4">
                                <div class="text-muted small">
                                    Effectué par
                                </div>

                                <div class="fw-semibold">
                                    {{ $inventoryAdjustment->approver?->name ?? '-' }}
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="text-muted small">
                                    Date de création
                                </div>

                                <div class="fw-semibold">
                                    {{ optional($inventoryAdjustment->created_at)->format('d/m/Y H:i') ?? '-' }}
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="text-muted small">
                                    Dernière modification
                                </div>

                                <div class="fw-semibold">
                                    {{ optional($inventoryAdjustment->updated_at)->format('d/m/Y H:i') ?? '-' }}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- ====================================================
                ACTIONS
            ==================================================== --}}
            <div class="col-12">
                <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">

                    <a
                        href="{{ route('inventory-adjustments.index') }}"
                        class="btn btn-secondary"
                    >
                        <i class="bx bx-x me-1"></i>
                        Annuler
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="bx bx-save me-1"></i>
                        Enregistrer les modifications
                    </button>

                </div>
            </div>

        </div>
    </form>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const oldQtyInput = document.getElementById('old_qty');
    const newQtyInput = document.getElementById('new_qty');
    const differencePreview = document.getElementById('difference-preview');

    const rayonSelect = document.getElementById('rayon_id');
    const locationSelect = document.getElementById('location_id');

    function updateDifference() {
        if (!oldQtyInput || !newQtyInput || !differencePreview) {
            return;
        }

        const oldQty = parseFloat(oldQtyInput.value || '0');
        const newQty = parseFloat(newQtyInput.value || '0');
        const difference = newQty - oldQty;

        if (Number.isNaN(difference)) {
            differencePreview.textContent = '-';
            differencePreview.className = 'fw-bold';
            return;
        }

        const formatted = difference.toLocaleString('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        if (difference > 0) {
            differencePreview.textContent = '+' + formatted;
            differencePreview.className = 'fw-bold text-success';
        } else if (difference < 0) {
            differencePreview.textContent = formatted;
            differencePreview.className = 'fw-bold text-danger';
        } else {
            differencePreview.textContent = '0,00';
            differencePreview.className = 'fw-bold text-secondary';
        }
    }

    function filterLocations() {
        if (!rayonSelect || !locationSelect) {
            return;
        }

        const selectedRayonId = rayonSelect.value;
        const currentValue = locationSelect.value;

        Array.from(locationSelect.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            const optionRayonId = option.dataset.rayonId || '';

            option.hidden =
                selectedRayonId !== ''
                && optionRayonId !== selectedRayonId;
        });

        const selectedOption =
            locationSelect.options[locationSelect.selectedIndex];

        if (
            selectedOption
            && selectedOption.value
            && selectedOption.hidden
        ) {
            locationSelect.value = '';
        }

        if (currentValue && locationSelect.value === '') {
            const oldOption = Array.from(locationSelect.options).find(
                option => option.value === currentValue
            );

            if (
                oldOption
                && (
                    selectedRayonId === ''
                    || oldOption.dataset.rayonId === selectedRayonId
                )
            ) {
                locationSelect.value = currentValue;
            }
        }
    }

    if (newQtyInput) {
        newQtyInput.addEventListener('input', updateDifference);
    }

    if (rayonSelect) {
        rayonSelect.addEventListener('change', filterLocations);
    }

    updateDifference();
    filterLocations();
});
</script>
@endpush
