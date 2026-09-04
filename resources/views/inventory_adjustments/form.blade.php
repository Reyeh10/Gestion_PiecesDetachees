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

    $adjustment = $inventoryAdjustment ?? null;

    $oldQty = (float) ($adjustment?->old_qty ?? 0);
    $newQty = (float) ($adjustment?->new_qty ?? 0);
    $difference = round($newQty - $oldQty, 2);

    $unit = $adjustment?->product?->unit_label
        ?? $adjustment?->product?->unit_type
        ?? 'Pièce';

    $depotName = $adjustment?->depot?->name;

    /*
    |--------------------------------------------------------------------------
    | LOCALISATION
    |--------------------------------------------------------------------------
    |
    | On privilégie la localisation mémorisée dans l'ajustement.
    | Si elle n'existe pas (anciens ajustements), on utilise celle du produit.
    |
    */
    $rayonName = $adjustment?->rayon?->name
        ?? $adjustment?->product?->rayon?->name;

    $locationName = $adjustment?->location?->name
        ?? $adjustment?->product?->location?->name;
@endphp

{{-- ================================================================
    MESSAGES
================================================================ --}}
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

{{-- ================================================================
    INFORMATIONS LOCALISATION
    UNIQUEMENT EN CONSULTATION
================================================================ --}}
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

            <input
                type="text"
                class="form-control bg-light"
                value="{{ ($adjustment?->product?->reference ?? '-') . ' - ' . ($adjustment?->product?->designation ?? '-') }}"
                readonly
            >

        @else

            <select
                name="product_id"
                id="product_id"
                class="form-select @error('product_id') is-invalid @enderror"
                required
            >

                <option value="">
                    -- Sélectionner un produit --
                </option>

                @foreach($products as $product)

                    <option
                        value="{{ $product->id }}"
                        data-quantity="{{ (float) ($product->quantity ?? 0) }}"
                        @selected(
                            old(
                                'product_id',
                                $adjustment?->product_id ?? ''
                            ) == $product->id
                        )
                    >
                        {{ $product->reference ?? '-' }}
                        -
                        {{ $product->designation ?? '-' }}
                    </option>

                @endforeach

            </select>

            @error('product_id')
                <div class="invalid-feedback">
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
            step="0.01"
            id="old_qty_display"
            class="form-control bg-light fw-bold"
            value="{{ $readonly ? $oldQty : 0 }}"
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

    {{-- ============================================================
        DIFFÉRENCE
    ============================================================ --}}
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

{{-- ================================================================
    MÉTADONNÉES
    UNIQUEMENT EN CONSULTATION
================================================================ --}}
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
                                    (float) ($product->quantity ?? 0);
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
                                    {{ $product->brand?->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $product->model?->name ?? 'Non défini' }}
                                </td>

                                <td class="text-center">

                                    @if($quantity <= 0)

                                        <span class="badge bg-danger">
                                            {{ number_format($quantity, 2, ',', ' ') }}
                                        </span>

                                    @elseif($quantity <= 5)

                                        <span class="badge bg-warning text-dark">
                                            {{ number_format($quantity, 2, ',', ' ') }}
                                        </span>

                                    @else

                                        <span class="badge bg-success">
                                            {{ number_format($quantity, 2, ',', ' ') }}
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
            !selectedOption
            || !selectedOption.value
        ) {
            if (oldQtyInput) {
                oldQtyInput.value = 0;
            }

            calculateDifference();

            return;
        }

        const quantity =
            parseNumber(
                selectedOption.dataset.quantity
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

            return;
        }

        const newQty =
            parseNumber(
                newQtyValue
            );

        const difference =
            Math.round(
                (newQty - oldQty) * 100
            ) / 100;

        /*
        |--------------------------------------------------------------------------
        | ENTRÉE STOCK
        |--------------------------------------------------------------------------
        */
        if (difference > 0) {

            differenceBox.textContent =
                '+' + formatNumber(difference);

            differenceBox.className =
                'form-control fw-bold text-success border-success';

            differenceText.textContent =
                'Entrée de stock : +'
                + formatNumber(difference);

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
                formatNumber(difference);

            differenceBox.className =
                'form-control fw-bold text-danger border-danger';

            differenceText.textContent =
                'Sortie de stock : '
                + formatNumber(
                    Math.abs(difference)
                );

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
                '0,00';

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
                    typeof window.jQuery !== 'undefined'
                    && window.jQuery(productSelect).data('select2')
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
