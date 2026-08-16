@extends('layouts.layoutMaster')

@section('content')

<div class="card shadow-sm border-0">

    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">

        <div>
            <h4 class="mb-1 fw-bold">
                Nouveau transfert entre dépôts
            </h4>

            <small class="text-muted">
                Transférer un ou plusieurs produits d’un dépôt vers un autre
            </small>
        </div>

        <a href="{{ route('depot-transfers.index') }}"
           class="btn btn-secondary">

            <i class="bx bx-arrow-back me-1"></i>

            Retour
        </a>

    </div>


    {{-- =========================================================
        BODY
    ========================================================== --}}
    <div class="card-body">

        {{-- =====================================================
            SUCCESS
        ====================================================== --}}
        @if(session('success'))

            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center">

                <i class="bx bx-check-circle fs-3 me-2"></i>

                <div>
                    <strong>Succès</strong>

                    <br>

                    {{ session('success') }}
                </div>

            </div>

        @endif


        {{-- =====================================================
            ERROR
        ====================================================== --}}
        @if(session('error'))

            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">

                <div class="d-flex align-items-center">

                    <i class="bx bx-error-circle fs-2 me-3"></i>

                    <div>
                        <strong>
                            Transfert impossible
                        </strong>

                        <br>

                        {{ session('error') }}
                    </div>

                </div>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                </button>

            </div>

        @endif


        {{-- =====================================================
            VALIDATION ERRORS
        ====================================================== --}}
        @if($errors->any())

            <div class="alert alert-danger border-0 shadow-sm">

                <strong>
                    Erreurs détectées :
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


        {{-- =====================================================
            FORMULAIRE
        ====================================================== --}}
        <form action="{{ route('depot-transfers.store') }}"
              method="POST"
              id="transferForm">

            @csrf


            {{-- =================================================
                DÉPÔTS
            ================================================== --}}
            <div class="card border mb-4">

                <div class="card-header bg-light">

                    <h6 class="mb-0 fw-bold">

                        <i class="bx bx-store me-1"></i>

                        Dépôts

                    </h6>

                </div>

                <div class="card-body">

                    <div class="row">


                        {{-- DEPOT SOURCE --}}
                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-semibold">

                                Dépôt source

                                <span class="text-danger">*</span>

                            </label>

                            <select name="source_depot_id"
                                    id="source_depot_id"
                                    class="form-select"
                                    required>

                                <option value="">
                                    -- Sélectionner le dépôt source --
                                </option>

                                @foreach($depots as $depot)

                                    <option value="{{ $depot->id }}"
                                        {{ old('source_depot_id') == $depot->id ? 'selected' : '' }}>

                                        {{ $depot->name }}

                                    </option>

                                @endforeach

                            </select>

                            <small class="text-muted">
                                Le stock sera retiré de ce dépôt.
                            </small>

                        </div>


                        {{-- DEPOT DESTINATION --}}
                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-semibold">

                                Dépôt destination

                                <span class="text-danger">*</span>

                            </label>

                            <select name="destination_depot_id"
                                    id="destination_depot_id"
                                    class="form-select"
                                    required>

                                <option value="">
                                    -- Sélectionner le dépôt destination --
                                </option>

                                @foreach($depots as $depot)

                                    <option value="{{ $depot->id }}"
                                        {{ old('destination_depot_id') == $depot->id ? 'selected' : '' }}>

                                        {{ $depot->name }}

                                    </option>

                                @endforeach

                            </select>

                            <small class="text-muted">
                                Le stock sera ajouté à ce dépôt.
                            </small>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =================================================
                PRODUITS
            ================================================== --}}
            <div class="card border mb-4">

                <div class="card-header bg-light d-flex justify-content-between align-items-center">

                    <div>

                        <h6 class="mb-1 fw-bold">

                            <i class="bx bx-package me-1"></i>

                            Produits à transférer

                        </h6>

                        <small class="text-muted">
                            Vous pouvez ajouter plusieurs produits dans le même transfert.
                        </small>

                    </div>


                    <button type="button"
                            class="btn btn-primary btn-sm"
                            id="addProductButton">

                        <i class="bx bx-plus me-1"></i>

                        Ajouter un produit

                    </button>

                </div>


                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered align-middle mb-0"
                               id="productsTable">

                            <thead class="table-light">

                                <tr>

                                    <th style="width: 45%;">
                                        Produit
                                    </th>

                                    <th style="width: 18%;"
                                        class="text-center">
                                        Stock disponible
                                    </th>

                                    <th style="width: 20%;">
                                        Quantité à transférer
                                    </th>

                                    <th style="width: 10%;"
                                        class="text-center">
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody id="productsContainer">

                                @php
                                    $oldItems = old('items', [
                                        [
                                            'product_id' => '',
                                            'quantity' => ''
                                        ]
                                    ]);
                                @endphp


                                @foreach($oldItems as $index => $oldItem)

                                    <tr class="product-row"
                                        data-index="{{ $index }}">

                                        {{-- PRODUIT --}}
                                        <td>

                                            <select
                                                name="items[{{ $index }}][product_id]"
                                                class="form-select product-select"
                                                required>

                                                <option value="">
                                                    -- Sélectionner un produit --
                                                </option>

                                                @foreach($products as $product)

                                                    <option
                                                        value="{{ $product->id }}"
                                                        {{ ($oldItem['product_id'] ?? '') == $product->id ? 'selected' : '' }}>

                                                        {{ $product->reference ?? '' }}

                                                        -

                                                        {{ $product->designation ?? $product->name ?? '' }}

                                                    </option>

                                                @endforeach

                                            </select>

                                        </td>


                                        {{-- STOCK DISPONIBLE --}}
                                        <td class="text-center">

                                            <div class="available-stock-wrapper">

                                                <span class="badge bg-secondary fs-6 available-stock">
                                                    -
                                                </span>

                                            </div>

                                            <small class="text-muted d-block mt-1 stock-message">
                                                Sélectionnez le dépôt source
                                            </small>

                                        </td>


                                        {{-- QUANTITE --}}
                                        <td>

                                            <input
                                                type="number"
                                                name="items[{{ $index }}][quantity]"
                                                class="form-control quantity-input"
                                                min="0.01"
                                                step="0.01"
                                                value="{{ $oldItem['quantity'] ?? '' }}"
                                                placeholder="0"
                                                required>

                                            <div class="invalid-feedback quantity-error">
                                                Quantité supérieure au stock disponible.
                                            </div>

                                        </td>


                                        {{-- ACTION --}}
                                        <td class="text-center">

                                            <button
                                                type="button"
                                                class="btn btn-danger btn-sm remove-product-button"
                                                title="Supprimer ce produit">

                                                <i class="bx bx-trash"></i>

                                            </button>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>


                    {{-- MESSAGE SI AUCUN PRODUIT --}}
                    <div id="noProductsMessage"
                         class="alert alert-warning mt-3 d-none">

                        <i class="bx bx-info-circle me-1"></i>

                        Vous devez ajouter au moins un produit.

                    </div>

                </div>

            </div>


            {{-- =================================================
                NOTE
            ================================================== --}}
            <div class="card border mb-4">

                <div class="card-header bg-light">

                    <h6 class="mb-0 fw-bold">

                        <i class="bx bx-note me-1"></i>

                        Informations complémentaires

                    </h6>

                </div>

                <div class="card-body">

                    <div class="mb-0">

                        <label class="form-label fw-semibold">
                            Note
                        </label>

                        <textarea
                            name="note"
                            class="form-control"
                            rows="3"
                            maxlength="1000"
                            placeholder="Ex : transfert urgent vers dépôt principal">{{ old('note') }}</textarea>

                    </div>

                </div>

            </div>


            {{-- =================================================
                BUTTONS
            ================================================== --}}
            <div class="d-flex justify-content-end gap-2">

                <a href="{{ route('depot-transfers.index') }}"
                   class="btn btn-secondary">

                    <i class="bx bx-x me-1"></i>

                    Annuler

                </a>


                <button type="submit"
                        class="btn btn-primary"
                        id="submitTransferButton">

                    <i class="bx bx-transfer me-1"></i>

                    Valider le transfert

                </button>

            </div>

        </form>

    </div>

</div>


{{-- =============================================================
    TEMPLATE POUR AJOUT D'UN NOUVEAU PRODUIT
============================================================= --}}
<template id="productRowTemplate">

    <tr class="product-row">

        {{-- PRODUIT --}}
        <td>

            <select class="form-select product-select"
                    required>

                <option value="">
                    -- Sélectionner un produit --
                </option>

                @foreach($products as $product)

                    <option value="{{ $product->id }}">

                        {{ $product->reference ?? '' }}

                        -

                        {{ $product->designation ?? $product->name ?? '' }}

                    </option>

                @endforeach

            </select>

        </td>


        {{-- STOCK --}}
        <td class="text-center">

            <div class="available-stock-wrapper">

                <span class="badge bg-secondary fs-6 available-stock">
                    -
                </span>

            </div>

            <small class="text-muted d-block mt-1 stock-message">
                Sélectionnez un produit
            </small>

        </td>


        {{-- QUANTITE --}}
        <td>

            <input type="number"
                   class="form-control quantity-input"
                   min="0.01"
                   step="0.01"
                   placeholder="0"
                   required>

            <div class="invalid-feedback quantity-error">
                Quantité supérieure au stock disponible.
            </div>

        </td>


        {{-- ACTION --}}
        <td class="text-center">

            <button
                type="button"
                class="btn btn-danger btn-sm remove-product-button"
                title="Supprimer ce produit">

                <i class="bx bx-trash"></i>

            </button>

        </td>

    </tr>

</template>

@endsection


@push('styles')

<style>

    /*
    |--------------------------------------------------------------------------
    | TABLE PRODUITS
    |--------------------------------------------------------------------------
    */

    #productsTable th {
        vertical-align: middle;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: .3px;
    }

    #productsTable td {
        vertical-align: middle;
    }


    /*
    |--------------------------------------------------------------------------
    | STOCK DISPONIBLE
    |--------------------------------------------------------------------------
    */

    .available-stock {
        min-width: 70px;
        padding: 8px 12px;
    }

    .stock-message {
        font-size: 0.75rem;
    }


    /*
    |--------------------------------------------------------------------------
    | SELECT
    |--------------------------------------------------------------------------
    */

    .product-select {
        width: 100%;
    }


    /*
    |--------------------------------------------------------------------------
    | QUANTITE
    |--------------------------------------------------------------------------
    */

    .quantity-input.is-invalid {
        border-color: #ff3e1d;
    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 768px) {

        #productsTable {
            min-width: 850px;
        }

    }

</style>

@endpush


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ELEMENTS
    |--------------------------------------------------------------------------
    */

    const sourceDepotSelect =
        document.getElementById('source_depot_id');

    const destinationDepotSelect =
        document.getElementById('destination_depot_id');

    const productsContainer =
        document.getElementById('productsContainer');

    const addProductButton =
        document.getElementById('addProductButton');

    const productRowTemplate =
        document.getElementById('productRowTemplate');

    const transferForm =
        document.getElementById('transferForm');

    const submitTransferButton =
        document.getElementById('submitTransferButton');

    const noProductsMessage =
        document.getElementById('noProductsMessage');


    /*
    |--------------------------------------------------------------------------
    | COMPTEUR DES LIGNES
    |--------------------------------------------------------------------------
    */

    let rowIndex =
        document.querySelectorAll('.product-row').length;


    /*
    |--------------------------------------------------------------------------
    | URL STOCK
    |--------------------------------------------------------------------------
    */

    const stockUrlTemplate =
        "{{ route('depot-transfers.stock', ['depot' => '__DEPOT__', 'product' => '__PRODUCT__']) }}";


    /*
    |--------------------------------------------------------------------------
    | METTRE À JOUR LES NOMS DES CHAMPS
    |--------------------------------------------------------------------------
    */

    function refreshRowIndexes() {

        const rows =
            productsContainer.querySelectorAll('.product-row');

        rows.forEach(function (row, index) {

            row.dataset.index = index;

            const productSelect =
                row.querySelector('.product-select');

            const quantityInput =
                row.querySelector('.quantity-input');

            productSelect.name =
                `items[${index}][product_id]`;

            quantityInput.name =
                `items[${index}][quantity]`;

        });

        noProductsMessage.classList.toggle(
            'd-none',
            rows.length > 0
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AFFICHER LE STOCK
    |--------------------------------------------------------------------------
    */

    function displayStock(
        row,
        quantity,
        message = ''
    ) {

        const stockBadge =
            row.querySelector('.available-stock');

        const stockMessage =
            row.querySelector('.stock-message');

        const quantityInput =
            row.querySelector('.quantity-input');


        quantity =
            parseFloat(quantity) || 0;


        /*
        |--------------------------------------------------------------------------
        | Stock affiché
        |--------------------------------------------------------------------------
        */

        stockBadge.textContent =
            formatQuantity(quantity);


        /*
        |--------------------------------------------------------------------------
        | Enregistrer le stock dans la ligne
        |--------------------------------------------------------------------------
        */

        row.dataset.availableStock =
            quantity;


        /*
        |--------------------------------------------------------------------------
        | Définir quantité maximale
        |--------------------------------------------------------------------------
        */

        quantityInput.max =
            quantity;


        /*
        |--------------------------------------------------------------------------
        | Couleurs
        |--------------------------------------------------------------------------
        */

        stockBadge.classList.remove(
            'bg-secondary',
            'bg-success',
            'bg-danger',
            'bg-warning'
        );


        if (quantity <= 0) {

            stockBadge.classList.add(
                'bg-danger'
            );

            stockMessage.textContent =
                message || 'Produit indisponible dans ce dépôt';

        } else {

            stockBadge.classList.add(
                'bg-success'
            );

            stockMessage.textContent =
                message || 'Disponible dans le dépôt source';

        }


        /*
        |--------------------------------------------------------------------------
        | Vérifier quantité déjà saisie
        |--------------------------------------------------------------------------
        */

        validateQuantity(row);
    }


    /*
    |--------------------------------------------------------------------------
    | FORMAT QUANTITÉ
    |--------------------------------------------------------------------------
    */

    function formatQuantity(quantity) {

        quantity =
            parseFloat(quantity) || 0;

        if (Number.isInteger(quantity)) {

            return quantity.toString();
        }

        return quantity.toFixed(2);
    }


    /*
    |--------------------------------------------------------------------------
    | CHARGER STOCK D'UNE LIGNE
    |--------------------------------------------------------------------------
    */

    async function loadStock(row) {

        const sourceDepotId =
            sourceDepotSelect.value;

        const productSelect =
            row.querySelector('.product-select');

        const productId =
            productSelect.value;

        const stockBadge =
            row.querySelector('.available-stock');

        const stockMessage =
            row.querySelector('.stock-message');


        /*
        |--------------------------------------------------------------------------
        | Pas de dépôt
        |--------------------------------------------------------------------------
        */

        if (!sourceDepotId) {

            stockBadge.textContent = '-';

            stockBadge.className =
                'badge bg-secondary fs-6 available-stock';

            stockMessage.textContent =
                'Sélectionnez le dépôt source';

            row.dataset.availableStock = '';

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Pas de produit
        |--------------------------------------------------------------------------
        */

        if (!productId) {

            stockBadge.textContent = '-';

            stockBadge.className =
                'badge bg-secondary fs-6 available-stock';

            stockMessage.textContent =
                'Sélectionnez un produit';

            row.dataset.availableStock = '';

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Chargement
        |--------------------------------------------------------------------------
        */

        stockBadge.textContent = '...';

        stockBadge.className =
            'badge bg-secondary fs-6 available-stock';

        stockMessage.textContent =
            'Vérification du stock...';


        try {

            const url =
                stockUrlTemplate
                    .replace(
                        '__DEPOT__',
                        sourceDepotId
                    )
                    .replace(
                        '__PRODUCT__',
                        productId
                    );


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
                        }
                    }
                );


            if (!response.ok) {

                throw new Error(
                    'Impossible de récupérer le stock.'
                );
            }


            const data =
                await response.json();


            displayStock(
                row,
                data.quantity
            );


        } catch (error) {

            console.error(error);

            stockBadge.textContent = 'Erreur';

            stockBadge.className =
                'badge bg-danger fs-6 available-stock';

            stockMessage.textContent =
                'Impossible de récupérer le stock';

            row.dataset.availableStock = '';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | CHARGER TOUS LES STOCKS
    |--------------------------------------------------------------------------
    */

    function loadAllStocks() {

        const rows =
            productsContainer.querySelectorAll('.product-row');

        rows.forEach(function (row) {

            loadStock(row);

        });
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATION QUANTITÉ
    |--------------------------------------------------------------------------
    */

    function validateQuantity(row) {

        const input =
            row.querySelector('.quantity-input');

        const error =
            row.querySelector('.quantity-error');

        const availableStock =
            parseFloat(
                row.dataset.availableStock
            );

        const requestedQuantity =
            parseFloat(
                input.value
            );


        /*
        |--------------------------------------------------------------------------
        | Rien saisi
        |--------------------------------------------------------------------------
        */

        if (
            !input.value ||
            isNaN(requestedQuantity)
        ) {

            input.classList.remove(
                'is-invalid'
            );

            return true;
        }


        /*
        |--------------------------------------------------------------------------
        | Quantité <= 0
        |--------------------------------------------------------------------------
        */

        if (requestedQuantity <= 0) {

            input.classList.add(
                'is-invalid'
            );

            error.textContent =
                'La quantité doit être supérieure à zéro.';

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Stock non chargé
        |--------------------------------------------------------------------------
        */

        if (isNaN(availableStock)) {

            input.classList.add(
                'is-invalid'
            );

            error.textContent =
                'Impossible de vérifier le stock disponible.';

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Quantité supérieure au stock
        |--------------------------------------------------------------------------
        */

        if (
            requestedQuantity >
            availableStock
        ) {

            input.classList.add(
                'is-invalid'
            );

            error.textContent =
                `Stock insuffisant. Disponible : ${formatQuantity(availableStock)}.`;

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | OK
        |--------------------------------------------------------------------------
        */

        input.classList.remove(
            'is-invalid'
        );

        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | VÉRIFIER LES PRODUITS EN DOUBLE
    |--------------------------------------------------------------------------
    */

    function validateDuplicateProducts() {

        const selectedProducts = [];

        let duplicateFound = false;


        productsContainer
            .querySelectorAll('.product-select')
            .forEach(function (select) {

                if (!select.value) {
                    return;
                }


                if (
                    selectedProducts.includes(
                        select.value
                    )
                ) {

                    duplicateFound = true;

                } else {

                    selectedProducts.push(
                        select.value
                    );
                }

            });


        return !duplicateFound;
    }


    /*
    |--------------------------------------------------------------------------
    | AJOUTER UN PRODUIT
    |--------------------------------------------------------------------------
    */

    addProductButton.addEventListener(
        'click',
        function () {

            const clone =
                productRowTemplate.content.cloneNode(
                    true
                );

            const row =
                clone.querySelector('.product-row');

            row.dataset.index =
                rowIndex;

            const productSelect =
                row.querySelector('.product-select');

            const quantityInput =
                row.querySelector('.quantity-input');


            productSelect.name =
                `items[${rowIndex}][product_id]`;

            quantityInput.name =
                `items[${rowIndex}][quantity]`;


            productsContainer.appendChild(
                clone
            );


            rowIndex++;


            refreshRowIndexes();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | SUPPRIMER UNE LIGNE
    |--------------------------------------------------------------------------
    */

    productsContainer.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '.remove-product-button'
                );

            if (!button) {
                return;
            }


            const row =
                button.closest(
                    '.product-row'
                );


            /*
            |--------------------------------------------------------------------------
            | Garder au moins une ligne
            |--------------------------------------------------------------------------
            */

            const totalRows =
                productsContainer.querySelectorAll(
                    '.product-row'
                ).length;


            if (totalRows <= 1) {

                const productSelect =
                    row.querySelector(
                        '.product-select'
                    );

                const quantityInput =
                    row.querySelector(
                        '.quantity-input'
                    );

                const stockBadge =
                    row.querySelector(
                        '.available-stock'
                    );

                const stockMessage =
                    row.querySelector(
                        '.stock-message'
                    );


                productSelect.value = '';

                quantityInput.value = '';

                quantityInput.classList.remove(
                    'is-invalid'
                );


                stockBadge.textContent = '-';

                stockBadge.className =
                    'badge bg-secondary fs-6 available-stock';


                stockMessage.textContent =
                    'Sélectionnez un produit';


                row.dataset.availableStock = '';

                return;
            }


            row.remove();

            refreshRowIndexes();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CHANGEMENT PRODUIT
    |--------------------------------------------------------------------------
    */

    productsContainer.addEventListener(
        'change',
        function (event) {

            if (
                !event.target.classList.contains(
                    'product-select'
                )
            ) {
                return;
            }


            const row =
                event.target.closest(
                    '.product-row'
                );


            /*
            |--------------------------------------------------------------------------
            | Vérifier doublon
            |--------------------------------------------------------------------------
            */

            if (!validateDuplicateProducts()) {

                alert(
                    'Ce produit est déjà présent dans le transfert.'
                );

                event.target.value = '';

                loadStock(row);

                return;
            }


            loadStock(row);

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CHANGEMENT QUANTITÉ
    |--------------------------------------------------------------------------
    */

    productsContainer.addEventListener(
        'input',
        function (event) {

            if (
                !event.target.classList.contains(
                    'quantity-input'
                )
            ) {
                return;
            }


            const row =
                event.target.closest(
                    '.product-row'
                );


            validateQuantity(row);

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CHANGEMENT DÉPÔT SOURCE
    |--------------------------------------------------------------------------
    */

    sourceDepotSelect.addEventListener(
        'change',
        function () {

            /*
            |--------------------------------------------------------------------------
            | Empêcher même dépôt
            |--------------------------------------------------------------------------
            */

            if (
                sourceDepotSelect.value &&
                sourceDepotSelect.value ===
                destinationDepotSelect.value
            ) {

                destinationDepotSelect.value = '';
            }


            loadAllStocks();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CHANGEMENT DÉPÔT DESTINATION
    |--------------------------------------------------------------------------
    */

    destinationDepotSelect.addEventListener(
        'change',
        function () {

            if (
                sourceDepotSelect.value &&
                destinationDepotSelect.value &&
                sourceDepotSelect.value ===
                destinationDepotSelect.value
            ) {

                alert(
                    'Le dépôt destination doit être différent du dépôt source.'
                );

                destinationDepotSelect.value = '';
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDATION DU FORMULAIRE
    |--------------------------------------------------------------------------
    */

    transferForm.addEventListener(
        'submit',
        function (event) {

            let valid = true;


            /*
            |--------------------------------------------------------------------------
            | Dépôts identiques
            |--------------------------------------------------------------------------
            */

            if (
                sourceDepotSelect.value ===
                destinationDepotSelect.value
            ) {

                event.preventDefault();

                alert(
                    'Le dépôt source et le dépôt destination doivent être différents.'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Produits en double
            |--------------------------------------------------------------------------
            */

            if (!validateDuplicateProducts()) {

                event.preventDefault();

                alert(
                    'Le même produit ne peut pas être ajouté plusieurs fois.'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Vérifier chaque ligne
            |--------------------------------------------------------------------------
            */

            const rows =
                productsContainer.querySelectorAll(
                    '.product-row'
                );


            rows.forEach(function (row) {

                const productSelect =
                    row.querySelector(
                        '.product-select'
                    );


                if (!productSelect.value) {

                    valid = false;
                }


                if (!validateQuantity(row)) {

                    valid = false;
                }

            });


            if (!valid) {

                event.preventDefault();

                alert(
                    'Veuillez vérifier les produits et les quantités avant de valider le transfert.'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Désactiver le bouton pour éviter double soumission
            |--------------------------------------------------------------------------
            */

            submitTransferButton.disabled =
                true;

            submitTransferButton.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1"></span> Transfert en cours...';

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CHARGER STOCKS AU DÉMARRAGE
    |--------------------------------------------------------------------------
    */

    refreshRowIndexes();

    if (sourceDepotSelect.value) {

        loadAllStocks();
    }

});

</script>

@endpush