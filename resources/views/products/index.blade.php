@extends('layouts.layoutMaster')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | VARIABLES DE PAGE
    |--------------------------------------------------------------------------
    */

    $hideButtons = $hideButtons ?? false;

    $isAllProducts = request()->routeIs('products.index');
    $isAvailableProducts = request()->routeIs('products.available');
    $isUnavailablePage =
        $isUnavailablePage
        ?? request()->routeIs('products.unavailable');
    $isSoldProducts = request()->routeIs('products.sold');

   /*
    |--------------------------------------------------------------------------
    | NOMBRE DE COLONNES
    |--------------------------------------------------------------------------
    |
    | Tous les produits :
    |
    | 7 informations :
    | - Référence
    | - Désignation
    | - Marque
    | - Modèle
    | - Famille
    | - Rayon
    | - Emplacement
    |
    | 3 quantités :
    | - Quantité initiale
    | - Quantité disponible
    | - Quantité vendue
    |
    | + Min
    | + Max
    | + Prix achat
    | + Prix vente
    | + Statut
    | + Actions
    |
    | TOTAL = 16 colonnes
    |
    | IMPORTANT :
    | "Qté reçue" et "Qté non disponible"
    | ne sont plus affichées dans "Tous les produits".
    |
    */

    $tableColspan =
        $isAllProducts
            ? 16
            : (
                $isUnavailablePage
                    ? 17
                    : 14
            );
@endphp

<style>
    /*
    |--------------------------------------------------------------------------
    | PAGE
    |--------------------------------------------------------------------------
    */

    .products-page {
        width: 100%;
        padding: 22px 18px 45px;
        overflow-x: hidden;
    }

    .products-page-inner {
        width: 100%;
        max-width: 1700px;
        margin: 0 auto;
    }

    .products-card {
        width: 100%;
        min-width: 0;
        overflow: hidden;

        background: #ffffff;

        border: 1px solid #e5e7eb;
        border-radius: 16px;

        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    }

    /*
    |--------------------------------------------------------------------------
    | HEADER
    |--------------------------------------------------------------------------
    */

    .products-card-header {
        padding: 24px 28px 22px;

        background: #ffffff;

        border-bottom: 1px solid #edf0f4;
    }

    .products-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 16px;
        flex-wrap: wrap;

        margin-bottom: 22px;
    }

    .products-title-row h4 {
        margin: 0;

        font-size: clamp(22px, 2vw, 28px);
        font-weight: 800;

        color: #334155;
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT
    |--------------------------------------------------------------------------
    */

    .product-import-grid {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;

        gap: 14px;

        width: 100%;
        min-width: 0;
    }

    .product-import-field {
        min-width: 0;
    }

    .product-import-field:nth-child(1),
    .product-import-field:nth-child(2) {
        flex: 1 1 220px;
    }

    .product-import-field:nth-child(3) {
        flex: 1.15 1 280px;
    }

    .product-actions-field {
        flex: 1.2 1 360px;
    }

    /*
    |--------------------------------------------------------------------------
    | LABELS
    |--------------------------------------------------------------------------
    */

    .product-import-field .form-label {
        display: block;

        margin-bottom: 8px;

        font-size: 11px;
        font-weight: 800;

        letter-spacing: .06em;

        color: #52657b;

        text-transform: uppercase;
    }

    /*
    |--------------------------------------------------------------------------
    | SELECT FOURNISSEUR / DÉPÔT
    |--------------------------------------------------------------------------
    */

    .modern-select-wrapper {
        position: relative;

        width: 100%;
    }

    .modern-select-icon {
        position: absolute;

        top: 50%;
        left: 15px;

        transform: translateY(-50%);

        z-index: 2;

        display: flex;
        align-items: center;
        justify-content: center;

        width: 22px;
        height: 22px;

        color: #64748b;

        pointer-events: none;
    }

    .modern-select-icon i {
        font-size: 20px;
    }

    .modern-select {
        width: 100%;
        height: 48px;

        padding: 0 44px 0 46px;

        font-size: 14px;
        font-weight: 500;

        color: #475569;

        background-color: #ffffff;

        border: 1px solid #d8dee8;
        border-radius: 10px;

        outline: none;

        cursor: pointer;

        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;

        background-image:
            url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");

        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 17px;

        transition:
            border-color .2s ease,
            box-shadow .2s ease,
            background-color .2s ease;
    }

    .modern-select:hover {
        border-color: #a5b4fc;

        background-color: #fafaff;
    }

    .modern-select:focus {
        border-color: #696cff;

        background-color: #ffffff;

        box-shadow:
            0 0 0 3px rgba(105, 108, 255, 0.12);
    }

    .modern-select option {
        color: #334155;

        background: #ffffff;
    }

    /*
    |--------------------------------------------------------------------------
    | FICHIER EXCEL
    |--------------------------------------------------------------------------
    */

    .product-file-input {
        width: 100%;
        height: 48px;

        padding: 5px 8px;

        font-size: 14px;

        color: #64748b;

        background: #ffffff;

        border: 1px solid #d8dee8;
        border-radius: 10px;

        box-shadow: none;

        transition:
            border-color .2s ease,
            box-shadow .2s ease;
    }

    .product-file-input:hover {
        border-color: #a5b4fc;
    }

    .product-file-input:focus {
        outline: none;

        border-color: #696cff;

        box-shadow:
            0 0 0 3px rgba(105, 108, 255, .12);
    }

    .product-file-input::file-selector-button {
        height: 36px;

        margin-right: 12px;

        padding: 0 14px;

        color: #475569;

        background: #f8fafc;

        border: none;
        border-right: 1px solid #e2e8f0;

        font-size: 13px;
        font-weight: 600;

        cursor: pointer;
    }

    /*
    |--------------------------------------------------------------------------
    | BOUTONS IMPORT / EXCEL / AJOUT
    |--------------------------------------------------------------------------
    */

    .product-action-buttons {
        display: grid;

        grid-template-columns:
            minmax(125px, 1.15fr)
            minmax(105px, 1fr)
            52px;

        gap: 10px;

        width: 100%;
        min-width: 0;
    }

    .product-action-buttons .btn {
        min-width: 0;
        min-height: 48px;

        padding: 10px 13px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 7px;

        white-space: nowrap;

        font-size: 14px;
        font-weight: 700;

        border-radius: 9px;

        box-shadow:
            0 5px 12px rgba(15, 23, 42, .10);
    }

    .product-action-buttons .btn-success {
        color: #ffffff;

        background: #34d399;
        border-color: #34d399;
    }

    .product-action-buttons .btn-success:hover {
        background: #10b981;
        border-color: #10b981;
    }

    .product-action-buttons .btn-info {
        color: #ffffff;

        background: #13c2c2;
        border-color: #13c2c2;
    }

    .product-action-buttons .btn-info:hover {
        background: #0ea5a5;
        border-color: #0ea5a5;
    }

    .product-add-button {
        width: 52px;

        min-width: 52px !important;

        padding: 0 !important;

        font-size: 23px !important;
    }

    /*
    |--------------------------------------------------------------------------
    | BODY
    |--------------------------------------------------------------------------
    */

    .products-card-body {
        padding: 22px 28px 28px;
    }

    /*
    |--------------------------------------------------------------------------
    | RECHERCHE
    |--------------------------------------------------------------------------
    */

    .product-search-grid {
        display: flex;
        align-items: stretch;
        flex-wrap: wrap;

        gap: 12px;

        width: 100%;

        margin-bottom: 22px;
    }

    .product-search-input {
        flex: 1 1 360px;

        min-width: 240px;
    }

    .product-search-grid > .btn {
        flex: 0 1 190px;

        min-width: 150px;
    }

    .product-search-grid .form-control,
    .product-search-grid .btn {
        min-height: 48px;

        border-radius: 9px;
    }

    .product-search-grid .form-control {
        border: 1px solid #d8dee8;

        box-shadow: none;
    }

    .product-search-grid .form-control:focus {
        border-color: #696cff;

        box-shadow:
            0 0 0 3px rgba(105, 108, 255, .12);
    }

    .product-search-grid .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 7px;

        font-weight: 700;
    }

    /*
    |--------------------------------------------------------------------------
    | TABLEAU
    |--------------------------------------------------------------------------
    */

    .products-table-wrapper {
        width: 100%;
        max-width: 100%;

        overflow-x: auto;
        overflow-y: visible;

        -webkit-overflow-scrolling: touch;

        border: 1px solid #edf0f4;
        border-radius: 11px;
    }

    .products-table {
        width: 100%;

        min-width: 1780px;

        margin: 0;
    }

    .products-table-unavailable {
        min-width: 1660px;
    }

    .products-table thead th {
        padding: 13px 12px;

        vertical-align: middle;

        white-space: nowrap;

        font-size: 11px;
        font-weight: 800;

        letter-spacing: .04em;

        text-transform: uppercase;

        color: #52657b;

        background: #e8edf3;
    }

    .products-table tbody td {
        padding: 13px 12px;

        vertical-align: middle;

        font-size: 13px;

        color: #52657b;
    }

    .products-table tbody tr:hover {
        background: #f8fafc;
    }

    .products-table .reference-cell {
        min-width: 180px;

        white-space: nowrap;
    }

    .products-table .designation-cell {
        min-width: 200px;
        max-width: 250px;
    }

    .products-table .numeric-cell {
        white-space: nowrap;

        text-align: right;
    }

    .products-table .actions-cell {
        min-width: 140px;

        white-space: nowrap;
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIONS TABLEAU
    |--------------------------------------------------------------------------
    */

    .product-row-actions {
        display: flex;
        align-items: center;
        justify-content: center;

        gap: 6px;
    }

    .product-row-actions .btn {
        width: 36px;
        height: 34px;

        padding: 0;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        border-radius: 7px;
    }

    .product-row-actions form {
        margin: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | BADGES DE QUANTITÉ
    |--------------------------------------------------------------------------
    */

    .quantity-badge {
        min-width: 88px;

        padding: 5px 8px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        border-radius: 6px;

        font-size: 12px;
        font-weight: 700;

        white-space: nowrap;
    }

    .quantity-initial {
        color: #4f46e5;

        background: #eef2ff;
    }

    .quantity-received {
        color: #0369a1;

        background: #e0f2fe;
    }

    .quantity-available {
        color: #15803d;

        background: #dcfce7;
    }

    .quantity-unavailable {
        color: #b45309;

        background: #fef3c7;
    }

    .quantity-sold {
        color: #b91c1c;

        background: #fee2e2;
    }

    /*
    |--------------------------------------------------------------------------
    | BADGES DE STATUT
    |--------------------------------------------------------------------------
    */

    .product-status-badge {
        min-width: 108px;

        padding: 6px 9px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        border-radius: 999px;

        font-size: 11px;
        font-weight: 800;

        white-space: nowrap;
    }

    .product-status-available {
        color: #166534;

        background: #dcfce7;
    }

    .product-status-unavailable {
        color: #991b1b;

        background: #fee2e2;
    }

    .product-status-partial {
        color: #92400e;

        background: #fef3c7;
    }

    .product-status-low {
        color: #9a3412;

        background: #ffedd5;
    }

    .product-status-sold {
        color: #475569;

        background: #e2e8f0;
    }

    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */

    .products-pagination {
        display: flex;
        justify-content: center;

        margin-top: 22px;
    }

    .products-pagination svg,
    .products-pagination nav svg {
        width: 18px !important;
        height: 18px !important;

        max-width: 18px !important;
        max-height: 18px !important;
    }

    /*
    |--------------------------------------------------------------------------
    | ÉCRANS MOYENS
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1100px) {

        .products-card-header,
        .products-card-body {
            padding-left: 20px;
            padding-right: 20px;
        }

        .product-actions-field {
            flex-basis: 100%;
        }

        .product-action-buttons {
            max-width: 430px;

            margin-left: auto;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TABLETTE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 768px) {

        .products-page {
            padding: 14px 10px 32px;
        }

        .products-card-header,
        .products-card-body {
            padding: 16px;
        }

        .product-import-field:nth-child(1),
        .product-import-field:nth-child(2),
        .product-import-field:nth-child(3),
        .product-actions-field {
            flex: 1 1 100%;
        }

        .product-action-buttons {
            max-width: none;

            margin-left: 0;
        }

        .product-search-input,
        .product-search-grid > .btn {
            flex: 1 1 100%;

            min-width: 0;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MOBILE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 480px) {

        .products-title-row {
            align-items: stretch;
            flex-direction: column;
        }

        .products-title-row .btn {
            width: 100%;
        }

        .product-action-buttons {
            grid-template-columns: 1fr;
        }

        .product-add-button {
            width: 100%;

            min-width: 0 !important;
        }

        .products-table {
            min-width: 1450px;
        }
    }
</style>


<div class="products-page">

    <div class="products-page-inner">

        {{-- ============================================================
            MESSAGES
        ============================================================ --}}

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


        <div class="products-card">

            {{-- ========================================================
                HEADER
            ======================================================== --}}

            <div class="products-card-header">

                <div class="products-title-row">

                    <h4>
                        {{ $pageTitle ?? 'Liste des produits' }}
                    </h4>


                    @if($isAvailableProducts)

                        <a
                            href="{{ route('sales.create') }}"
                            class="btn btn-primary"
                        >

                            <i class="bx bx-cart me-1"></i>

                            Nouvelle vente

                        </a>

                    @endif

                </div>


                {{-- ====================================================
                    IMPORTATION
                ==================================================== --}}

                @if(
                    !$hideButtons
                    &&
                    in_array(
                        auth()->user()->role,
                        [
                            'admin',
                            'chef_magasinier',
                            'magasinier'
                        ],
                        true
                    )
                )

                    <form
                        action="{{ route('products.preview') }}"
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        @csrf


                        <div class="product-import-grid">


                            {{-- ==========================================
                                FOURNISSEUR
                            ========================================== --}}

                            <div class="product-import-field">

                                <label
                                    for="supplier_id"
                                    class="form-label"
                                >
                                    Fournisseur
                                </label>


                                <div class="modern-select-wrapper">

                                    <span class="modern-select-icon">

                                        <i class="bx bx-buildings"></i>

                                    </span>


                                    <select
                                        name="supplier_id"
                                        id="supplier_id"
                                        class="modern-select"
                                        required
                                    >

                                        <option value="">
                                            Sélectionner un fournisseur
                                        </option>


                                        @foreach($suppliers as $supplier)

                                            <option
                                                value="{{ $supplier->id }}"
                                                @selected(
                                                    old('supplier_id')
                                                    == $supplier->id
                                                )
                                            >

                                                {{ $supplier->name }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>

                            </div>


                            {{-- ==========================================
                                DÉPÔT
                            ========================================== --}}

                            <div class="product-import-field">

                                <label
                                    for="depot_id"
                                    class="form-label"
                                >
                                    Dépôt
                                </label>


                                <div class="modern-select-wrapper">

                                    <span class="modern-select-icon">

                                        <i class="bx bx-store-alt"></i>

                                    </span>


                                    <select
                                        name="depot_id"
                                        id="depot_id"
                                        class="modern-select"
                                        required
                                    >

                                        <option value="">
                                            Sélectionner un dépôt
                                        </option>


                                        @foreach($depots as $depot)

                                            <option
                                                value="{{ $depot->id }}"
                                                @selected(
                                                    old('depot_id')
                                                    == $depot->id
                                                )
                                            >

                                                {{ $depot->name }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>

                            </div>


                            {{-- ==========================================
                                FICHIER EXCEL
                            ========================================== --}}

                            <div class="product-import-field">

                                <label
                                    for="product_import_file"
                                    class="form-label"
                                >
                                    Fichier Excel
                                </label>


                                <input
                                    type="file"
                                    name="file"
                                    id="product_import_file"
                                    class="product-file-input"
                                    accept=".xlsx,.xls,.csv"
                                    required
                                >

                            </div>


                            {{-- ==========================================
                                ACTIONS
                            ========================================== --}}

                            <div
                                class="
                                    product-import-field
                                    product-actions-field
                                "
                            >

                                <div class="product-action-buttons">


                                    {{-- IMPORTER --}}

                                    <button
                                        type="submit"
                                        class="btn btn-success"
                                    >

                                        <i class="bx bx-upload"></i>

                                        Importer

                                    </button>


                                    {{-- EXCEL --}}

                                    <a
                                        href="{{
                                            route(
                                                'products.export.excel'
                                            )
                                        }}"
                                        class="btn btn-info"
                                    >

                                        <i class="bx bx-download"></i>

                                        Excel

                                    </a>


                                    {{-- AJOUTER --}}

                                    <a
                                        href="{{ route('products.create') }}"
                                        class="
                                            btn
                                            btn-primary
                                            product-add-button
                                        "
                                        title="Ajouter un produit"
                                        aria-label="Ajouter un produit"
                                    >

                                        <i class="bx bx-plus"></i>

                                    </a>

                                </div>

                            </div>

                        </div>

                    </form>

                @endif

            </div>


            {{-- ========================================================
                BODY
            ======================================================== --}}

            <div class="products-card-body">


                {{-- ====================================================
                    RECHERCHE
                ==================================================== --}}

                <form
                    method="GET"
                    action="{{ url()->current() }}"
                    class="product-search-grid"
                >

                    <div class="product-search-input">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Rechercher par référence ou désignation..."
                            value="{{ request('search') }}"
                        >

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="bx bx-search"></i>

                        Rechercher

                    </button>


                    <a
                        href="{{ url()->current() }}"
                        class="btn btn-secondary"
                    >

                        <i class="bx bx-reset"></i>

                        Réinitialiser

                    </a>

                </form>


                {{-- ====================================================
                    TABLEAU
                ==================================================== --}}

                <div class="products-table-wrapper">

                    <table
                        class="
                            table
                            table-hover
                            align-middle
                            products-table
                            {{ $isUnavailablePage ? 'products-table-unavailable' : '' }}
                        "
                    >

                        <thead>

                            <tr>

                                <th>Référence</th>

                                <th>Désignation</th>

                                <th>Marque</th>

                                <th>Modèle</th>

                                <th>Famille</th>

                                <th>Rayon</th>

                                <th>Emplacement</th>


                                {{-- ======================================
                                    TOUS LES PRODUITS
                                ====================================== --}}

                                @if($isAllProducts)

                                    <th>Qté initiale</th>

                                    <!--th>Qté reçue</th-->

                                    <th>Qté disponible</th>

                                    <!--th>Qté non dispo.</th-->

                                    <th>Qté vendue</th>

                                @endif


                                {{-- ======================================
                                    PRODUITS DISPONIBLES
                                ====================================== --}}

                                @if($isAvailableProducts)

                                    <th>Qté disponible</th>

                                @endif


                                {{-- ======================================
                                    PIÈCES NON DISPONIBLES
                                ====================================== --}}

                                @if($isUnavailablePage)

                                    <th>Qté initiale</th>

                                    <th>Qté reçue</th>

                                    <th>Qté disponible</th>

                                    <th>Qté non dispo.</th>

                                @endif


                                {{-- ======================================
                                    PRODUITS VENDUS
                                ====================================== --}}

                                @if($isSoldProducts)

                                    <th>Qté vendue</th>

                                @endif


                                <th>Min</th>

                                <th>Max</th>

                                <th>Prix achat</th>

                                <th>Prix vente</th>

                                <th>Statut</th>


                                <th class="text-center">

                                    Actions

                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($products as $product)


                                @php

                                    /*
                                    |--------------------------------------------------------------------------
                                    | QUANTITÉ INITIALE
                                    |--------------------------------------------------------------------------
                                    */

                                    $initialQty =
                                        (float) (
                                            $product->initial_quantity
                                            ?? 0
                                        );


                                    /*
                                    |--------------------------------------------------------------------------
                                    | QUANTITÉ REÇUE
                                    |--------------------------------------------------------------------------
                                    */

                                    $receivedQty =
                                        (float) (
                                            $product->received_quantity
                                            ?? 0
                                        );


                                    /*
                                    |--------------------------------------------------------------------------
                                    | QUANTITÉ DISPONIBLE
                                    |--------------------------------------------------------------------------
                                    */

                                    $availableQty =
                                        (float) (
                                            $product->quantity
                                            ?? 0
                                        );


                                    /*
                                    |--------------------------------------------------------------------------
                                    | QUANTITÉ NON DISPONIBLE
                                    |--------------------------------------------------------------------------
                                    |
                                    | IMPORTANT :
                                    |
                                    | Qté non disponible =
                                    | Qté initiale - Qté reçue
                                    |
                                    | On ne fait PAS :
                                    | initiale - disponible
                                    |
                                    | car une vente ferait alors augmenter à tort
                                    | la quantité non disponible.
                                    |
                                    */

                                    $unavailableQty =
                                        max(
                                            0,
                                            $initialQty - $receivedQty
                                        );


                                    /*
                                    |--------------------------------------------------------------------------
                                    | QUANTITÉ VENDUE
                                    |--------------------------------------------------------------------------
                                    |
                                    | Ne pas compter les ventes annulées.
                                    |
                                    | Le contrôleur "sold()" peut déjà fournir
                                    | sold_quantity via withSum().
                                    |
                                    */

                                    if (
                                        isset($product->sold_quantity)
                                        &&
                                        $product->sold_quantity !== null
                                    ) {

                                        $soldQty =
                                            (float) $product->sold_quantity;

                                    } else {

                                        $soldQty = (float) $product
                                            ->saleItems()
                                            ->whereHas(
                                                'sale',
                                                function ($query) {

                                                    $query->whereNotIn(
                                                        'status',
                                                        [
                                                            'cancelled',
                                                            'annulé',
                                                            'annule'
                                                        ]
                                                    );

                                                }
                                            )
                                            ->sum('quantity');
                                    }


                                    /*
                                    |--------------------------------------------------------------------------
                                    | UNITÉ
                                    |--------------------------------------------------------------------------
                                    */

                                    $unitLabel =
                                        $product->unit_label
                                        ?: 'Pièce';


                                    /*
                                |--------------------------------------------------------------------------
                                | STATUT AFFICHÉ
                                |--------------------------------------------------------------------------
                                |
                                | IMPORTANT :
                                |
                                | Le statut général d'un produit dépend uniquement :
                                |
                                | - de son statut métier "vendu"
                                | - de sa quantité disponible
                                | - de son stock minimum
                                |
                                | La quantité reçue et la quantité non disponible servent uniquement
                                | au suivi des pièces à commander / pièces non disponibles.
                                |
                                | Elles ne doivent PAS produire "Disponible partiel"
                                | dans la liste générale des produits.
                                |
                                */

                                if ($isSoldProducts) {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | PAGE PRODUITS VENDUS
                                    |--------------------------------------------------------------------------
                                    */

                                    $displayStatus =
                                        'Vendu';

                                    $statusClass =
                                        'product-status-sold';

                                } elseif (
                                    strtolower((string) $product->status) === 'vendu'
                                ) {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | PRODUIT VENDU
                                    |--------------------------------------------------------------------------
                                    */

                                    $displayStatus =
                                        'Vendu';

                                    $statusClass =
                                        'product-status-sold';

                                } elseif (
                                    $availableQty <= 0
                                ) {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | RUPTURE DE STOCK
                                    |--------------------------------------------------------------------------
                                    */

                                    $displayStatus =
                                        'En rupture';

                                    $statusClass =
                                        'product-status-unavailable';

                                } elseif (
                                    (float) $product->min_stock > 0
                                    &&
                                    $availableQty <= (float) $product->min_stock
                                ) {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | STOCK FAIBLE
                                    |--------------------------------------------------------------------------
                                    */

                                    $displayStatus =
                                        'Stock faible';

                                    $statusClass =
                                        'product-status-low';

                                } else {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | PRODUIT DISPONIBLE
                                    |--------------------------------------------------------------------------
                                    */

                                    $displayStatus =
                                        'Disponible';

                                    $statusClass =
                                        'product-status-available';
                                }
                                @endphp


                                <tr>


                                    {{-- REFERENCE --}}

                                    <td class="reference-cell">

                                        <strong>

                                            {{ $product->reference }}

                                        </strong>

                                    </td>


                                    {{-- DESIGNATION --}}

                                    <td class="designation-cell">

                                        {{ $product->designation }}

                                    </td>


                                    {{-- MARQUE --}}

                                    <td>

                                        {{
                                            $product->brand?->name
                                            ?? 'Non défini'
                                        }}

                                    </td>


                                    {{-- MODELE --}}

                                    <td>

                                        {{
                                            $product->model?->name
                                            ?? 'Non défini'
                                        }}

                                    </td>


                                    {{-- FAMILLE --}}

                                    <td>

                                        {{
                                            $product->family?->name
                                            ?? 'Non défini'
                                        }}

                                    </td>


                                    {{-- RAYON --}}

                                    <td>

                                        {{
                                            $product->rayon?->name
                                            ?? 'Non défini'
                                        }}

                                    </td>


                                    {{-- EMPLACEMENT --}}

                                    <td>

                                        {{
                                            $product->location?->name
                                            ?? 'Non défini'
                                        }}

                                    </td>


                                    {{-- ==================================
                                        PAGE TOUS LES PRODUITS
                                    ================================== --}}

                                    @if($isAllProducts)


                                        {{-- QUANTITÉ INITIALE --}}

                                        <td class="numeric-cell">

                                            <span
                                                class="
                                                    quantity-badge
                                                    quantity-initial
                                                "
                                            >

                                                {{
                                                    number_format(
                                                        $initialQty,
                                                        2,
                                                        ',',
                                                        ' '
                                                    )
                                                }}

                                                {{ $unitLabel }}

                                            </span>

                                        </td>


                                        {{-- QUANTITÉ REÇUE --}}

                                        <!--td class="numeric-cell">

                                            <span
                                                class="
                                                    quantity-badge
                                                    quantity-received
                                                "
                                            >

                                                { {
                                                    number_format(
                                                        $receivedQty,
                                                        2,
                                                        ',',
                                                        ' '
                                                    )
                                                }}

                                                { { $unitLabel }}

                                            </span>

                                        </td-->


                                        {{-- QUANTITÉ DISPONIBLE --}}

                                        <td class="numeric-cell">

                                            <span
                                                class="
                                                    quantity-badge
                                                    quantity-available
                                                "
                                            >

                                                {{
                                                    number_format(
                                                        $availableQty,
                                                        2,
                                                        ',',
                                                        ' '
                                                    )
                                                }}

                                                {{ $unitLabel }}

                                            </span>

                                        </td>


                                        {{-- QUANTITÉ NON DISPONIBLE --}}

                                        <!--td class="numeric-cell">

                                            <span
                                                class="
                                                    quantity-badge
                                                    quantity-unavailable
                                                "
                                            >

                                                { 
                                                    number_format(
                                                        $unavailableQty,
                                                        2,
                                                        ',',
                                                        ' '
                                                    )
                                                }}

                                                { { $unitLabel }}

                                            </span>

                                        </td-->


                                        {{-- QUANTITÉ VENDUE --}}

                                        <td class="numeric-cell">

                                            <span
                                                class="
                                                    quantity-badge
                                                    quantity-sold
                                                "
                                            >

                                                {{
                                                    number_format(
                                                        $soldQty,
                                                        2,
                                                        ',',
                                                        ' '
                                                    )
                                                }}

                                                {{ $unitLabel }}

                                            </span>

                                        </td>

                                    @endif


                                    {{-- ==================================
                                        PRODUITS DISPONIBLES
                                    ================================== --}}

                                    @if($isAvailableProducts)

                                        <td class="numeric-cell">

                                            <span
                                                class="
                                                    quantity-badge
                                                    quantity-available
                                                "
                                            >

                                                {{
                                                    number_format(
                                                        $availableQty,
                                                        2,
                                                        ',',
                                                        ' '
                                                    )
                                                }}

                                                {{ $unitLabel }}

                                            </span>

                                        </td>

                                    @endif


                                    {{-- ==================================
                                        PIÈCES NON DISPONIBLES
                                    ================================== --}}

                                    @if($isUnavailablePage)


                                        {{-- QUANTITÉ INITIALE --}}

                                        <td class="numeric-cell">

                                            <span
                                                class="
                                                    quantity-badge
                                                    quantity-initial
                                                "
                                            >

                                                {{
                                                    number_format(
                                                        $initialQty,
                                                        2,
                                                        ',',
                                                        ' '
                                                    )
                                                }}

                                                {{ $unitLabel }}

                                            </span>

                                        </td>


                                        {{-- QUANTITÉ REÇUE --}}

                                        <td class="numeric-cell">

                                            <span
                                                class="
                                                    quantity-badge
                                                    quantity-received
                                                "
                                            >

                                                {{
                                                    number_format(
                                                        $receivedQty,
                                                        2,
                                                        ',',
                                                        ' '
                                                    )
                                                }}

                                                {{ $unitLabel }}

                                            </span>

                                        </td>


                                        {{-- QUANTITÉ DISPONIBLE --}}

                                        <td class="numeric-cell">

                                            <span
                                                class="
                                                    quantity-badge
                                                    quantity-available
                                                "
                                            >

                                                {{
                                                    number_format(
                                                        $availableQty,
                                                        2,
                                                        ',',
                                                        ' '
                                                    )
                                                }}

                                                {{ $unitLabel }}

                                            </span>

                                        </td>


                                        {{-- QUANTITÉ NON DISPONIBLE --}}

                                        <td class="numeric-cell">

                                            <span
                                                class="
                                                    quantity-badge
                                                    quantity-unavailable
                                                "
                                            >

                                                {{
                                                    number_format(
                                                        $unavailableQty,
                                                        2,
                                                        ',',
                                                        ' '
                                                    )
                                                }}

                                                {{ $unitLabel }}

                                            </span>

                                        </td>

                                    @endif


                                    {{-- ==================================
                                        PRODUITS VENDUS
                                    ================================== --}}

                                    @if($isSoldProducts)

                                        <td class="numeric-cell">

                                            <span
                                                class="
                                                    quantity-badge
                                                    quantity-sold
                                                "
                                            >

                                                {{
                                                    number_format(
                                                        $soldQty,
                                                        2,
                                                        ',',
                                                        ' '
                                                    )
                                                }}

                                                {{ $unitLabel }}

                                            </span>

                                        </td>

                                    @endif


                                    {{-- MIN --}}

                                    <td class="numeric-cell">

                                        {{
                                            number_format(
                                                (float)
                                                $product->min_stock,
                                                2,
                                                ',',
                                                ' '
                                            )
                                        }}

                                    </td>


                                    {{-- MAX --}}

                                    <td class="numeric-cell">

                                        {{
                                            number_format(
                                                (float)
                                                $product->max_stock,
                                                2,
                                                ',',
                                                ' '
                                            )
                                        }}

                                    </td>


                                    {{-- PRIX ACHAT --}}

                                    <td class="numeric-cell">

                                        {{
                                            number_format(
                                                (float)
                                                $product->purchase_price,
                                                2,
                                                ',',
                                                ' '
                                            )
                                        }}

                                    </td>


                                    {{-- PRIX VENTE --}}

                                    <td class="numeric-cell">

                                        <strong>

                                            {{
                                                number_format(
                                                    (float)
                                                    $product->sale_price,
                                                    2,
                                                    ',',
                                                    ' '
                                                )
                                            }}

                                        </strong>

                                    </td>


                                    {{-- ==================================
                                        STATUT
                                    ================================== --}}

                                    <td>

                                        <span
                                            class="
                                                product-status-badge
                                                {{ $statusClass }}
                                            "
                                        >

                                            {{ $displayStatus }}

                                        </span>

                                    </td>


                                    {{-- ==================================
                                        ACTIONS
                                    ================================== --}}

                                    <td
                                        class="
                                            actions-cell
                                            text-center
                                        "
                                    >

                                        <div class="product-row-actions">


                                            {{-- VOIR --}}

                                            <a
                                                href="{{
                                                    route(
                                                        'products.show',
                                                        $product
                                                    )
                                                }}"
                                                class="
                                                    btn
                                                    btn-info
                                                    btn-sm
                                                "
                                                title="Voir"
                                            >

                                                <i class="bx bx-show"></i>

                                            </a>


                                            {{-- MODIFIER / SUPPRIMER --}}

                                            @if(
                                                in_array(
                                                    auth()->user()->role,
                                                    [
                                                        'admin',
                                                        'chef_magasinier'
                                                    ],
                                                    true
                                                )
                                            )


                                                {{-- MODIFIER --}}

                                                <a
                                                    href="{{
                                                        route(
                                                            'products.edit',
                                                            $product
                                                        )
                                                    }}"
                                                    class="
                                                        btn
                                                        btn-warning
                                                        btn-sm
                                                    "
                                                    title="Modifier"
                                                >

                                                    <i
                                                        class="
                                                            bx
                                                            bx-edit
                                                        "
                                                    ></i>

                                                </a>


                                                {{-- SUPPRIMER --}}

                                                <form
                                                    action="{{
                                                        route(
                                                            'products.destroy',
                                                            $product
                                                        )
                                                    }}"
                                                    method="POST"
                                                    class="
                                                        delete-form
                                                        mb-0
                                                    "
                                                >

                                                    @csrf

                                                    @method('DELETE')


                                                    <button
                                                        type="submit"
                                                        class="
                                                            btn
                                                            btn-danger
                                                            btn-sm
                                                        "
                                                        title="Supprimer"
                                                    >

                                                        <i
                                                            class="
                                                                bx
                                                                bx-trash
                                                            "
                                                        ></i>

                                                    </button>

                                                </form>

                                            @endif

                                        </div>

                                    </td>

                                </tr>


                            @empty


                                <tr>

                                    <td
                                        colspan="{{ $tableColspan }}"
                                        class="
                                            text-center
                                            text-muted
                                            py-5
                                        "
                                    >

                                        <i
                                            class="
                                                bx
                                                bx-package
                                                fs-1
                                                d-block
                                                mb-2
                                            "
                                        ></i>

                                        Aucun produit trouvé.

                                    </td>

                                </tr>


                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- ====================================================
                    PAGINATION
                ==================================================== --}}

                @if(
                    method_exists($products, 'links')
                    &&
                    $products->hasPages()
                )

                    <div class="products-pagination">

                        {{
                            $products
                                ->appends(request()->query())
                                ->links()
                        }}

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>


<script>
    document.addEventListener(
        'DOMContentLoaded',
        function () {

            /*
            |--------------------------------------------------------------------------
            | SUPPRESSION PRODUIT
            |--------------------------------------------------------------------------
            */

            document
                .querySelectorAll('.delete-form')
                .forEach(function (form) {

                    form.addEventListener(
                        'submit',
                        function (event) {

                            event.preventDefault();

                            /*
                            |--------------------------------------------------------------------------
                            | SI SWEETALERT N'EST PAS CHARGÉ
                            |--------------------------------------------------------------------------
                            */

                            if (typeof Swal === 'undefined') {

                                if (
                                    window.confirm(
                                        'Voulez-vous supprimer ce produit ?'
                                    )
                                ) {

                                    form.submit();
                                }

                                return;
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | SWEETALERT
                            |--------------------------------------------------------------------------
                            */

                            Swal.fire({

                                title:
                                    'Supprimer le produit ?',

                                text:
                                    'Cette action est irréversible.',

                                icon:
                                    'warning',

                                showCancelButton:
                                    true,

                                confirmButtonColor:
                                    '#ef4444',

                                cancelButtonColor:
                                    '#6b7280',

                                confirmButtonText:
                                    'Oui, supprimer',

                                cancelButtonText:
                                    'Annuler',

                                background:
                                    '#0f172a',

                                color:
                                    '#ffffff'

                            }).then(
                                function (result) {

                                    if (
                                        result.isConfirmed
                                    ) {

                                        form.submit();
                                    }
                                }
                            );
                        }
                    );
                });
        }
    );
</script>

@endsection
