@extends('layouts.layoutMaster')

@section('title', 'Nouvelle vente')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@php
    /*
    |--------------------------------------------------------------------------
    | DONNÉES PRODUITS + STOCKS PAR DÉPÔT
    |--------------------------------------------------------------------------
    |
    | Le contrôleur Sales doit charger :
    | - brand
    | - model
    | - depotStocks.depot
    |
    | Un même produit peut exister dans plusieurs dépôts.
    |
    */
    $searchableProducts = $products
        ->map(function ($product) {
            $depots = $product->depotStocks
                ->filter(function ($stock) {
                    return
                        (float) $stock->quantity > 0
                        &&
                        $stock->depot !== null;
                })
                ->map(function ($stock) {
                    return [
                        'depot_id' =>
                            (int) $stock->depot_id,

                        'name' =>
                            (string) $stock->depot->name,

                        'code' =>
                            (string) ($stock->depot->code ?? ''),

                        'quantity' =>
                            (float) $stock->quantity,
                    ];
                })
                ->values();

            return [
                'id' =>
                    (int) $product->id,

                'reference' =>
                    (string) ($product->reference ?? ''),

                'designation' =>
                    (string) ($product->designation ?? ''),

                'brand' =>
                    (string) (optional($product->brand)->name ?? ''),

                'model' =>
                    (string) (optional($product->model)->name ?? ''),

                'price' =>
                    (float) ($product->sale_price ?? 0),

                'unit' =>
                    (string) ($product->unit_label ?? 'Pièce'),

                'stock' =>
                    (float) $depots->sum('quantity'),

                'depots' =>
                    $depots->all(),
            ];
        })
        ->values();
@endphp

<style>
    .sale-card {
        border: 1px solid #e8edf3 !important;
        border-radius: 18px;
        box-shadow: 0 12px 35px rgba(67, 89, 113, .08);
        overflow: visible;
    }

    .sale-card .card-header {
        padding: 24px 28px 16px;
        border: 0;
        background: linear-gradient(
            135deg,
            rgba(105, 108, 255, .07),
            #fff 70%
        );
        border-radius: 18px 18px 0 0;
    }

    .sale-title {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .sale-title-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(105, 108, 255, .12);
        color: #696cff;
        font-size: 24px;
        flex: 0 0 auto;
    }

    .sale-subtitle {
        margin-top: 3px;
        color: #8592a3;
        font-size: 13px;
    }

    .sale-section {
        padding: 20px;
        margin-bottom: 22px;
        border: 1px solid #edf0f4;
        border-radius: 14px;
        background: #fff;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
        color: #566a7f;
        font-size: 15px;
        font-weight: 800;
    }

    .section-title i {
        color: #696cff;
        font-size: 20px;
    }

    .form-label {
        color: #5d6b7e;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .vehicle-message {
        min-height: 20px;
    }

    .product-search-wrapper {
        position: relative;
        z-index: 50;
    }

    .product-search-results {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        z-index: 1090;
        max-height: 380px;
        overflow-y: auto;
        border: 1px solid #d9dee3;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 12px 30px rgba(67, 89, 113, .18);
    }

    .product-search-result-item {
        width: 100%;
        padding: 12px 15px;
        border: 0;
        border-bottom: 1px solid #edf0f4;
        background: #fff;
        text-align: left;
        cursor: pointer;
    }

    .product-search-result-item:hover {
        background: #f6f7ff;
    }

    .product-search-reference {
        color: #566a7f;
        font-weight: 800;
    }

    .product-search-designation {
        margin-top: 2px;
        color: #697a8d;
        font-size: 13px;
    }

    .product-search-meta {
        margin-top: 3px;
        color: #8592a3;
        font-size: 12px;
    }

    .product-search-depots {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 7px;
    }

    .depot-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 8px;
        border-radius: 999px;
        background: #f2f3ff;
        color: #5d5fef;
        font-size: 11px;
        font-weight: 800;
    }

    .items-panel {
        overflow-x: auto;
        border: 1px solid #e5e9ef;
        border-radius: 14px;
    }

    #itemsTable {
        min-width: 1250px;
        margin-bottom: 0;
    }

    #itemsTable th {
        padding: 14px 14px;
        white-space: nowrap;
        vertical-align: middle;
        background: #f4f6f8;
        color: #607085;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    #itemsTable td {
        padding: 12px 12px;
        vertical-align: middle;
    }

    .price-display {
        min-width: 125px;
        text-align: right;
        font-weight: 800;
    }

    .depot-select {
        min-width: 245px;
        font-size: 13px;
        font-weight: 700;
    }

    .depot-list {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 7px;
    }

    .depot-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 8px;
        border: 1px solid #e0e5ec;
        border-radius: 999px;
        background: #fafbfc;
        color: #697a8d;
        font-size: 11px;
        font-weight: 700;
    }

    .depot-badge strong {
        color: #28a745;
    }

    .selected-depot-stock {
        margin-top: 6px;
        min-height: 17px;
        color: #8592a3;
        font-size: 11px;
    }

    .summary-card {
        padding: 20px 22px;
        border: 1px solid #e7eaf0;
        border-radius: 16px;
        background: linear-gradient(
            180deg,
            #fff,
            #fafbff
        );
    }

    .summary-line {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 13px;
        color: #667085;
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        padding-top: 16px;
        margin-top: 14px;
        border-top: 1px dashed #d9dee7;
    }

    .summary-total-label {
        color: #344054;
        font-size: 18px;
        font-weight: 800;
    }

    .summary-total-value {
        color: #696cff;
        font-size: 26px;
        font-weight: 900;
    }

    .payment-note {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin-top: 18px;
        padding: 12px 14px;
        border-radius: 10px;
        background: rgba(3, 195, 236, .08);
        color: #566a7f;
        font-size: 13px;
    }

    .payment-note i {
        margin-top: 1px;
        color: #03c3ec;
        font-size: 18px;
    }

    .sale-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 18px 28px 24px;
        border: 0;
        background: #fff;
    }

    .generate-btn {
        min-width: 215px;
        border-radius: 10px;
        font-weight: 800;
        box-shadow: 0 8px 20px rgba(105, 108, 255, .22);
    }

    .stcd-swal-popup {
        border-radius: 18px !important;
        box-shadow: 0 20px 60px rgba(15, 23, 42, .20) !important;
    }

    @media (max-width: 767.98px) {
        .sale-card .card-header,
        .sale-card .card-body,
        .sale-actions {
            padding-left: 15px;
            padding-right: 15px;
        }

        .sale-section {
            padding: 15px;
        }

        .sale-actions {
            flex-direction: column-reverse;
        }

        .sale-actions .btn {
            width: 100%;
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

    <div class="card sale-card">

        <div class="card-header">
            <div class="sale-title">

                <div class="sale-title-icon">
                    <i class="bx bx-receipt"></i>
                </div>

                <div>
                    <h3 class="mb-0 fw-bold">
                        Nouvelle vente
                    </h3>

                    <div class="sale-subtitle">
                        Sélectionnez le client, le véhicule, les produits et
                        le dépôt utilisé pour chaque produit.
                    </div>
                </div>

            </div>
        </div>

        <div class="card-body">

            {{-- CLIENT / VÉHICULE --}}
            <div class="sale-section">

                <div class="section-title">
                    <i class="bx bx-user-circle"></i>
                    Informations de la vente
                </div>

                <div class="row g-3">

                    <div class="col-lg-6">

                        <label
                            for="customer_id"
                            class="form-label"
                        >
                            Client
                        </label>

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
                                    @selected(
                                        old('customer_id')
                                        ==
                                        $customer->id
                                    )
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

                    <div class="col-lg-6">

                        <label
                            for="vehicle_id"
                            class="form-label"
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

                </div>
            </div>

            {{-- RECHERCHE --}}
            <div class="sale-section">

                <div class="section-title">
                    <i class="bx bx-search-alt-2"></i>
                    Recherche produit
                </div>

                <label
                    for="productGlobalSearch"
                    class="form-label"
                >
                    Référence / produit
                </label>

                <div class="product-search-wrapper">

                    <div class="input-group">

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

                <small class="text-muted d-block mt-2">
                    Les quantités affichées proviennent directement des stocks
                    disponibles dans chaque dépôt.
                </small>

            </div>

            {{-- PRODUITS --}}
            <div class="section-title">
                <i class="bx bx-package"></i>
                Produits de la facture
            </div>

            <div class="items-panel">

                <table
                    class="table table-bordered align-middle"
                    id="itemsTable"
                >
                    <thead>
                        <tr>
                            <th style="min-width: 340px;">
                                Référence / produit
                            </th>

                            <th
                                style="min-width: 120px;"
                                class="text-center"
                            >
                                Stock total
                            </th>

                            <th style="min-width: 290px;">
                                Dépôt / stock disponible
                            </th>

                            <th style="min-width: 190px;">
                                Prix unitaire
                            </th>

                            <th style="min-width: 120px;">
                                Quantité
                            </th>

                            <th style="min-width: 145px;">
                                Total
                            </th>

                            <th
                                style="min-width: 80px;"
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

            {{-- TOTALS --}}
            <div class="row justify-content-end">

                <div class="col-xl-5 col-lg-6 col-md-7">

                    <div class="summary-card">

                        <div class="summary-line">
                            <strong>Sous-total :</strong>

                            <span>
                                <span id="subTotal">0.00</span>
                                FDJ
                            </span>
                        </div>

                        <div class="mb-3">

                            <label
                                for="discount"
                                class="form-label"
                            >
                                Remise (%)
                            </label>

                            <input
                                type="number"
                                name="discount"
                                id="discount"
                                class="form-control"
                                step="0.01"
                                min="0"
                                max="100"
                                value="{{ old('discount', 0) }}"
                            >

                        </div>

                        <div class="summary-line">
                            <strong>Montant de la remise :</strong>

                            <span class="text-danger">
                                -
                                <span id="discountAmount">0.00</span>
                                FDJ
                            </span>
                        </div>

                        <div class="summary-line">
                            <strong>TVA (10 %) :</strong>

                            <span>
                                <span id="tvaAmount">0.00</span>
                                FDJ
                            </span>
                        </div>

                        <div class="summary-total">

                            <span class="summary-total-label">
                                Total :
                            </span>

                            <span class="summary-total-value">
                                <span id="grandTotal">0.00</span>
                                FDJ
                            </span>

                        </div>

                        <div class="payment-note">
                            <i class="bx bx-info-circle"></i>

                            <div>
                                Le mode de paiement sera demandé uniquement
                                au moment du règlement de la facture.
                            </div>
                        </div>

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

        <div class="card-footer sale-actions">

            <a
                href="{{ route('sales.index') }}"
                class="btn btn-outline-secondary"
            >
                <i class="bx bx-arrow-back me-1"></i>
                Annuler
            </a>

            <button
                type="submit"
                class="btn btn-primary generate-btn"
            >
                <i class="bx bx-file me-1"></i>
                Générer la facture
            </button>

        </div>

    </div>
</form>

{{-- MODAL CLIENT --}}
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
                        Code
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
                        Nom
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

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        let rowIndex = 0;

        const products =
            @json($searchableProducts);

        const itemsTableBody =
            document.querySelector(
                '#itemsTable tbody'
            );

        const addProductButton =
            document.getElementById(
                'addProductButton'
            );

        const discountInput =
            document.getElementById(
                'discount'
            );

        const customerSelect =
            document.getElementById(
                'customer_id'
            );

        const vehicleSelect =
            document.getElementById(
                'vehicle_id'
            );

        const saleForm =
            document.getElementById(
                'saleForm'
            );

        const productGlobalSearch =
            document.getElementById(
                'productGlobalSearch'
            );

        const productSearchResults =
            document.getElementById(
                'productSearchResults'
            );

        const clearProductSearch =
            document.getElementById(
                'clearProductSearch'
            );

        const vehicleLoadingMessage =
            document.getElementById(
                'vehicleLoadingMessage'
            );

        const vehicleErrorMessage =
            document.getElementById(
                'vehicleErrorMessage'
            );

        const saveCustomerBtn =
            document.getElementById(
                'saveCustomerBtn'
            );

        function showWarning(
            title,
            message
        ) {
            Swal.fire({
                icon:
                    'warning',

                title:
                    title,

                text:
                    message,

                confirmButtonText:
                    'Compris',

                confirmButtonColor:
                    '#696cff',

                customClass: {
                    popup:
                        'stcd-swal-popup'
                }
            });
        }

        function formatNumber(value) {
            return new Intl.NumberFormat(
                'fr-FR',
                {
                    minimumFractionDigits:
                        2,

                    maximumFractionDigits:
                        2
                }
            ).format(
                parseFloat(value)
                ||
                0
            );
        }

        function normalize(value) {
            return String(
                value
                ||
                ''
            )
            .normalize('NFD')
            .replace(
                /[\u0300-\u036f]/g,
                ''
            )
            .toLowerCase()
            .trim();
        }

        function escapeHtml(value) {
            const div =
                document.createElement(
                    'div'
                );

            div.textContent =
                String(
                    value
                    ??
                    ''
                );

            return div.innerHTML;
        }

        function productById(id) {
            return products.find(
                product =>
                    String(product.id)
                    ===
                    String(id)
            );
        }

        function hideSearchResults() {
            productSearchResults.innerHTML =
                '';

            productSearchResults.classList.add(
                'd-none'
            );
        }

        function searchProducts(value) {
            const search =
                normalize(value);

            if (!search) {
                hideSearchResults();
                return;
            }

            const results =
                products
                    .filter(
                        function (product) {
                            if (
                                parseFloat(
                                    product.stock
                                )
                                <=
                                0
                            ) {
                                return false;
                            }

                            const haystack =
                                normalize([
                                    product.reference,
                                    product.designation,
                                    product.brand,
                                    product.model
                                ].join(' '));

                            return haystack.includes(
                                search
                            );
                        }
                    )
                    .slice(
                        0,
                        20
                    );

            if (
                results.length
                ===
                0
            ) {
                productSearchResults.innerHTML =
                    `
                        <div class="p-3 text-center text-muted">
                            Aucun produit disponible trouvé.
                        </div>
                    `;

                productSearchResults.classList.remove(
                    'd-none'
                );

                return;
            }

            productSearchResults.innerHTML =
                results
                    .map(
                        function (product) {
                            const depots =
                                (
                                    product.depots
                                    ||
                                    []
                                )
                                .map(
                                    function (depot) {
                                        return `
                                            <span class="depot-chip">
                                                <i class="bx bx-building-house"></i>
                                                ${escapeHtml(depot.name)}
                                                :
                                                ${formatNumber(depot.quantity)}
                                            </span>
                                        `;
                                    }
                                )
                                .join('');

                            const brandModel =
                                [
                                    product.brand,
                                    product.model
                                ]
                                .filter(Boolean)
                                .join(' - ');

                            return `
                                <button
                                    type="button"
                                    class="product-search-result-item"
                                    data-product-id="${product.id}"
                                >
                                    <div class="product-search-reference">
                                        ${escapeHtml(product.reference)}
                                    </div>

                                    <div class="product-search-designation">
                                        ${escapeHtml(product.designation)}
                                    </div>

                                    ${
                                        brandModel
                                            ? `
                                                <div class="product-search-meta">
                                                    ${escapeHtml(brandModel)}
                                                </div>
                                            `
                                            : ''
                                    }

                                    <div class="product-search-meta">
                                        Stock total :
                                        <strong>
                                            ${formatNumber(product.stock)}
                                            ${escapeHtml(product.unit)}
                                        </strong>
                                        —
                                        Prix :
                                        <strong>
                                            ${formatNumber(product.price)}
                                            FDJ
                                        </strong>
                                    </div>

                                    <div class="product-search-depots">
                                        ${depots}
                                    </div>
                                </button>
                            `;
                        }
                    )
                    .join('');

            productSearchResults.classList.remove(
                'd-none'
            );
        }

        function buildDepotOptions(
            product,
            selectedDepotId = null
        ) {
            const depots =
                product?.depots
                ||
                [];

            let html =
                '<option value="">Choisir le dépôt</option>';

            depots.forEach(
                function (depot) {
                    if (
                        parseFloat(
                            depot.quantity
                            ||
                            0
                        )
                        <=
                        0
                    ) {
                        return;
                    }

                    const selected =
                        selectedDepotId
                        &&
                        String(selectedDepotId)
                        ===
                        String(depot.depot_id)
                            ? 'selected'
                            : '';

                    html += `
                        <option
                            value="${depot.depot_id}"
                            data-quantity="${depot.quantity}"
                            ${selected}
                        >
                            ${escapeHtml(depot.name)}
                            ${
                                depot.code
                                    ? ` (${escapeHtml(depot.code)})`
                                    : ''
                            }
                            — ${formatNumber(depot.quantity)}
                            ${escapeHtml(product.unit)}
                        </option>
                    `;
                }
            );

            return html;
        }

        function buildDepotBadges(product) {
            const depots =
                product?.depots
                ||
                [];

            if (
                depots.length
                ===
                0
            ) {
                return `
                    <span class="text-muted small fst-italic">
                        Aucun stock dépôt
                    </span>
                `;
            }

            return depots
                .map(
                    function (depot) {
                        return `
                            <span class="depot-badge">
                                <i class="bx bx-building-house"></i>
                                ${escapeHtml(depot.name)}
                                <strong>
                                    ${formatNumber(depot.quantity)}
                                </strong>
                            </span>
                        `;
                    }
                )
                .join('');
        }

        function addRow(oldItem = null) {
            const currentIndex =
                rowIndex;

            const row =
                document.createElement(
                    'tr'
                );

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
                    <span
                        id="stock_${currentIndex}"
                    >
                        0.00
                    </span>

                    <br>

                    <small
                        id="stock_unit_${currentIndex}"
                        class="text-muted"
                    >
                        Pièce
                    </small>
                </td>

                <td>
                    <select
                        name="items[${currentIndex}][depot_id]"
                        id="depot_${currentIndex}"
                        class="form-select depot-select"
                        required
                        disabled
                    >
                        <option value="">
                            Sélectionnez d’abord un produit
                        </option>
                    </select>

                    <div
                        id="depot_list_${currentIndex}"
                        class="depot-list"
                    ></div>

                    <div
                        id="selected_depot_stock_${currentIndex}"
                        class="selected-depot-stock"
                    ></div>
                </td>

                <td>
                    <input
                        type="hidden"
                        name="items[${currentIndex}][price]"
                        id="price_${currentIndex}"
                        value="0"
                    >

                    <div class="d-flex align-items-center gap-2 justify-content-end">
                        <div
                            class="form-control price-display bg-white"
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
                        name="items[${currentIndex}][quantity]"
                        id="qty_${currentIndex}"
                        class="form-control"
                        min="0.01"
                        step="0.01"
                        value="1"
                        required
                    >
                </td>

                <td class="text-end fw-bold">
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
                        title="Supprimer"
                    >
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            `;

            itemsTableBody.appendChild(
                row
            );

            const productSelect =
                row.querySelector(
                    '.product-select'
                );

            const depotSelect =
                row.querySelector(
                    '.depot-select'
                );

            const quantityInput =
                row.querySelector(
                    `#qty_${currentIndex}`
                );

            const removeButton =
                row.querySelector(
                    '.remove-row'
                );

            function productChanged() {
                const product =
                    productById(
                        productSelect.value
                    );

                const stockElement =
                    document.getElementById(
                        `stock_${currentIndex}`
                    );

                const unitElement =
                    document.getElementById(
                        `stock_unit_${currentIndex}`
                    );

                const priceInput =
                    document.getElementById(
                        `price_${currentIndex}`
                    );

                const priceDisplay =
                    document.getElementById(
                        `price_display_${currentIndex}`
                    );

                const depotList =
                    document.getElementById(
                        `depot_list_${currentIndex}`
                    );

                if (!product) {
                    stockElement.textContent =
                        '0.00';

                    unitElement.textContent =
                        'Pièce';

                    priceInput.value =
                        '0';

                    priceDisplay.textContent =
                        '0.00';

                    depotSelect.innerHTML =
                        '<option value="">Sélectionnez d’abord un produit</option>';

                    depotSelect.disabled =
                        true;

                    depotList.innerHTML =
                        '';

                    updateSelectedDepotStock(
                        currentIndex
                    );

                    calculateRow(
                        currentIndex
                    );

                    return;
                }

                stockElement.textContent =
                    formatNumber(
                        product.stock
                    );

                unitElement.textContent =
                    product.unit;

                priceInput.value =
                    parseFloat(
                        product.price
                        ||
                        0
                    ).toFixed(2);

                priceDisplay.textContent =
                    formatNumber(
                        product.price
                    );

                depotSelect.innerHTML =
                    buildDepotOptions(
                        product,
                        oldItem?.depot_id
                    );

                depotSelect.disabled =
                    (
                        product.depots
                        ||
                        []
                    ).length === 0;

                depotList.innerHTML =
                    buildDepotBadges(
                        product
                    );

                updateSelectedDepotStock(
                    currentIndex
                );

                calculateRow(
                    currentIndex
                );
            }

            if (
                window.jQuery
                &&
                $.fn.select2
            ) {
                $(productSelect)
                    .select2({
                        width:
                            '100%',

                        placeholder:
                            'Rechercher un produit',

                        allowClear:
                            true
                    });

                $(productSelect)
                    .on(
                        'change',
                        productChanged
                    );
            } else {
                productSelect.addEventListener(
                    'change',
                    productChanged
                );
            }

            depotSelect.addEventListener(
                'change',
                function () {
                    updateSelectedDepotStock(
                        currentIndex
                    );

                    calculateRow(
                        currentIndex
                    );
                }
            );

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
                        window.jQuery
                        &&
                        $.fn.select2
                        &&
                        $(productSelect)
                            .hasClass(
                                'select2-hidden-accessible'
                            )
                    ) {
                        $(productSelect)
                            .select2(
                                'destroy'
                            );
                    }

                    row.remove();

                    if (
                        itemsTableBody
                            .querySelectorAll(
                                'tr'
                            )
                            .length
                        ===
                        0
                    ) {
                        addRow();
                    }

                    calculateGrandTotal();
                }
            );

            if (
                oldItem
                &&
                oldItem.product_id
            ) {
                if (
                    window.jQuery
                    &&
                    $.fn.select2
                ) {
                    $(productSelect)
                        .val(
                            String(
                                oldItem.product_id
                            )
                        )
                        .trigger(
                            'change'
                        );
                } else {
                    productSelect.value =
                        String(
                            oldItem.product_id
                        );

                    productChanged();
                }

                quantityInput.value =
                    oldItem.quantity
                    ??
                    1;

                if (
                    oldItem.depot_id
                ) {
                    depotSelect.value =
                        String(
                            oldItem.depot_id
                        );
                }

                updateSelectedDepotStock(
                    currentIndex
                );

                calculateRow(
                    currentIndex
                );
            }

            rowIndex++;

            return productSelect;
        }

        function updateSelectedDepotStock(
            index
        ) {
            const depotSelect =
                document.getElementById(
                    `depot_${index}`
                );

            const quantityInput =
                document.getElementById(
                    `qty_${index}`
                );

            const message =
                document.getElementById(
                    `selected_depot_stock_${index}`
                );

            if (
                !depotSelect
                ||
                !quantityInput
                ||
                !message
            ) {
                return;
            }

            const option =
                depotSelect.options[
                    depotSelect.selectedIndex
                ];

            const available =
                parseFloat(
                    option?.dataset?.quantity
                    ||
                    0
                )
                ||
                0;

            if (
                depotSelect.value
                &&
                available > 0
            ) {
                quantityInput.max =
                    String(
                        available
                    );

                message.innerHTML =
                    '<i class="bx bx-check-circle text-success me-1"></i>'
                    +
                    'Disponible dans le dépôt sélectionné : '
                    +
                    '<strong>'
                    +
                    formatNumber(
                        available
                    )
                    +
                    '</strong>';
            } else {
                quantityInput.removeAttribute(
                    'max'
                );

                message.textContent =
                    'Sélectionnez le dépôt à utiliser.';
            }
        }

        function calculateRow(
            index
        ) {
            const priceInput =
                document.getElementById(
                    `price_${index}`
                );

            const quantityInput =
                document.getElementById(
                    `qty_${index}`
                );

            const depotSelect =
                document.getElementById(
                    `depot_${index}`
                );

            const totalElement =
                document.getElementById(
                    `total_${index}`
                );

            if (
                !priceInput
                ||
                !quantityInput
                ||
                !totalElement
            ) {
                return;
            }

            const price =
                parseFloat(
                    priceInput.value
                )
                ||
                0;

            let quantity =
                parseFloat(
                    quantityInput.value
                )
                ||
                0;

            if (
                depotSelect
                &&
                depotSelect.value
            ) {
                const option =
                    depotSelect.options[
                        depotSelect.selectedIndex
                    ];

                const available =
                    parseFloat(
                        option?.dataset?.quantity
                        ||
                        0
                    )
                    ||
                    0;

                if (
                    quantity > available
                ) {
                    showWarning(
                        'Stock insuffisant dans ce dépôt',
                        `Stock disponible : ${available}`
                    );

                    quantity =
                        available;

                    quantityInput.value =
                        available;
                }
            }

            const lineTotal =
                price
                *
                quantity;

            totalElement.textContent =
                lineTotal.toFixed(2);

            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let subtotal =
                0;

            document
                .querySelectorAll(
                    '.line-total'
                )
                .forEach(
                    function (element) {
                        subtotal +=
                            parseFloat(
                                element.textContent
                            )
                            ||
                            0;
                    }
                );

            let discount =
                parseFloat(
                    discountInput.value
                )
                ||
                0;

            discount =
                Math.max(
                    0,
                    Math.min(
                        100,
                        discount
                    )
                );

            discountInput.value =
                discount;

            const discountAmount =
                subtotal
                *
                discount
                /
                100;

            const taxable =
                Math.max(
                    0,
                    subtotal
                    -
                    discountAmount
                );

            const tva =
                taxable
                *
                0.10;

            const total =
                taxable
                +
                tva;

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

        async function loadVehicles(
            customerId,
            selectedVehicleId = null
        ) {
            vehicleErrorMessage.classList.add(
                'd-none'
            );

            vehicleErrorMessage.textContent =
                '';

            vehicleSelect.innerHTML =
                '';

            if (!customerId) {
                vehicleSelect.innerHTML =
                    '<option value="">Sélectionnez d’abord un client</option>';

                vehicleSelect.disabled =
                    true;

                return;
            }

            vehicleLoadingMessage.classList.remove(
                'd-none'
            );

            vehicleSelect.disabled =
                true;

            vehicleSelect.innerHTML =
                '<option value="">Chargement...</option>';

            try {
                const url =
                    "{{ url('/sales/customers') }}/"
                    +
                    encodeURIComponent(
                        customerId
                    )
                    +
                    "/vehicles";

                const response =
                    await fetch(
                        url,
                        {
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
                        'Erreur HTTP '
                        +
                        response.status
                    );
                }

                const data =
                    await response.json();

                vehicleSelect.innerHTML =
                    '<option value="">Sélectionner une immatriculation</option>';

                if (
                    data.success
                    &&
                    Array.isArray(
                        data.vehicles
                    )
                    &&
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
                                vehicle.label
                                ||
                                vehicle.plate_number;

                            if (
                                selectedVehicleId
                                &&
                                String(
                                    vehicle.id
                                )
                                ===
                                String(
                                    selectedVehicleId
                                )
                            ) {
                                option.selected =
                                    true;
                            }

                            vehicleSelect.appendChild(
                                option
                            );
                        }
                    );

                    vehicleSelect.disabled =
                        false;
                } else {
                    vehicleSelect.innerHTML =
                        '<option value="">Aucun véhicule associé à ce client</option>';

                    vehicleErrorMessage.classList.remove(
                        'd-none'
                    );

                    vehicleErrorMessage.textContent =
                        'Ce client ne possède aucun véhicule associé.';
                }

                if (
                    window.jQuery
                    &&
                    $.fn.select2
                ) {
                    $('#vehicle_id')
                        .trigger(
                            'change.select2'
                        );
                }
            } catch (error) {
                console.error(
                    error
                );

                vehicleSelect.innerHTML =
                    '<option value="">Erreur lors du chargement</option>';

                vehicleErrorMessage.classList.remove(
                    'd-none'
                );

                vehicleErrorMessage.textContent =
                    'Impossible de charger les véhicules.';
            } finally {
                vehicleLoadingMessage.classList.add(
                    'd-none'
                );
            }
        }

        if (
            window.jQuery
            &&
            $.fn.select2
        ) {
            $('#customer_id')
                .select2({
                    width:
                        '100%',

                    placeholder:
                        'Rechercher un client',

                    allowClear:
                        true
                });

            $('#vehicle_id')
                .select2({
                    width:
                        '100%',

                    placeholder:
                        'Sélectionner une immatriculation',

                    allowClear:
                        true
                });

            $('#customer_id')
                .on(
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

        productGlobalSearch.addEventListener(
            'input',
            function () {
                searchProducts(
                    this.value
                );
            }
        );

        productSearchResults.addEventListener(
            'click',
            function (event) {
                const button =
                    event.target.closest(
                        '.product-search-result-item'
                    );

                if (!button) {
                    return;
                }

                let productSelect =
                    Array.from(
                        itemsTableBody.querySelectorAll(
                            '.product-select'
                        )
                    )
                    .find(
                        select =>
                            !select.value
                    );

                if (!productSelect) {
                    productSelect =
                        addRow();
                }

                if (
                    window.jQuery
                    &&
                    $.fn.select2
                ) {
                    $(productSelect)
                        .val(
                            String(
                                button.dataset.productId
                            )
                        )
                        .trigger(
                            'change'
                        );
                } else {
                    productSelect.value =
                        String(
                            button.dataset.productId
                        );

                    productSelect.dispatchEvent(
                        new Event(
                            'change',
                            {
                                bubbles:
                                    true
                            }
                        )
                    );
                }

                productGlobalSearch.value =
                    '';

                hideSearchResults();

                const row =
                    productSelect.closest(
                        'tr'
                    );

                row
                    ?.querySelector(
                        '.depot-select'
                    )
                    ?.focus();
            }
        );

        clearProductSearch.addEventListener(
            'click',
            function () {
                productGlobalSearch.value =
                    '';

                productGlobalSearch.focus();

                hideSearchResults();
            }
        );

        document.addEventListener(
            'click',
            function (event) {
                if (
                    !event.target.closest(
                        '.product-search-wrapper'
                    )
                ) {
                    hideSearchResults();
                }
            }
        );

        addProductButton.addEventListener(
            'click',
            function () {
                addRow();
            }
        );

        discountInput.addEventListener(
            'input',
            calculateGrandTotal
        );

        @if(
            is_array(old('items'))
            &&
            count(old('items')) > 0
        )
            const oldItems =
                @json(old('items'));

            oldItems.forEach(
                function (item) {
                    addRow(
                        item
                    );
                }
            );
        @else
            addRow();
        @endif

        const initialCustomerId =
            customerSelect.value;

        const oldVehicleId =
            vehicleSelect.dataset.selected
            ||
            null;

        if (initialCustomerId) {
            loadVehicles(
                initialCustomerId,
                oldVehicleId
            );
        }

        saleForm.addEventListener(
            'submit',
            function (event) {
                if (!customerSelect.value) {
                    event.preventDefault();

                    showWarning(
                        'Client obligatoire',
                        'Veuillez sélectionner un client.'
                    );

                    return;
                }

                if (!vehicleSelect.value) {
                    event.preventDefault();

                    showWarning(
                        'Véhicule obligatoire',
                        'Veuillez sélectionner le véhicule du client.'
                    );

                    return;
                }

                const rows =
                    itemsTableBody.querySelectorAll(
                        'tr'
                    );

                let validLine =
                    false;

                let missingDepot =
                    false;

                let invalidQuantity =
                    false;

                rows.forEach(
                    function (row) {
                        const productSelect =
                            row.querySelector(
                                '.product-select'
                            );

                        if (
                            !productSelect
                            ||
                            !productSelect.value
                        ) {
                            return;
                        }

                        validLine =
                            true;

                        const depotSelect =
                            row.querySelector(
                                '.depot-select'
                            );

                        const quantityInput =
                            row.querySelector(
                                'input[name$="[quantity]"]'
                            );

                        if (
                            !depotSelect
                            ||
                            !depotSelect.value
                        ) {
                            missingDepot =
                                true;
                        }

                        if (
                            !quantityInput
                            ||
                            parseFloat(
                                quantityInput.value
                            )
                            <=
                            0
                        ) {
                            invalidQuantity =
                                true;
                        }
                    }
                );

                if (!validLine) {
                    event.preventDefault();

                    showWarning(
                        'Produit obligatoire',
                        'Veuillez ajouter au moins un produit.'
                    );

                    return;
                }

                if (missingDepot) {
                    event.preventDefault();

                    showWarning(
                        'Dépôt obligatoire',
                        'Veuillez sélectionner le dépôt à utiliser pour chaque produit.'
                    );

                    return;
                }

                if (invalidQuantity) {
                    event.preventDefault();

                    showWarning(
                        'Quantité invalide',
                        'La quantité doit être supérieure à zéro.'
                    );

                    return;
                }
            }
        );

        if (saveCustomerBtn) {
            saveCustomerBtn.addEventListener(
                'click',
                async function () {
                    const code =
                        document.getElementById(
                            'customer_code'
                        ).value.trim();

                    const name =
                        document.getElementById(
                            'customer_name'
                        ).value.trim();

                    const phone =
                        document.getElementById(
                            'customer_phone'
                        ).value.trim();

                    const email =
                        document.getElementById(
                            'customer_email'
                        ).value.trim();

                    const errorBox =
                        document.getElementById(
                            'customerModalError'
                        );

                    errorBox.classList.add(
                        'd-none'
                    );

                    errorBox.innerHTML =
                        '';

                    if (!name) {
                        errorBox.textContent =
                            'Le nom du client est obligatoire.';

                        errorBox.classList.remove(
                            'd-none'
                        );

                        return;
                    }

                    try {
                        saveCustomerBtn.disabled =
                            true;

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
                                            code:
                                                code,

                                            name:
                                                name,

                                            phone:
                                                phone,

                                            email:
                                                email
                                        })
                                }
                            );

                        const data =
                            await response.json();

                        if (
                            !response.ok
                            ||
                            !data.success
                            ||
                            !data.customer
                        ) {
                            throw data;
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
                            window.jQuery
                            &&
                            $.fn.select2
                        ) {
                            $('#customer_id')
                                .val(
                                    String(
                                        data.customer.id
                                    )
                                )
                                .trigger(
                                    'change'
                                );
                        } else {
                            customerSelect.value =
                                String(
                                    data.customer.id
                                );

                            customerSelect.dispatchEvent(
                                new Event(
                                    'change'
                                )
                            );
                        }

                        const modalElement =
                            document.getElementById(
                                'customerModal'
                            );

                        if (
                            window.bootstrap
                            &&
                            modalElement
                        ) {
                            bootstrap.Modal
                                .getOrCreateInstance(
                                    modalElement
                                )
                                .hide();
                        }
                    } catch (error) {
                        let message =
                            'Une erreur est survenue.';

                        if (
                            error
                            &&
                            error.errors
                        ) {
                            message =
                                Object.values(
                                    error.errors
                                )
                                .flat()
                                .join(
                                    '<br>'
                                );
                        }

                        errorBox.innerHTML =
                            message;

                        errorBox.classList.remove(
                            'd-none'
                        );
                    } finally {
                        saveCustomerBtn.disabled =
                            false;
                    }
                }
            );
        }

        calculateGrandTotal();
    }
);
</script>

@endsection
