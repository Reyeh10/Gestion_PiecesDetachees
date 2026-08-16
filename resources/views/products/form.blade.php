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

    @media (max-width: 767.98px) {
        .stock-preview-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

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
                <div class="invalid-feedback">{{ $message }}</div>
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
                <div class="invalid-feedback">{{ $message }}</div>
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
                <div class="invalid-feedback">{{ $message }}</div>
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
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- MARQUE --}}
        <div class="col-md-3 mb-3">
            <label for="brand_id" class="form-label">
                Marque <span class="text-danger">*</span>
            </label>

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

            @error('brand_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- MODÈLE --}}
        <div class="col-md-3 mb-3">
            <label for="model_id" class="form-label">
                Modèle <span class="text-danger">*</span>
            </label>

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

            @error('model_id')
                <div class="invalid-feedback">{{ $message }}</div>
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

        {{-- EMPLACEMENT --}}
        <div class="col-md-4 mb-3">
            <label for="location_id" class="form-label">
                Emplacement
            </label>

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
    </div>
</div>

<div class="product-form-section">
    <div class="product-form-section-title">
        <i class="bx bx-cube"></i>
        Gestion du stock
    </div>

    @if(!$isEdit)
        <div class="row mb-3">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <label
                    class="stock-mode-card {{ $currentStatus === 'disponible' ? 'active' : '' }}"
                    for="status_disponible"
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
                    class="stock-mode-card {{ $currentStatus === 'non_disponible' ? 'active' : '' }}"
                    for="status_non_disponible"
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
                <div class="invalid-feedback">{{ $message }}</div>
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
                value="{{ $isEdit ? (float) ($product->unavailable_quantity ?? 0) : 0 }}"
                readonly
            >

            <div class="product-form-help">
                Quantité initiale moins quantité reçue.
            </div>
        </div>

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
            <div class="col-md-4 mb-3">
                <label class="form-label">
                    Statut actuel
                </label>

                <input
                    type="text"
                    class="form-control"
                    value="{{ $product->status === 'disponible' ? 'Disponible' : ($product->status === 'vendu' ? 'Vendu' : 'Non disponible') }}"
                    readonly
                >
            </div>
        @endif
    </div>

    @if(!$isEdit)
        <div class="stock-preview">
            <div class="stock-preview-grid">
                <div class="stock-preview-item">
                    <span class="stock-preview-label">Quantité initiale</span>
                    <span class="stock-preview-value" id="preview_initial">0</span>
                </div>

                <div class="stock-preview-item">
                    <span class="stock-preview-label">Quantité reçue</span>
                    <span class="stock-preview-value" id="preview_received">0</span>
                </div>

                <div class="stock-preview-item">
                    <span class="stock-preview-label">Quantité disponible</span>
                    <span class="stock-preview-value" id="preview_available">0</span>
                </div>
            </div>
        </div>
    @endif
</div>

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

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ÉLÉMENTS - UNITÉS
    |--------------------------------------------------------------------------
    */

    const unitType =
        document.getElementById('unit_type');

    const unitLabel =
        document.getElementById('unit_label');


    /*
    |--------------------------------------------------------------------------
    | ÉLÉMENTS - STOCK
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | ÉLÉMENTS - PRIX
    |--------------------------------------------------------------------------
    */

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
    | CONVERSION VALEUR NUMÉRIQUE
    |--------------------------------------------------------------------------
    */

    function toNumber(value) {

        if (
            value === null ||
            value === undefined ||
            value === ''
        ) {
            return 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Accepter également une virgule
        |--------------------------------------------------------------------------
        |
        | Exemple :
        |
        | 1,5 devient 1.5
        |
        */

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


    /*
    |--------------------------------------------------------------------------
    | FORMAT QUANTITÉ
    |--------------------------------------------------------------------------
    */

    function formatQuantity(value) {

        return toNumber(value).toFixed(2);
    }


    /*
    |--------------------------------------------------------------------------
    | FORMAT PRIX
    |--------------------------------------------------------------------------
    */

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
                unitLabel.value === '' ||
                unitLabel.value === 'Pièce'
            ) {
                unitLabel.value = 'L';
            }

        } else {

            if (
                unitLabel.value === '' ||
                unitLabel.value === 'L'
            ) {
                unitLabel.value = 'Pièce';
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ÉTAT DU STOCK SÉLECTIONNÉ
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


    /*
    |--------------------------------------------------------------------------
    | CARTE ACTIVE
    |--------------------------------------------------------------------------
    */

    function updateStockCards() {

        const cards =
            document.querySelectorAll(
                '.stock-mode-card'
            );

        cards.forEach(function (card) {

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
            checked.closest(
                '.stock-mode-card'
            );

        if (card) {
            card.classList.add('active');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | APERÇU DU STOCK
    |--------------------------------------------------------------------------
    */

    function updateStockPreview() {

        /*
        |--------------------------------------------------------------------------
        | Sur la page Edit certains éléments peuvent ne pas exister
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | PRODUIT DÉJÀ DISPONIBLE
        |--------------------------------------------------------------------------
        |
        | quantité initiale = quantité saisie
        | quantité reçue    = quantité saisie
        | quantité disponible = quantité saisie
        | quantité non disponible = 0
        |
        */


        /*
        |--------------------------------------------------------------------------
        | PRODUIT NON DISPONIBLE
        |--------------------------------------------------------------------------
        |
        | quantité initiale = quantité saisie
        | quantité reçue    = 0
        | quantité disponible = 0
        | quantité non disponible = quantité saisie
        |
        */

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


        /*
        |--------------------------------------------------------------------------
        | APERÇU
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | CHAMPS READONLY
        |--------------------------------------------------------------------------
        */

        if (receivedPreview) {

            receivedPreview.value =
                formatQuantity(received);
        }

        if (unavailablePreview) {

            unavailablePreview.value =
                formatQuantity(unavailable);
        }


        /*
        |--------------------------------------------------------------------------
        | TEXTE D'AIDE
        |--------------------------------------------------------------------------
        */

        if (quantityHelp) {

            if (stockState === 'unavailable') {

                quantityHelp.textContent =
                    'Cette quantité sera enregistrée comme quantité initiale à recevoir. Le stock disponible commencera à 0.';

            } else {

                quantityHelp.textContent =
                    'Cette quantité est déjà physiquement reçue et sera immédiatement disponible.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ACTUALISER LES CARTES
        |--------------------------------------------------------------------------
        */

        updateStockCards();
    }


    /*
    |--------------------------------------------------------------------------
    | CALCUL AUTOMATIQUE DES PRIX
    |--------------------------------------------------------------------------
    |
    | Exemple :
    |
    | Prix achat     = 1500
    | Coef achat     = 1
    |
    | Prix revient   = 1500 × 1
    |                = 1500
    |
    | Coef vente     = 1.5
    |
    | Prix vente     = 1500 × 1.5
    |                = 2250
    |
    */

    function calculatePrices() {

        if (
            !purchasePrice ||
            !coefPurchase ||
            !costPrice ||
            !coefSale ||
            !salePrice
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | RÉCUPÉRATION DES VALEURS
        |--------------------------------------------------------------------------
        */

        const purchase =
            Math.max(
                0,
                toNumber(
                    purchasePrice.value
                )
            );

        const purchaseCoefficient =
            Math.max(
                0,
                toNumber(
                    coefPurchase.value
                )
            );

        const saleCoefficient =
            Math.max(
                0,
                toNumber(
                    coefSale.value
                )
            );


        /*
        |--------------------------------------------------------------------------
        | PRIX DE REVIENT
        |--------------------------------------------------------------------------
        */

        const calculatedCostPrice =
            purchase *
            purchaseCoefficient;


        /*
        |--------------------------------------------------------------------------
        | PRIX DE VENTE
        |--------------------------------------------------------------------------
        */

        const calculatedSalePrice =
            calculatedCostPrice *
            saleCoefficient;


        /*
        |--------------------------------------------------------------------------
        | AFFICHAGE
        |--------------------------------------------------------------------------
        */

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
    | UNITÉ : ÉVÉNEMENT
    |--------------------------------------------------------------------------
    */

    if (unitType) {

        unitType.addEventListener(
            'change',
            function () {

                syncUnitLabel();
            }
        );


        /*
        |--------------------------------------------------------------------------
        | SELECT2
        |--------------------------------------------------------------------------
        */

        if (
            typeof window.jQuery !== 'undefined'
        ) {

            window.jQuery(unitType)
                .on(
                    'select2:select change',
                    function () {

                        syncUnitLabel();
                    }
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | QUANTITÉ : ÉVÉNEMENT
    |--------------------------------------------------------------------------
    */

    if (quantity) {

        quantity.addEventListener(
            'input',
            function () {

                updateStockPreview();
            }
        );

        quantity.addEventListener(
            'change',
            function () {

                updateStockPreview();
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ÉTAT STOCK : ÉVÉNEMENT
    |--------------------------------------------------------------------------
    */

    stockStateRadios.forEach(
        function (radio) {

            radio.addEventListener(
                'change',
                function () {

                    updateStockPreview();
                }
            );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | PRIX ACHAT
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | COEFFICIENT ACHAT
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | COEFFICIENT VENTE
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | IMPORTANT
    |--------------------------------------------------------------------------
    |
    | Cette ligne manquait dans votre version.
    |
    | Elle permet également de calculer les prix immédiatement
    | lorsque la page est chargée.
    |
    */

    calculatePrices();

});

</script>