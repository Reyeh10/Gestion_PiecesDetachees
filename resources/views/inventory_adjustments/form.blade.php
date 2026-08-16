@php

    /*
    |--------------------------------------------------------------------------
    | MODE DU FORMULAIRE
    |--------------------------------------------------------------------------
    |
    | false = création / modification possible
    | true  = consultation uniquement
    |
    */

    $readonly = $readonly ?? false;

@endphp


{{-- ================================================================
    MESSAGES
================================================================ --}}

@if(!$readonly)

    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="bx bx-error-circle me-1"></i>

            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    @if($errors->any())

        <div class="alert alert-danger">

            <strong>
                Veuillez corriger les erreurs suivantes :
            </strong>

            <ul class="mb-0 mt-2">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif

@endif


{{-- ================================================================
    INFORMATIONS AJUSTEMENT
================================================================ --}}

<div class="row g-3">

    {{-- ============================================================
        PRODUIT
    ============================================================ --}}

    <div class="col-lg-5 col-md-6">

        <label class="form-label fw-semibold">

            Produit

            @if(!$readonly)
                <span class="text-danger">*</span>
            @endif

        </label>


        @if($readonly)

            {{-- MODE CONSULTATION --}}

            <input
                type="text"
                class="form-control bg-light"
                value="{{ ($inventoryAdjustment->product->reference ?? '-') . ' - ' . ($inventoryAdjustment->product->designation ?? '-') }}"
                readonly
            >

        @else

            {{-- MODE CRÉATION --}}

            <select
                name="product_id"
                id="product_id"
                class="form-select"
                required
            >

                <option value="">
                    -- Sélectionner un produit --
                </option>

                @foreach($products as $product)

                    <option
                        value="{{ $product->id }}"
                        data-quantity="{{ (int) ($product->quantity ?? 0) }}"

                        {{ old(
                            'product_id',
                            $inventoryAdjustment->product_id ?? ''
                        ) == $product->id ? 'selected' : '' }}
                    >

                        {{ $product->reference ?? '-' }}
                        -
                        {{ $product->designation ?? '-' }}

                    </option>

                @endforeach

            </select>


            @error('product_id')

                <div class="text-danger small mt-1">

                    {{ $message }}

                </div>

            @enderror

        @endif

    </div>


    {{-- ============================================================
        ANCIEN STOCK / QUANTITÉ ACTUELLE
    ============================================================ --}}

    <div class="col-lg-2 col-md-3">

        <label class="form-label fw-semibold">

            @if($readonly)
                Ancien stock
            @else
                Quantité actuelle
            @endif

        </label>


        <input
            type="number"
            id="old_qty_display"
            class="form-control bg-light fw-bold"

            value="{{
                $readonly
                    ? (int) ($inventoryAdjustment->old_qty ?? 0)
                    : 0
            }}"

            readonly
        >


        <small class="text-muted">

            @if($readonly)

                Stock avant ajustement

            @else

                Stock système

            @endif

        </small>

    </div>


    {{-- ============================================================
        NOUVELLE QUANTITÉ
    ============================================================ --}}

    <div class="col-lg-2 col-md-3">

        <label class="form-label fw-semibold">

            Nouvelle quantité

            @if(!$readonly)
                <span class="text-danger">*</span>
            @endif

        </label>


        @if($readonly)

            <input
                type="number"
                class="form-control bg-light fw-bold"
                value="{{ (int) ($inventoryAdjustment->new_qty ?? 0) }}"
                readonly
            >

            <small class="text-muted">
                Stock après ajustement
            </small>

        @else

            <input
                type="number"
                min="0"
                step="1"
                name="new_qty"
                id="new_qty"
                class="form-control"
                value="{{ old(
                    'new_qty',
                    $inventoryAdjustment->new_qty ?? ''
                ) }}"
                placeholder="0"
                required
            >

            <small class="text-muted">
                Quantité réellement comptée
            </small>


            @error('new_qty')

                <div class="text-danger small mt-1">

                    {{ $message }}

                </div>

            @enderror

        @endif

    </div>


    {{-- ============================================================
        DIFFÉRENCE
    ============================================================ --}}

    <div class="col-lg-3 col-md-4">

        <label class="form-label fw-semibold">
            Différence
        </label>


        @if($readonly)

            @php

                $difference =
                    (int) ($inventoryAdjustment->new_qty ?? 0)
                    -
                    (int) ($inventoryAdjustment->old_qty ?? 0);

            @endphp


            <div
                class="
                    form-control
                    bg-light
                    fw-bold

                    @if($difference > 0)
                        text-success
                        border-success
                    @elseif($difference < 0)
                        text-danger
                        border-danger
                    @endif
                "
            >

                @if($difference > 0)

                    +{{ $difference }}

                @else

                    {{ $difference }}

                @endif

            </div>


            @if($difference > 0)

                <small class="text-success fw-semibold">

                    Entrée de stock :
                    +{{ $difference }}

                </small>

            @elseif($difference < 0)

                <small class="text-danger fw-semibold">

                    Sortie de stock :
                    {{ abs($difference) }}

                </small>

            @else

                <small class="text-muted">

                    Aucun changement de stock

                </small>

            @endif


        @else

            <div
                id="differenceBox"
                class="form-control bg-light fw-bold"
            >

                0

            </div>


            <small
                id="differenceText"
                class="text-muted"
            >

                Aucun changement

            </small>

        @endif

    </div>


    {{-- ============================================================
        RAISON
    ============================================================ --}}

    <div class="col-12">

        <label class="form-label fw-semibold">

            Raison de l'ajustement

            @if(!$readonly)
                <span class="text-danger">*</span>
            @endif

        </label>


        @if($readonly)

            <textarea
                rows="4"
                class="form-control bg-light"
                readonly
            >{{ $inventoryAdjustment->reason ?? '' }}</textarea>

        @else

            <textarea
                name="reason"
                id="reason"
                rows="4"
                maxlength="1000"
                class="form-control"
                placeholder="Exemple : différence constatée pendant l'inventaire physique..."
                required
            >{{ old(
                'reason',
                $inventoryAdjustment->reason ?? ''
            ) }}</textarea>


            @error('reason')

                <div class="text-danger small mt-1">

                    {{ $message }}

                </div>

            @enderror

        @endif

    </div>

</div>


{{-- ================================================================
    LISTE DES PRODUITS
    UNIQUEMENT EN MODE CRÉATION
================================================================ --}}

@if(!$readonly)

    <div class="card shadow-sm border-0 mt-4">

        <div class="card-header bg-white border-bottom">

            <div class="row align-items-center g-3">

                <div class="col-md-7">

                    <h5 class="mb-1 fw-bold">

                        <i class="bx bx-package me-1"></i>

                        Produits et quantités actuelles

                    </h5>


                    <small class="text-muted">

                        {{ $products->count() }} produit(s)

                    </small>

                </div>


                <div class="col-md-5">

                    <div class="input-group">

                        <span class="input-group-text">

                            <i class="bx bx-search"></i>

                        </span>


                        <input
                            type="text"
                            id="productSearch"
                            class="form-control"
                            placeholder="Rechercher par référence, désignation, marque..."
                        >

                    </div>

                </div>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table
                    class="table table-hover table-bordered align-middle mb-0"
                    id="productsTable"
                >

                    <thead class="table-light">

                        <tr>

                            <th style="width: 60px;">
                                #
                            </th>

                            <th>
                                Référence
                            </th>

                            <th>
                                Désignation
                            </th>

                            <th>
                                Marque
                            </th>

                            <th>
                                Modèle
                            </th>

                            <th class="text-center">
                                Quantité actuelle
                            </th>

                            <th
                                class="text-center"
                                style="width: 130px;"
                            >
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($products as $product)

                            @php

                                $quantity =
                                    (int) ($product->quantity ?? 0);

                            @endphp


                            <tr class="product-row">

                                <td>

                                    {{ $loop->iteration }}

                                </td>


                                <td>

                                    <strong>

                                        {{ $product->reference ?? '-' }}

                                    </strong>

                                </td>


                                <td>

                                    {{ $product->designation ?? '-' }}

                                </td>


                                <td>

                                    {{ $product->brand->name ?? '-' }}

                                </td>


                                <td>

                                    {{ $product->model->name ?? 'Non défini' }}

                                </td>


                                <td class="text-center">

                                    @if($quantity <= 0)

                                        <span class="badge bg-danger">

                                            {{ $quantity }}

                                        </span>

                                    @elseif($quantity <= 5)

                                        <span class="badge bg-warning text-dark">

                                            {{ $quantity }}

                                        </span>

                                    @else

                                        <span class="badge bg-success">

                                            {{ $quantity }}

                                        </span>

                                    @endif

                                </td>


                                <td class="text-center">

                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm select-product"
                                        data-product-id="{{ $product->id }}"
                                    >

                                        <i class="bx bx-check me-1"></i>

                                        Choisir

                                    </button>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="7"
                                    class="text-center py-4 text-muted"
                                >

                                    Aucun produit disponible.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endif


{{-- ================================================================
    JAVASCRIPT
    UNIQUEMENT EN MODE CRÉATION
================================================================ --}}

@if(!$readonly)

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ÉLÉMENTS
    |--------------------------------------------------------------------------
    */

    const productSelect =
        document.getElementById('product_id');

    const oldQtyInput =
        document.getElementById('old_qty_display');

    const newQtyInput =
        document.getElementById('new_qty');

    const differenceBox =
        document.getElementById('differenceBox');

    const differenceText =
        document.getElementById('differenceText');

    const productSearch =
        document.getElementById('productSearch');


    /*
    |--------------------------------------------------------------------------
    | QUANTITÉ ACTUELLE
    |--------------------------------------------------------------------------
    */

    function updateCurrentQuantity()
    {
        if (!productSelect) {
            return;
        }


        const selectedOption =
            productSelect.options[
                productSelect.selectedIndex
            ];


        if (
            !selectedOption ||
            !selectedOption.value
        ) {

            if (oldQtyInput) {

                oldQtyInput.value = 0;

            }

            calculateDifference();

            return;
        }


        const quantity =
            parseInt(
                selectedOption.dataset.quantity || 0
            );


        if (oldQtyInput) {

            oldQtyInput.value =
                quantity;

        }


        calculateDifference();
    }


    /*
    |--------------------------------------------------------------------------
    | CALCUL DIFFÉRENCE
    |--------------------------------------------------------------------------
    */

    function calculateDifference()
    {
        if (
            !oldQtyInput ||
            !newQtyInput ||
            !differenceBox ||
            !differenceText
        ) {
            return;
        }


        const oldQty =
            parseInt(
                oldQtyInput.value || 0
            );


        const newQtyValue =
            newQtyInput.value;


        if (newQtyValue === '') {

            differenceBox.textContent = '0';

            differenceBox.className =
                'form-control bg-light fw-bold';

            differenceText.textContent =
                'Saisissez la quantité réellement comptée';

            differenceText.className =
                'text-muted';

            return;
        }


        const newQty =
            parseInt(
                newQtyValue || 0
            );


        const difference =
            newQty - oldQty;


        /*
        |--------------------------------------------------------------------------
        | ENTRÉE STOCK
        |--------------------------------------------------------------------------
        */

        if (difference > 0) {

            differenceBox.textContent =
                '+' + difference;


            differenceBox.className =
                'form-control fw-bold text-success border-success';


            differenceText.textContent =
                'Entrée de stock : +' +
                difference;


            differenceText.className =
                'text-success fw-semibold';

        }

        /*
        |--------------------------------------------------------------------------
        | SORTIE STOCK
        |--------------------------------------------------------------------------
        */

        else if (difference < 0) {

            differenceBox.textContent =
                difference;


            differenceBox.className =
                'form-control fw-bold text-danger border-danger';


            differenceText.textContent =
                'Sortie de stock : ' +
                Math.abs(difference);


            differenceText.className =
                'text-danger fw-semibold';

        }

        /*
        |--------------------------------------------------------------------------
        | AUCUN CHANGEMENT
        |--------------------------------------------------------------------------
        */

        else {

            differenceBox.textContent =
                '0';


            differenceBox.className =
                'form-control bg-light fw-bold';


            differenceText.textContent =
                'Aucun changement de stock';


            differenceText.className =
                'text-muted';

        }
    }


    /*
    |--------------------------------------------------------------------------
    | CHANGEMENT PRODUIT
    |--------------------------------------------------------------------------
    */

    if (productSelect) {

        productSelect.addEventListener(
            'change',
            updateCurrentQuantity
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CHANGEMENT NOUVELLE QUANTITÉ
    |--------------------------------------------------------------------------
    */

    if (newQtyInput) {

        newQtyInput.addEventListener(
            'input',
            calculateDifference
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CHOISIR UN PRODUIT DANS LA LISTE
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll(
        '.select-product'
    ).forEach(function (button) {

        button.addEventListener(
            'click',
            function () {

                const productId =
                    this.dataset.productId;


                if (!productSelect) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | SELECT NORMAL
                |--------------------------------------------------------------------------
                */

                productSelect.value =
                    productId;


                /*
                |--------------------------------------------------------------------------
                | SELECT2
                |--------------------------------------------------------------------------
                */

                if (
                    typeof window.jQuery !== 'undefined' &&
                    window.jQuery(productSelect).data('select2')
                ) {

                    window.jQuery(productSelect)
                        .val(productId)
                        .trigger('change');

                } else {

                    updateCurrentQuantity();

                }


                /*
                |--------------------------------------------------------------------------
                | REMONTER VERS LE FORMULAIRE
                |--------------------------------------------------------------------------
                */

                const form =
                    document.getElementById(
                        'inventoryAdjustmentForm'
                    );


                if (form) {

                    form.scrollIntoView({

                        behavior: 'smooth',

                        block: 'start'

                    });

                }


                /*
                |--------------------------------------------------------------------------
                | FOCUS SUR NOUVELLE QUANTITÉ
                |--------------------------------------------------------------------------
                */

                setTimeout(function () {

                    if (newQtyInput) {

                        newQtyInput.focus();

                    }

                }, 400);

            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | RECHERCHE
    |--------------------------------------------------------------------------
    */

    if (productSearch) {

        productSearch.addEventListener(
            'input',
            function () {

                const search =
                    this.value
                        .toLowerCase()
                        .trim();


                document.querySelectorAll(
                    '#productsTable tbody .product-row'
                ).forEach(function (row) {

                    const rowText =
                        row.textContent
                            .toLowerCase();


                    row.style.display =
                        rowText.includes(search)
                            ? ''
                            : 'none';

                });

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | INITIALISATION
    |--------------------------------------------------------------------------
    */

    updateCurrentQuantity();

});

</script>

@endif