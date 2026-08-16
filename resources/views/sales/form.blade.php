@extends('layouts.layoutMaster')

@section('content')

{{-- ============================================================
     SWEETALERT2
============================================================ --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .stcd-swal-popup {
        border: 0 !important;
        border-radius: 20px !important;
        padding: 28px 30px 30px !important;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.20) !important;
    }

    .stcd-swal-title {
        color: #344054 !important;
        font-size: 23px !important;
        font-weight: 700 !important;
        line-height: 1.3 !important;
        padding-top: 5px !important;
    }

    .stcd-swal-message {
        color: #667085 !important;
        font-size: 15px !important;
        line-height: 1.6 !important;
        margin-top: 5px !important;
    }

    .stcd-swal-confirm {
        min-width: 130px !important;
        padding: 11px 24px !important;
        border: 0 !important;
        border-radius: 9px !important;
        background: #696cff !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        box-shadow: 0 5px 14px rgba(105, 108, 255, 0.30) !important;
        transition: transform .15s ease, box-shadow .15s ease !important;
    }

    .stcd-swal-confirm:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(105, 108, 255, 0.35) !important;
    }

    .swal2-icon.swal2-warning {
        border-color: #ffab00 !important;
        color: #ffab00 !important;
    }

    .swal2-close {
        color: #98a2b3 !important;
        font-size: 25px !important;
    }

    .swal2-close:hover {
        color: #696cff !important;
    }

    .swal2-actions {
        margin-top: 24px !important;
    }

    .sale-card {
        border-radius: 14px;
        overflow: visible;
    }

    .sale-card .card-header {
        background: #fff;
    }

    #itemsTable th {
        white-space: nowrap;
        vertical-align: middle;
    }

    #itemsTable td {
        vertical-align: middle;
    }

    .price-display {
        min-width: 145px;
        width: 145px;
        font-size: 16px;
        white-space: nowrap;
    }

    .vehicle-message {
        min-height: 18px;
    }

    /* ============================================================
       RECHERCHE RAPIDE PRODUIT
    ============================================================ */

    .product-search-wrapper {
        position: relative;
        z-index: 50;
    }

    .product-search-input-group {
        border-radius: 8px;
    }

    .product-search-results {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        z-index: 1090;
        max-height: 360px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #d9dee3;
        border-radius: 10px;
        box-shadow: 0 12px 30px rgba(67, 89, 113, 0.18);
    }

    .product-search-result-item {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        padding: 12px 15px;
        border: 0;
        border-bottom: 1px solid #edf0f4;
        background: #fff;
        text-align: left;
        cursor: pointer;
        transition: background .15s ease;
    }

    .product-search-result-item:last-child {
        border-bottom: 0;
    }

    .product-search-result-item:hover,
    .product-search-result-item:focus {
        background: #f5f6ff;
        outline: none;
    }

    .product-search-reference {
        color: #566a7f;
        font-weight: 800;
        margin-bottom: 2px;
    }

    .product-search-designation {
        color: #697a8d;
        font-size: 13px;
    }

    .product-search-meta,
    .product-search-price {
        color: #8592a3;
        font-size: 12px;
        margin-top: 3px;
    }

    .product-search-stock {
        min-width: 120px;
        text-align: right;
        font-weight: 800;
        color: #28a745;
        white-space: nowrap;
    }

    .product-search-no-result {
        padding: 18px;
        text-align: center;
        color: #8592a3;
    }

    .product-search-hint {
        color: #8592a3;
        font-size: 12px;
        margin-top: 6px;
    }

    @media (max-width: 991.98px) {
        .price-display {
            min-width: 120px;
            width: 120px;
        }
    }

    @media (max-width: 767.98px) {
        .product-search-result-item {
            align-items: flex-start;
        }

        .product-search-stock {
            min-width: auto;
        }
    }
</style>

<form
    action="{{ route('sales.store') }}"
    method="POST"
    id="saleForm"
    novalidate
>
    @csrf

    {{-- =========================================================
         ALERTES
    ========================================================= --}}

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

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Veuillez corriger les erreurs suivantes :</strong>

            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="card shadow-sm border-0 sale-card">

        {{-- =====================================================
             TITRE
        ===================================================== --}}

        <div class="card-header border-0 pb-0">
            <h3 class="mb-0 fw-bold">
                Nouvelle vente
            </h3>
        </div>


        <div class="card-body">

            {{-- =================================================
                 CLIENT / VÉHICULE / PAIEMENT
            ================================================= --}}

            <div class="row align-items-end">

                {{-- CLIENT --}}
                <div class="col-lg-4 col-md-6 mb-3">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label
                            for="customer_id"
                            class="form-label fw-semibold mb-0"
                        >
                            Client
                        </label>

                        <button
                            type="button"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#customerModal"
                        >
                            <i class="bx bx-plus"></i>
                            Nouveau client
                        </button>
                    </div>

                    <select
                        name="customer_id"
                        id="customer_id"
                        class="form-control select2"
                        required
                    >
                        <option value="">
                            Sélectionner un client
                        </option>

                        @foreach($customers as $customer)
                            <option
                                value="{{ $customer->id }}"
                                {{ old('customer_id') == $customer->id ? 'selected' : '' }}
                            >
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('customer_id')
                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- VÉHICULE --}}
                <div class="col-lg-4 col-md-6 mb-3">

                    <label
                        for="vehicle_id"
                        class="form-label fw-semibold"
                    >
                        Numéro d’immatriculation
                    </label>

                    <select
                        name="vehicle_id"
                        id="vehicle_id"
                        class="form-control"
                        data-selected="{{ old('vehicle_id') }}"
                        disabled
                        required
                    >
                        <option value="">
                            Sélectionnez d’abord un client
                        </option>
                    </select>

                    <div class="vehicle-message">
                        <div
                            id="vehicleLoadingMessage"
                            class="small text-muted mt-1 d-none"
                        >
                            <i class="bx bx-loader-alt bx-spin"></i>
                            Chargement des véhicules...
                        </div>

                        <div
                            id="vehicleErrorMessage"
                            class="small text-danger mt-1 d-none"
                        ></div>
                    </div>

                    @error('vehicle_id')
                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- PAIEMENT --}}
                <div class="col-lg-4 col-md-6 mb-3">

                    <label
                        for="payment_type"
                        class="form-label fw-semibold"
                    >
                        Mode de paiement
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        name="payment_type"
                        id="payment_type"
                        class="form-control"
                        required
                    >
                        <option value="">
                            Sélectionner un mode de paiement
                        </option>

                        <option value="cash" {{ old('payment_type') === 'cash' ? 'selected' : '' }}>
                            Cash
                        </option>

                        <option value="echeance" {{ old('payment_type') === 'echeance' ? 'selected' : '' }}>
                            Échéance
                        </option>

                        <option value="bon_commande" {{ old('payment_type') === 'bon_commande' ? 'selected' : '' }}>
                            Bon de commande
                        </option>

                        <option value="cheque" {{ old('payment_type') === 'cheque' ? 'selected' : '' }}>
                            Chèque
                        </option>

                        <option value="virement_bancaire" {{ old('payment_type') === 'virement_bancaire' ? 'selected' : '' }}>
                            Virement bancaire
                        </option>

                        <option value="carte_bancaire" {{ old('payment_type') === 'carte_bancaire' ? 'selected' : '' }}>
                            Carte bancaire
                        </option>

                        <option value="paiement_en_ligne" {{ old('payment_type') === 'paiement_en_ligne' ? 'selected' : '' }}>
                            Paiement en ligne
                        </option>

                        <option value="mobile_money" {{ old('payment_type') === 'mobile_money' ? 'selected' : '' }}>
                            Mobile Money
                        </option>

                        <option value="autre" {{ old('payment_type') === 'autre' ? 'selected' : '' }}>
                            Autre
                        </option>
                    </select>

                    @error('payment_type')
                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

            </div>


            <hr class="my-4">


            {{-- ============================================================
                 RECHERCHE RAPIDE PRODUIT
            ============================================================ --}}

            <div class="mb-4">

                <label
                    for="productGlobalSearch"
                    class="form-label fw-semibold"
                >
                    Recherche Référence / Produit
                </label>

                <div class="product-search-wrapper">

                    <div class="input-group product-search-input-group">

                        <span class="input-group-text bg-white">
                            <i class="bx bx-search"></i>
                        </span>

                        <input
                            type="text"
                            id="productGlobalSearch"
                            class="form-control"
                            placeholder="Rechercher par référence, désignation, marque ou modèle..."
                            autocomplete="off"
                        >

                        <button
                            type="button"
                            class="btn btn-secondary"
                            id="clearProductSearch"
                            title="Effacer la recherche"
                        >
                            <i class="bx bx-x"></i>
                            Réinitialiser
                        </button>

                    </div>

                    <div
                        id="productSearchResults"
                        class="product-search-results d-none"
                    ></div>

                </div>

                <div class="product-search-hint">
                    Commencez à saisir une référence, une désignation, une marque ou un modèle.
                    Cliquez ensuite sur le produit pour l’ajouter à la vente.
                </div>

            </div>


            {{-- =================================================
                 PRODUITS
            ================================================= --}}

            <div class="table-responsive">
                <table
                    class="table table-bordered align-middle mb-0"
                    id="itemsTable"
                >
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 360px;">
                                Référence / Produit
                            </th>

                            <th
                                style="min-width: 120px;"
                                class="text-center"
                            >
                                Stock
                            </th>

                            <th style="min-width: 260px;">
                                Prix unitaire
                            </th>

                            <th style="min-width: 120px;">
                                Quantité
                            </th>

                            <th style="min-width: 150px;">
                                Total
                            </th>

                            <th
                                style="min-width: 90px;"
                                class="text-center"
                            >
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody></tbody>
                </table>
            </div>


            <button
                type="button"
                class="btn btn-success mt-3"
                id="addProductButton"
            >
                <i class="bx bx-plus"></i>
                Ajouter produit
            </button>


            <hr class="my-4">


            {{-- =================================================
                 TOTAUX
            ================================================= --}}

            <div class="row justify-content-end">
                <div class="col-lg-5 col-md-6">

                    <div class="d-flex justify-content-between mb-3">
                        <strong>Sous-total :</strong>

                        <span>
                            <span id="subTotal">0.00</span>
                            FDJ
                        </span>
                    </div>


                    <div class="mb-3">
                        <label
                            for="discount"
                            class="form-label fw-semibold"
                        >
                            Remise (%)
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            name="discount"
                            id="discount"
                            value="{{ old('discount', 0) }}"
                            class="form-control"
                        >

                        @error('discount')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>


                    <div class="d-flex justify-content-between mb-3">
                        <strong>
                            Montant de la remise :
                        </strong>

                        <span class="text-danger">
                            -
                            <span id="discountAmount">0.00</span>
                            FDJ
                        </span>
                    </div>


                    <div class="d-flex justify-content-between mb-3">
                        <strong>
                            TVA (10 %) :
                        </strong>

                        <span>
                            <span id="tvaAmount">0.00</span>
                            FDJ
                        </span>
                    </div>


                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <h4 class="mb-0 fw-bold">
                            Total :
                        </h4>

                        <h3 class="text-primary fw-bold mb-0">
                            <span id="grandTotal">0.00</span>
                            FDJ
                        </h3>
                    </div>

                </div>
            </div>


            <input
                type="hidden"
                name="final_total"
                id="final_total_input"
                value="0"
            >

        </div>


        <div class="card-footer bg-white text-end border-0">
            <button
                type="submit"
                class="btn btn-primary px-4"
            >
                <i class="bx bx-check-circle me-1"></i>
                Valider la vente
            </button>
        </div>

    </div>
</form>


{{-- =============================================================
     MODAL NOUVEAU CLIENT
============================================================= --}}

<div
    class="modal fade"
    id="customerModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Nouveau client
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fermer"
                ></button>
            </div>


            <div class="modal-body">

                <div
                    id="customerModalError"
                    class="alert alert-danger d-none"
                ></div>


                <div class="mb-3">
                    <label
                        for="customer_code"
                        class="form-label"
                    >
                        Code *
                    </label>

                    <input
                        type="text"
                        id="customer_code"
                        class="form-control"
                    >
                </div>


                <div class="mb-3">
                    <label
                        for="customer_name"
                        class="form-label"
                    >
                        Nom *
                    </label>

                    <input
                        type="text"
                        id="customer_name"
                        class="form-control"
                    >
                </div>


                <div class="mb-3">
                    <label
                        for="customer_phone"
                        class="form-label"
                    >
                        Téléphone
                    </label>

                    <input
                        type="text"
                        id="customer_phone"
                        class="form-control"
                    >
                </div>


                <div class="mb-3">
                    <label
                        for="customer_email"
                        class="form-label"
                    >
                        Email
                    </label>

                    <input
                        type="email"
                        id="customer_email"
                        class="form-control"
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
                    id="saveCustomerBtn"
                    class="btn btn-primary"
                >
                    Enregistrer
                </button>
            </div>

        </div>
    </div>
</div>


{{-- =============================================================
     JAVASCRIPT
============================================================= --}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    let rowIndex = 0;

    const itemsTableBody =
        document.querySelector('#itemsTable tbody');

    const addProductButton =
        document.getElementById('addProductButton');

    const discountInput =
        document.getElementById('discount');

    const customerSelect =
        document.getElementById('customer_id');

    const vehicleSelect =
        document.getElementById('vehicle_id');

    const paymentTypeSelect =
        document.getElementById('payment_type');

    const saleForm =
        document.getElementById('saleForm');

    const vehicleLoadingMessage =
        document.getElementById('vehicleLoadingMessage');

    const vehicleErrorMessage =
        document.getElementById('vehicleErrorMessage');

    const saveCustomerBtn =
        document.getElementById('saveCustomerBtn');

    const productGlobalSearch =
        document.getElementById('productGlobalSearch');

    const productSearchResults =
        document.getElementById('productSearchResults');

    const clearProductSearch =
        document.getElementById('clearProductSearch');


    /*
    |--------------------------------------------------------------------------
    | DONNÉES PRODUITS POUR LA RECHERCHE
    |--------------------------------------------------------------------------
    */

    @php
        $searchableProducts = $products
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'reference' => $product->reference ?? '',
                    'designation' => $product->designation ?? '',
                    'brand' => optional($product->brand)->name ?? '',
                    'model' => optional($product->model)->name ?? '',
                    'stock' => (float) ($product->quantity ?? 0),
                    'price' => (float) ($product->sale_price ?? 0),
                    'unit' => $product->unit_label ?? 'Pièce',
                ];
            })
            ->values();
    @endphp

    const searchableProducts = @json($searchableProducts);


    /*
    |--------------------------------------------------------------------------
    | SELECT2
    |--------------------------------------------------------------------------
    */

    if (
        window.jQuery &&
        $.fn.select2
    ) {
        $('#customer_id').select2({
            width: '100%',
            placeholder: 'Rechercher un client',
            allowClear: true
        });

        $('#vehicle_id').select2({
            width: '100%',
            placeholder: 'Sélectionnez d’abord un client',
            allowClear: true
        });

        $('#payment_type').select2({
            width: '100%',
            placeholder: 'Sélectionner un mode de paiement',
            allowClear: true
        });
    }


    /*
    |--------------------------------------------------------------------------
    | POPUP
    |--------------------------------------------------------------------------
    */

    function showWarning(title, message) {
        Swal.fire({
            icon: 'warning',
            title: title,
            text: message,
            confirmButtonText: 'Compris',
            confirmButtonColor: '#696cff',
            background: '#ffffff',
            color: '#344054',
            width: 440,
            padding: '2rem',
            buttonsStyling: true,
            allowOutsideClick: false,
            allowEscapeKey: true,
            showCloseButton: true,
            customClass: {
                popup: 'stcd-swal-popup',
                title: 'stcd-swal-title',
                htmlContainer: 'stcd-swal-message',
                confirmButton: 'stcd-swal-confirm'
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | FORMATAGE
    |--------------------------------------------------------------------------
    */

    function formatNumber(value) {
        return new Intl.NumberFormat('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(parseFloat(value) || 0);
    }

    function normalizeSearchText(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function escapeProductHtml(value) {
        const div = document.createElement('div');
        div.textContent = String(value ?? '');
        return div.innerHTML;
    }


    /*
    |--------------------------------------------------------------------------
    | RECHERCHE RAPIDE PRODUIT
    |--------------------------------------------------------------------------
    */

    function hideProductSearchResults() {
        if (!productSearchResults) {
            return;
        }

        productSearchResults.innerHTML = '';
        productSearchResults.classList.add('d-none');
    }

    function getAlreadySelectedProductIds() {
        return Array.from(
            itemsTableBody.querySelectorAll('.product-select')
        )
        .map(function (select) {
            return String(select.value || '');
        })
        .filter(Boolean);
    }

    function searchProducts(searchValue) {
        const search = normalizeSearchText(searchValue);

        if (search.length < 1) {
            hideProductSearchResults();
            return;
        }

        const alreadySelectedIds =
            getAlreadySelectedProductIds();

        const results = searchableProducts
            .filter(function (product) {

                if (parseFloat(product.stock) <= 0) {
                    return false;
                }

                const searchableText =
                    normalizeSearchText([
                        product.reference,
                        product.designation,
                        product.brand,
                        product.model
                    ].join(' '));

                return searchableText.includes(search);
            })
            .sort(function (a, b) {

                const aRef =
                    normalizeSearchText(a.reference);

                const bRef =
                    normalizeSearchText(b.reference);

                const aDesignation =
                    normalizeSearchText(a.designation);

                const bDesignation =
                    normalizeSearchText(b.designation);

                const aExact =
                    aRef === search ||
                    aDesignation === search;

                const bExact =
                    bRef === search ||
                    bDesignation === search;

                if (aExact && !bExact) {
                    return -1;
                }

                if (!aExact && bExact) {
                    return 1;
                }

                const aStarts =
                    aRef.startsWith(search) ||
                    aDesignation.startsWith(search);

                const bStarts =
                    bRef.startsWith(search) ||
                    bDesignation.startsWith(search);

                if (aStarts && !bStarts) {
                    return -1;
                }

                if (!aStarts && bStarts) {
                    return 1;
                }

                return String(a.reference)
                    .localeCompare(
                        String(b.reference),
                        'fr'
                    );
            })
            .slice(0, 20);

        if (results.length === 0) {
            productSearchResults.innerHTML = `
                <div class="product-search-no-result">
                    <i class="bx bx-search-alt mb-1"></i>
                    <div>Aucun produit disponible trouvé.</div>
                </div>
            `;

            productSearchResults.classList.remove('d-none');
            return;
        }

        productSearchResults.innerHTML =
            results.map(function (product) {

                const brandModel = [
                    product.brand,
                    product.model
                ]
                .filter(Boolean)
                .join(' - ');

                const isAlreadySelected =
                    alreadySelectedIds.includes(
                        String(product.id)
                    );

                return `
                    <button
                        type="button"
                        class="product-search-result-item"
                        data-product-id="${product.id}"
                        ${isAlreadySelected ? 'data-already-selected="1"' : ''}
                    >
                        <div>
                            <div class="product-search-reference">
                                ${escapeProductHtml(product.reference)}
                            </div>

                            <div class="product-search-designation">
                                ${escapeProductHtml(product.designation)}
                            </div>

                            ${
                                brandModel
                                    ? `
                                        <div class="product-search-meta">
                                            ${escapeProductHtml(brandModel)}
                                        </div>
                                    `
                                    : ''
                            }

                            <div class="product-search-price">
                                Prix :
                                ${formatNumber(product.price)}
                                FDJ
                                ${
                                    isAlreadySelected
                                        ? ' — déjà ajouté'
                                        : ''
                                }
                            </div>
                        </div>

                        <div class="product-search-stock">
                            ${formatNumber(product.stock)}
                            ${escapeProductHtml(product.unit)}
                        </div>
                    </button>
                `;
            }).join('');

        productSearchResults.classList.remove('d-none');
    }


    /*
    |--------------------------------------------------------------------------
    | AJOUTER UNE LIGNE PRODUIT
    |--------------------------------------------------------------------------
    */

    function addRow(oldItem = null) {

        const currentIndex = rowIndex;

        const row =
            document.createElement('tr');

        row.innerHTML = `
            <td>
                <select
                    name="items[${currentIndex}][product_id]"
                    class="form-control product-select"
                    required
                >
                    <option value="">
                        Choisir un produit
                    </option>

                    @foreach($products as $product)
                        <option
                            value="{{ $product->id }}"
                            data-price="{{ (float) $product->sale_price }}"
                            data-stock="{{ (float) $product->quantity }}"
                            data-unit="{{ $product->unit_label ?? 'Pièce' }}"
                        >
                            {{ $product->reference }}
                            |
                            {{ $product->designation }}
                            |
                            {{ $product->brand->name ?? '' }}
                            |
                            {{ $product->model->name ?? '' }}
                        </option>
                    @endforeach
                </select>
            </td>

            <td class="text-center fw-bold">
                <span id="stock_${currentIndex}">0</span>

                <br>

                <small
                    id="stock_unit_${currentIndex}"
                    class="text-muted"
                >
                    Pièce
                </small>
            </td>

            <td style="min-width: 260px;">
                <input
                    type="hidden"
                    name="items[${currentIndex}][price]"
                    id="price_${currentIndex}"
                    value="0"
                >

                <div class="d-flex align-items-center justify-content-end gap-2">
                    <div
                        class="form-control text-end fw-bold bg-white price-display"
                        id="price_display_${currentIndex}"
                    >
                        0.00
                    </div>

                    <span class="text-nowrap">
                        FDJ
                    </span>
                </div>
            </td>

            <td>
                <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="items[${currentIndex}][quantity]"
                    id="qty_${currentIndex}"
                    class="form-control"
                    value="1"
                    required
                >
            </td>

            <td class="fw-bold text-end">
                <span
                    id="total_${currentIndex}"
                    class="line-total"
                >
                    0.00
                </span>
                FDJ
            </td>

            <td class="text-center">
                <button
                    type="button"
                    class="btn btn-danger btn-sm remove-row"
                    title="Supprimer cette ligne"
                >
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        `;


        itemsTableBody.appendChild(row);


        const productSelect =
            row.querySelector('.product-select');

        const quantityInput =
            row.querySelector(`#qty_${currentIndex}`);

        const removeButton =
            row.querySelector('.remove-row');


        if (
            window.jQuery &&
            $.fn.select2
        ) {
            $(productSelect).select2({
                width: '100%',
                placeholder:
                    'Rechercher par référence, désignation, marque ou modèle',
                allowClear: true
            });

            $(productSelect).on(
                'change',
                function () {
                    updatePriceAndStock(
                        this,
                        currentIndex
                    );
                }
            );
        } else {
            productSelect.addEventListener(
                'change',
                function () {
                    updatePriceAndStock(
                        this,
                        currentIndex
                    );
                }
            );
        }


        quantityInput.addEventListener(
            'input',
            function () {
                calculateRow(
                    currentIndex
                );
            }
        );


        removeButton.addEventListener(
            'click',
            function () {

                if (
                    window.jQuery &&
                    $.fn.select2 &&
                    $(productSelect)
                        .hasClass('select2-hidden-accessible')
                ) {
                    $(productSelect)
                        .select2('destroy');
                }

                row.remove();

                if (
                    itemsTableBody
                        .querySelectorAll('tr')
                        .length === 0
                ) {
                    addRow();
                }

                calculateGrandTotal();
            }
        );


        if (
            oldItem &&
            oldItem.product_id
        ) {
            selectProductInRow(
                productSelect,
                oldItem.product_id
            );

            quantityInput.value =
                oldItem.quantity ?? 1;

            calculateRow(
                currentIndex
            );
        }


        rowIndex++;

        return productSelect;
    }


    /*
    |--------------------------------------------------------------------------
    | SÉLECTIONNER UN PRODUIT DANS UNE LIGNE
    |--------------------------------------------------------------------------
    */

    function selectProductInRow(
        productSelect,
        productId
    ) {
        if (!productSelect) {
            return;
        }

        productSelect.value =
            String(productId);

        if (
            window.jQuery &&
            $.fn.select2
        ) {
            $(productSelect)
                .val(String(productId))
                .trigger('change');
        } else {
            productSelect.dispatchEvent(
                new Event(
                    'change',
                    {
                        bubbles: true
                    }
                )
            );
        }
    }

    function getEmptyProductRow() {
        const selects =
            itemsTableBody
                .querySelectorAll(
                    '.product-select'
                );

        for (const select of selects) {
            if (!select.value) {
                return select;
            }
        }

        return null;
    }

    function findSelectedProductRow(productId) {
        const selects =
            itemsTableBody
                .querySelectorAll(
                    '.product-select'
                );

        for (const select of selects) {
            if (
                String(select.value)
                ===
                String(productId)
            ) {
                return select.closest('tr');
            }
        }

        return null;
    }

    function selectProductFromSearch(productId) {

        const existingRow =
            findSelectedProductRow(productId);

        if (existingRow) {
            const quantityInput =
                existingRow.querySelector(
                    'input[name$="[quantity]"]'
                );

            if (quantityInput) {
                quantityInput.focus();
                quantityInput.select();
            }

            hideProductSearchResults();

            productGlobalSearch.value = '';

            showWarning(
                'Produit déjà ajouté',
                'Ce produit se trouve déjà dans la vente. Modifiez sa quantité dans la ligne existante.'
            );

            return;
        }

        let productSelect =
            getEmptyProductRow();

        if (!productSelect) {
            productSelect =
                addRow();
        }

        selectProductInRow(
            productSelect,
            productId
        );

        if (productGlobalSearch) {
            productGlobalSearch.value = '';
        }

        hideProductSearchResults();

        const row =
            productSelect.closest('tr');

        const quantityInput =
            row?.querySelector(
                'input[name$="[quantity]"]'
            );

        if (quantityInput) {
            quantityInput.focus();
            quantityInput.select();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ÉVÉNEMENTS RECHERCHE
    |--------------------------------------------------------------------------
    */

    if (productGlobalSearch) {
        productGlobalSearch.addEventListener(
            'input',
            function () {
                searchProducts(
                    this.value
                );
            }
        );

        productGlobalSearch.addEventListener(
            'focus',
            function () {
                if (
                    this.value.trim()
                    !==
                    ''
                ) {
                    searchProducts(
                        this.value
                    );
                }
            }
        );

        productGlobalSearch.addEventListener(
            'keydown',
            function (event) {
                if (event.key === 'Escape') {
                    hideProductSearchResults();
                    this.blur();
                }
            }
        );
    }

    if (productSearchResults) {
        productSearchResults.addEventListener(
            'click',
            function (event) {

                const result =
                    event.target.closest(
                        '.product-search-result-item'
                    );

                if (!result) {
                    return;
                }

                const productId =
                    result.dataset.productId;

                if (!productId) {
                    return;
                }

                selectProductFromSearch(
                    productId
                );
            }
        );
    }

    if (clearProductSearch) {
        clearProductSearch.addEventListener(
            'click',
            function () {

                if (productGlobalSearch) {
                    productGlobalSearch.value = '';
                    productGlobalSearch.focus();
                }

                hideProductSearchResults();
            }
        );
    }

    document.addEventListener(
        'click',
        function (event) {

            if (
                !event.target.closest(
                    '.product-search-wrapper'
                )
            ) {
                hideProductSearchResults();
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | PRIX ET STOCK
    |--------------------------------------------------------------------------
    */

    function updatePriceAndStock(
        select,
        index
    ) {

        const selectedOption =
            select.options[
                select.selectedIndex
            ];

        const price =
            parseFloat(
                selectedOption?.dataset?.price || 0
            ) || 0;

        const stock =
            parseFloat(
                selectedOption?.dataset?.stock || 0
            ) || 0;

        const unit =
            selectedOption?.dataset?.unit ||
            'Pièce';


        const priceInput =
            document.getElementById(
                `price_${index}`
            );

        const priceDisplay =
            document.getElementById(
                `price_display_${index}`
            );

        const stockDisplay =
            document.getElementById(
                `stock_${index}`
            );

        const stockUnit =
            document.getElementById(
                `stock_unit_${index}`
            );


        if (priceInput) {
            priceInput.value =
                price.toFixed(2);
        }

        if (priceDisplay) {
            priceDisplay.textContent =
                formatNumber(price);
        }

        if (stockDisplay) {
            stockDisplay.textContent =
                stock;
        }

        if (stockUnit) {
            stockUnit.textContent =
                unit;
        }


        calculateRow(
            index
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CALCUL D'UNE LIGNE
    |--------------------------------------------------------------------------
    */

    function calculateRow(index) {

        const priceInput =
            document.getElementById(
                `price_${index}`
            );

        const quantityInput =
            document.getElementById(
                `qty_${index}`
            );

        const stockElement =
            document.getElementById(
                `stock_${index}`
            );

        const unitElement =
            document.getElementById(
                `stock_unit_${index}`
            );

        const totalElement =
            document.getElementById(
                `total_${index}`
            );


        if (
            !priceInput ||
            !quantityInput ||
            !stockElement ||
            !totalElement
        ) {
            return;
        }


        const price =
            parseFloat(
                priceInput.value
            ) || 0;

        let quantity =
            parseFloat(
                quantityInput.value
            ) || 0;

        const stock =
            parseFloat(
                stockElement.textContent
            ) || 0;

        const unit =
            unitElement?.textContent ||
            'Pièce';


        if (
            quantity > stock &&
            stock >= 0
        ) {

            showWarning(
                'Stock insuffisant',
                `Stock disponible : ${stock} ${unit}`
            );

            quantity = stock;

            quantityInput.value =
                stock;
        }


        const lineTotal =
            price * quantity;

        totalElement.textContent =
            lineTotal.toFixed(2);

        calculateGrandTotal();
    }


    /*
    |--------------------------------------------------------------------------
    | CALCUL TOTAL
    |--------------------------------------------------------------------------
    */

    function calculateGrandTotal() {

        let subtotal = 0;

        document
            .querySelectorAll('.line-total')
            .forEach(function (element) {
                subtotal +=
                    parseFloat(
                        element.textContent
                    ) || 0;
            });


        let discountPercent =
            parseFloat(
                discountInput.value
            ) || 0;


        if (discountPercent < 0) {
            discountPercent = 0;
            discountInput.value = 0;
        }

        if (discountPercent > 100) {
            discountPercent = 100;
            discountInput.value = 100;
        }


        const discountAmount =
            subtotal *
            discountPercent /
            100;

        const taxable =
            Math.max(
                0,
                subtotal - discountAmount
            );

        const tva =
            taxable * 0.10;

        const total =
            taxable + tva;


        document.getElementById(
            'subTotal'
        ).textContent =
            subtotal.toFixed(2);

        document.getElementById(
            'discountAmount'
        ).textContent =
            discountAmount.toFixed(2);

        document.getElementById(
            'tvaAmount'
        ).textContent =
            tva.toFixed(2);

        document.getElementById(
            'grandTotal'
        ).textContent =
            total.toFixed(2);

        document.getElementById(
            'final_total_input'
        ).value =
            total.toFixed(2);
    }


    /*
    |--------------------------------------------------------------------------
    | CLIENT -> VÉHICULES
    |--------------------------------------------------------------------------
    */

    async function loadVehicles(
        customerId,
        selectedVehicleId = null
    ) {

        vehicleErrorMessage
            .classList
            .add('d-none');

        vehicleErrorMessage.textContent =
            '';

        vehicleSelect.innerHTML =
            '';


        if (!customerId) {

            vehicleSelect.innerHTML =
                '<option value="">Sélectionnez d’abord un client</option>';

            vehicleSelect.disabled =
                true;

            if (
                window.jQuery &&
                $.fn.select2
            ) {
                $('#vehicle_id')
                    .val('')
                    .trigger('change.select2');
            }

            return;
        }


        vehicleLoadingMessage
            .classList
            .remove('d-none');

        vehicleSelect.disabled =
            true;

        vehicleSelect.innerHTML =
            '<option value="">Chargement des véhicules...</option>';


        try {

            const url =
                "{{ url('/sales/customers') }}/" +
                encodeURIComponent(customerId) +
                "/vehicles";


            const response =
                await fetch(
                    url,
                    {
                        method: 'GET',

                        headers: {
                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest'
                        },

                        cache:
                            'no-store'
                    }
                );


            if (!response.ok) {
                throw new Error(
                    'Erreur HTTP ' +
                    response.status
                );
            }


            const data =
                await response.json();


            vehicleSelect.innerHTML =
                '';


            const defaultOption =
                document.createElement(
                    'option'
                );

            defaultOption.value =
                '';

            defaultOption.textContent =
                'Sélectionner une immatriculation';

            vehicleSelect.appendChild(
                defaultOption
            );


            if (
                data.success === true &&
                Array.isArray(data.vehicles) &&
                data.vehicles.length > 0
            ) {

                data.vehicles.forEach(
                    function (vehicle) {

                        const option =
                            document.createElement(
                                'option'
                            );

                        option.value =
                            vehicle.id;

                        option.textContent =
                            vehicle.label ||
                            vehicle.plate_number ||
                            (
                                'Véhicule #' +
                                vehicle.id
                            );

                        option.dataset.customerId =
                            vehicle.customer_id ||
                            customerId;

                        if (
                            selectedVehicleId &&
                            String(vehicle.id) ===
                            String(selectedVehicleId)
                        ) {
                            option.selected =
                                true;
                        }

                        vehicleSelect
                            .appendChild(
                                option
                            );
                    }
                );


                vehicleSelect.disabled =
                    false;


                if (selectedVehicleId) {
                    vehicleSelect.value =
                        String(
                            selectedVehicleId
                        );
                } else {
                    vehicleSelect.value =
                        '';
                }


                if (
                    window.jQuery &&
                    $.fn.select2
                ) {
                    $('#vehicle_id')
                        .val(
                            selectedVehicleId
                                ? String(selectedVehicleId)
                                : ''
                        )
                        .trigger(
                            'change.select2'
                        );
                }

            } else {

                vehicleSelect.innerHTML =
                    '<option value="">Aucun véhicule associé à ce client</option>';

                vehicleSelect.disabled =
                    true;

                vehicleErrorMessage
                    .classList
                    .remove('d-none');

                vehicleErrorMessage.textContent =
                    'Ce client ne possède actuellement aucun véhicule associé.';
            }

        } catch (error) {

            console.error(
                'Erreur chargement véhicules :',
                error
            );

            vehicleSelect.innerHTML =
                '<option value="">Erreur lors du chargement</option>';

            vehicleSelect.disabled =
                true;

            vehicleErrorMessage
                .classList
                .remove('d-none');

            vehicleErrorMessage.textContent =
                'Impossible de charger les véhicules du client.';

        } finally {

            vehicleLoadingMessage
                .classList
                .add('d-none');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CHANGEMENT CLIENT
    |--------------------------------------------------------------------------
    */

    if (
        window.jQuery &&
        $.fn.select2
    ) {

        $('#customer_id').on(
            'change',
            function () {
                loadVehicles(
                    $(this).val()
                );
            }
        );

    } else {

        customerSelect.addEventListener(
            'change',
            function () {
                loadVehicles(
                    this.value
                );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | NOUVEAU CLIENT AJAX
    |--------------------------------------------------------------------------
    */

    saveCustomerBtn.addEventListener(
        'click',
        async function () {

            const code =
                document
                    .getElementById(
                        'customer_code'
                    )
                    .value
                    .trim();

            const name =
                document
                    .getElementById(
                        'customer_name'
                    )
                    .value
                    .trim();

            const phone =
                document
                    .getElementById(
                        'customer_phone'
                    )
                    .value
                    .trim();

            const email =
                document
                    .getElementById(
                        'customer_email'
                    )
                    .value
                    .trim();

            const errorBox =
                document.getElementById(
                    'customerModalError'
                );


            errorBox.classList.add(
                'd-none'
            );

            errorBox.innerHTML =
                '';


            if (!code || !name) {

                errorBox.innerHTML =
                    'Le code et le nom du client sont obligatoires.';

                errorBox.classList.remove(
                    'd-none'
                );

                return;
            }


            try {

                saveCustomerBtn.disabled = true;
                saveCustomerBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1"></span> Enregistrement...';

                const response =
                    await fetch(
                        "{{ route('customers.store') }}",
                        {
                            method:
                                'POST',

                            headers: {
                                'Content-Type':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                    '{{ csrf_token() }}',

                                'Accept':
                                    'application/json'
                            },

                            body:
                                JSON.stringify({
                                    code: code,
                                    name: name,
                                    phone: phone,
                                    email: email
                                })
                        }
                    );


                const data =
                    await response.json();


                if (!response.ok) {
                    throw data;
                }


                if (
                    !data.success ||
                    !data.customer
                ) {
                    throw new Error(
                        'La réponse du serveur est invalide.'
                    );
                }


                const option =
                    new Option(
                        data.customer.name,
                        data.customer.id,
                        true,
                        true
                    );


                customerSelect.add(
                    option
                );


                if (
                    window.jQuery &&
                    $.fn.select2
                ) {

                    $('#customer_id')
                        .val(
                            String(
                                data.customer.id
                            )
                        )
                        .trigger('change');

                } else {

                    customerSelect.value =
                        String(
                            data.customer.id
                        );

                    customerSelect
                        .dispatchEvent(
                            new Event(
                                'change'
                            )
                        );
                }


                document.getElementById(
                    'customer_code'
                ).value = '';

                document.getElementById(
                    'customer_name'
                ).value = '';

                document.getElementById(
                    'customer_phone'
                ).value = '';

                document.getElementById(
                    'customer_email'
                ).value = '';


                const modalElement =
                    document.getElementById(
                        'customerModal'
                    );


                if (
                    window.bootstrap &&
                    modalElement
                ) {

                    const modal =
                        bootstrap.Modal
                            .getOrCreateInstance(
                                modalElement
                            );

                    modal.hide();
                }


                Swal.fire({
                    icon: 'success',
                    title: 'Client créé',
                    text: 'Le client a été créé avec succès.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#696cff',
                    customClass: {
                        popup: 'stcd-swal-popup',
                        title: 'stcd-swal-title',
                        htmlContainer: 'stcd-swal-message',
                        confirmButton: 'stcd-swal-confirm'
                    }
                });

            } catch (error) {

                let message =
                    'Une erreur est survenue pendant la création du client.';


                if (
                    error &&
                    error.errors
                ) {
                    message =
                        Object
                            .values(
                                error.errors
                            )
                            .flat()
                            .join('<br>');
                } else if (
                    error &&
                    error.message
                ) {
                    message =
                        error.message;
                }


                errorBox.innerHTML =
                    message;

                errorBox.classList.remove(
                    'd-none'
                );

            } finally {

                saveCustomerBtn.disabled = false;
                saveCustomerBtn.innerHTML =
                    'Enregistrer';
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | AJOUT PRODUIT
    |--------------------------------------------------------------------------
    */

    addProductButton.addEventListener(
        'click',
        function () {
            addRow();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | REMISE
    |--------------------------------------------------------------------------
    */

    discountInput.addEventListener(
        'input',
        calculateGrandTotal
    );


    /*
    |--------------------------------------------------------------------------
    | RESTAURATION OLD()
    |--------------------------------------------------------------------------
    */

    @if(is_array(old('items')) && count(old('items')) > 0)

        const oldItems =
            @json(old('items'));

        oldItems.forEach(
            function (item) {
                addRow(item);
            }
        );

    @else

        addRow();

    @endif


    /*
    |--------------------------------------------------------------------------
    | RESTAURATION CLIENT / VÉHICULE
    |--------------------------------------------------------------------------
    */

    const initialCustomerId =
        customerSelect.value;

    const oldVehicleId =
        vehicleSelect.dataset.selected ||
        null;


    if (initialCustomerId) {
        loadVehicles(
            initialCustomerId,
            oldVehicleId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATION FORMULAIRE
    |--------------------------------------------------------------------------
    */

    saleForm.addEventListener(
        'submit',
        function (event) {

            if (
                !customerSelect.value &&
                !vehicleSelect.value
            ) {

                event.preventDefault();

                showWarning(
                    'Informations obligatoires',
                    'Veuillez sélectionner le client et son véhicule.'
                );

                return;
            }


            if (!customerSelect.value) {

                event.preventDefault();

                showWarning(
                    'Client obligatoire',
                    'Veuillez sélectionner un client avant de valider la vente.'
                );

                return;
            }


            if (!vehicleSelect.value) {

                event.preventDefault();

                showWarning(
                    'Véhicule obligatoire',
                    'Veuillez sélectionner le véhicule associé au client.'
                );

                return;
            }


            if (
                !paymentTypeSelect ||
                !paymentTypeSelect.value
            ) {

                event.preventDefault();

                showWarning(
                    'Mode de paiement obligatoire',
                    'Veuillez sélectionner un mode de paiement.'
                );

                return;
            }


            const rows =
                itemsTableBody.querySelectorAll('tr');


            if (rows.length === 0) {

                event.preventDefault();

                showWarning(
                    'Produit obligatoire',
                    'Veuillez ajouter au moins un produit.'
                );

                return;
            }


            let validProduct =
                false;


            rows.forEach(
                function (row) {

                    const select =
                        row.querySelector(
                            '.product-select'
                        );

                    if (
                        select &&
                        select.value
                    ) {
                        validProduct =
                            true;
                    }
                }
            );


            if (!validProduct) {

                event.preventDefault();

                showWarning(
                    'Produit obligatoire',
                    'Veuillez sélectionner au moins un produit.'
                );

                return;
            }


            let invalidQuantity =
                false;


            rows.forEach(
                function (row) {

                    const select =
                        row.querySelector(
                            '.product-select'
                        );

                    if (
                        !select ||
                        !select.value
                    ) {
                        return;
                    }

                    const quantityInput =
                        row.querySelector(
                            'input[name$="[quantity]"]'
                        );

                    if (
                        quantityInput &&
                        (
                            !quantityInput.value ||
                            parseFloat(
                                quantityInput.value
                            ) <= 0
                        )
                    ) {
                        invalidQuantity =
                            true;
                    }
                }
            );


            if (invalidQuantity) {

                event.preventDefault();

                showWarning(
                    'Quantité invalide',
                    'Veuillez saisir une quantité supérieure à zéro pour chaque produit.'
                );

                return;
            }
        }
    );


    calculateGrandTotal();

});
</script>

@endsection
