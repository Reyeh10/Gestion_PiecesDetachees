@php
    $editingRequest = isset($vehiclePartRequest) && $vehiclePartRequest;

    $selectedVehicle = old(
        'vehicle_id',
        $vehiclePartRequest->vehicle_id
            ?? $selectedVehicleId
            ?? null
    );

    $selectedProduct = old(
        'product_id',
        $vehiclePartRequest->product_id ?? ''
    );

    $selectedSupplier = old(
        'supplier_id',
        $vehiclePartRequest->supplier_id ?? ''
    );

    $selectedUnit = old(
        'unit',
        $vehiclePartRequest->unit ?? 'Piece'
    );
@endphp

<style>
    .vpr-form-section {
        margin-bottom: 24px;
        padding: 20px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
    }

    .vpr-form-section:last-child {
        margin-bottom: 0;
    }

    .vpr-form-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 18px;
        font-size: 15px;
        font-weight: 800;
        color: #334155;
    }

    .vpr-form-section-title i {
        font-size: 20px;
        color: #696cff;
    }

    .vpr-form-help {
        margin-top: 6px;
        font-size: 12px;
        color: #64748b;
    }

    .selected-product-info {
        display: none;
        margin-top: 10px;
        padding: 12px 14px;
        color: #334155;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        font-size: 13px;
    }

    .selected-product-info.show {
        display: block;
    }

    .selected-product-info strong {
        color: #1f2937;
    }

    .form-label {
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 800;
        color: #52657b;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .form-control,
    .form-select {
        min-height: 44px;
        border-color: #d8dee8;
        border-radius: 9px;
        box-shadow: none;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 3px rgba(105, 108, 255, .10);
    }

    textarea.form-control {
        min-height: auto;
    }
</style>

<div class="vpr-form-section">
    <div class="vpr-form-section-title">
        <i class="bx bx-car"></i>
        Véhicule et pièce
    </div>

    <div class="row g-3">

        {{-- VÉHICULE --}}
        <div class="col-md-6">
            <label for="vehicle_id" class="form-label">
                Véhicule
                <span class="text-danger">*</span>
            </label>

            <select
                name="vehicle_id"
                id="vehicle_id"
                class="form-select @error('vehicle_id') is-invalid @enderror"
                required
            >
                <option value="">
                    Sélectionner un véhicule
                </option>

                @foreach($vehicles as $vehicle)
                    <option
                        value="{{ $vehicle->id }}"
                        @selected((string) $selectedVehicle === (string) $vehicle->id)
                    >
                        {{ $vehicle->plate_number ?? $vehicle->vin ?? 'Sans immatriculation' }}

                        @if($vehicle->customer)
                            - {{ $vehicle->customer->name }}
                        @endif

                        @if($vehicle->brand || $vehicle->model)
                            - {{ $vehicle->brand ?? '' }} {{ $vehicle->model ?? '' }}
                        @endif
                    </option>
                @endforeach
            </select>

            @error('vehicle_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- PRODUIT EXISTANT --}}
        <div class="col-md-6">
            <label for="product_id" class="form-label">
                Produit existant
            </label>

            <select
                name="product_id"
                id="product_id"
                class="form-select @error('product_id') is-invalid @enderror"
            >
                <option value="">
                    La pièce n’existe pas encore dans le catalogue
                </option>

                @foreach($products as $product)
                    @php
                        $productAvailableQty = (float) ($product->quantity ?? 0);
                        $productUnit = $product->unit_label ?: 'Pièce';
                    @endphp

                    <option
                        value="{{ $product->id }}"
                        data-reference="{{ $product->reference ?? '' }}"
                        data-name="{{ $product->designation ?? $product->name ?? '' }}"
                        data-unit-type="{{ $product->unit_type ?? 'piece' }}"
                        data-unit-label="{{ $productUnit }}"
                        data-quantity="{{ $productAvailableQty }}"
                        data-status="{{ $product->status ?? '' }}"
                        @selected((string) $selectedProduct === (string) $product->id)
                    >
                        {{ $product->reference ?? 'Sans référence' }}
                        -
                        {{ $product->designation ?? $product->name ?? '' }}
                    </option>
                @endforeach
            </select>

            @error('product_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror

            <div class="vpr-form-help">
                Facultatif. Sélectionnez une pièce existante pour remplir automatiquement sa référence et sa désignation.
            </div>

            <div
                id="selected_product_info"
                class="selected-product-info"
            ></div>
        </div>

        {{-- RÉFÉRENCE --}}
        <div class="col-md-4">
            <label for="reference" class="form-label">
                Référence de la pièce
            </label>

            <input
                type="text"
                name="reference"
                id="reference"
                value="{{ old('reference', $vehiclePartRequest->reference ?? '') }}"
                class="form-control @error('reference') is-invalid @enderror"
                maxlength="255"
            >

            @error('reference')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- NOM DE LA PIÈCE --}}
        <div class="col-md-8">
            <label for="part_name" class="form-label">
                Nom de la pièce
                <span class="text-danger">*</span>
            </label>

            <input
                type="text"
                name="part_name"
                id="part_name"
                value="{{ old('part_name', $vehiclePartRequest->part_name ?? '') }}"
                class="form-control @error('part_name') is-invalid @enderror"
                placeholder="Exemple : Filtre à huile"
                maxlength="255"
                required
            >

            @error('part_name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- QUANTITÉ --}}
        <div class="col-md-3">
            <label for="quantity" class="form-label">
                Quantité demandée
                <span class="text-danger">*</span>
            </label>

            <input
                type="number"
                name="quantity"
                id="quantity"
                min="0.01"
                step="0.01"
                value="{{ old('quantity', $vehiclePartRequest->quantity ?? 1) }}"
                class="form-control @error('quantity') is-invalid @enderror"
                required
            >

            @error('quantity')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- UNITÉ --}}
        <div class="col-md-3">
            <label for="unit" class="form-label">
                Unité
                <span class="text-danger">*</span>
            </label>

            <select
                name="unit"
                id="unit"
                class="form-select @error('unit') is-invalid @enderror"
                required
            >
                <option
                    value="Piece"
                    @selected($selectedUnit === 'Piece')
                >
                    Pièce
                </option>

                <option
                    value="Litre"
                    @selected($selectedUnit === 'Litre')
                >
                    Litre
                </option>
            </select>

            @error('unit')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- FOURNISSEUR --}}
        <div class="col-md-6">
            <label for="supplier_id" class="form-label">
                Fournisseur
            </label>

            <select
                name="supplier_id"
                id="supplier_id"
                class="form-select @error('supplier_id') is-invalid @enderror"
            >
                <option value="">
                    Aucun fournisseur sélectionné
                </option>

                @foreach($suppliers as $supplier)
                    <option
                        value="{{ $supplier->id }}"
                        @selected((string) $selectedSupplier === (string) $supplier->id)
                    >
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>

            @error('supplier_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
</div>

<div class="vpr-form-section">
    <div class="vpr-form-section-title">
        <i class="bx bx-purchase-tag"></i>
        Informations fournisseur et prix
    </div>

    <div class="row g-3">

        {{-- RÉFÉRENCE FOURNISSEUR --}}
        <div class="col-md-6">
            <label for="supplier_reference" class="form-label">
                Référence fournisseur
            </label>

            <input
                type="text"
                name="supplier_reference"
                id="supplier_reference"
                value="{{ old('supplier_reference', $vehiclePartRequest->supplier_reference ?? '') }}"
                class="form-control @error('supplier_reference') is-invalid @enderror"
                maxlength="255"
            >

            @error('supplier_reference')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- RÉFÉRENCE COMMANDE --}}
        <div class="col-md-6">
            <label for="order_reference" class="form-label">
                Référence de commande
            </label>

            <input
                type="text"
                name="order_reference"
                id="order_reference"
                value="{{ old('order_reference', $vehiclePartRequest->order_reference ?? '') }}"
                class="form-control @error('order_reference') is-invalid @enderror"
                maxlength="255"
            >

            @error('order_reference')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- PRIX ESTIMÉ --}}
        <div class="col-md-6">
            <label for="estimated_price" class="form-label">
                Prix estimé
            </label>

            <input
                type="number"
                name="estimated_price"
                id="estimated_price"
                step="0.01"
                min="0"
                value="{{ old('estimated_price', $vehiclePartRequest->estimated_price ?? '') }}"
                class="form-control @error('estimated_price') is-invalid @enderror"
            >

            @error('estimated_price')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- PRIX ACHAT --}}
        <div class="col-md-6">
            <label for="purchase_price" class="form-label">
                Prix d’achat
            </label>

            <input
                type="number"
                name="purchase_price"
                id="purchase_price"
                step="0.01"
                min="0"
                value="{{ old('purchase_price', $vehiclePartRequest->purchase_price ?? '') }}"
                class="form-control @error('purchase_price') is-invalid @enderror"
            >

            @error('purchase_price')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
</div>

<div class="vpr-form-section">
    <div class="vpr-form-section-title">
        <i class="bx bx-note"></i>
        Description et notes
    </div>

    <div class="row g-3">

        <div class="col-12">
            <label for="description" class="form-label">
                Description
            </label>

            <textarea
                name="description"
                id="description"
                rows="3"
                class="form-control @error('description') is-invalid @enderror"
            >{{ old('description', $vehiclePartRequest->description ?? '') }}</textarea>

            @error('description')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-12">
            <label for="notes" class="form-label">
                Notes
            </label>

            <textarea
                name="notes"
                id="notes"
                rows="3"
                class="form-control @error('notes') is-invalid @enderror"
            >{{ old('notes', $vehiclePartRequest->notes ?? '') }}</textarea>

            @error('notes')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const productSelect = document.getElementById('product_id');
    const referenceInput = document.getElementById('reference');
    const partNameInput = document.getElementById('part_name');
    const unitSelect = document.getElementById('unit');
    const productInfo = document.getElementById('selected_product_info');

    if (!productSelect) {
        return;
    }

    function updateSelectedProduct() {
        const selectedOption =
            productSelect.options[productSelect.selectedIndex];

        if (!selectedOption || !selectedOption.value) {
            if (productInfo) {
                productInfo.classList.remove('show');
                productInfo.innerHTML = '';
            }

            return;
        }

        const reference =
            selectedOption.getAttribute('data-reference') || '';

        const partName =
            selectedOption.getAttribute('data-name') || '';

        const unitType =
            selectedOption.getAttribute('data-unit-type') || 'piece';

        const unitLabel =
            selectedOption.getAttribute('data-unit-label') || 'Pièce';

        const availableQuantity =
            parseFloat(
                selectedOption.getAttribute('data-quantity') || 0
            );

        const status =
            selectedOption.getAttribute('data-status') || '';

        if (referenceInput && reference) {
            referenceInput.value = reference;
        }

        if (partNameInput && partName) {
            partNameInput.value = partName;
        }

        if (unitSelect) {
            unitSelect.value =
                unitType === 'litre'
                    ? 'Litre'
                    : 'Piece';
        }

        if (productInfo) {
            const statusText =
                availableQuantity > 0
                    ? 'Disponible'
                    : 'Non disponible';

            productInfo.innerHTML =
                '<strong>Stock actuel :</strong> ' +
                availableQuantity.toFixed(2) +
                ' ' +
                unitLabel +
                ' &nbsp; | &nbsp; ' +
                '<strong>État :</strong> ' +
                statusText;

            productInfo.classList.add('show');
        }
    }

    productSelect.addEventListener(
        'change',
        updateSelectedProduct
    );

    updateSelectedProduct();
});
</script>
@endpush