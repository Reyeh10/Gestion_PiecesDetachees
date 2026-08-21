@php

    /*
    |--------------------------------------------------------------------------
    | VALEURS ACTUELLES
    |--------------------------------------------------------------------------
    */

    $editingRequest =
        isset($vehiclePartRequest)
        && $vehiclePartRequest;

    $selectedVehicle = old(
        'vehicle_id',
        $vehiclePartRequest->vehicle_id
            ?? $selectedVehicleId
            ?? null
    );

    $selectedProduct = old(
        'product_id',
        $vehiclePartRequest->product_id
            ?? ''
    );

    $selectedSupplier = old(
        'supplier_id',
        $vehiclePartRequest->supplier_id
            ?? ''
    );

    $selectedUnit = old(
        'unit',
        $vehiclePartRequest->unit
            ?? 'Piece'
    );

@endphp


<style>

    /*
    |--------------------------------------------------------------------------
    | SECTIONS
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | TITRES
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | LABELS
    |--------------------------------------------------------------------------
    */

    .form-label {
        margin-bottom: 7px;

        font-size: 12px;
        font-weight: 800;

        color: #52657b;

        text-transform: uppercase;
        letter-spacing: .03em;
    }


    /*
    |--------------------------------------------------------------------------
    | INPUTS
    |--------------------------------------------------------------------------
    */

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

        box-shadow:
            0 0 0 3px rgba(105, 108, 255, .10);
    }

    textarea.form-control {
        min-height: auto;
    }


    /*
    |--------------------------------------------------------------------------
    | AIDE
    |--------------------------------------------------------------------------
    */

    .vpr-form-help {
        margin-top: 6px;

        font-size: 12px;

        color: #64748b;
    }


    /*
    |--------------------------------------------------------------------------
    | BARRES DE RECHERCHE
    |--------------------------------------------------------------------------
    */

    .vpr-search-wrapper {
        position: relative;

        margin-bottom: 8px;
    }

    .vpr-search-wrapper .vpr-search-icon {
        position: absolute;

        top: 50%;
        left: 14px;

        z-index: 5;

        transform: translateY(-50%);

        color: #94a3b8;

        font-size: 18px;

        pointer-events: none;
    }

    .vpr-search-wrapper .vpr-search-input {
        min-height: 44px;

        padding-left: 42px;
        padding-right: 38px;

        background: #f8fafc;
    }

    .vpr-search-wrapper .vpr-search-input:focus {
        background: #ffffff;
    }

    .vpr-search-clear {
        position: absolute;

        top: 50%;
        right: 10px;

        z-index: 6;

        transform: translateY(-50%);

        width: 28px;
        height: 28px;

        display: none;
        align-items: center;
        justify-content: center;

        padding: 0;

        border: none;
        border-radius: 50%;

        color: #64748b;
        background: transparent;

        cursor: pointer;
    }

    .vpr-search-clear:hover {
        color: #334155;
        background: #e2e8f0;
    }

    .vpr-search-clear.show {
        display: inline-flex;
    }


    /*
    |--------------------------------------------------------------------------
    | RÉSULTATS RECHERCHE
    |--------------------------------------------------------------------------
    */

    .vpr-search-result {
        display: none;

        margin-top: 5px;
        margin-bottom: 8px;

        font-size: 11px;

        color: #64748b;
    }

    .vpr-search-result.show {
        display: block;
    }

    .vpr-search-result.no-result {
        color: #dc2626;
    }


    /*
    |--------------------------------------------------------------------------
    | PRODUIT SÉLECTIONNÉ
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | BADGES STOCK
    |--------------------------------------------------------------------------
    */

    .vpr-stock-available {
        color: #15803d;
        font-weight: 700;
    }

    .vpr-stock-unavailable {
        color: #dc2626;
        font-weight: 700;
    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 767.98px) {

        .vpr-form-section {
            padding: 15px;
        }

    }

</style>


{{-- ============================================================= --}}
{{-- VÉHICULE ET PIÈCE --}}
{{-- ============================================================= --}}

<div class="vpr-form-section">

    <div class="vpr-form-section-title">

        <i class="bx bx-car"></i>

        Véhicule et pièce

    </div>


    <div class="row g-3">


        {{-- ===================================================== --}}
        {{-- VÉHICULE --}}
        {{-- ===================================================== --}}

        <div class="col-md-6">

            <label
                for="vehicle_id"
                class="form-label"
            >

                Véhicule

                <span class="text-danger">*</span>

            </label>


            {{-- BARRE RECHERCHE VÉHICULE --}}
            <div class="vpr-search-wrapper">

                <i
                    class="
                        bx
                        bx-search
                        vpr-search-icon
                    "
                ></i>

                <input
                    type="text"
                    id="vehicle_search"
                    class="
                        form-control
                        vpr-search-input
                    "
                    placeholder="Rechercher VIN, immatriculation, marque, modèle ou client..."
                    autocomplete="off"
                >

                <button
                    type="button"
                    id="vehicle_search_clear"
                    class="vpr-search-clear"
                    title="Effacer la recherche"
                >

                    <i class="bx bx-x"></i>

                </button>

            </div>


            {{-- COMPTEUR --}}
            <div
                id="vehicle_search_result"
                class="vpr-search-result"
            ></div>


            {{-- LISTE DES VÉHICULES --}}
            <select
                name="vehicle_id"
                id="vehicle_id"
                class="
                    form-select
                    @error('vehicle_id')
                        is-invalid
                    @enderror
                "
                required
            >

                <option value="">

                    Sélectionner un véhicule

                </option>


                @foreach($vehicles as $vehicle)

                    <option
                        value="{{ $vehicle->id }}"

                        data-vin="{{ $vehicle->vin ?? '' }}"

                        data-plate="{{ $vehicle->plate_number ?? '' }}"

                        data-brand="{{ $vehicle->brand ?? '' }}"

                        data-model="{{ $vehicle->model ?? '' }}"

                        data-customer="{{ $vehicle->customer->name ?? '' }}"

                        @selected(
                            (string) $selectedVehicle
                            ===
                            (string) $vehicle->id
                        )
                    >

                        {{
                            $vehicle->plate_number
                            ?? $vehicle->vin
                            ?? 'Sans immatriculation'
                        }}

                        @if($vehicle->customer)

                            - {{ $vehicle->customer->name }}

                        @endif

                        @if(
                            $vehicle->brand
                            ||
                            $vehicle->model
                        )

                            -
                            {{ $vehicle->brand ?? '' }}
                            {{ $vehicle->model ?? '' }}

                        @endif

                    </option>

                @endforeach

            </select>


            @error('vehicle_id')

                <div class="invalid-feedback">

                    {{ $message }}

                </div>

            @enderror


            <div class="vpr-form-help">

                Recherche par VIN, immatriculation,
                marque, modèle ou nom du client.

            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- PRODUIT EXISTANT --}}
        {{-- ===================================================== --}}

        <div class="col-md-6">

            <label
                for="product_id"
                class="form-label"
            >

                Produit existant

            </label>


            {{-- BARRE RECHERCHE PRODUIT --}}
            <div class="vpr-search-wrapper">

                <i
                    class="
                        bx
                        bx-search
                        vpr-search-icon
                    "
                ></i>

                <input
                    type="text"
                    id="product_search"
                    class="
                        form-control
                        vpr-search-input
                    "
                    placeholder="Rechercher par référence ou désignation..."
                    autocomplete="off"
                >

                <button
                    type="button"
                    id="product_search_clear"
                    class="vpr-search-clear"
                    title="Effacer la recherche"
                >

                    <i class="bx bx-x"></i>

                </button>

            </div>


            {{-- COMPTEUR --}}
            <div
                id="product_search_result"
                class="vpr-search-result"
            ></div>


            {{-- LISTE PRODUITS --}}
            <select
                name="product_id"
                id="product_id"
                class="
                    form-select
                    @error('product_id')
                        is-invalid
                    @enderror
                "
            >

                <option value="">

                    La pièce n’existe pas encore dans le catalogue

                </option>


                @foreach($products as $product)

                    @php

                        $productAvailableQty =
                            (float) (
                                $product->quantity
                                ?? 0
                            );

                        $productUnit =
                            $product->unit_label
                            ?: 'Pièce';

                    @endphp


                    <option
                        value="{{ $product->id }}"

                        data-reference="{{ $product->reference ?? '' }}"

                        data-name="{{
                            $product->designation
                            ?? $product->name
                            ?? ''
                        }}"

                        data-unit-type="{{
                            $product->unit_type
                            ?? 'piece'
                        }}"

                        data-unit-label="{{ $productUnit }}"

                        data-quantity="{{ $productAvailableQty }}"

                        data-status="{{ $product->status ?? '' }}"

                        @selected(
                            (string) $selectedProduct
                            ===
                            (string) $product->id
                        )
                    >

                        {{
                            $product->reference
                            ?? 'Sans référence'
                        }}

                        -

                        {{
                            $product->designation
                            ?? $product->name
                            ?? ''
                        }}

                    </option>

                @endforeach

            </select>


            @error('product_id')

                <div class="invalid-feedback">

                    {{ $message }}

                </div>

            @enderror


            <div class="vpr-form-help">

                Facultatif. Recherchez une pièce par
                référence ou désignation.

            </div>


            <div
                id="selected_product_info"
                class="selected-product-info"
            ></div>

        </div>


        {{-- ===================================================== --}}
        {{-- RÉFÉRENCE PIÈCE --}}
        {{-- ===================================================== --}}

        <div class="col-md-4">

            <label
                for="reference"
                class="form-label"
            >

                Référence de la pièce

            </label>

            <input
                type="text"
                name="reference"
                id="reference"
                value="{{
                    old(
                        'reference',
                        $vehiclePartRequest->reference
                            ?? ''
                    )
                }}"
                class="
                    form-control
                    @error('reference')
                        is-invalid
                    @enderror
                "
                maxlength="255"
            >

            @error('reference')

                <div class="invalid-feedback">

                    {{ $message }}

                </div>

            @enderror

        </div>


        {{-- ===================================================== --}}
        {{-- NOM PIÈCE --}}
        {{-- ===================================================== --}}

        <div class="col-md-8">

            <label
                for="part_name"
                class="form-label"
            >

                Nom de la pièce

                <span class="text-danger">*</span>

            </label>

            <input
                type="text"
                name="part_name"
                id="part_name"
                value="{{
                    old(
                        'part_name',
                        $vehiclePartRequest->part_name
                            ?? ''
                    )
                }}"
                class="
                    form-control
                    @error('part_name')
                        is-invalid
                    @enderror
                "
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


        {{-- ===================================================== --}}
        {{-- QUANTITÉ --}}
        {{-- ===================================================== --}}

        <div class="col-md-3">

            <label
                for="quantity"
                class="form-label"
            >

                Quantité demandée

                <span class="text-danger">*</span>

            </label>

            <input
                type="number"
                name="quantity"
                id="quantity"
                min="0.01"
                step="0.01"
                value="{{
                    old(
                        'quantity',
                        $vehiclePartRequest->quantity
                            ?? 1
                    )
                }}"
                class="
                    form-control
                    @error('quantity')
                        is-invalid
                    @enderror
                "
                required
            >

            @error('quantity')

                <div class="invalid-feedback">

                    {{ $message }}

                </div>

            @enderror

        </div>


        {{-- ===================================================== --}}
        {{-- UNITÉ --}}
        {{-- ===================================================== --}}

        <div class="col-md-3">

            <label
                for="unit"
                class="form-label"
            >

                Unité

                <span class="text-danger">*</span>

            </label>

            <select
                name="unit"
                id="unit"
                class="
                    form-select
                    @error('unit')
                        is-invalid
                    @enderror
                "
                required
            >

                <option
                    value="Piece"
                    @selected(
                        $selectedUnit === 'Piece'
                    )
                >

                    Pièce

                </option>

                <option
                    value="Litre"
                    @selected(
                        $selectedUnit === 'Litre'
                    )
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


        {{-- ===================================================== --}}
        {{-- FOURNISSEUR --}}
        {{-- ===================================================== --}}

        <div class="col-md-6">

            <label
                for="supplier_id"
                class="form-label"
            >

                Fournisseur

            </label>

            <select
                name="supplier_id"
                id="supplier_id"
                class="
                    form-select
                    @error('supplier_id')
                        is-invalid
                    @enderror
                "
            >

                <option value="">

                    Aucun fournisseur sélectionné

                </option>


                @foreach($suppliers as $supplier)

                    <option
                        value="{{ $supplier->id }}"
                        @selected(
                            (string) $selectedSupplier
                            ===
                            (string) $supplier->id
                        )
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


{{-- ============================================================= --}}
{{-- INFORMATIONS FOURNISSEUR ET PRIX --}}
{{-- ============================================================= --}}

<!--div class="vpr-form-section">

    <div class="vpr-form-section-title">

        <i class="bx bx-purchase-tag"></i>

        Informations fournisseur et prix

    </div>


    <div class="row g-3"0>


        {{-- ===================================================== --}}
        {{-- RÉFÉRENCE FOURNISSEUR --}}
        {{-- ===================================================== --}}

        <div class="col-md-6">

            <label
                for="supplier_reference"
                class="form-label"
            >

                Référence fournisseur

            </label>

            <input
                type="text"
                name="supplier_reference"
                id="supplier_reference"
                value="{ {
                    old(
                        'supplier_reference',
                        $vehiclePartRequest->supplier_reference
                            ?? ''
                    )
                }}"
                class="
                    form-control
                    @ error('supplier_reference')
                        is-invalid
                    @ enderror
                "
                maxlength="255"
            >

            @ error('supplier_reference')

                <div class="invalid-feedback">

                    { { $message }}

                </div>

            @ enderror

        </div>


        {{-- ===================================================== --}}
        {{-- RÉFÉRENCE COMMANDE --}}
        {{-- ===================================================== --}}

        <div class="col-md-6">

            <label
                for="order_reference"
                class="form-label"
            >

                Référence de commande

            </label>

            <input
                type="text"
                name="order_reference"
                id="order_reference"
                value="{ {
                    old(
                        'order_reference',
                        $vehiclePartRequest->order_reference
                            ?? ''
                    )
                }}"
                class="
                    form-control
                    @ error('order_reference')
                        is-invalid
                    @ enderror
                "
                maxlength="255"
            >

            @ error('order_reference')

                <div class="invalid-feedback">

                    { { $message }}

                </div>

            @ enderror

        </div>


        {{-- ===================================================== --}}
        {{-- PRIX ESTIMÉ --}}
        {{-- ===================================================== --}}

        <div class="col-md-6">

            <label
                for="estimated_price"
                class="form-label"
            >

                Prix estimé

            </label>

            <input
                type="number"
                name="estimated_price"
                id="estimated_price"
                step="0.01"
                min="0"
                value="{ {
                    old(
                        'estimated_price',
                        $vehiclePartRequest->estimated_price
                            ?? ''
                    )
                }}"
                class="
                    form-control
                    @ error('estimated_price')
                        is-invalid
                    @ enderror
                "
            >

            @ error('estimated_price')

                <div class="invalid-feedback">

                    { { $message }}

                </div>

            @ enderror

        </div>


        {{-- ===================================================== --}}
        {{-- PRIX ACHAT --}}
        {{-- ===================================================== --}}

        <div class="col-md-6">

            <label
                for="purchase_price"
                class="form-label"
            >

                Prix d’achat

            </label>

            <input
                type="number"
                name="purchase_price"
                id="purchase_price"
                step="0.01"
                min="0"
                value="{ {
                    old(
                        'purchase_price',
                        $vehiclePartRequest->purchase_price
                            ?? ''
                    )
                }}"
                class="
                    form-control
                    @ error('purchase_price')
                        is-invalid
                    @ enderror
                "
            >

            @ error('purchase_price')

                <div class="invalid-feedback">

                    { { $message }}

                </div>

            @ enderror

        </div>

    </div>

</div-->


{{-- ============================================================= --}}
{{-- DESCRIPTION ET NOTES --}}
{{-- ============================================================= --}}

<div class="vpr-form-section">

    <div class="vpr-form-section-title">

        <i class="bx bx-note"></i>

        Description et notes

    </div>


    <div class="row g-3">


        {{-- ===================================================== --}}
        {{-- DESCRIPTION --}}
        {{-- ===================================================== --}}

        <div class="col-12">

            <label
                for="description"
                class="form-label"
            >

                Description

            </label>

            <textarea
                name="description"
                id="description"
                rows="3"
                class="
                    form-control
                    @error('description')
                        is-invalid
                    @enderror
                "
            >{{ old(
                'description',
                $vehiclePartRequest->description
                    ?? ''
            ) }}</textarea>


            @error('description')

                <div class="invalid-feedback">

                    {{ $message }}

                </div>

            @enderror

        </div>


        {{-- ===================================================== --}}
        {{-- NOTES --}}
        {{-- ===================================================== --}}

        <div class="col-12">

            <label
                for="notes"
                class="form-label"
            >

                Notes

            </label>

            <textarea
                name="notes"
                id="notes"
                rows="3"
                class="
                    form-control
                    @error('notes')
                        is-invalid
                    @enderror
                "
            >{{ old(
                'notes',
                $vehiclePartRequest->notes
                    ?? ''
            ) }}</textarea>


            @error('notes')

                <div class="invalid-feedback">

                    {{ $message }}

                </div>

            @enderror

        </div>

    </div>

</div>


{{-- ============================================================= --}}
{{-- JAVASCRIPT --}}
{{-- ============================================================= --}}

@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | ÉLÉMENTS VÉHICULE
        |--------------------------------------------------------------------------
        */

        const vehicleSearch =
            document.getElementById(
                'vehicle_search'
            );

        const vehicleSearchClear =
            document.getElementById(
                'vehicle_search_clear'
            );

        const vehicleSearchResult =
            document.getElementById(
                'vehicle_search_result'
            );

        const vehicleSelect =
            document.getElementById(
                'vehicle_id'
            );


        /*
        |--------------------------------------------------------------------------
        | ÉLÉMENTS PRODUIT
        |--------------------------------------------------------------------------
        */

        const productSearch =
            document.getElementById(
                'product_search'
            );

        const productSearchClear =
            document.getElementById(
                'product_search_clear'
            );

        const productSearchResult =
            document.getElementById(
                'product_search_result'
            );

        const productSelect =
            document.getElementById(
                'product_id'
            );


        /*
        |--------------------------------------------------------------------------
        | AUTRES CHAMPS
        |--------------------------------------------------------------------------
        */

        const referenceInput =
            document.getElementById(
                'reference'
            );

        const partNameInput =
            document.getElementById(
                'part_name'
            );

        const unitSelect =
            document.getElementById(
                'unit'
            );

        const productInfo =
            document.getElementById(
                'selected_product_info'
            );


        /*
        |--------------------------------------------------------------------------
        | NORMALISER TEXTE
        |--------------------------------------------------------------------------
        |
        | Permet par exemple :
        |
        | "Pièce" = "piece"
        |
        */

        function normalizeText(value) {

            return String(value || '')
                .normalize('NFD')
                .replace(
                    /[\u0300-\u036f]/g,
                    ''
                )
                .toLowerCase()
                .trim();
        }


        /*
        |--------------------------------------------------------------------------
        | OPTIONS VÉHICULE ORIGINALES
        |--------------------------------------------------------------------------
        */

        const originalVehicleOptions =
            vehicleSelect
                ? Array
                    .from(
                        vehicleSelect.options
                    )
                    .slice(1)
                    .map(
                        option =>
                            option.cloneNode(true)
                    )
                : [];


        /*
        |--------------------------------------------------------------------------
        | OPTIONS PRODUIT ORIGINALES
        |--------------------------------------------------------------------------
        */

        const originalProductOptions =
            productSelect
                ? Array
                    .from(
                        productSelect.options
                    )
                    .slice(1)
                    .map(
                        option =>
                            option.cloneNode(true)
                    )
                : [];


        /*
        |--------------------------------------------------------------------------
        | RECONSTRUIRE UN SELECT
        |--------------------------------------------------------------------------
        */

        function rebuildSelect(
            selectElement,
            originalOptions,
            searchValue,
            matcher,
            firstOptionText,
            resultElement
        ) {

            if (!selectElement) {
                return 0;
            }


            /*
            |--------------------------------------------------------------------------
            | VALEUR ACTUELLE
            |--------------------------------------------------------------------------
            */

            const currentValue =
                String(
                    selectElement.value
                    || ''
                );


            /*
            |--------------------------------------------------------------------------
            | RECHERCHE NORMALISÉE
            |--------------------------------------------------------------------------
            */

            const search =
                normalizeText(
                    searchValue
                );


            /*
            |--------------------------------------------------------------------------
            | FILTRAGE
            |--------------------------------------------------------------------------
            */

            const filteredOptions =
                originalOptions.filter(
                    function (option) {

                        if (search === '') {

                            return true;
                        }

                        return matcher(
                            option,
                            search
                        );
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | VIDER SELECT
            |--------------------------------------------------------------------------
            */

            selectElement.innerHTML = '';


            /*
            |--------------------------------------------------------------------------
            | PREMIÈRE OPTION
            |--------------------------------------------------------------------------
            */

            const firstOption =
                new Option(
                    firstOptionText,
                    '',
                    false,
                    false
                );

            selectElement.add(
                firstOption
            );


            /*
            |--------------------------------------------------------------------------
            | AJOUTER OPTIONS FILTRÉES
            |--------------------------------------------------------------------------
            */

            filteredOptions.forEach(
                function (option) {

                    selectElement.add(
                        option.cloneNode(
                            true
                        )
                    );
                }
            );


            /*
            |--------------------------------------------------------------------------
            | RESTAURER SÉLECTION
            |--------------------------------------------------------------------------
            */

            const currentStillExists =
                Array
                    .from(
                        selectElement.options
                    )
                    .some(
                        option =>
                            String(option.value)
                            ===
                            currentValue
                    );


            if (currentStillExists) {

                selectElement.value =
                    currentValue;

            } else {

                selectElement.value =
                    '';
            }


            /*
            |--------------------------------------------------------------------------
            | AFFICHAGE NOMBRE RÉSULTATS
            |--------------------------------------------------------------------------
            */

            if (resultElement) {

                resultElement
                    .classList
                    .remove(
                        'show',
                        'no-result'
                    );


                if (search !== '') {

                    const count =
                        filteredOptions.length;

                    if (count === 0) {

                        resultElement.textContent =
                            'Aucun résultat trouvé.';

                        resultElement
                            .classList
                            .add(
                                'show',
                                'no-result'
                            );

                    } else {

                        resultElement.textContent =
                            count
                            +
                            (
                                count > 1
                                    ? ' résultats trouvés'
                                    : ' résultat trouvé'
                            );

                        resultElement
                            .classList
                            .add('show');
                    }

                } else {

                    resultElement.textContent =
                        '';
                }
            }


            return filteredOptions.length;
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRER LES VÉHICULES
        |--------------------------------------------------------------------------
        */

        function filterVehicles() {

            if (
                !vehicleSearch
                ||
                !vehicleSelect
            ) {
                return;
            }


            rebuildSelect(
                vehicleSelect,

                originalVehicleOptions,

                vehicleSearch.value,

                function (
                    option,
                    search
                ) {

                    const text =
                        normalizeText(
                            [
                                option.textContent,
                                option.dataset.vin,
                                option.dataset.plate,
                                option.dataset.brand,
                                option.dataset.model,
                                option.dataset.customer
                            ].join(' ')
                        );

                    return text.includes(
                        search
                    );
                },

                'Sélectionner un véhicule',

                vehicleSearchResult
            );


            /*
            |--------------------------------------------------------------------------
            | BOUTON EFFACER
            |--------------------------------------------------------------------------
            */

            if (vehicleSearchClear) {

                vehicleSearchClear
                    .classList
                    .toggle(
                        'show',
                        vehicleSearch.value !== ''
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRER LES PRODUITS
        |--------------------------------------------------------------------------
        */

        function filterProducts() {

            if (
                !productSearch
                ||
                !productSelect
            ) {
                return;
            }


            rebuildSelect(
                productSelect,

                originalProductOptions,

                productSearch.value,

                function (
                    option,
                    search
                ) {

                    const text =
                        normalizeText(
                            [
                                option.dataset.reference,
                                option.dataset.name,
                                option.textContent
                            ].join(' ')
                        );

                    return text.includes(
                        search
                    );
                },

                'La pièce n’existe pas encore dans le catalogue',

                productSearchResult
            );


            /*
            |--------------------------------------------------------------------------
            | BOUTON EFFACER
            |--------------------------------------------------------------------------
            */

            if (productSearchClear) {

                productSearchClear
                    .classList
                    .toggle(
                        'show',
                        productSearch.value !== ''
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | INFORMATION PRODUIT SÉLECTIONNÉ
        |--------------------------------------------------------------------------
        */

        function updateSelectedProduct() {

            if (!productSelect) {
                return;
            }


            const selectedOption =
                productSelect.options[
                    productSelect.selectedIndex
                ];


            /*
            |--------------------------------------------------------------------------
            | AUCUN PRODUIT
            |--------------------------------------------------------------------------
            */

            if (
                !selectedOption
                ||
                !selectedOption.value
            ) {

                if (productInfo) {

                    productInfo.innerHTML =
                        '';

                    productInfo
                        .classList
                        .remove('show');
                }

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | DONNÉES
            |--------------------------------------------------------------------------
            */

            const reference =
                selectedOption
                    .getAttribute(
                        'data-reference'
                    )
                || '';

            const partName =
                selectedOption
                    .getAttribute(
                        'data-name'
                    )
                || '';

            const unitType =
                selectedOption
                    .getAttribute(
                        'data-unit-type'
                    )
                || 'piece';

            const unitLabel =
                selectedOption
                    .getAttribute(
                        'data-unit-label'
                    )
                || 'Pièce';

            const availableQuantity =
                parseFloat(
                    selectedOption
                        .getAttribute(
                            'data-quantity'
                        )
                    || '0'
                );


            /*
            |--------------------------------------------------------------------------
            | RÉFÉRENCE
            |--------------------------------------------------------------------------
            */

            if (referenceInput) {

                referenceInput.value =
                    reference;
            }


            /*
            |--------------------------------------------------------------------------
            | NOM PIÈCE
            |--------------------------------------------------------------------------
            */

            if (partNameInput) {

                partNameInput.value =
                    partName;
            }


            /*
            |--------------------------------------------------------------------------
            | UNITÉ
            |--------------------------------------------------------------------------
            */

            if (unitSelect) {

                unitSelect.value =
                    normalizeText(
                        unitType
                    ) === 'litre'
                        ? 'Litre'
                        : 'Piece';

                unitSelect
                    .dispatchEvent(
                        new Event(
                            'change',
                            {
                                bubbles: true
                            }
                        )
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | STOCK
            |--------------------------------------------------------------------------
            */

            if (productInfo) {

                const isAvailable =
                    availableQuantity > 0;

                const statusText =
                    isAvailable
                        ? 'Disponible'
                        : 'Non disponible';

                const statusClass =
                    isAvailable
                        ? 'vpr-stock-available'
                        : 'vpr-stock-unavailable';


                productInfo.innerHTML =

                    '<strong>Stock actuel :</strong> '

                    +

                    availableQuantity.toFixed(2)

                    +

                    ' '

                    +

                    unitLabel

                    +

                    ' &nbsp; | &nbsp; '

                    +

                    '<strong>État :</strong> '

                    +

                    '<span class="'
                    +
                    statusClass
                    +
                    '">'
                    +
                    statusText
                    +
                    '</span>';


                productInfo
                    .classList
                    .add('show');
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ÉVÉNEMENT RECHERCHE VÉHICULE
        |--------------------------------------------------------------------------
        */

        if (vehicleSearch) {

            vehicleSearch.addEventListener(
                'input',
                filterVehicles
            );
        }


        /*
        |--------------------------------------------------------------------------
        | EFFACER RECHERCHE VÉHICULE
        |--------------------------------------------------------------------------
        */

        if (
            vehicleSearchClear
            &&
            vehicleSearch
        ) {

            vehicleSearchClear
                .addEventListener(
                    'click',
                    function () {

                        vehicleSearch.value =
                            '';

                        filterVehicles();

                        vehicleSearch.focus();
                    }
                );
        }


        /*
        |--------------------------------------------------------------------------
        | ÉVÉNEMENT RECHERCHE PRODUIT
        |--------------------------------------------------------------------------
        */

        if (productSearch) {

            productSearch.addEventListener(
                'input',
                filterProducts
            );
        }


        /*
        |--------------------------------------------------------------------------
        | EFFACER RECHERCHE PRODUIT
        |--------------------------------------------------------------------------
        */

        if (
            productSearchClear
            &&
            productSearch
        ) {

            productSearchClear
                .addEventListener(
                    'click',
                    function () {

                        productSearch.value =
                            '';

                        filterProducts();

                        productSearch.focus();
                    }
                );
        }


        /*
        |--------------------------------------------------------------------------
        | CHANGEMENT PRODUIT
        |--------------------------------------------------------------------------
        */

        if (productSelect) {

            productSelect.addEventListener(
                'change',
                updateSelectedProduct
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ENTRÉE :
        | SÉLECTION AUTOMATIQUE DU VÉHICULE
        |--------------------------------------------------------------------------
        */

        if (
            vehicleSearch
            &&
            vehicleSelect
        ) {

            vehicleSearch.addEventListener(
                'keydown',
                function (event) {

                    if (
                        event.key !== 'Enter'
                    ) {
                        return;
                    }


                    const results =
                        Array
                            .from(
                                vehicleSelect.options
                            )
                            .filter(
                                option =>
                                    option.value !== ''
                            );


                    if (
                        results.length === 1
                    ) {

                        event.preventDefault();

                        vehicleSelect.value =
                            results[0].value;

                        vehicleSelect
                            .dispatchEvent(
                                new Event(
                                    'change',
                                    {
                                        bubbles: true
                                    }
                                )
                            );
                    }
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ENTRÉE :
        | SÉLECTION AUTOMATIQUE DU PRODUIT
        |--------------------------------------------------------------------------
        */

        if (
            productSearch
            &&
            productSelect
        ) {

            productSearch.addEventListener(
                'keydown',
                function (event) {

                    if (
                        event.key !== 'Enter'
                    ) {
                        return;
                    }


                    const results =
                        Array
                            .from(
                                productSelect.options
                            )
                            .filter(
                                option =>
                                    option.value !== ''
                            );


                    if (
                        results.length === 1
                    ) {

                        event.preventDefault();

                        productSelect.value =
                            results[0].value;

                        productSelect
                            .dispatchEvent(
                                new Event(
                                    'change',
                                    {
                                        bubbles: true
                                    }
                                )
                            );
                    }
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | INITIALISATION
        |--------------------------------------------------------------------------
        */

        filterVehicles();

        filterProducts();

        updateSelectedProduct();

    }
);

</script>

@endpush