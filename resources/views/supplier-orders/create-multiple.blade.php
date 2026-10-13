@extends('layouts.layoutMaster')

@section('title', isset($supplierOrder) ? 'Modifier le bon de commande' : 'Nouveau bon de commande')

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

    /*
    | MODE ÉDITION : même formulaire pour modifier un BC pas encore
    | approuvé (SupplierOrderController::edit / update).
    */
    $supplierOrder = $supplierOrder ?? null;
    $modeEdition = $supplierOrder !== null;
    $piecesAjoutables = $piecesAjoutables ?? collect();

    $commonSupplier =
        $supplier
        ?? $vehiclePartRequests->first()?->supplier;

    // Lignes du tableau : [pièce, quantité, prix]
    $lignesBc = $modeEdition
        ? $lignes
        : $vehiclePartRequests->values()->map(fn ($partRequest, $index) => [
            'part'     => $partRequest,
            'quantity' => old("items.$index.quantity_ordered", $partRequest->quantity ?? 1),
            'price'    => old("items.$index.unit_price", $partRequest->purchase_price ?? $partRequest->estimated_price ?? 0),
        ]);

    /*
    |--------------------------------------------------------------------------
    | DEVISE PAR DÉFAUT
    |--------------------------------------------------------------------------
    */

    $defaultCurrency =
        old(
            'currency',
            $supplierOrder?->currency ?? $commonSupplier?->currency ?? 'DJF'
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

    .bc-remove-row {
        width: 32px;
        height: 32px;
        color: #dc2626;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
    }

    .bc-remove-row:hover { background: #fee2e2; }
    .bc-remove-row:disabled { opacity: .35; cursor: not-allowed; }

    .bc-add-piece {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        padding: 14px 18px;
        border-top: 1px solid #eef0f4;
    }

    .bc-add-piece .bc-control {
        flex: 1 1 360px;
        max-width: 640px;
    }

    .bc-add-hint { flex-basis: 100%; }

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
                    {{ $modeEdition ? 'Modifier le bon de commande ' . $supplierOrder->order_number : 'Nouveau bon de commande' }}
                </h1>

                <p class="bc-subtitle">
                    @if($modeEdition)
                        Modifiable tant que l'administrateur ne l'a pas approuvé :
                        fournisseur, informations, pièces (ajouter, retirer, quantités, prix).
                    @else
                        Création d'un bon de commande fournisseur contenant
                        plusieurs pièces commandées
                    @endif
                </p>
            </div>

            <a
                href="{{ $modeEdition ? route('supplier-orders.show', $supplierOrder) : route('vehicle-part-requests.ordered') }}"
                class="bc-back"
            >
                {{ $modeEdition ? '← Retour au bon de commande' : '← Retour aux pièces commandées' }}
            </a>

        </div>


        {{-- ================================================================
             ERREURS
        ================================================================ --}}

        @if(session('error'))
            <div class="bc-alert bc-alert-danger">{{ session('error') }}</div>
        @endif

        @if($modeEdition && $supplierOrder->status === \App\Models\SupplierOrder::STATUS_REJECTED)
            <div class="bc-alert bc-alert-danger">
                <strong>Bon de commande rejeté</strong>
                @if($supplierOrder->rejection_reason)
                    — motif : {{ $supplierOrder->rejection_reason }}
                @endif
                <br>Après modification, il repassera en brouillon pour être approuvé.
            </div>
        @endif

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
                    <span class="bcNbPieces">{{ $lignesBc->count() }}</span>&nbsp;pièce(s)
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

                        <div class="bc-info-value bcNbPieces">
                            {{ $lignesBc->count() }}
                        </div>
                    </div>

                    <div class="bc-info">
                        <span class="bc-info-label">
                            {{ $modeEdition ? 'Statut du BC' : 'Statut requis' }}
                        </span>

                        <div class="bc-info-value">
                            {{ $modeEdition
                                ? ($supplierOrder->status === \App\Models\SupplierOrder::STATUS_REJECTED ? 'REJETÉ' : 'BROUILLON')
                                : 'COMMANDÉE' }}
                        </div>
                    </div>

                </div>

            </div>

        </div>


        <form
            method="POST"
            action="{{ $modeEdition ? route('supplier-orders.update', $supplierOrder) : route('supplier-orders.store') }}"
            id="supplierOrderForm"
        >

            @csrf

            @if($modeEdition)
                @method('PUT')
            @endif

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
                                            old('depot_id', $supplierOrder?->depot_id)
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
                                    $supplierOrder?->order_date?->format('Y-m-d') ?? now()->format('Y-m-d')
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
                                    'expected_delivery_date',
                                    $supplierOrder?->expected_delivery_date?->format('Y-m-d')
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
                                value="{{ old('payment_terms', $supplierOrder?->payment_terms) }}"
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
                                value="{{ old('delivery_terms', $supplierOrder?->delivery_terms) }}"
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

                    <span class="bc-count bcNbPieces">
                        {{ $lignesBc->count() }}
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
                                <th style="width:60px;"></th>
                            </tr>
                        </thead>

                        <tbody id="bcItemsBody">

                            @foreach(
                                $lignesBc as $index => $ligneBc
                            )

                                @php
                                    $partRequest =
                                        $ligneBc['part'];

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
                                        $ligneBc['quantity'];

                                    $unitPrice =
                                        $ligneBc['price'];
                                @endphp

                                <tr
                                    class="bc-item-row"
                                    data-index="{{ $index }}"
                                >

                                    <td class="bc-row-number">
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

                                    <td>
                                        <button
                                            type="button"
                                            class="bc-remove-row"
                                            title="Retirer cette pièce du bon de commande"
                                        >✕</button>
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

                @if($modeEdition)

                    {{-- Ajouter une pièce COMMANDÉE qui n'est dans aucun BC actif --}}
                    <div class="bc-add-piece">

                        @if($piecesAjoutables->isEmpty())

                            <span class="bc-secondary">
                                Aucune autre pièce COMMANDÉE disponible à ajouter
                                (toutes sont déjà dans un bon de commande).
                            </span>

                        @else

                            <select id="bcAddSelect" class="bc-control">
                                <option value="">— Ajouter une pièce commandée —</option>
                                @foreach($piecesAjoutables as $piece)
                                    @php
                                        $vehiculeAjout = $piece->vehicle;
                                    @endphp
                                    <option
                                        value="{{ $piece->id }}"
                                        data-reference="{{ $piece->reference ?? $piece->product?->reference ?? '-' }}"
                                        data-description="{{ $piece->part_name ?? $piece->product?->designation ?? 'Pièce' }}"
                                        data-notes="{{ $piece->notes }}"
                                        data-vehicle="{{ $vehiculeAjout?->registration_number ?? $vehiculeAjout?->immatriculation ?? $vehiculeAjout?->vin ?? '-' }}"
                                        data-customer="{{ $vehiculeAjout?->customer?->name ?? $vehiculeAjout?->customer?->nom ?? '-' }}"
                                        data-unit="{{ $piece->unit ?? $piece->product?->unit_label ?? 'Pièce' }}"
                                        data-quantity="{{ $piece->quantity ?? 1 }}"
                                        data-price="{{ $piece->purchase_price ?? $piece->estimated_price ?? 0 }}"
                                    >
                                        {{ $piece->reference ?? $piece->product?->reference ?? '-' }}
                                        — {{ $piece->part_name ?? $piece->product?->designation ?? 'Pièce' }}
                                        ({{ $piece->supplier?->name ?? 'sans fournisseur' }})
                                    </option>
                                @endforeach
                            </select>

                            <button type="button" id="bcAddButton" class="bc-btn bc-btn-secondary">
                                + Ajouter
                            </button>

                            <span class="bc-secondary bc-add-hint">
                                La pièce ajoutée passera au fournisseur de ce bon de commande.
                            </span>

                        @endif

                    </div>

                @endif

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
                            >{{ old('notes', $supplierOrder?->notes) }}</textarea>

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
                                        $supplierOrder?->discount_rate ?? 0
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
                                        $supplierOrder?->shipping_cost ?? 0
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
                                        $supplierOrder?->tax_rate ?? 0
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
                                $modeEdition
                                    ? route('supplier-orders.show', $supplierOrder)
                                    : route('vehicle-part-requests.ordered')
                            }}"
                            class="bc-btn bc-btn-secondary"
                        >
                            Annuler
                        </a>

                        <button
                            type="submit"
                            class="bc-btn bc-btn-primary"
                        >
                            {{ $modeEdition ? '✓ Enregistrer les modifications' : '✓ Créer le bon de commande' }}
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

    const itemsBody =
        document.getElementById('bcItemsBody');

    // Lignes lues à chaque calcul : on peut en ajouter / retirer
    function itemRowsList() {
        return document.querySelectorAll('.bc-item-row');
    }

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

        itemRowsList().forEach(function (row) {

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
    | ÉVÉNEMENTS DES LIGNES (aussi pour les lignes ajoutées)
    |--------------------------------------------------------------------------
    */

    ['input', 'change'].forEach(function (type) {
        itemsBody.addEventListener(type, function (event) {
            if (event.target.matches('.bc-item-quantity, .bc-item-price')) {
                calculate();
            }
        });
    });

    // Numéros, nombre de pièces, et au moins une pièce dans le BC
    function majLignes() {
        const rows = itemRowsList();
        rows.forEach(function (row, i) {
            const numero = row.querySelector('.bc-row-number');
            if (numero) numero.textContent = i + 1;
            const retirer = row.querySelector('.bc-remove-row');
            if (retirer) retirer.disabled = rows.length <= 1;
        });
        document.querySelectorAll('.bcNbPieces').forEach(function (el) {
            el.textContent = rows.length;
        });
        calculate();
    }

    const addSelect = document.getElementById('bcAddSelect');

    itemsBody.addEventListener('click', function (event) {
        const bouton = event.target.closest('.bc-remove-row');
        if (!bouton || bouton.disabled) return;
        const row = bouton.closest('.bc-item-row');
        const id = row.querySelector('input[name$="[vehicle_part_request_id]"]').value;

        // La pièce retirée redevient proposée dans « Ajouter »
        if (addSelect) {
            const option = addSelect.querySelector('option[value="' + id + '"]');
            if (option) option.hidden = false;
        }

        row.remove();
        majLignes();
    });

    function echapper(texte) {
        return String(texte ?? '').replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    let prochainIndex = itemRowsList().length + 1000;

    const addButton = document.getElementById('bcAddButton');

    if (addButton && addSelect) {
        addButton.addEventListener('click', function () {
            const option = addSelect.selectedOptions[0];
            if (!option || !option.value) return;

            const d = option.dataset;
            const index = prochainIndex++;
            const devise = currency ? currency.value : '';
            const tr = document.createElement('tr');
            tr.className = 'bc-item-row';
            tr.dataset.index = index;
            tr.innerHTML =
                '<td class="bc-row-number"></td>'
                + '<td><input type="hidden" name="items[' + index + '][vehicle_part_request_id]" value="' + echapper(option.value) + '">'
                + '<span class="bc-reference">' + echapper(d.reference) + '</span></td>'
                + '<td class="bc-description">' + echapper(d.description)
                + (d.notes ? '<span class="bc-secondary">' + echapper(d.notes) + '</span>' : '') + '</td>'
                + '<td><strong>' + echapper(d.vehicle) + '</strong><span class="bc-secondary">' + echapper(d.customer) + '</span></td>'
                + '<td>' + echapper(d.unit) + '</td>'
                + '<td><input type="number" name="items[' + index + '][quantity_ordered]" class="bc-control bc-item-quantity" step="0.01" min="0.01" value="' + echapper(d.quantity) + '" required></td>'
                + '<td><input type="number" name="items[' + index + '][unit_price]" class="bc-control bc-item-price" step="0.0001" min="0" value="' + echapper(d.price) + '" required></td>'
                + '<td class="bc-number"><strong class="bc-item-total">0,00</strong> <span class="bcCurrency">' + echapper(devise) + '</span></td>'
                + '<td><button type="button" class="bc-remove-row" title="Retirer cette pièce du bon de commande">✕</button></td>';

            itemsBody.appendChild(tr);
            option.hidden = true;
            addSelect.value = '';
            majLignes();
        });
    }

    majLignes();

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
