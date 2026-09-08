@php

    /*
    |--------------------------------------------------------------------------
    | MODE DU FORMULAIRE
    |--------------------------------------------------------------------------
    |
    | $readonly = true
    |     => consultation uniquement
    |
    | $readonly = false + adjustment existant
    |     => modification
    |
    | $readonly = false + aucun adjustment
    |     => création
    |
    */

    $readonly = $readonly ?? false;

    $adjustment = $inventoryAdjustment ?? null;

    $isCreating =
        !$readonly
        && !$adjustment;

    $isEditing =
        !$readonly
        && $adjustment;

    /*
    |--------------------------------------------------------------------------
    | DONNÉES AJUSTEMENT
    |--------------------------------------------------------------------------
    */

    $oldQty =
        (float) ($adjustment?->old_qty ?? 0);

    $newQty =
        (float) ($adjustment?->new_qty ?? 0);

    $difference =
        round(
            $newQty - $oldQty,
            2
        );

    $unit =
        $adjustment?->product?->unit_label
        ?? $adjustment?->product?->unit_type
        ?? 'Pièce';

    /*
    |--------------------------------------------------------------------------
    | DÉPÔT
    |--------------------------------------------------------------------------
    */

    $depotName =
        $adjustment?->depot?->name;

    /*
    |--------------------------------------------------------------------------
    | LOCALISATION
    |--------------------------------------------------------------------------
    */

    $rayonName =
        $adjustment?->rayon?->name
        ?? $adjustment?->product?->rayon?->name;

    $locationName =
        $adjustment?->location?->name
        ?? $adjustment?->product?->location?->name;

    /*
    |--------------------------------------------------------------------------
    | STOCKS DÉPÔTS
    |--------------------------------------------------------------------------
    |
    | depotStocks est fourni par le contrôleur create().
    |
    */

    $depotStocks =
        $depotStocks ?? collect();

@endphp


{{-- ====================================================================== --}}
{{-- MESSAGES --}}
{{-- ====================================================================== --}}

@if(!$readonly)

    @if(session('error'))

        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >

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


{{-- ====================================================================== --}}
{{-- CONSULTATION : DÉPÔT / RAYON / EMPLACEMENT --}}
{{-- ====================================================================== --}}

@if($readonly)

    <div class="row g-3 mb-4">

        {{-- DÉPÔT --}}
        <div class="col-lg-4 col-md-6">

            <label class="form-label fw-semibold">
                Dépôt
            </label>

            <div class="form-control bg-light">

                @if($depotName)

                    <i class="bx bx-building-house me-1"></i>

                    {{ $depotName }}

                @else

                    <span class="text-muted">
                        Non renseigné
                    </span>

                @endif

            </div>

        </div>


        {{-- RAYON --}}
        <div class="col-lg-4 col-md-6">

            <label class="form-label fw-semibold">
                Rayon
            </label>

            <div class="form-control bg-light">

                @if($rayonName)

                    <i class="bx bx-grid-alt me-1"></i>

                    {{ $rayonName }}

                @else

                    <span class="text-muted">
                        Non renseigné
                    </span>

                @endif

            </div>

        </div>


        {{-- EMPLACEMENT --}}
        <div class="col-lg-4 col-md-6">

            <label class="form-label fw-semibold">
                Emplacement
            </label>

            <div class="form-control bg-light">

                @if($locationName)

                    <i class="bx bx-map-pin me-1"></i>

                    {{ $locationName }}

                @else

                    <span class="text-muted">
                        Non renseigné
                    </span>

                @endif

            </div>

        </div>

    </div>

@endif


{{-- ====================================================================== --}}
{{-- CRÉATION / MODIFICATION : DÉPÔT --}}
{{-- ====================================================================== --}}

@if(!$readonly)

    <div class="row g-3 mb-4">

        <div class="col-lg-6 col-md-8">

            <label
                for="depot_id"
                class="form-label fw-semibold"
            >
                Dépôt

                <span class="text-danger">
                    *
                </span>
            </label>

            <select
                name="depot_id"
                id="depot_id"
                class="form-select @error('depot_id') is-invalid @enderror"
                required
            >

                <option value="">
                    -- Sélectionner un dépôt --
                </option>

                @foreach($depots ?? [] as $depot)

                    <option
                        value="{{ $depot->id }}"
                        @selected(
                            old(
                                'depot_id',
                                $adjustment?->depot_id ?? ''
                            ) == $depot->id
                        )
                    >

                        {{ $depot->name }}

                        @if(!empty($depot->code))

                            - {{ $depot->code }}

                        @endif

                    </option>

                @endforeach

            </select>

            @error('depot_id')

                <div class="invalid-feedback">
                    {{ $message }}
                </div>

            @enderror

            <small class="text-muted">

                <i class="bx bx-info-circle me-1"></i>

                Sélectionnez d'abord le dépôt concerné par
                l'inventaire.

            </small>

        </div>

    </div>

@endif


{{-- ====================================================================== --}}
{{-- INFORMATIONS AJUSTEMENT --}}
{{-- ====================================================================== --}}

<div class="row g-3">

    {{-- ================================================================== --}}
    {{-- PRODUIT --}}
    {{-- ================================================================== --}}

    <div class="col-lg-5 col-md-6">

        <label class="form-label fw-semibold">

            Produit

            @if(!$readonly)

                <span class="text-danger">
                    *
                </span>

            @endif

        </label>


        @if($readonly)

            <input
                type="text"
                class="form-control bg-light"
                value="{{ ($adjustment?->product?->reference ?? '-') . ' - ' . ($adjustment?->product?->designation ?? '-') }}"
                readonly
            >

        @elseif($isEditing)

            {{-- ========================================================== --}}
            {{-- EN MODIFICATION : PRODUIT NON MODIFIABLE --}}
            {{-- ========================================================== --}}

            <input
                type="hidden"
                name="product_id"
                value="{{ $adjustment?->product_id }}"
            >

            <input
                type="text"
                class="form-control bg-light"
                value="{{ ($adjustment?->product?->reference ?? '-') . ' - ' . ($adjustment?->product?->designation ?? '-') }}"
                readonly
            >

            <small class="text-muted">
                Le produit d'un ajustement existant ne peut pas être modifié.
            </small>

        @else

            {{-- ========================================================== --}}
            {{-- CRÉATION --}}
            {{-- ========================================================== --}}

            <select
                name="product_id"
                id="product_id"
                class="form-select @error('product_id') is-invalid @enderror"
                required
                disabled
            >

                <option value="">
                    -- Sélectionnez d'abord un dépôt --
                </option>

            </select>

            @error('product_id')

                <div class="invalid-feedback">
                    {{ $message }}
                </div>

            @enderror

            <small
                class="text-muted"
                id="productHelp"
            >
                Sélectionnez d'abord un dépôt.
            </small>

        @endif

    </div>


    {{-- ================================================================== --}}
    {{-- ANCIEN STOCK / QUANTITÉ ACTUELLE --}}
    {{-- ================================================================== --}}

    <div class="col-lg-2 col-md-3">

        <label class="form-label fw-semibold">

            @if($readonly || $isEditing)

                Ancien stock

            @else

                Quantité actuelle

            @endif

        </label>

        <input
            type="number"
            step="0.01"
            id="old_qty_display"
            class="form-control bg-light fw-bold"
            value="{{ $readonly || $isEditing ? $oldQty : 0 }}"
            readonly
        >

        <small class="text-muted">

            @if($readonly || $isEditing)

                Stock avant ajustement

            @else

                Stock dans le dépôt sélectionné

            @endif

        </small>

    </div>


    {{-- ================================================================== --}}
    {{-- NOUVELLE QUANTITÉ --}}
    {{-- ================================================================== --}}

    <div class="col-lg-2 col-md-3">

        <label class="form-label fw-semibold">

            Nouvelle quantité

            @if(!$readonly)

                <span class="text-danger">
                    *
                </span>

            @endif

        </label>


        @if($readonly)

            <input
                type="number"
                step="0.01"
                class="form-control bg-light fw-bold"
                value="{{ $newQty }}"
                readonly
            >

            <small class="text-muted">
                Stock après ajustement
            </small>

        @else

            <input
                type="number"
                min="0"
                step="0.01"
                name="new_qty"
                id="new_qty"
                class="form-control @error('new_qty') is-invalid @enderror"
                value="{{ old('new_qty', $adjustment?->new_qty ?? '') }}"
                placeholder="0"
                required
                @disabled($isCreating)
            >

            <small class="text-muted">
                Quantité réellement comptée
            </small>

            @error('new_qty')

                <div class="invalid-feedback">
                    {{ $message }}
                </div>

            @enderror

        @endif

    </div>


    {{-- ================================================================== --}}
    {{-- DIFFÉRENCE --}}
    {{-- ================================================================== --}}

    <div class="col-lg-3 col-md-4">

        <label class="form-label fw-semibold">
            Différence
        </label>


        @if($readonly)

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

                    +{{ number_format($difference, 2, ',', ' ') }}

                @else

                    {{ number_format($difference, 2, ',', ' ') }}

                @endif

            </div>


            @if($difference > 0)

                <small class="text-success fw-semibold">

                    <i class="bx bx-plus-circle me-1"></i>

                    Entrée de stock :

                    +{{ number_format($difference, 2, ',', ' ') }}

                    {{ $unit }}

                </small>

            @elseif($difference < 0)

                <small class="text-danger fw-semibold">

                    <i class="bx bx-minus-circle me-1"></i>

                    Sortie de stock :

                    {{ number_format(abs($difference), 2, ',', ' ') }}

                    {{ $unit }}

                </small>

            @else

                <small class="text-muted">

                    <i class="bx bx-minus me-1"></i>

                    Aucun changement de stock

                </small>

            @endif

        @else

            <div
                id="differenceBox"
                class="form-control bg-light fw-bold"
            >
                0,00
            </div>

            <small
                id="differenceText"
                class="text-muted"
            >
                Saisissez la quantité réellement comptée
            </small>

        @endif

    </div>


    {{-- ================================================================== --}}
    {{-- RAISON --}}
    {{-- ================================================================== --}}

    <div class="col-12">

        <label class="form-label fw-semibold">

            Raison de l'ajustement

            @if(!$readonly)

                <span class="text-danger">
                    *
                </span>

            @endif

        </label>


        @if($readonly)

            <textarea
                rows="4"
                class="form-control bg-light"
                readonly
            >{{ $adjustment?->reason ?? '' }}</textarea>

        @else

            <textarea
                name="reason"
                id="reason"
                rows="4"
                maxlength="1000"
                class="form-control @error('reason') is-invalid @enderror"
                placeholder="Exemple : différence constatée pendant l'inventaire physique..."
                required
            >{{ old('reason', $adjustment?->reason ?? '') }}</textarea>

            @error('reason')

                <div class="invalid-feedback">
                    {{ $message }}
                </div>

            @enderror

        @endif

    </div>

</div>


{{-- ====================================================================== --}}
{{-- MÉTADONNÉES CONSULTATION --}}
{{-- ====================================================================== --}}

@if($readonly)

    <div class="row g-3 mt-1">

        {{-- EFFECTUÉ PAR --}}
        <div class="col-md-6">

            <label class="form-label fw-semibold">
                Effectué par
            </label>

            <div class="form-control bg-light">

                <i class="bx bx-user me-1"></i>

                {{ $adjustment?->approver?->name ?? 'Non renseigné' }}

            </div>

        </div>


        {{-- DATE --}}
        <div class="col-md-6">

            <label class="form-label fw-semibold">
                Date de l'ajustement
            </label>

            <div class="form-control bg-light">

                <i class="bx bx-calendar me-1"></i>

                {{
                    optional(
                        $adjustment?->created_at
                    )->format('d/m/Y H:i')
                    ?? '-'
                }}

            </div>

        </div>

    </div>

@endif


{{-- ====================================================================== --}}
{{-- LISTE DES PRODUITS --}}
{{-- UNIQUEMENT EN CRÉATION --}}
{{-- ====================================================================== --}}

@if($isCreating)

    <div class="card shadow-sm border-0 mt-4">

        <div class="card-header bg-white border-bottom">

            <div class="row align-items-center g-3">

                <div class="col-md-7">

                    <h5 class="mb-1 fw-bold">

                        <i class="bx bx-package me-1"></i>

                        Produits du dépôt sélectionné

                    </h5>

                    <small
                        class="text-muted"
                        id="productsCount"
                    >
                        Sélectionnez un dépôt
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
                            disabled
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


                    <tbody id="productsTableBody">

                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-5 text-muted"
                            >

                                <i
                                    class="bx bx-building-house d-block mb-2"
                                    style="font-size: 36px;"
                                ></i>

                                Sélectionnez d'abord un dépôt.

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endif


{{-- ====================================================================== --}}
{{-- JAVASCRIPT CRÉATION --}}
{{-- ====================================================================== --}}

@if($isCreating)

    @php

        /*
        |--------------------------------------------------------------------------
        | PRODUITS POUR JAVASCRIPT
        |--------------------------------------------------------------------------
        */

        $productsForJs =
            collect($products ?? [])
                ->map(function ($product) {

                    return [
                        'id' =>
                            $product->id,

                        'reference' =>
                            $product->reference ?? '',

                        'designation' =>
                            $product->designation ?? '',

                        'brand' =>
                            $product->brand?->name ?? '',

                        'model' =>
                            $product->model?->name ?? '',
                    ];

                })
                ->values();

    @endphp


    <script>

        document.addEventListener(
            'DOMContentLoaded',
            function () {

                /*
                |--------------------------------------------------------------------------
                | DONNÉES
                |--------------------------------------------------------------------------
                */

                const products =
                    @json($productsForJs);

                const depotStocks =
                    @json($depotStocks);

                const oldDepotId =
                    @json((string) old('depot_id', ''));

                const oldProductId =
                    @json((string) old('product_id', ''));

                const oldNewQty =
                    @json(old('new_qty', ''));


                /*
                |--------------------------------------------------------------------------
                | ÉLÉMENTS
                |--------------------------------------------------------------------------
                */

                const depotSelect =
                    document.getElementById(
                        'depot_id'
                    );

                const productSelect =
                    document.getElementById(
                        'product_id'
                    );

                const oldQtyInput =
                    document.getElementById(
                        'old_qty_display'
                    );

                const newQtyInput =
                    document.getElementById(
                        'new_qty'
                    );

                const differenceBox =
                    document.getElementById(
                        'differenceBox'
                    );

                const differenceText =
                    document.getElementById(
                        'differenceText'
                    );

                const productSearch =
                    document.getElementById(
                        'productSearch'
                    );

                const productsTableBody =
                    document.getElementById(
                        'productsTableBody'
                    );

                const productsCount =
                    document.getElementById(
                        'productsCount'
                    );

                const productHelp =
                    document.getElementById(
                        'productHelp'
                    );

                const submitButton =
                    document.getElementById(
                        'submitAdjustmentButton'
                    );


                /*
                |--------------------------------------------------------------------------
                | HELPERS
                |--------------------------------------------------------------------------
                */

                function parseNumber(value)
                {
                    const parsed =
                        parseFloat(
                            String(value ?? '')
                                .replace(',', '.')
                        );

                    return Number.isFinite(parsed)
                        ? parsed
                        : 0;
                }


                function formatNumber(value)
                {
                    return Number(value)
                        .toLocaleString(
                            'fr-FR',
                            {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }
                        );
                }


                function escapeHtml(value)
                {
                    const element =
                        document.createElement(
                            'div'
                        );

                    element.textContent =
                        value ?? '';

                    return element.innerHTML;
                }


                /*
                |--------------------------------------------------------------------------
                | STOCK PRODUIT / DÉPÔT
                |--------------------------------------------------------------------------
                */

                function getDepotQuantity(
                    depotId,
                    productId
                ) {
                    if (
                        !depotId
                        || !productId
                    ) {
                        return 0;
                    }

                    if (
                        !depotStocks[depotId]
                    ) {
                        return 0;
                    }

                    const value =
                        depotStocks[depotId][productId];

                    if (
                        value === undefined
                        || value === null
                    ) {
                        return 0;
                    }

                    return parseNumber(
                        value
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | PRODUITS PRÉSENTS DANS LE DÉPÔT
                |--------------------------------------------------------------------------
                */

                function getProductsForDepot(
                    depotId
                ) {
                    if (
                        !depotId
                        || !depotStocks[depotId]
                    ) {
                        return [];
                    }

                    const productIds =
                        Object.keys(
                            depotStocks[depotId]
                        )
                        .map(String);

                    return products.filter(
                        function (product) {

                            return productIds.includes(
                                String(product.id)
                            );

                        }
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | REMPLIR SELECT PRODUIT
                |--------------------------------------------------------------------------
                */

                function populateProductSelect(
                    depotId,
                    selectedProductId = ''
                ) {
                    if (!productSelect) {
                        return;
                    }

                    productSelect.innerHTML =
                        '';

                    if (!depotId) {

                        productSelect.disabled =
                            true;

                        const option =
                            document.createElement(
                                'option'
                            );

                        option.value = '';

                        option.textContent =
                            '-- Sélectionnez d\'abord un dépôt --';

                        productSelect.appendChild(
                            option
                        );

                        if (productHelp) {

                            productHelp.textContent =
                                'Sélectionnez d\'abord un dépôt.';

                        }

                        return;
                    }


                    const depotProducts =
                        getProductsForDepot(
                            depotId
                        );


                    productSelect.disabled =
                        false;


                    const firstOption =
                        document.createElement(
                            'option'
                        );

                    firstOption.value = '';

                    firstOption.textContent =
                        '-- Sélectionner un produit --';

                    productSelect.appendChild(
                        firstOption
                    );


                    depotProducts.forEach(
                        function (product) {

                            const option =
                                document.createElement(
                                    'option'
                                );

                            option.value =
                                product.id;

                            option.textContent =
                                (
                                    product.reference
                                    || '-'
                                )
                                + ' - '
                                + (
                                    product.designation
                                    || '-'
                                );

                            if (
                                String(product.id)
                                ===
                                String(
                                    selectedProductId
                                )
                            ) {
                                option.selected =
                                    true;
                            }

                            productSelect.appendChild(
                                option
                            );

                        }
                    );


                    if (productHelp) {

                        productHelp.textContent =
                            depotProducts.length
                            + ' produit(s) disponible(s) dans ce dépôt.';

                    }
                }


                /*
                |--------------------------------------------------------------------------
                | CALCUL DIFFÉRENCE
                |--------------------------------------------------------------------------
                */

                function calculateDifference()
                {
                    if (
                        !oldQtyInput
                        || !newQtyInput
                        || !differenceBox
                        || !differenceText
                    ) {
                        return;
                    }

                    const oldQty =
                        parseNumber(
                            oldQtyInput.value
                        );

                    const newQtyValue =
                        newQtyInput.value;


                    if (newQtyValue === '') {

                        differenceBox.textContent =
                            '0,00';

                        differenceBox.className =
                            'form-control bg-light fw-bold';

                        differenceText.textContent =
                            'Saisissez la quantité réellement comptée';

                        differenceText.className =
                            'text-muted';

                        if (submitButton) {
                            submitButton.disabled =
                                true;
                        }

                        return;
                    }


                    const newQty =
                        parseNumber(
                            newQtyValue
                        );


                    const difference =
                        Math.round(
                            (
                                newQty
                                - oldQty
                            )
                            * 100
                        )
                        / 100;


                    /*
                    |--------------------------------------------------------------------------
                    | ENTRÉE
                    |--------------------------------------------------------------------------
                    */

                    if (difference > 0) {

                        differenceBox.textContent =
                            '+'
                            + formatNumber(
                                difference
                            );

                        differenceBox.className =
                            'form-control fw-bold text-success border-success';

                        differenceText.textContent =
                            'Entrée de stock : +'
                            + formatNumber(
                                difference
                            );

                        differenceText.className =
                            'text-success fw-semibold';

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | SORTIE
                    |--------------------------------------------------------------------------
                    */

                    else if (difference < 0) {

                        differenceBox.textContent =
                            formatNumber(
                                difference
                            );

                        differenceBox.className =
                            'form-control fw-bold text-danger border-danger';

                        differenceText.textContent =
                            'Sortie de stock : '
                            + formatNumber(
                                Math.abs(
                                    difference
                                )
                            );

                        differenceText.className =
                            'text-danger fw-semibold';

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | AUCUNE DIFFÉRENCE
                    |--------------------------------------------------------------------------
                    */

                    else {

                        differenceBox.textContent =
                            '0,00';

                        differenceBox.className =
                            'form-control bg-light fw-bold';

                        differenceText.textContent =
                            'Aucun changement de stock';

                        differenceText.className =
                            'text-muted';

                    }


                    if (submitButton) {

                        submitButton.disabled =
                            !depotSelect.value
                            || !productSelect.value
                            || newQtyValue === '';

                    }
                }


                /*
                |--------------------------------------------------------------------------
                | METTRE À JOUR QUANTITÉ ACTUELLE
                |--------------------------------------------------------------------------
                */

                function updateCurrentQuantity()
                {
                    const depotId =
                        depotSelect.value;

                    const productId =
                        productSelect.value;


                    if (
                        !depotId
                        || !productId
                    ) {

                        oldQtyInput.value =
                            0;

                        newQtyInput.disabled =
                            true;

                        if (!oldNewQty) {

                            newQtyInput.value =
                                '';

                        }

                        calculateDifference();

                        return;
                    }


                    const quantity =
                        getDepotQuantity(
                            depotId,
                            productId
                        );


                    oldQtyInput.value =
                        quantity;


                    newQtyInput.disabled =
                        false;


                    calculateDifference();
                }


                /*
                |--------------------------------------------------------------------------
                | RENDU DU TABLEAU
                |--------------------------------------------------------------------------
                */

                function renderProductsTable(
                    search = ''
                ) {
                    const depotId =
                        depotSelect.value;


                    if (!depotId) {

                        productsCount.textContent =
                            'Sélectionnez un dépôt';

                        productsTableBody.innerHTML = `
                            <tr>
                                <td
                                    colspan="7"
                                    class="text-center py-5 text-muted"
                                >
                                    <i
                                        class="bx bx-building-house d-block mb-2"
                                        style="font-size:36px;"
                                    ></i>

                                    Sélectionnez d'abord un dépôt.
                                </td>
                            </tr>
                        `;

                        return;
                    }


                    let depotProducts =
                        getProductsForDepot(
                            depotId
                        );


                    const term =
                        String(search ?? '')
                            .trim()
                            .toLowerCase();


                    if (term !== '') {

                        depotProducts =
                            depotProducts.filter(
                                function (product) {

                                    const text =
                                        (
                                            product.reference
                                            + ' '
                                            + product.designation
                                            + ' '
                                            + product.brand
                                            + ' '
                                            + product.model
                                        )
                                        .toLowerCase();

                                    return text.includes(
                                        term
                                    );

                                }
                            );

                    }


                    productsCount.textContent =
                        depotProducts.length
                        + ' produit(s)';


                    if (
                        depotProducts.length === 0
                    ) {

                        productsTableBody.innerHTML = `
                            <tr>
                                <td
                                    colspan="7"
                                    class="text-center py-5 text-muted"
                                >
                                    Aucun produit trouvé
                                    dans ce dépôt.
                                </td>
                            </tr>
                        `;

                        return;
                    }


                    let html = '';


                    depotProducts.forEach(
                        function (
                            product,
                            index
                        ) {

                            const quantity =
                                getDepotQuantity(
                                    depotId,
                                    product.id
                                );


                            let badgeClass =
                                'bg-success';


                            if (quantity <= 0) {

                                badgeClass =
                                    'bg-danger';

                            } else if (
                                quantity <= 5
                            ) {

                                badgeClass =
                                    'bg-warning text-dark';

                            }


                            html += `

                                <tr class="product-row">

                                    <td>
                                        ${index + 1}
                                    </td>

                                    <td>
                                        <strong>
                                            ${escapeHtml(
                                                product.reference
                                                || '-'
                                            )}
                                        </strong>
                                    </td>

                                    <td>
                                        ${escapeHtml(
                                            product.designation
                                            || '-'
                                        )}
                                    </td>

                                    <td>
                                        ${escapeHtml(
                                            product.brand
                                            || '-'
                                        )}
                                    </td>

                                    <td>
                                        ${escapeHtml(
                                            product.model
                                            || 'Non défini'
                                        )}
                                    </td>

                                    <td class="text-center">

                                        <span
                                            class="badge ${badgeClass}"
                                        >
                                            ${formatNumber(
                                                quantity
                                            )}
                                        </span>

                                    </td>

                                    <td class="text-center">

                                        <button
                                            type="button"
                                            class="btn btn-primary btn-sm select-product"
                                            data-product-id="${product.id}"
                                        >

                                            <i class="bx bx-check me-1"></i>

                                            Choisir

                                        </button>

                                    </td>

                                </tr>
                            `;

                        }
                    );


                    productsTableBody.innerHTML =
                        html;


                    bindChooseButtons();
                }


                /*
                |--------------------------------------------------------------------------
                | BOUTONS CHOISIR
                |--------------------------------------------------------------------------
                */

                function bindChooseButtons()
                {
                    document
                        .querySelectorAll(
                            '.select-product'
                        )
                        .forEach(
                            function (button) {

                                button.addEventListener(
                                    'click',
                                    function () {

                                        const productId =
                                            this.dataset.productId;


                                        productSelect.value =
                                            productId;


                                        /*
                                        |--------------------------------------------------------------------------
                                        | SELECT2 SI ACTIF
                                        |--------------------------------------------------------------------------
                                        */

                                        if (
                                            typeof window.jQuery
                                            !== 'undefined'
                                            &&
                                            window
                                                .jQuery(
                                                    productSelect
                                                )
                                                .data(
                                                    'select2'
                                                )
                                        ) {

                                            window
                                                .jQuery(
                                                    productSelect
                                                )
                                                .val(
                                                    productId
                                                )
                                                .trigger(
                                                    'change'
                                                );

                                        } else {

                                            productSelect.dispatchEvent(
                                                new Event(
                                                    'change'
                                                )
                                            );

                                        }


                                        /*
                                        |--------------------------------------------------------------------------
                                        | REMONTER AU FORMULAIRE
                                        |--------------------------------------------------------------------------
                                        */

                                        const form =
                                            document.getElementById(
                                                'inventoryAdjustmentForm'
                                            );

                                        if (form) {

                                            form.scrollIntoView({
                                                behavior:
                                                    'smooth',

                                                block:
                                                    'start'
                                            });

                                        }


                                        setTimeout(
                                            function () {

                                                if (
                                                    newQtyInput
                                                    &&
                                                    !newQtyInput.disabled
                                                ) {

                                                    newQtyInput.focus();

                                                }

                                            },
                                            400
                                        );

                                    }
                                );

                            }
                        );
                }


                /*
                |--------------------------------------------------------------------------
                | CHANGEMENT DÉPÔT
                |--------------------------------------------------------------------------
                */

                depotSelect.addEventListener(
                    'change',
                    function () {

                        const depotId =
                            this.value;


                        productSelect.value =
                            '';


                        oldQtyInput.value =
                            0;


                        newQtyInput.value =
                            '';


                        newQtyInput.disabled =
                            true;


                        differenceBox.textContent =
                            '0,00';


                        differenceBox.className =
                            'form-control bg-light fw-bold';


                        differenceText.textContent =
                            'Sélectionnez un produit';


                        differenceText.className =
                            'text-muted';


                        if (submitButton) {

                            submitButton.disabled =
                                true;

                        }


                        if (depotId) {

                            productSearch.disabled =
                                false;

                        } else {

                            productSearch.disabled =
                                true;

                            productSearch.value =
                                '';

                        }


                        populateProductSelect(
                            depotId
                        );


                        renderProductsTable();

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | CHANGEMENT PRODUIT
                |--------------------------------------------------------------------------
                */

                productSelect.addEventListener(
                    'change',
                    updateCurrentQuantity
                );


                /*
                |--------------------------------------------------------------------------
                | NOUVELLE QUANTITÉ
                |--------------------------------------------------------------------------
                */

                newQtyInput.addEventListener(
                    'input',
                    calculateDifference
                );


                /*
                |--------------------------------------------------------------------------
                | RECHERCHE
                |--------------------------------------------------------------------------
                */

                productSearch.addEventListener(
                    'input',
                    function () {

                        renderProductsTable(
                            this.value
                        );

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | RESTAURATION OLD()
                |--------------------------------------------------------------------------
                */

                if (oldDepotId !== '') {

                    depotSelect.value =
                        oldDepotId;


                    productSearch.disabled =
                        false;


                    populateProductSelect(
                        oldDepotId,
                        oldProductId
                    );


                    renderProductsTable();


                    if (oldProductId !== '') {

                        productSelect.value =
                            oldProductId;


                        newQtyInput.disabled =
                            false;


                        if (oldNewQty !== '') {

                            newQtyInput.value =
                                oldNewQty;

                        }


                        updateCurrentQuantity();

                    }

                } else {

                    productSelect.disabled =
                        true;


                    newQtyInput.disabled =
                        true;


                    productSearch.disabled =
                        true;


                    if (submitButton) {

                        submitButton.disabled =
                            true;

                    }

                }

            }
        );

    </script>

@endif


{{-- ====================================================================== --}}
{{-- JAVASCRIPT MODIFICATION --}}
{{-- ====================================================================== --}}

@if($isEditing)

    <script>

        document.addEventListener(
            'DOMContentLoaded',
            function () {

                const oldQtyInput =
                    document.getElementById(
                        'old_qty_display'
                    );

                const newQtyInput =
                    document.getElementById(
                        'new_qty'
                    );

                const differenceBox =
                    document.getElementById(
                        'differenceBox'
                    );

                const differenceText =
                    document.getElementById(
                        'differenceText'
                    );


                function parseNumber(value)
                {
                    const parsed =
                        parseFloat(
                            String(value ?? '')
                                .replace(',', '.')
                        );

                    return Number.isFinite(parsed)
                        ? parsed
                        : 0;
                }


                function formatNumber(value)
                {
                    return Number(value)
                        .toLocaleString(
                            'fr-FR',
                            {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }
                        );
                }


                function calculateDifference()
                {
                    const oldQty =
                        parseNumber(
                            oldQtyInput.value
                        );

                    if (
                        newQtyInput.value === ''
                    ) {

                        differenceBox.textContent =
                            '0,00';

                        differenceText.textContent =
                            'Saisissez la nouvelle quantité';

                        return;
                    }


                    const newQty =
                        parseNumber(
                            newQtyInput.value
                        );


                    const difference =
                        Math.round(
                            (
                                newQty
                                - oldQty
                            )
                            * 100
                        )
                        / 100;


                    if (difference > 0) {

                        differenceBox.textContent =
                            '+'
                            + formatNumber(
                                difference
                            );

                        differenceBox.className =
                            'form-control fw-bold text-success border-success';

                        differenceText.textContent =
                            'Entrée de stock : +'
                            + formatNumber(
                                difference
                            );

                        differenceText.className =
                            'text-success fw-semibold';

                    } else if (
                        difference < 0
                    ) {

                        differenceBox.textContent =
                            formatNumber(
                                difference
                            );

                        differenceBox.className =
                            'form-control fw-bold text-danger border-danger';

                        differenceText.textContent =
                            'Sortie de stock : '
                            + formatNumber(
                                Math.abs(
                                    difference
                                )
                            );

                        differenceText.className =
                            'text-danger fw-semibold';

                    } else {

                        differenceBox.textContent =
                            '0,00';

                        differenceBox.className =
                            'form-control bg-light fw-bold';

                        differenceText.textContent =
                            'Aucun changement de stock';

                        differenceText.className =
                            'text-muted';

                    }
                }


                if (
                    newQtyInput
                    && oldQtyInput
                ) {

                    newQtyInput.addEventListener(
                        'input',
                        calculateDifference
                    );

                    calculateDifference();

                }

            }
        );

    </script>

@endif
