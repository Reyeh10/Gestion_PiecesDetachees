@extends('layouts.layoutMaster')

@section('title', 'Nouveau bon de commande')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | FOURNISSEUR COMMUN
    |--------------------------------------------------------------------------
    |
    | createFromPartRequests() a déjà vérifié côté serveur que toutes les
    | pièces appartiennent au même fournisseur.
    |
    */

    $commonSupplier =
        $supplier
        ?? $vehiclePartRequests->first()?->supplier;

    /*
    |--------------------------------------------------------------------------
    | DEVISE PAR DÉFAUT
    |--------------------------------------------------------------------------
    */

    $defaultCurrency =
        old(
            'currency',
            $commonSupplier?->currency ?? 'DJF'
        );

    /*
    |--------------------------------------------------------------------------
    | DEVISES DISPONIBLES
    |--------------------------------------------------------------------------
    */

    $currencies = [
        'DJF' => 'Franc djiboutien',
        'USD' => 'Dollar américain',
        'EUR' => 'Euro',
        'AED' => 'Dirham des Émirats',
        'CNY' => 'Yuan chinois',
    ];
@endphp


<style>
    .bc-page {
        padding: 24px;
    }

    .bc-container {
        max-width: 1450px;
        margin: 0 auto;
    }

    .bc-header-card,
    .bc-card {
        background: #fff;
        border-radius: 18px;
        border: 1px solid #e7eaf3;
        box-shadow: 0 8px 28px rgba(28, 39, 76, .06);
    }

    .bc-header-card {
        padding: 24px 28px;
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }

    .bc-title {
        margin: 0;
        font-size: 27px;
        font-weight: 800;
        color: #27304a;
    }

    .bc-subtitle {
        margin: 6px 0 0;
        color: #8b95ad;
        font-size: 14px;
    }

    .bc-back {
        text-decoration: none;
        border: 1px solid #dfe4ef;
        color: #58627a;
        background: #fff;
        padding: 11px 18px;
        border-radius: 10px;
        font-weight: 700;
        transition: .2s;
        white-space: nowrap;
    }

    .bc-back:hover {
        background: #f6f7fb;
        color: #27304a;
    }

    .bc-card {
        overflow: hidden;
        margin-bottom: 22px;
    }

    .bc-card-header {
        padding: 18px 24px;
        border-bottom: 1px solid #edf0f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .bc-card-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        color: #30384f;
    }

    .bc-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 28px;
        padding: 0 10px;
        border-radius: 999px;
        background: #eef0ff;
        color: #696cff;
        font-size: 12px;
        font-weight: 800;
    }

    .bc-card-body {
        padding: 24px;
    }

    .bc-info-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .bc-info {
        padding: 15px 17px;
        border-radius: 12px;
        background: #f7f8fc;
        border: 1px solid #edf0f6;
    }

    .bc-info-label {
        display: block;
        color: #929bb0;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .7px;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .bc-info-value {
        color: #343c54;
        font-weight: 750;
        overflow-wrap: anywhere;
    }

    .bc-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .bc-field-full {
        grid-column: 1 / -1;
    }

    .bc-label {
        display: block;
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 800;
        color: #606a82;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .bc-required {
        color: #e25563;
    }

    .bc-control {
        width: 100%;
        min-height: 44px;
        border: 1px solid #dce1ec;
        border-radius: 10px;
        padding: 9px 12px;
        background: #fff;
        color: #3b4359;
        outline: none;
        transition: .2s;
    }

    .bc-control:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 3px rgba(105, 108, 255, .10);
    }

    textarea.bc-control {
        min-height: 100px;
        resize: vertical;
    }

    .bc-invalid {
        margin-top: 5px;
        color: #e25563;
        font-size: 12px;
        font-weight: 600;
    }

    .bc-table-wrapper {
        overflow-x: auto;
    }

    .bc-line-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .bc-line-table th {
        padding: 12px 10px;
        background: #f4f5fa;
        color: #657089;
        text-transform: uppercase;
        font-size: 10px;
        letter-spacing: .45px;
        text-align: left;
        white-space: nowrap;
    }

    .bc-line-table td {
        padding: 11px 10px;
        border-bottom: 1px solid #edf0f5;
        color: #414a61;
        vertical-align: middle;
        font-size: 13px;
    }

    .bc-line-table tbody tr:last-child td {
        border-bottom: none;
    }

    .bc-line-table .bc-control {
        min-height: 39px;
        padding: 7px 9px;
    }

    .bc-reference {
        font-weight: 800;
        color: #30384f;
        white-space: nowrap;
    }

    .bc-description {
        min-width: 180px;
    }

    .bc-secondary {
        display: block;
        margin-top: 3px;
        color: #929bb0;
        font-size: 11px;
    }

    .bc-number {
        text-align: right;
        white-space: nowrap;
    }

    .bc-price-grid {
        display: grid;
        grid-template-columns: 1fr 420px;
        gap: 24px;
        align-items: start;
    }

    .bc-summary {
        background: #f7f8fc;
        border: 1px solid #e8ebf3;
        border-radius: 14px;
        padding: 20px;
    }

    .bc-summary-row {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        padding: 9px 0;
        color: #59627a;
    }

    .bc-summary-total {
        border-top: 1px solid #dfe3ed;
        margin-top: 8px;
        padding-top: 15px;
        font-size: 19px;
        font-weight: 900;
        color: #30384f;
    }

    .bc-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
    }

    .bc-btn {
        border: none;
        border-radius: 10px;
        padding: 11px 18px;
        font-weight: 800;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .bc-btn-primary {
        background: #696cff;
        color: #fff;
        box-shadow: 0 6px 16px rgba(105, 108, 255, .25);
    }

    .bc-btn-secondary {
        background: #eef0f5;
        color: #59627a;
    }

    .bc-alert {
        padding: 14px 18px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .bc-alert-danger {
        background: #fff1f2;
        color: #b42333;
        border: 1px solid #ffd5d9;
    }

    @media (max-width: 1100px) {
        .bc-info-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .bc-price-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .bc-page {
            padding: 12px;
        }

        .bc-header-card {
            align-items: flex-start;
            flex-direction: column;
        }

        .bc-info-grid,
        .bc-form-grid {
            grid-template-columns: 1fr;
        }

        .bc-field-full {
            grid-column: auto;
        }
    }
</style>


<div class="bc-page">

    <div class="bc-container">

        {{-- ================================================================
             EN-TÊTE
        ================================================================ --}}

        <div class="bc-header-card">

            <div>
                <h1 class="bc-title">
                    Nouveau bon de commande
                </h1>

                <p class="bc-subtitle">
                    Création d'un bon de commande fournisseur contenant
                    plusieurs pièces commandées
                </p>
            </div>

            <a
                href="{{ route('vehicle-part-requests.ordered') }}"
                class="bc-back"
            >
                ← Retour aux pièces commandées
            </a>

        </div>


        {{-- ================================================================
             ERREURS
        ================================================================ --}}

        @if($errors->any())

            <div class="bc-alert bc-alert-danger">

                <strong>
                    Le formulaire contient des erreurs.
                </strong>

                <ul style="margin:8px 0 0 18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>

        @endif


        {{-- ================================================================
             RÉSUMÉ
        ================================================================ --}}

        <div class="bc-card">

            <div class="bc-card-header">
                <h5>Résumé de la commande</h5>

                <span class="bc-count">
                    {{ $vehiclePartRequests->count() }}
                    pièce{{ $vehiclePartRequests->count() > 1 ? 's' : '' }}
                </span>
            </div>

            <div class="bc-card-body">

                <div class="bc-info-grid">

                    <div class="bc-info">
                        <span class="bc-info-label">
                            Fournisseur
                        </span>

                        <div class="bc-info-value">
                            {{ $commonSupplier?->code
                                ? $commonSupplier->code . ' - '
                                : '' }}

                            {{ $commonSupplier?->name ?? '-' }}
                        </div>
                    </div>

                    <div class="bc-info">
                        <span class="bc-info-label">
                            Nombre de pièces
                        </span>

                        <div class="bc-info-value">
                            {{ $vehiclePartRequests->count() }}
                        </div>
                    </div>

                    <div class="bc-info">
                        <span class="bc-info-label">
                            Statut requis
                        </span>

                        <div class="bc-info-value">
                            COMMANDÉE
                        </div>
                    </div>

                </div>

            </div>

        </div>


        <form
            method="POST"
            action="{{ route('supplier-orders.store') }}"
            id="supplierOrderForm"
        >

            @csrf

            {{-- ============================================================
                 INFORMATIONS DU BC
            ============================================================ --}}

            <div class="bc-card">

                <div class="bc-card-header">
                    <h5>Informations du bon de commande</h5>
                </div>

                <div class="bc-card-body">

                    <div class="bc-form-grid">

                        <div>

                            <label class="bc-label">
                                Fournisseur
                                <span class="bc-required">*</span>
                            </label>

                            <select
                                name="supplier_id"
                                class="bc-control"
                                required
                            >

                                <option value="">
                                    Sélectionner
                                </option>

                                @foreach($suppliers as $supplierOption)

                                    <option
                                        value="{{ $supplierOption->id }}"
                                        @selected(
                                            old(
                                                'supplier_id',
                                                $commonSupplier?->id
                                            )
                                            == $supplierOption->id
                                        )
                                    >
                                        {{ $supplierOption->code
                                            ? $supplierOption->code . ' - '
                                            : '' }}

                                        {{ $supplierOption->name }}
                                    </option>

                                @endforeach

                            </select>

                            @error('supplier_id')
                                <div class="bc-invalid">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div>

                            <label class="bc-label">
                                Dépôt de destination
                                <span class="bc-required">*</span>
                            </label>

                            <select
                                name="depot_id"
                                class="bc-control"
                                required
                            >

                                <option value="">
                                    Sélectionner le dépôt
                                </option>

                                @foreach($depots as $depot)

                                    <option
                                        value="{{ $depot->id }}"
                                        @selected(
                                            old('depot_id')
                                            == $depot->id
                                        )
                                    >
                                        {{ $depot->code
                                            ? $depot->code . ' - '
                                            : '' }}

                                        {{ $depot->name }}
                                    </option>

                                @endforeach

                            </select>

                            @error('depot_id')
                                <div class="bc-invalid">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div>

                            <label class="bc-label">
                                Date de commande
                                <span class="bc-required">*</span>
                            </label>

                            <input
                                type="date"
                                name="order_date"
                                class="bc-control"
                                value="{{ old(
                                    'order_date',
                                    now()->format('Y-m-d')
                                ) }}"
                                required
                            >

                            @error('order_date')
                                <div class="bc-invalid">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div>

                            <label class="bc-label">
                                Livraison prévue
                            </label>

                            <input
                                type="date"
                                name="expected_delivery_date"
                                class="bc-control"
                                value="{{ old(
                                    'expected_delivery_date'
                                ) }}"
                            >

                            @error('expected_delivery_date')
                                <div class="bc-invalid">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div>

                            <label class="bc-label">
                                Devise
                                <span class="bc-required">*</span>
                            </label>

                            <select
                                name="currency"
                                id="currency"
                                class="bc-control"
                                required
                            >

                                @foreach(
                                    $currencies
                                    as $currency => $currencyLabel
                                )

                                    <option
                                        value="{{ $currency }}"
                                        @selected(
                                            $defaultCurrency === $currency
                                        )
                                    >
                                        {{ $currency }}
                                        - {{ $currencyLabel }}
                                    </option>

                                @endforeach

                            </select>

                            @error('currency')
                                <div class="bc-invalid">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div>

                            <label class="bc-label">
                                Conditions de paiement
                            </label>

                            <input
                                type="text"
                                name="payment_terms"
                                class="bc-control"
                                value="{{ old('payment_terms') }}"
                                placeholder="Ex. 30 jours"
                            >

                        </div>


                        <div class="bc-field-full">

                            <label class="bc-label">
                                Conditions de livraison
                            </label>

                            <input
                                type="text"
                                name="delivery_terms"
                                class="bc-control"
                                value="{{ old('delivery_terms') }}"
                                placeholder="Ex. Livraison au dépôt principal"
                            >

                        </div>

                    </div>

                </div>

            </div>


            {{-- ============================================================
                 PIÈCES DU BON DE COMMANDE
            ============================================================ --}}

            <div class="bc-card">

                <div class="bc-card-header">

                    <h5>
                        Pièces commandées
                    </h5>

                    <span class="bc-count">
                        {{ $vehiclePartRequests->count() }}
                    </span>

                </div>

                <div class="bc-table-wrapper">

                    <table class="bc-line-table">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Référence</th>
                                <th>Désignation</th>
                                <th>Véhicule / Client</th>
                                <th>Unité</th>
                                <th style="width:135px;">Quantité</th>
                                <th style="width:170px;">Prix unitaire</th>
                                <th style="width:170px;text-align:right;">
                                    Total
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach(
                                $vehiclePartRequests as $index => $partRequest
                            )

                                @php
                                    $product =
                                        $partRequest->product;

                                    $vehicle =
                                        $partRequest->vehicle;

                                    $customer =
                                        $vehicle?->customer;

                                    $reference =
                                        $partRequest->reference
                                        ?? $product?->reference
                                        ?? '-';

                                    $description =
                                        $partRequest->part_name
                                        ?? $product?->designation
                                        ?? 'Pièce';

                                    $unit =
                                        $partRequest->unit
                                        ?? $product?->unit_label
                                        ?? 'Pièce';

                                    $quantity =
                                        old(
                                            "items.$index.quantity_ordered",
                                            $partRequest->quantity ?? 1
                                        );

                                    $unitPrice =
                                        old(
                                            "items.$index.unit_price",
                                            $partRequest->purchase_price
                                                ?? $partRequest->estimated_price
                                                ?? 0
                                        );
                                @endphp

                                <tr
                                    class="bc-item-row"
                                    data-index="{{ $index }}"
                                >

                                    <td>
                                        {{ $loop->iteration }}
                                    </td>

                                    <td>

                                        <input
                                            type="hidden"
                                            name="items[{{ $index }}][vehicle_part_request_id]"
                                            value="{{ $partRequest->id }}"
                                        >

                                        <span class="bc-reference">
                                            {{ $reference }}
                                        </span>

                                    </td>

                                    <td class="bc-description">

                                        {{ $description }}

                                        @if($partRequest->notes)
                                            <span class="bc-secondary">
                                                {{ $partRequest->notes }}
                                            </span>
                                        @endif

                                    </td>

                                    <td>

                                        <strong>
                                            {{ $vehicle?->registration_number
                                                ?? $vehicle?->immatriculation
                                                ?? $vehicle?->vin
                                                ?? '-' }}
                                        </strong>

                                        <span class="bc-secondary">
                                            {{ $customer?->name
                                                ?? $customer?->nom
                                                ?? '-' }}
                                        </span>

                                    </td>

                                    <td>
                                        {{ $unit }}
                                    </td>

                                    <td>

                                        <input
                                            type="number"
                                            name="items[{{ $index }}][quantity_ordered]"
                                            class="bc-control bc-item-quantity"
                                            step="0.01"
                                            min="0.01"
                                            value="{{ $quantity }}"
                                            required
                                        >

                                        @error(
                                            "items.$index.quantity_ordered"
                                        )
                                            <div class="bc-invalid">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                    </td>

                                    <td>

                                        <input
                                            type="number"
                                            name="items[{{ $index }}][unit_price]"
                                            class="bc-control bc-item-price"
                                            step="0.0001"
                                            min="0"
                                            value="{{ $unitPrice }}"
                                            required
                                        >

                                        @error(
                                            "items.$index.unit_price"
                                        )
                                            <div class="bc-invalid">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                    </td>

                                    <td class="bc-number">

                                        <strong
                                            class="bc-item-total"
                                        >
                                            0,00
                                        </strong>

                                        <span class="bcCurrency">
                                            {{ $defaultCurrency }}
                                        </span>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>


            {{-- ============================================================
                 MONTANTS
            ============================================================ --}}

            <div class="bc-card">

                <div class="bc-card-header">
                    <h5>Montants et observations</h5>
                </div>

                <div class="bc-card-body">

                    <div class="bc-price-grid">

                        <div>

                            <label class="bc-label">
                                Notes
                            </label>

                            <textarea
                                name="notes"
                                class="bc-control"
                                placeholder="Observations concernant ce bon de commande..."
                            >{{ old('notes') }}</textarea>

                        </div>


                        <div>

                            <div style="margin-bottom:14px;">

                                <label class="bc-label">
                                    Remise (%)
                                </label>

                                <input
                                    type="number"
                                    name="discount_rate"
                                    id="discount_rate"
                                    class="bc-control"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    value="{{ old(
                                        'discount_rate',
                                        0
                                    ) }}"
                                >

                                <div
                                    style="
                                        margin-top:6px;
                                        font-size:11px;
                                        color:#8a93a6;
                                    "
                                >
                                    Montant de la remise :

                                    <strong>
                                        <span id="discountAmountSmall">
                                            0,00
                                        </span>

                                        <span class="bcCurrency">
                                            {{ $defaultCurrency }}
                                        </span>
                                    </strong>
                                </div>

                            </div>


                            <div style="margin-bottom:14px;">

                                <label class="bc-label">
                                    Frais de transport
                                </label>

                                <input
                                    type="number"
                                    name="shipping_cost"
                                    id="shipping_cost"
                                    class="bc-control"
                                    step="0.01"
                                    min="0"
                                    value="{{ old(
                                        'shipping_cost',
                                        0
                                    ) }}"
                                >

                            </div>


                            <div style="margin-bottom:20px;">

                                <label class="bc-label">
                                    Taxe / TVA (%)
                                </label>

                                <input
                                    type="number"
                                    name="tax_rate"
                                    id="tax_rate"
                                    class="bc-control"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    value="{{ old(
                                        'tax_rate',
                                        0
                                    ) }}"
                                >

                                <div
                                    style="
                                        margin-top:6px;
                                        font-size:11px;
                                        color:#8a93a6;
                                    "
                                >
                                    Montant de la taxe :

                                    <strong>
                                        <span id="taxAmountSmall">
                                            0,00
                                        </span>

                                        <span class="bcCurrency">
                                            {{ $defaultCurrency }}
                                        </span>
                                    </strong>
                                </div>

                            </div>


                            <div class="bc-summary">

                                <div class="bc-summary-row">

                                    <span>Sous-total</span>

                                    <strong>
                                        <span id="subtotal">
                                            0,00
                                        </span>

                                        <span class="bcCurrency">
                                            {{ $defaultCurrency }}
                                        </span>
                                    </strong>

                                </div>


                                <div class="bc-summary-row">

                                    <span>
                                        Remise
                                        (<span id="discountRateDisplay">
                                            0
                                        </span> %)
                                    </span>

                                    <strong>
                                        <span id="discountDisplay">
                                            0,00
                                        </span>

                                        <span class="bcCurrency">
                                            {{ $defaultCurrency }}
                                        </span>
                                    </strong>

                                </div>


                                <div class="bc-summary-row">

                                    <span>Transport</span>

                                    <strong>
                                        <span id="shippingDisplay">
                                            0,00
                                        </span>

                                        <span class="bcCurrency">
                                            {{ $defaultCurrency }}
                                        </span>
                                    </strong>

                                </div>


                                <div class="bc-summary-row">

                                    <span>
                                        Taxe
                                        (<span id="taxRateDisplay">
                                            0
                                        </span> %)
                                    </span>

                                    <strong>
                                        <span id="taxDisplay">
                                            0,00
                                        </span>

                                        <span class="bcCurrency">
                                            {{ $defaultCurrency }}
                                        </span>
                                    </strong>

                                </div>


                                <div
                                    class="
                                        bc-summary-row
                                        bc-summary-total
                                    "
                                >

                                    <span>TOTAL</span>

                                    <span>
                                        <span id="grandTotal">
                                            0,00
                                        </span>

                                        <span class="bcCurrency">
                                            {{ $defaultCurrency }}
                                        </span>
                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="bc-actions">

                        <a
                            href="{{
                                route(
                                    'vehicle-part-requests.ordered'
                                )
                            }}"
                            class="bc-btn bc-btn-secondary"
                        >
                            Annuler
                        </a>

                        <button
                            type="submit"
                            class="bc-btn bc-btn-primary"
                        >
                            ✓ Créer le bon de commande
                        </button>

                    </div>

                </div>

            </div>

        </form>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ÉLÉMENTS GLOBAUX
    |--------------------------------------------------------------------------
    */

    const itemRows =
        document.querySelectorAll('.bc-item-row');

    const discountRate =
        document.getElementById('discount_rate');

    const shipping =
        document.getElementById('shipping_cost');

    const taxRate =
        document.getElementById('tax_rate');

    const currency =
        document.getElementById('currency');


    /*
    |--------------------------------------------------------------------------
    | CONVERTIR UNE VALEUR EN NOMBRE
    |--------------------------------------------------------------------------
    */

    function numberValue(element) {

        if (!element) {
            return 0;
        }

        const value =
            parseFloat(element.value);

        return Number.isFinite(value)
            ? value
            : 0;
    }


    /*
    |--------------------------------------------------------------------------
    | POURCENTAGE ENTRE 0 ET 100
    |--------------------------------------------------------------------------
    */

    function percentageValue(element) {

        return Math.max(
            0,
            Math.min(
                100,
                numberValue(element)
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORMAT MONÉTAIRE
    |--------------------------------------------------------------------------
    */

    function formatMoney(value) {

        return Number(value).toLocaleString(
            'fr-FR',
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORMAT POURCENTAGE
    |--------------------------------------------------------------------------
    */

    function formatRate(value) {

        return Number(value).toLocaleString(
            'fr-FR',
            {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MODIFIER UN TEXTE
    |--------------------------------------------------------------------------
    */

    function setText(id, value) {

        const element =
            document.getElementById(id);

        if (element) {
            element.textContent = value;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CALCULER LE BON DE COMMANDE
    |--------------------------------------------------------------------------
    */

    function calculate() {

        let subtotal = 0;


        /*
        |--------------------------------------------------------------------------
        | CALCUL DE CHAQUE LIGNE
        |--------------------------------------------------------------------------
        */

        itemRows.forEach(function (row) {

            const quantity =
                row.querySelector(
                    '.bc-item-quantity'
                );

            const unitPrice =
                row.querySelector(
                    '.bc-item-price'
                );

            const totalElement =
                row.querySelector(
                    '.bc-item-total'
                );

            const qty =
                Math.max(
                    0,
                    numberValue(quantity)
                );

            const price =
                Math.max(
                    0,
                    numberValue(unitPrice)
                );

            const lineTotal =
                qty * price;

            subtotal += lineTotal;

            if (totalElement) {
                totalElement.textContent =
                    formatMoney(lineTotal);
            }
        });


        /*
        |--------------------------------------------------------------------------
        | REMISE
        |--------------------------------------------------------------------------
        */

        const discountRateValue =
            percentageValue(discountRate);

        const discountAmount =
            subtotal
            * discountRateValue
            / 100;


        /*
        |--------------------------------------------------------------------------
        | APRÈS REMISE
        |--------------------------------------------------------------------------
        */

        const afterDiscount =
            Math.max(
                0,
                subtotal - discountAmount
            );


        /*
        |--------------------------------------------------------------------------
        | TRANSPORT
        |--------------------------------------------------------------------------
        */

        const shippingValue =
            Math.max(
                0,
                numberValue(shipping)
            );


        /*
        |--------------------------------------------------------------------------
        | BASE TAXABLE
        |--------------------------------------------------------------------------
        */

        const taxableAmount =
            afterDiscount
            + shippingValue;


        /*
        |--------------------------------------------------------------------------
        | TAXE
        |--------------------------------------------------------------------------
        */

        const taxRateValue =
            percentageValue(taxRate);

        const taxAmount =
            taxableAmount
            * taxRateValue
            / 100;


        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */

        const total =
            Math.max(
                0,
                taxableAmount
                + taxAmount
            );


        /*
        |--------------------------------------------------------------------------
        | AFFICHAGE
        |--------------------------------------------------------------------------
        */

        setText(
            'subtotal',
            formatMoney(subtotal)
        );

        setText(
            'discountDisplay',
            formatMoney(discountAmount)
        );

        setText(
            'discountAmountSmall',
            formatMoney(discountAmount)
        );

        setText(
            'discountRateDisplay',
            formatRate(discountRateValue)
        );

        setText(
            'shippingDisplay',
            formatMoney(shippingValue)
        );

        setText(
            'taxDisplay',
            formatMoney(taxAmount)
        );

        setText(
            'taxAmountSmall',
            formatMoney(taxAmount)
        );

        setText(
            'taxRateDisplay',
            formatRate(taxRateValue)
        );

        setText(
            'grandTotal',
            formatMoney(total)
        );


        /*
        |--------------------------------------------------------------------------
        | DEVISE
        |--------------------------------------------------------------------------
        */

        if (currency) {

            document
                .querySelectorAll('.bcCurrency')
                .forEach(function (element) {

                    element.textContent =
                        currency.value;
                });
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ÉVÉNEMENTS DES LIGNES
    |--------------------------------------------------------------------------
    */

    itemRows.forEach(function (row) {

        const quantity =
            row.querySelector(
                '.bc-item-quantity'
            );

        const unitPrice =
            row.querySelector(
                '.bc-item-price'
            );

        [
            quantity,
            unitPrice
        ].forEach(function (element) {

            if (!element) {
                return;
            }

            element.addEventListener(
                'input',
                calculate
            );

            element.addEventListener(
                'change',
                calculate
            );
        });
    });


    /*
    |--------------------------------------------------------------------------
    | ÉVÉNEMENTS DES MONTANTS GLOBAUX
    |--------------------------------------------------------------------------
    */

    [
        discountRate,
        shipping,
        taxRate,
        currency
    ].forEach(function (element) {

        if (!element) {
            return;
        }

        element.addEventListener(
            'input',
            calculate
        );

        element.addEventListener(
            'change',
            calculate
        );
    });


    /*
    |--------------------------------------------------------------------------
    | PREMIER CALCUL
    |--------------------------------------------------------------------------
    */

    calculate();

});
</script>

@endsection
