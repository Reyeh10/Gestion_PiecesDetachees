@php
    $isEdit = isset($product) && $product && $product->exists;

    $currentStatus = old(
        'status',
        $product->status ?? 'disponible'
    );

    $currentUnitType = old(
        'unit_type',
        $product->unit_type ?? 'piece'
    );

    $currentUnitLabel = old(
        'unit_label',
        $product->unit_label ?? 'Pièce'
    );
@endphp


@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">

        <strong>Veuillez corriger les erreurs suivantes :</strong>

        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Fermer"
        ></button>

    </div>
@endif


<style>
    .product-form-section {
        margin-bottom: 24px;
        padding: 20px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
    }

    .product-form-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 18px;
        font-size: 15px;
        font-weight: 800;
        color: #334155;
    }

    .product-form-section-title i {
        font-size: 20px;
        color: #696cff;
    }

    .product-form-help {
        margin-top: 6px;
        font-size: 12px;
        color: #64748b;
    }

    .stock-mode-card {
        height: 100%;
        padding: 14px 16px;
        border: 1px solid #d8dee8;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        transition: .2s ease;
    }

    .stock-mode-card:hover {
        border-color: #a5b4fc;
        background: #fafaff;
    }

    .stock-mode-card.active {
        border-color: #696cff;
        box-shadow: 0 0 0 3px rgba(105, 108, 255, .10);
        background: #f8f8ff;
    }

    .stock-mode-title {
        font-weight: 800;
        color: #334155;
    }

    .stock-mode-description {
        margin-top: 4px;
        font-size: 12px;
        color: #64748b;
    }

    .stock-preview {
        padding: 15px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
    }

    .stock-preview-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .stock-preview-item {
        padding: 11px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    .stock-preview-label {
        display: block;
        margin-bottom: 4px;
        font-size: 11px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
    }

    .stock-preview-value {
        font-size: 15px;
        font-weight: 800;
        color: #1f2937;
    }

    .form-control[readonly],
    .form-control:disabled,
    .form-select:disabled {
        background-color: #f8fafc;
    }

    /*
    |--------------------------------------------------------------------------
    | BOUTONS + RÉFÉRENTIELS
    |--------------------------------------------------------------------------
    */

    .reference-select-group {
        display: flex;
        align-items: stretch;
        gap: 8px;
    }

    .reference-select-group .reference-select-wrapper {
        flex: 1 1 auto;
        min-width: 0;
    }

    .reference-select-group .select2-container {
        width: 100% !important;
    }

    .reference-add-btn {
        width: 42px;
        min-width: 42px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 8px;
    }

    .reference-add-btn i {
        font-size: 20px;
    }

    .reference-modal .modal-header {
        border-bottom: 1px solid #e5e7eb;
    }

    .reference-modal .modal-footer {
        border-top: 1px solid #e5e7eb;
    }

    .reference-modal-help {
        padding: 10px 12px;
        margin-bottom: 16px;
        border-radius: 8px;
        background: #f8fafc;
        color: #64748b;
        font-size: 12px;
    }

    .ajax-field-error {
        margin-top: 5px;
        font-size: 12px;
        color: #dc3545;
    }

    @media (max-width: 767.98px) {
        .stock-preview-grid {
            grid-template-columns: 1fr;
        }
    }
</style>


{{-- ============================================================
    INFORMATIONS GÉNÉRALES
============================================================ --}}
<div class="product-form-section">

    <div class="product-form-section-title">
        <i class="bx bx-package"></i>
        Informations générales
    </div>

    <div class="row">

        {{-- RÉFÉRENCE --}}
        <div class="col-md-4 mb-3">

            <label for="reference" class="form-label">
                Référence <span class="text-danger">*</span>
            </label>

            <input
                type="text"
                name="reference"
                id="reference"
                class="form-control @error('reference') is-invalid @enderror"
                value="{{ old('reference', $product->reference ?? '') }}"
                required
            >

            @error('reference')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror

        </div>


        {{-- DÉSIGNATION --}}
        <div class="col-md-8 mb-3">

            <label for="designation" class="form-label">
                Désignation <span class="text-danger">*</span>
            </label>

            <input
                type="text"
                name="designation"
                id="designation"
                class="form-control @error('designation') is-invalid @enderror"
                value="{{ old('designation', $product->designation ?? '') }}"
                required
            >

            @error('designation')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror

        </div>


        {{-- TYPE UNITÉ --}}
        <div class="col-md-3 mb-3">

            <label for="unit_type" class="form-label">
                Type unité
            </label>

            <select
                name="unit_type"
                id="unit_type"
                class="form-control select2 @error('unit_type') is-invalid @enderror"
            >
                <option
                    value="piece"
                    {{ $currentUnitType === 'piece' ? 'selected' : '' }}
                >
                    Pièce
                </option>

                <option
                    value="litre"
                    {{ $currentUnitType === 'litre' ? 'selected' : '' }}
                >
                    Litre
                </option>
            </select>

            @error('unit_type')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror

        </div>


        {{-- LIBELLÉ UNITÉ --}}
        <div class="col-md-3 mb-3">

            <label for="unit_label" class="form-label">
                Libellé unité
            </label>

            <input
                type="text"
                name="unit_label"
                id="unit_label"
                class="form-control @error('unit_label') is-invalid @enderror"
                value="{{ $currentUnitLabel }}"
                maxlength="50"
            >

            @error('unit_label')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror

        </div>


        {{-- MARQUE --}}
        <div class="col-md-3 mb-3">

            <label for="brand_id" class="form-label">
                Marque <span class="text-danger">*</span>
            </label>

            <div class="reference-select-group">

                <div class="reference-select-wrapper">
                    <select
                        name="brand_id"
                        id="brand_id"
                        class="form-control select2 @error('brand_id') is-invalid @enderror"
                        required
                    >
                        <option value="">Sélectionner</option>

                        @foreach($brands as $brand)
                            <option
                                value="{{ $brand->id }}"
                                {{ (string) old('brand_id', $product->brand_id ?? '') === (string) $brand->id ? 'selected' : '' }}
                            >
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="button"
                    class="btn btn-primary reference-add-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#brandModal"
                    title="Ajouter une marque"
                >
                    <i class="bx bx-plus"></i>
                </button>

            </div>

            @error('brand_id')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
            @enderror

        </div>


        {{-- MODÈLE --}}
        <div class="col-md-3 mb-3">

            <label for="model_id" class="form-label">
                Modèle <span class="text-danger">*</span>
            </label>

            <div class="reference-select-group">

                <div class="reference-select-wrapper">
                    <select
                        name="model_id"
                        id="model_id"
                        class="form-control select2 @error('model_id') is-invalid @enderror"
                        required
                    >
                        <option value="">Sélectionner</option>

                        @foreach($models as $model)
                            <option
                                value="{{ $model->id }}"
                                data-brand="{{ $model->brand_id }}"
                                {{ (string) old('model_id', $product->model_id ?? '') === (string) $model->id ? 'selected' : '' }}
                            >
                                {{ $model->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="button"
                    class="btn btn-primary reference-add-btn"
                    id="openModelModalButton"
                    title="Ajouter un modèle"
                >
                    <i class="bx bx-plus"></i>
                </button>

            </div>

            @error('model_id')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
            @enderror

        </div>


        {{-- FAMILLE --}}
        <div class="col-md-4 mb-3">

            <label for="family_id" class="form-label">
                Famille
            </label>

            <select
                name="family_id"
                id="family_id"
                class="form-control select2 @error('family_id') is-invalid @enderror"
            >
                <option value="">Sélectionner</option>

                @foreach($families as $family)
                    <option
                        value="{{ $family->id }}"
                        {{ (string) old('family_id', $product->family_id ?? '') === (string) $family->id ? 'selected' : '' }}
                    >
                        {{ $family->name }}
                    </option>
                @endforeach
            </select>

        </div>


        {{-- SOUS-FAMILLE --}}
        <div class="col-md-4 mb-3">

            <label for="subfamily_id" class="form-label">
                Sous-famille
            </label>

            <select
                name="subfamily_id"
                id="subfamily_id"
                class="form-control select2 @error('subfamily_id') is-invalid @enderror"
            >
                <option value="">Sélectionner</option>

                @foreach($subfamilies as $sub)
                    <option
                        value="{{ $sub->id }}"
                        data-family="{{ $sub->family_id }}"
                        {{ (string) old('subfamily_id', $product->subfamily_id ?? '') === (string) $sub->id ? 'selected' : '' }}
                    >
                        {{ $sub->name }}
                    </option>
                @endforeach
            </select>

        </div>


        {{-- RAYON --}}
        <div class="col-md-4 mb-3">

            <label for="rayon_id" class="form-label">
                Rayon
            </label>

            <div class="reference-select-group">

                <div class="reference-select-wrapper">
                    <select
                        name="rayon_id"
                        id="rayon_id"
                        class="form-control select2 @error('rayon_id') is-invalid @enderror"
                    >
                        <option value="">Sélectionner</option>

                        @foreach($rayons as $rayon)
                            <option
                                value="{{ $rayon->id }}"
                                {{ (string) old('rayon_id', $product->rayon_id ?? '') === (string) $rayon->id ? 'selected' : '' }}
                            >
                                {{ $rayon->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="button"
                    class="btn btn-primary reference-add-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#rayonModal"
                    title="Ajouter un rayon"
                >
                    <i class="bx bx-plus"></i>
                </button>

            </div>

        </div>


        {{-- EMPLACEMENT --}}
        <div class="col-md-4 mb-3">

            <label for="location_id" class="form-label">
                Emplacement
            </label>

            <div class="reference-select-group">

                <div class="reference-select-wrapper">
                    <select
                        name="location_id"
                        id="location_id"
                        class="form-control select2 @error('location_id') is-invalid @enderror"
                    >
                        <option value="">Sélectionner</option>

                        @foreach($locations as $location)
                            <option
                                value="{{ $location->id }}"
                                data-rayon="{{ $location->rayon_id }}"
                                {{ (string) old('location_id', $product->location_id ?? '') === (string) $location->id ? 'selected' : '' }}
                            >
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="button"
                    class="btn btn-primary reference-add-btn"
                    id="openLocationModalButton"
                    title="Ajouter un emplacement"
                >
                    <i class="bx bx-plus"></i>
                </button>

            </div>

        </div>

    </div>

</div>


{{-- ============================================================
    GESTION DU STOCK
============================================================ --}}
<div class="product-form-section">

    <div class="product-form-section-title">
        <i class="bx bx-cube"></i>
        Gestion du stock
    </div>

    @if(!$isEdit)

        <div class="row mb-3">

            <div class="col-lg-6 mb-3 mb-lg-0">

                <label
                    class="stock-mode-card {{ old('stock_state', 'available') === 'available' ? 'active' : '' }}"
                    for="stock_state_available"
                >

                    <div class="d-flex gap-3 align-items-start">

                        <input
                            type="radio"
                            name="stock_state"
                            id="stock_state_available"
                            value="available"
                            class="form-check-input mt-1"
                            {{ old('stock_state', 'available') === 'available' ? 'checked' : '' }}
                        >

                        <div>
                            <div class="stock-mode-title">
                                Produit déjà disponible
                            </div>

                            <div class="stock-mode-description">
                                La quantité saisie est déjà physiquement reçue et disponible en stock.
                            </div>
                        </div>

                    </div>

                </label>

            </div>


            <div class="col-lg-6">

                <label
                    class="stock-mode-card {{ old('stock_state') === 'unavailable' ? 'active' : '' }}"
                    for="stock_state_unavailable"
                >

                    <div class="d-flex gap-3 align-items-start">

                        <input
                            type="radio"
                            name="stock_state"
                            id="stock_state_unavailable"
                            value="unavailable"
                            class="form-check-input mt-1"
                            {{ old('stock_state') === 'unavailable' ? 'checked' : '' }}
                        >

                        <div>
                            <div class="stock-mode-title">
                                Produit non disponible / à commander
                            </div>

                            <div class="stock-mode-description">
                                La quantité saisie devient la quantité initiale prévue. La quantité reçue et disponible restent à zéro.
                            </div>
                        </div>

                    </div>

                </label>

            </div>

        </div>

    @else

        <div class="alert alert-info">
            <i class="bx bx-info-circle me-1"></i>
            Le stock n'est pas modifiable depuis la fiche produit.
            Utilisez les réceptions, les ajustements d'inventaire ou les mouvements de stock.
        </div>

    @endif


    <div class="row">

        {{-- QUANTITÉ --}}
        <div class="col-md-4 mb-3">

            <label for="quantity" class="form-label">
                {{ $isEdit ? 'Quantité disponible actuelle' : 'Quantité initiale' }}

                @if(!$isEdit)
                    <span class="text-danger">*</span>
                @endif
            </label>

            <input
                type="number"
                step="0.01"
                min="0"
                name="quantity"
                id="quantity"
                class="form-control @error('quantity') is-invalid @enderror"
                value="{{ old('quantity', $product->quantity ?? 0) }}"
                {{ $isEdit ? 'readonly' : 'required' }}
            >

            @if(!$isEdit)
                <div class="product-form-help" id="quantity_help">
                    Si le produit est disponible, cette quantité sera immédiatement disponible.
                </div>
            @else
                <div class="product-form-help">
                    Quantité réellement disponible actuellement.
                </div>
            @endif

            @error('quantity')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror

        </div>


        {{-- QUANTITÉ REÇUE --}}
        <div class="col-md-4 mb-3">

            <label class="form-label">
                Quantité reçue
            </label>

            <input
                type="number"
                step="0.01"
                class="form-control"
                id="received_quantity_preview"
                value="{{ $isEdit ? (float) ($product->received_quantity ?? 0) : 0 }}"
                readonly
            >

            <div class="product-form-help">
                Mise à jour automatiquement lors des réceptions.
            </div>

        </div>


        {{-- QUANTITÉ NON DISPONIBLE --}}
        <div class="col-md-4 mb-3">

            <label class="form-label">
                Quantité non disponible
            </label>

            <input
                type="number"
                step="0.01"
                class="form-control"
                id="unavailable_quantity_preview"
                value="{{ $isEdit
                ? max(
                    0,
                    (float) ($product->initial_quantity ?? 0)
                    - (float) ($product->received_quantity ?? 0)
                )
                : 0
            }}"
                readonly
            >

            <div class="product-form-help">
                Quantité initiale moins quantité reçue.
            </div>

        </div>


        {{-- SEUIL MINIMUM --}}
        <div class="col-md-4 mb-3">

            <label for="min_stock" class="form-label">
                Seuil minimum
            </label>

            <input
                type="number"
                step="0.01"
                min="0"
                name="min_stock"
                id="min_stock"
                class="form-control @error('min_stock') is-invalid @enderror"
                value="{{ old('min_stock', $product->min_stock ?? 0) }}"
            >

        </div>


        {{-- SEUIL MAXIMUM --}}
        <div class="col-md-4 mb-3">

            <label for="max_stock" class="form-label">
                Seuil maximum
            </label>

            <input
                type="number"
                step="0.01"
                min="0"
                name="max_stock"
                id="max_stock"
                class="form-control @error('max_stock') is-invalid @enderror"
                value="{{ old('max_stock', $product->max_stock ?? 0) }}"
            >

        </div>


       @if($isEdit)

        @php

            $editInitialQty =
                (float) ($product->initial_quantity ?? 0);

            $editReceivedQty =
                (float) ($product->received_quantity ?? 0);

            $editAvailableQty =
                (float) ($product->quantity ?? 0);

            $editSupplyStatus =
                $product->supply_status ?? null;


            if ($product->status === 'vendu') {

                $editDisplayStatus =
                    'Vendu';

            } elseif ($editSupplyStatus === 'en_recherche') {

                $editDisplayStatus =
                    'En recherche';

            } elseif ($editSupplyStatus === 'en_commande') {

                $editDisplayStatus =
                    'En commande';

            } elseif (
                $editReceivedQty > 0
                &&
                $editReceivedQty < $editInitialQty
            ) {

                $editDisplayStatus =
                    'Partiellement reçu';

            } elseif (
                $editReceivedQty <= 0
                ||
                $editAvailableQty <= 0
            ) {

                $editDisplayStatus =
                    'Rupture';

            } elseif (
                $editInitialQty > 0
                &&
                $editReceivedQty >= $editInitialQty
            ) {

                $editDisplayStatus =
                    'Reçu';

            } else {

                $editDisplayStatus =
                    'Disponible';
            }

        @endphp


    <div class="col-md-4 mb-3">

        <label class="form-label">
            Statut actuel
        </label>

        <input
            type="text"
            class="form-control"
            value="{{ $editDisplayStatus }}"
            readonly
        >

    </div>

@endif

    </div>


    @if(!$isEdit)

        <div class="stock-preview">

            <div class="stock-preview-grid">

                <div class="stock-preview-item">
                    <span class="stock-preview-label">
                        Quantité initiale
                    </span>

                    <span
                        class="stock-preview-value"
                        id="preview_initial"
                    >
                        0
                    </span>
                </div>


                <div class="stock-preview-item">
                    <span class="stock-preview-label">
                        Quantité reçue
                    </span>

                    <span
                        class="stock-preview-value"
                        id="preview_received"
                    >
                        0
                    </span>
                </div>


                <div class="stock-preview-item">
                    <span class="stock-preview-label">
                        Quantité disponible
                    </span>

                    <span
                        class="stock-preview-value"
                        id="preview_available"
                    >
                        0
                    </span>
                </div>

            </div>

        </div>

    @endif

</div>


{{-- ============================================================
    PRIX
============================================================ --}}
<div class="product-form-section">

    <div class="product-form-section-title">
        <i class="bx bx-money"></i>
        Prix
    </div>

    <div class="row">

        <div class="col-md-4 mb-3">

            <label for="purchase_price" class="form-label">
                Prix achat <span class="text-danger">*</span>
            </label>

            <input
                type="number"
                step="0.01"
                min="0"
                name="purchase_price"
                id="purchase_price"
                class="form-control @error('purchase_price') is-invalid @enderror"
                value="{{ old('purchase_price', $product->purchase_price ?? 0) }}"
                required
            >

        </div>


        <div class="col-md-4 mb-3">

            <label for="coef_purchase" class="form-label">
                Coef achat <span class="text-danger">*</span>
            </label>

            <input
                type="number"
                step="0.01"
                min="0"
                name="coef_purchase"
                id="coef_purchase"
                class="form-control @error('coef_purchase') is-invalid @enderror"
                value="{{ old('coef_purchase', $product->coef_purchase ?? 1) }}"
                required
            >

        </div>


        <div class="col-md-4 mb-3">

            <label for="cost_price" class="form-label">
                Prix revient
            </label>

            <input
                type="number"
                step="0.01"
                min="0"
                name="cost_price"
                id="cost_price"
                class="form-control"
                value="{{ old('cost_price', $product->cost_price ?? 0) }}"
                readonly
            >

        </div>


        <div class="col-md-4 mb-3">

            <label for="coef_sale" class="form-label">
                Coef vente <span class="text-danger">*</span>
            </label>

            <input
                type="number"
                step="0.01"
                min="0"
                name="coef_sale"
                id="coef_sale"
                class="form-control @error('coef_sale') is-invalid @enderror"
                value="{{ old('coef_sale', $product->coef_sale ?? 1) }}"
                required
            >

        </div>


        <div class="col-md-4 mb-3">

            <label for="sale_price" class="form-label">
                Prix vente <span class="text-danger">*</span>
            </label>

            <input
                type="number"
                step="0.01"
                min="0"
                name="sale_price"
                id="sale_price"
                class="form-control @error('sale_price') is-invalid @enderror"
                value="{{ old('sale_price', $product->sale_price ?? 0) }}"
                required
            >

        </div>

    </div>

</div>


{{-- ============================================================
    MODALE : NOUVELLE MARQUE
============================================================ --}}
<div
    class="modal fade reference-modal"
    id="brandModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Ajouter une marque
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fermer"
                ></button>
            </div>

            <div class="modal-body">

                <div class="reference-modal-help">
                    La nouvelle marque sera immédiatement ajoutée à la liste et sélectionnée.
                </div>

                <div
                    class="alert alert-danger d-none"
                    id="brandModalError"
                ></div>

                <label
                    for="new_brand_name"
                    class="form-label"
                >
                    Nom de la marque
                    <span class="text-danger">*</span>
                </label>

                <input
                    type="text"
                    id="new_brand_name"
                    class="form-control"
                    maxlength="255"
                    autocomplete="off"
                >

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Annuler
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="saveBrandButton"
                >
                    <i class="bx bx-save me-1"></i>
                    Enregistrer
                </button>

            </div>

        </div>

    </div>
</div>


{{-- ============================================================
    MODALE : NOUVEAU MODÈLE
============================================================ --}}
<div
    class="modal fade reference-modal"
    id="modelModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Ajouter un modèle
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fermer"
                ></button>

            </div>

            <div class="modal-body">

                <div class="reference-modal-help">
                    Le modèle sera rattaché à la marque sélectionnée.
                </div>

                <div
                    class="alert alert-danger d-none"
                    id="modelModalError"
                ></div>

                <div class="mb-3">

                    <label
                        for="new_model_brand_id"
                        class="form-label"
                    >
                        Marque
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        id="new_model_brand_id"
                        class="form-control"
                    >
                        <option value="">
                            Sélectionner une marque
                        </option>

                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>

                </div>


                <div>

                    <label
                        for="new_model_name"
                        class="form-label"
                    >
                        Nom du modèle
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        id="new_model_name"
                        class="form-control"
                        maxlength="255"
                        autocomplete="off"
                    >

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Annuler
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="saveModelButton"
                >
                    <i class="bx bx-save me-1"></i>
                    Enregistrer
                </button>

            </div>

        </div>

    </div>
</div>


{{-- ============================================================
    MODALE : NOUVEAU RAYON
============================================================ --}}
<div
    class="modal fade reference-modal"
    id="rayonModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Ajouter un rayon
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fermer"
                ></button>

            </div>

            <div class="modal-body">

                <div class="reference-modal-help">
                    Le nouveau rayon sera immédiatement ajouté à la liste et sélectionné.
                </div>

                <div
                    class="alert alert-danger d-none"
                    id="rayonModalError"
                ></div>

                <label
                    for="new_rayon_name"
                    class="form-label"
                >
                    Nom du rayon
                    <span class="text-danger">*</span>
                </label>

                <input
                    type="text"
                    id="new_rayon_name"
                    class="form-control"
                    maxlength="255"
                    autocomplete="off"
                >

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Annuler
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="saveRayonButton"
                >
                    <i class="bx bx-save me-1"></i>
                    Enregistrer
                </button>

            </div>

        </div>

    </div>
</div>


{{-- ============================================================
    MODALE : NOUVEL EMPLACEMENT
============================================================ --}}
<div
    class="modal fade reference-modal"
    id="locationModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Ajouter un emplacement
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fermer"
                ></button>

            </div>

            <div class="modal-body">

                <div class="reference-modal-help">
                    L’emplacement sera rattaché au rayon sélectionné.
                </div>

                <div
                    class="alert alert-danger d-none"
                    id="locationModalError"
                ></div>

                <div class="mb-3">

                    <label
                        for="new_location_rayon_id"
                        class="form-label"
                    >
                        Rayon
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        id="new_location_rayon_id"
                        class="form-control"
                    >
                        <option value="">
                            Sélectionner un rayon
                        </option>

                        @foreach($rayons as $rayon)
                            <option value="{{ $rayon->id }}">
                                {{ $rayon->name }}
                            </option>
                        @endforeach
                    </select>

                </div>


                <div>

                    <label
                        for="new_location_name"
                        class="form-label"
                    >
                        Nom de l’emplacement
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        id="new_location_name"
                        class="form-control"
                        maxlength="255"
                        autocomplete="off"
                    >

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Annuler
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="saveLocationButton"
                >
                    <i class="bx bx-save me-1"></i>
                    Enregistrer
                </button>

            </div>

        </div>

    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ÉLÉMENTS
    |--------------------------------------------------------------------------
    */

    const unitType =
        document.getElementById('unit_type');

    const unitLabel =
        document.getElementById('unit_label');

    const quantity =
        document.getElementById('quantity');

    const stockStateRadios =
        document.querySelectorAll(
            'input[name="stock_state"]'
        );

    const previewInitial =
        document.getElementById('preview_initial');

    const previewReceived =
        document.getElementById('preview_received');

    const previewAvailable =
        document.getElementById('preview_available');

    const receivedPreview =
        document.getElementById(
            'received_quantity_preview'
        );

    const unavailablePreview =
        document.getElementById(
            'unavailable_quantity_preview'
        );

    const quantityHelp =
        document.getElementById('quantity_help');

    const purchasePrice =
        document.getElementById('purchase_price');

    const coefPurchase =
        document.getElementById('coef_purchase');

    const costPrice =
        document.getElementById('cost_price');

    const coefSale =
        document.getElementById('coef_sale');

    const salePrice =
        document.getElementById('sale_price');


    /*
    |--------------------------------------------------------------------------
    | RÉFÉRENTIELS
    |--------------------------------------------------------------------------
    */

    const brandSelect =
        document.getElementById('brand_id');

    const modelSelect =
        document.getElementById('model_id');

    const familySelect =
        document.getElementById('family_id');

    const subfamilySelect =
        document.getElementById('subfamily_id');

    const rayonSelect =
        document.getElementById('rayon_id');

    const locationSelect =
        document.getElementById('location_id');

    const openModelModalButton =
        document.getElementById(
            'openModelModalButton'
        );

    const openLocationModalButton =
        document.getElementById(
            'openLocationModalButton'
        );


    /*
    |--------------------------------------------------------------------------
    | URLS AJAX
    |--------------------------------------------------------------------------
    */

    const ajaxUrls = {
        brand:
            @json(
                route('product-options.brands.store')
            ),

        model:
            @json(
                route('product-options.models.store')
            ),

        rayon:
            @json(
                route('product-options.rayons.store')
            ),

        location:
            @json(
                route('product-options.locations.store')
            )
    };


    const csrfToken =
        @json(csrf_token());


    /*
    |--------------------------------------------------------------------------
    | SELECT2
    |--------------------------------------------------------------------------
    */

    function refreshSelect2(selectElement) {

        if (
            !selectElement
            ||
            typeof window.jQuery === 'undefined'
            ||
            !window.jQuery.fn.select2
        ) {
            return;
        }

        const $select =
            window.jQuery(selectElement);

        if (
            $select.hasClass(
                'select2-hidden-accessible'
            )
        ) {
            $select.trigger('change.select2');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CONVERSION NUMÉRIQUE
    |--------------------------------------------------------------------------
    */

    function toNumber(value) {

        if (
            value === null
            ||
            value === undefined
            ||
            value === ''
        ) {
            return 0;
        }

        const normalized =
            String(value)
                .replace(',', '.')
                .trim();

        const number =
            parseFloat(normalized);

        return Number.isFinite(number)
            ? number
            : 0;
    }


    function formatQuantity(value) {
        return toNumber(value).toFixed(2);
    }


    function formatPrice(value) {
        return toNumber(value).toFixed(2);
    }


    /*
    |--------------------------------------------------------------------------
    | UNITÉ
    |--------------------------------------------------------------------------
    */

    function syncUnitLabel() {

        if (!unitType || !unitLabel) {
            return;
        }

        if (unitType.value === 'litre') {

            if (
                unitLabel.value === ''
                ||
                unitLabel.value === 'Pièce'
            ) {
                unitLabel.value = 'L';
            }

        } else {

            if (
                unitLabel.value === ''
                ||
                unitLabel.value === 'L'
            ) {
                unitLabel.value = 'Pièce';
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | STOCK
    |--------------------------------------------------------------------------
    */

    function getSelectedStockState() {

        const checked =
            document.querySelector(
                'input[name="stock_state"]:checked'
            );

        return checked
            ? checked.value
            : 'available';
    }


    function updateStockCards() {

        document
            .querySelectorAll('.stock-mode-card')
            .forEach(function (card) {
                card.classList.remove('active');
            });

        const checked =
            document.querySelector(
                'input[name="stock_state"]:checked'
            );

        if (!checked) {
            return;
        }

        const card =
            checked.closest('.stock-mode-card');

        if (card) {
            card.classList.add('active');
        }
    }


    function updateStockPreview() {

        if (!quantity) {
            updateStockCards();
            return;
        }

        const qty =
            Math.max(
                0,
                toNumber(quantity.value)
            );

        const stockState =
            getSelectedStockState();

        const received =
            stockState === 'available'
                ? qty
                : 0;

        const available =
            stockState === 'available'
                ? qty
                : 0;

        const unavailable =
            Math.max(
                0,
                qty - received
            );

        if (previewInitial) {
            previewInitial.textContent =
                formatQuantity(qty);
        }

        if (previewReceived) {
            previewReceived.textContent =
                formatQuantity(received);
        }

        if (previewAvailable) {
            previewAvailable.textContent =
                formatQuantity(available);
        }

        if (receivedPreview && !@json($isEdit)) {
            receivedPreview.value =
                formatQuantity(received);
        }

        if (unavailablePreview && !@json($isEdit)) {
            unavailablePreview.value =
                formatQuantity(unavailable);
        }

        if (quantityHelp) {

            if (stockState === 'unavailable') {

                quantityHelp.textContent =
                    'Cette quantité sera enregistrée comme quantité initiale à recevoir. Le stock disponible commencera à 0.';

            } else {

                quantityHelp.textContent =
                    'Cette quantité est déjà physiquement reçue et sera immédiatement disponible.';
            }
        }

        updateStockCards();
    }


    /*
    |--------------------------------------------------------------------------
    | PRIX
    |--------------------------------------------------------------------------
    */

    function calculatePrices() {

        if (
            !purchasePrice
            ||
            !coefPurchase
            ||
            !costPrice
            ||
            !coefSale
            ||
            !salePrice
        ) {
            return;
        }

        const purchase =
            Math.max(
                0,
                toNumber(purchasePrice.value)
            );

        const purchaseCoefficient =
            Math.max(
                0,
                toNumber(coefPurchase.value)
            );

        const saleCoefficient =
            Math.max(
                0,
                toNumber(coefSale.value)
            );

        const calculatedCostPrice =
            purchase * purchaseCoefficient;

        const calculatedSalePrice =
            calculatedCostPrice * saleCoefficient;

        costPrice.value =
            formatPrice(
                calculatedCostPrice
            );

        salePrice.value =
            formatPrice(
                calculatedSalePrice
            );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTRER LES MODÈLES SELON LA MARQUE
    |--------------------------------------------------------------------------
    */

    function filterModelsByBrand(
        preserveCurrentValue = false
    ) {

        if (!brandSelect || !modelSelect) {
            return;
        }

        const brandId =
            String(brandSelect.value || '');

        const currentModelValue =
            preserveCurrentValue
                ? String(modelSelect.value || '')
                : '';

        Array.from(
            modelSelect.options
        ).forEach(function (option, index) {

            if (index === 0) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const optionBrandId =
                String(
                    option.dataset.brand || ''
                );

            const visible =
                brandId !== ''
                &&
                optionBrandId === brandId;

            option.hidden = !visible;
            option.disabled = !visible;
        });

        if (!preserveCurrentValue) {
            modelSelect.value = '';
        } else {

            const selectedOption =
                modelSelect.querySelector(
                    'option[value="'
                    +
                    CSS.escape(currentModelValue)
                    +
                    '"]'
                );

            if (
                !selectedOption
                ||
                selectedOption.disabled
            ) {
                modelSelect.value = '';
            }
        }

        refreshSelect2(modelSelect);
    }


    /*
    |--------------------------------------------------------------------------
    | FILTRER SOUS-FAMILLES SELON FAMILLE
    |--------------------------------------------------------------------------
    */

    function filterSubfamiliesByFamily(
        preserveCurrentValue = false
    ) {

        if (!familySelect || !subfamilySelect) {
            return;
        }

        const familyId =
            String(familySelect.value || '');

        const currentValue =
            preserveCurrentValue
                ? String(subfamilySelect.value || '')
                : '';

        Array.from(
            subfamilySelect.options
        ).forEach(function (option, index) {

            if (index === 0) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const optionFamilyId =
                String(
                    option.dataset.family || ''
                );

            const visible =
                familyId !== ''
                &&
                optionFamilyId === familyId;

            option.hidden = !visible;
            option.disabled = !visible;
        });

        if (!preserveCurrentValue) {
            subfamilySelect.value = '';
        } else {

            const selectedOption =
                Array.from(
                    subfamilySelect.options
                ).find(
                    option =>
                        String(option.value)
                        ===
                        currentValue
                );

            if (
                !selectedOption
                ||
                selectedOption.disabled
            ) {
                subfamilySelect.value = '';
            }
        }

        refreshSelect2(subfamilySelect);
    }


    /*
    |--------------------------------------------------------------------------
    | FILTRER EMPLACEMENTS SELON RAYON
    |--------------------------------------------------------------------------
    */

    function filterLocationsByRayon(
        preserveCurrentValue = false
    ) {

        if (!rayonSelect || !locationSelect) {
            return;
        }

        const rayonId =
            String(rayonSelect.value || '');

        const currentValue =
            preserveCurrentValue
                ? String(locationSelect.value || '')
                : '';

        Array.from(
            locationSelect.options
        ).forEach(function (option, index) {

            if (index === 0) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const optionRayonId =
                String(
                    option.dataset.rayon || ''
                );

            const visible =
                rayonId !== ''
                &&
                optionRayonId === rayonId;

            option.hidden = !visible;
            option.disabled = !visible;
        });

        if (!preserveCurrentValue) {
            locationSelect.value = '';
        } else {

            const selectedOption =
                Array.from(
                    locationSelect.options
                ).find(
                    option =>
                        String(option.value)
                        ===
                        currentValue
                );

            if (
                !selectedOption
                ||
                selectedOption.disabled
            ) {
                locationSelect.value = '';
            }
        }

        refreshSelect2(locationSelect);
    }


    /*
    |--------------------------------------------------------------------------
    | OUTILS AJAX
    |--------------------------------------------------------------------------
    */

    function clearAjaxError(element) {

        if (!element) {
            return;
        }

        element.classList.add('d-none');
        element.innerHTML = '';
    }


    function showAjaxError(
        element,
        message
    ) {

        if (!element) {
            return;
        }

        element.textContent =
            message
            ||
            'Une erreur est survenue.';

        element.classList.remove('d-none');
    }


    function firstValidationMessage(data) {

        if (
            data
            &&
            data.errors
        ) {

            const keys =
                Object.keys(data.errors);

            if (keys.length > 0) {

                const first =
                    data.errors[keys[0]];

                if (
                    Array.isArray(first)
                    &&
                    first.length > 0
                ) {
                    return first[0];
                }
            }
        }

        return data?.message
            ||
            'Une erreur est survenue.';
    }


    async function postJson(
        url,
        payload
    ) {

        const response =
            await fetch(
                url,
                {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            csrfToken
                    },

                    body:
                        JSON.stringify(payload)
                }
            );

        let data = {};

        try {
            data = await response.json();
        } catch (error) {
            data = {};
        }

        if (!response.ok) {

            const exception =
                new Error(
                    firstValidationMessage(data)
                );

            exception.responseData =
                data;

            throw exception;
        }

        return data;
    }


    function addOptionAndSelect(
        selectElement,
        item,
        dataAttributes = {}
    ) {

        if (!selectElement || !item) {
            return;
        }

        let option =
            Array.from(
                selectElement.options
            ).find(
                current =>
                    String(current.value)
                    ===
                    String(item.id)
            );

        if (!option) {

            option =
                new Option(
                    item.name,
                    item.id,
                    true,
                    true
                );

            Object
                .entries(dataAttributes)
                .forEach(
                    ([key, value]) => {
                        option.dataset[key] =
                            value ?? '';
                    }
                );

            selectElement.add(option);

        } else {

            option.text =
                item.name;

            option.selected =
                true;

            Object
                .entries(dataAttributes)
                .forEach(
                    ([key, value]) => {
                        option.dataset[key] =
                            value ?? '';
                    }
                );
        }

        selectElement.value =
            String(item.id);

        if (
            typeof window.jQuery !== 'undefined'
        ) {
            window
                .jQuery(selectElement)
                .val(String(item.id))
                .trigger('change');
        } else {

            selectElement.dispatchEvent(
                new Event(
                    'change',
                    {
                        bubbles: true
                    }
                )
            );
        }
    }


    function closeBootstrapModal(modalId) {

        const modalElement =
            document.getElementById(modalId);

        if (
            !modalElement
            ||
            typeof bootstrap === 'undefined'
        ) {
            return;
        }

        const modal =
            bootstrap.Modal.getOrCreateInstance(
                modalElement
            );

        modal.hide();
    }


    function openBootstrapModal(modalId) {

        const modalElement =
            document.getElementById(modalId);

        if (
            !modalElement
            ||
            typeof bootstrap === 'undefined'
        ) {
            return;
        }

        const modal =
            bootstrap.Modal.getOrCreateInstance(
                modalElement
            );

        modal.show();
    }


    function setButtonLoading(
        button,
        loading
    ) {

        if (!button) {
            return;
        }

        if (loading) {

            button.dataset.originalHtml =
                button.innerHTML;

            button.disabled =
                true;

            button.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1"></span> Enregistrement...';

        } else {

            button.disabled =
                false;

            if (
                button.dataset.originalHtml
            ) {
                button.innerHTML =
                    button.dataset.originalHtml;
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | AJOUT MARQUE
    |--------------------------------------------------------------------------
    */

    const saveBrandButton =
        document.getElementById(
            'saveBrandButton'
        );

    if (saveBrandButton) {

        saveBrandButton.addEventListener(
            'click',
            async function () {

                const input =
                    document.getElementById(
                        'new_brand_name'
                    );

                const errorBox =
                    document.getElementById(
                        'brandModalError'
                    );

                clearAjaxError(errorBox);

                const name =
                    String(
                        input?.value || ''
                    ).trim();

                if (!name) {

                    showAjaxError(
                        errorBox,
                        'Veuillez saisir le nom de la marque.'
                    );

                    return;
                }

                try {

                    setButtonLoading(
                        saveBrandButton,
                        true
                    );

                    const data =
                        await postJson(
                            ajaxUrls.brand,
                            {
                                name: name
                            }
                        );

                    addOptionAndSelect(
                        brandSelect,
                        data.item
                    );

                    /*
                    | Ajouter aussi la marque dans
                    | la modale "Nouveau modèle".
                    */
                    const modalBrandSelect =
                        document.getElementById(
                            'new_model_brand_id'
                        );

                    if (modalBrandSelect) {

                        const exists =
                            Array
                                .from(
                                    modalBrandSelect.options
                                )
                                .some(
                                    option =>
                                        String(option.value)
                                        ===
                                        String(data.item.id)
                                );

                        if (!exists) {

                            modalBrandSelect.add(
                                new Option(
                                    data.item.name,
                                    data.item.id
                                )
                            );
                        }

                        modalBrandSelect.value =
                            String(data.item.id);
                    }

                    filterModelsByBrand(false);

                    if (input) {
                        input.value = '';
                    }

                    closeBootstrapModal(
                        'brandModal'
                    );

                } catch (error) {

                    showAjaxError(
                        errorBox,
                        error.message
                    );

                } finally {

                    setButtonLoading(
                        saveBrandButton,
                        false
                    );
                }
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | OUVRIR MODÈLE
    |--------------------------------------------------------------------------
    */

    if (openModelModalButton) {

        openModelModalButton.addEventListener(
            'click',
            function () {

                const currentBrandId =
                    brandSelect?.value || '';

                if (!currentBrandId) {

                    alert(
                        'Veuillez sélectionner une marque avant d’ajouter un modèle.'
                    );

                    return;
                }

                const modalBrandSelect =
                    document.getElementById(
                        'new_model_brand_id'
                    );

                if (modalBrandSelect) {
                    modalBrandSelect.value =
                        currentBrandId;
                }

                clearAjaxError(
                    document.getElementById(
                        'modelModalError'
                    )
                );

                openBootstrapModal(
                    'modelModal'
                );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AJOUT MODÈLE
    |--------------------------------------------------------------------------
    */

    const saveModelButton =
        document.getElementById(
            'saveModelButton'
        );

    if (saveModelButton) {

        saveModelButton.addEventListener(
            'click',
            async function () {

                const brandInput =
                    document.getElementById(
                        'new_model_brand_id'
                    );

                const nameInput =
                    document.getElementById(
                        'new_model_name'
                    );

                const errorBox =
                    document.getElementById(
                        'modelModalError'
                    );

                clearAjaxError(errorBox);

                const brandId =
                    brandInput?.value || '';

                const name =
                    String(
                        nameInput?.value || ''
                    ).trim();

                if (!brandId) {

                    showAjaxError(
                        errorBox,
                        'Veuillez sélectionner une marque.'
                    );

                    return;
                }

                if (!name) {

                    showAjaxError(
                        errorBox,
                        'Veuillez saisir le nom du modèle.'
                    );

                    return;
                }

                try {

                    setButtonLoading(
                        saveModelButton,
                        true
                    );

                    const data =
                        await postJson(
                            ajaxUrls.model,
                            {
                                brand_id:
                                    brandId,

                                name:
                                    name
                            }
                        );

                    /*
                    | La marque du formulaire produit
                    | est synchronisée avec celle du modèle.
                    */
                    if (
                        brandSelect
                        &&
                        String(brandSelect.value)
                        !==
                        String(data.item.brand_id)
                    ) {

                        brandSelect.value =
                            String(
                                data.item.brand_id
                            );

                        refreshSelect2(
                            brandSelect
                        );
                    }

                    const option =
                        new Option(
                            data.item.name,
                            data.item.id,
                            true,
                            true
                        );

                    option.dataset.brand =
                        data.item.brand_id;

                    modelSelect.add(option);

                    filterModelsByBrand(true);

                    modelSelect.value =
                        String(data.item.id);

                    refreshSelect2(
                        modelSelect
                    );

                    if (
                        typeof window.jQuery
                        !==
                        'undefined'
                    ) {
                        window
                            .jQuery(modelSelect)
                            .val(
                                String(
                                    data.item.id
                                )
                            )
                            .trigger(
                                'change.select2'
                            );
                    }

                    if (nameInput) {
                        nameInput.value = '';
                    }

                    closeBootstrapModal(
                        'modelModal'
                    );

                } catch (error) {

                    showAjaxError(
                        errorBox,
                        error.message
                    );

                } finally {

                    setButtonLoading(
                        saveModelButton,
                        false
                    );
                }
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AJOUT RAYON
    |--------------------------------------------------------------------------
    */

    const saveRayonButton =
        document.getElementById(
            'saveRayonButton'
        );

    if (saveRayonButton) {

        saveRayonButton.addEventListener(
            'click',
            async function () {

                const input =
                    document.getElementById(
                        'new_rayon_name'
                    );

                const errorBox =
                    document.getElementById(
                        'rayonModalError'
                    );

                clearAjaxError(errorBox);

                const name =
                    String(
                        input?.value || ''
                    ).trim();

                if (!name) {

                    showAjaxError(
                        errorBox,
                        'Veuillez saisir le nom du rayon.'
                    );

                    return;
                }

                try {

                    setButtonLoading(
                        saveRayonButton,
                        true
                    );

                    const data =
                        await postJson(
                            ajaxUrls.rayon,
                            {
                                name: name
                            }
                        );

                    addOptionAndSelect(
                        rayonSelect,
                        data.item
                    );

                    /*
                    | Ajouter aussi le rayon dans
                    | la modale "Nouvel emplacement".
                    */
                    const modalRayonSelect =
                        document.getElementById(
                            'new_location_rayon_id'
                        );

                    if (modalRayonSelect) {

                        const exists =
                            Array
                                .from(
                                    modalRayonSelect.options
                                )
                                .some(
                                    option =>
                                        String(option.value)
                                        ===
                                        String(data.item.id)
                                );

                        if (!exists) {

                            modalRayonSelect.add(
                                new Option(
                                    data.item.name,
                                    data.item.id
                                )
                            );
                        }

                        modalRayonSelect.value =
                            String(data.item.id);
                    }

                    filterLocationsByRayon(false);

                    if (input) {
                        input.value = '';
                    }

                    closeBootstrapModal(
                        'rayonModal'
                    );

                } catch (error) {

                    showAjaxError(
                        errorBox,
                        error.message
                    );

                } finally {

                    setButtonLoading(
                        saveRayonButton,
                        false
                    );
                }
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | OUVRIR EMPLACEMENT
    |--------------------------------------------------------------------------
    */

    if (openLocationModalButton) {

        openLocationModalButton.addEventListener(
            'click',
            function () {

                const currentRayonId =
                    rayonSelect?.value || '';

                if (!currentRayonId) {

                    alert(
                        'Veuillez sélectionner un rayon avant d’ajouter un emplacement.'
                    );

                    return;
                }

                const modalRayonSelect =
                    document.getElementById(
                        'new_location_rayon_id'
                    );

                if (modalRayonSelect) {
                    modalRayonSelect.value =
                        currentRayonId;
                }

                clearAjaxError(
                    document.getElementById(
                        'locationModalError'
                    )
                );

                openBootstrapModal(
                    'locationModal'
                );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AJOUT EMPLACEMENT
    |--------------------------------------------------------------------------
    */

    const saveLocationButton =
        document.getElementById(
            'saveLocationButton'
        );

    if (saveLocationButton) {

        saveLocationButton.addEventListener(
            'click',
            async function () {

                const rayonInput =
                    document.getElementById(
                        'new_location_rayon_id'
                    );

                const nameInput =
                    document.getElementById(
                        'new_location_name'
                    );

                const errorBox =
                    document.getElementById(
                        'locationModalError'
                    );

                clearAjaxError(errorBox);

                const rayonId =
                    rayonInput?.value || '';

                const name =
                    String(
                        nameInput?.value || ''
                    ).trim();

                if (!rayonId) {

                    showAjaxError(
                        errorBox,
                        'Veuillez sélectionner un rayon.'
                    );

                    return;
                }

                if (!name) {

                    showAjaxError(
                        errorBox,
                        'Veuillez saisir le nom de l’emplacement.'
                    );

                    return;
                }

                try {

                    setButtonLoading(
                        saveLocationButton,
                        true
                    );

                    const data =
                        await postJson(
                            ajaxUrls.location,
                            {
                                rayon_id:
                                    rayonId,

                                name:
                                    name
                            }
                        );

                    if (
                        rayonSelect
                        &&
                        String(rayonSelect.value)
                        !==
                        String(data.item.rayon_id)
                    ) {

                        rayonSelect.value =
                            String(
                                data.item.rayon_id
                            );

                        refreshSelect2(
                            rayonSelect
                        );
                    }

                    const option =
                        new Option(
                            data.item.name,
                            data.item.id,
                            true,
                            true
                        );

                    option.dataset.rayon =
                        data.item.rayon_id;

                    locationSelect.add(option);

                    filterLocationsByRayon(
                        true
                    );

                    locationSelect.value =
                        String(data.item.id);

                    refreshSelect2(
                        locationSelect
                    );

                    if (
                        typeof window.jQuery
                        !==
                        'undefined'
                    ) {
                        window
                            .jQuery(
                                locationSelect
                            )
                            .val(
                                String(
                                    data.item.id
                                )
                            )
                            .trigger(
                                'change.select2'
                            );
                    }

                    if (nameInput) {
                        nameInput.value = '';
                    }

                    closeBootstrapModal(
                        'locationModal'
                    );

                } catch (error) {

                    showAjaxError(
                        errorBox,
                        error.message
                    );

                } finally {

                    setButtonLoading(
                        saveLocationButton,
                        false
                    );
                }
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ÉVÉNEMENTS RÉFÉRENTIELS
    |--------------------------------------------------------------------------
    */

    if (brandSelect) {

        brandSelect.addEventListener(
            'change',
            function () {
                filterModelsByBrand(false);
            }
        );

        if (
            typeof window.jQuery !== 'undefined'
        ) {
            window
                .jQuery(brandSelect)
                .on(
                    'select2:select select2:clear',
                    function () {
                        filterModelsByBrand(false);
                    }
                );
        }
    }


    if (familySelect) {

        familySelect.addEventListener(
            'change',
            function () {
                filterSubfamiliesByFamily(false);
            }
        );

        if (
            typeof window.jQuery !== 'undefined'
        ) {
            window
                .jQuery(familySelect)
                .on(
                    'select2:select select2:clear',
                    function () {
                        filterSubfamiliesByFamily(false);
                    }
                );
        }
    }


    if (rayonSelect) {

        rayonSelect.addEventListener(
            'change',
            function () {
                filterLocationsByRayon(false);
            }
        );

        if (
            typeof window.jQuery !== 'undefined'
        ) {
            window
                .jQuery(rayonSelect)
                .on(
                    'select2:select select2:clear',
                    function () {
                        filterLocationsByRayon(false);
                    }
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ÉVÉNEMENTS STOCK / PRIX
    |--------------------------------------------------------------------------
    */

    if (unitType) {

        unitType.addEventListener(
            'change',
            syncUnitLabel
        );

        if (
            typeof window.jQuery !== 'undefined'
        ) {
            window
                .jQuery(unitType)
                .on(
                    'select2:select change',
                    syncUnitLabel
                );
        }
    }


    if (quantity) {

        quantity.addEventListener(
            'input',
            updateStockPreview
        );

        quantity.addEventListener(
            'change',
            updateStockPreview
        );
    }


    stockStateRadios.forEach(
        function (radio) {

            radio.addEventListener(
                'change',
                updateStockPreview
            );
        }
    );


    if (purchasePrice) {

        purchasePrice.addEventListener(
            'input',
            calculatePrices
        );

        purchasePrice.addEventListener(
            'change',
            calculatePrices
        );
    }


    if (coefPurchase) {

        coefPurchase.addEventListener(
            'input',
            calculatePrices
        );

        coefPurchase.addEventListener(
            'change',
            calculatePrices
        );
    }


    if (coefSale) {

        coefSale.addEventListener(
            'input',
            calculatePrices
        );

        coefSale.addEventListener(
            'change',
            calculatePrices
        );
    }


    /*
    |--------------------------------------------------------------------------
    | INITIALISATION
    |--------------------------------------------------------------------------
    */

    syncUnitLabel();

    updateStockCards();

    updateStockPreview();

    calculatePrices();

    filterModelsByBrand(true);

    filterSubfamiliesByFamily(true);

    filterLocationsByRayon(true);

});
</script>
