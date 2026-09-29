@extends('layouts.layoutMaster')

@section(
    'title',
    'Bon de commande ' . $supplierOrder->order_number
)

@section('content')

@php

    $statusLabels = [
        'draft' => 'Brouillon',
        'approved' => 'Approuvé',
        'sent' => 'Envoyé',
        'partial_received' => 'Réception partielle',
        'received' => 'Reçu',
        'cancelled' => 'Annulé',
    ];

    $statusLabel =
        $statusLabels[$supplierOrder->status]
        ?? strtoupper($supplierOrder->status);

@endphp


<style>
    .order-page {
        padding: 24px;
    }

    .order-wrapper {
        max-width: 1250px;
        margin: auto;
    }

    .order-toolbar {
        background: #fff;
        border: 1px solid #e5e8f1;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .order-toolbar-actions {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
    }

    .order-btn {
        border: 0;
        border-radius: 9px;
        padding: 10px 16px;
        font-weight: 750;
        text-decoration: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .order-btn-primary {
        background: #696cff;
        color: white;
    }

    .order-btn-success {
        background: #32b768;
        color: white;
    }

    .order-btn-dark {
        background: #3b4359;
        color: white;
    }

    .order-btn-danger {
        background: #e85d68;
        color: white;
    }

    .order-btn-light {
        background: #eef0f5;
        color: #4c566d;
    }

    .order-document {
        background: #fff;
        padding: 45px 48px;
        border-radius: 16px;
        border: 1px solid #e5e8f1;
        box-shadow: 0 12px 35px rgba(28, 39, 76, .07);
    }

    .order-head {
        display: flex;
        justify-content: space-between;
        gap: 30px;
        padding-bottom: 28px;
        border-bottom: 3px solid #696cff;
    }

    .order-company {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .order-company-logo {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        border: 1px solid #e0e3ec;
        object-fit: contain;
        background: white;
    }

    .order-company h2 {
        margin: 0;
        font-size: 27px;
        font-weight: 900;
        color: #6f42ed;
    }

    .order-company p {
        margin: 3px 0 0;
        color: #69738b;
    }

    .order-title-box {
        text-align: right;
    }

    .order-title-box h1 {
        margin: 0;
        color: #2f3850;
        font-size: 30px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .order-number {
        margin-top: 7px;
        color: #696cff;
        font-size: 18px;
        font-weight: 900;
    }

    .order-status {
        display: inline-block;
        margin-top: 10px;
        padding: 6px 12px;
        background: #eef0ff;
        color: #595cd9;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .order-info-section {
        margin-top: 30px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    .order-info-box {
        background: #f8f9fc;
        border: 1px solid #eaedf4;
        border-radius: 12px;
        padding: 20px;
    }

    .order-info-title {
        color: #8a94aa;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: .8px;
        font-weight: 900;
        margin-bottom: 13px;
    }

    .order-info-name {
        font-size: 18px;
        color: #30384f;
        font-weight: 900;
        margin-bottom: 6px;
    }

    .order-info-line {
        color: #667087;
        margin: 4px 0;
    }

    .order-meta {
        margin-top: 25px;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        border: 1px solid #e5e8f1;
        border-radius: 12px;
        overflow: hidden;
    }

    .order-meta-item {
        padding: 15px;
        border-right: 1px solid #e5e8f1;
    }

    .order-meta-item:last-child {
        border-right: none;
    }

    .order-meta-label {
        color: #929bb0;
        font-size: 10px;
        text-transform: uppercase;
        font-weight: 900;
        margin-bottom: 6px;
    }

    .order-meta-value {
        color: #384158;
        font-weight: 800;
    }

    .order-table {
        margin-top: 30px;
        width: 100%;
        border-collapse: collapse;
    }

    .order-table th {
        background: #363f58;
        color: #fff;
        padding: 13px 12px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .4px;
        text-align: left;
    }

    .order-table td {
        border-bottom: 1px solid #e9ecf2;
        padding: 15px 12px;
        color: #465067;
    }

    .order-table .text-right {
        text-align: right;
    }

    .order-table .text-center {
        text-align: center;
    }

    .order-summary-wrapper {
        display: flex;
        justify-content: flex-end;
        margin-top: 25px;
    }

    .order-summary {
        width: 390px;
    }

    .order-summary-row {
        display: flex;
        justify-content: space-between;
        padding: 9px 3px;
        color: #5e687e;
    }

    .order-summary-total {
        margin-top: 8px;
        padding: 14px;
        background: #696cff;
        color: white;
        border-radius: 8px;
        font-size: 18px;
        font-weight: 900;
    }

    .order-conditions {
        margin-top: 35px;
        border-top: 1px solid #e6e9f0;
        padding-top: 25px;
    }

    .order-conditions h4 {
        color: #323b53;
        margin: 0 0 12px;
    }

    .order-condition-line {
        margin: 7px 0;
        color: #657087;
    }

    .order-signatures {
        margin-top: 60px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 80px;
    }

    .order-signature {
        text-align: center;
    }

    .order-signature-line {
        border-top: 1px solid #333;
        margin: 55px auto 8px;
        max-width: 220px;
    }

    .order-signature strong {
        color: #343d54;
    }

    .order-stamp {
        margin-top: 50px;
        text-align: center;
        font-weight: 800;
        color: #3e475e;
    }

    .order-footer {
        margin-top: 45px;
        border-top: 1px solid #e6e9f0;
        padding-top: 15px;
        text-align: center;
        color: #929aab;
        font-size: 11px;
    }

    .order-alert {
        padding: 13px 17px;
        border-radius: 9px;
        margin-bottom: 15px;
        font-weight: 700;
    }

    .order-alert-success {
        background: #eaf9ef;
        color: #27834a;
    }

    .order-alert-error {
        background: #fff0f1;
        color: #bd3f4a;
    }

   /* ============================================================
   IMPRESSION PROFESSIONNELLE A4 — UNE SEULE PAGE
   ============================================================ */

@media print {

    @page {
        size: A4 portrait;
        margin: 5mm 7mm;
    }

    html,
    body {
        width: 100% !important;
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        font-size: 10px !important;
    }

    /* --------------------------------------------------------
       ÉLÉMENTS DE L'APPLICATION À MASQUER
       -------------------------------------------------------- */

    .layout-menu,
    .layout-navbar,
    .order-toolbar,
    footer,
    .content-footer,
    .buy-now {
        display: none !important;
    }

    /* --------------------------------------------------------
       SUPPRIMER LES MARGES DU LAYOUT
       -------------------------------------------------------- */

    .layout-wrapper,
    .layout-container,
    .layout-page,
    .content-wrapper,
    .container-xxl,
    .container-fluid,
    .order-page,
    .order-wrapper {
        width: 100% !important;
        max-width: none !important;
        min-width: 0 !important;

        margin: 0 !important;
        padding: 0 !important;

        background: #ffffff !important;
    }

    /* --------------------------------------------------------
       DOCUMENT
       -------------------------------------------------------- */

    .order-document {
        width: 100% !important;
        max-width: none !important;

        margin: 0 !important;
        padding: 5mm 7mm !important;

        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;

        background: #ffffff !important;

        box-sizing: border-box !important;
    }

    /* --------------------------------------------------------
       ENTÊTE
       -------------------------------------------------------- */

    .order-head {
        display: flex !important;
        flex-direction: row !important;

        justify-content: space-between !important;
        align-items: center !important;

        gap: 15px !important;

        padding-bottom: 10px !important;
        margin-bottom: 0 !important;

        border-bottom: 2px solid #696cff !important;

        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .order-company {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
    }

    .order-company-logo {
        display: block !important;

        width: 48px !important;
        height: 48px !important;

        min-width: 48px !important;

        padding: 2px !important;

        border-radius: 50% !important;

        object-fit: contain !important;

        background: #ffffff !important;

        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .order-company h2 {
        margin: 0 !important;

        font-size: 20px !important;
        line-height: 1.1 !important;

        color: #6f42ed !important;
    }

    .order-company p {
        margin: 2px 0 0 !important;

        font-size: 10px !important;
        line-height: 1.1 !important;
    }

    .order-title-box {
        text-align: right !important;
    }

    .order-title-box h1 {
        margin: 0 !important;

        font-size: 21px !important;
        line-height: 1.05 !important;
    }

    .order-number {
        margin-top: 4px !important;

        font-size: 13px !important;
        line-height: 1.1 !important;
    }

    .order-status {
        margin-top: 5px !important;

        padding: 3px 8px !important;

        font-size: 8px !important;
        line-height: 1.1 !important;

        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* --------------------------------------------------------
       FOURNISSEUR + LIVRAISON
       -------------------------------------------------------- */

    .order-info-section {
        display: grid !important;

        grid-template-columns: 1fr 1fr !important;

        gap: 10px !important;

        margin-top: 12px !important;

        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }


    /*
    |--------------------------------------------------------
    | VÉHICULE + CLIENT / PROPRIÉTAIRE À L'IMPRESSION
    |--------------------------------------------------------
    */

    .order-vehicle-customer-section {
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
        margin-top: 10px !important;
    }

    .order-vehicle-customer-row {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 10px !important;

        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .order-info-box {
        padding: 9px 11px !important;

        border-radius: 7px !important;

        background: #f8f9fc !important;

        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .order-info-title {
        margin-bottom: 5px !important;

        font-size: 7px !important;
        line-height: 1.1 !important;
    }

    .order-info-name {
        margin-bottom: 3px !important;

        font-size: 12px !important;
        line-height: 1.15 !important;
    }

    .order-info-line {
        margin: 2px 0 !important;

        font-size: 9px !important;
        line-height: 1.2 !important;
    }

    /* --------------------------------------------------------
       MÉTADONNÉES
       -------------------------------------------------------- */

    .order-meta {
        display: grid !important;

        grid-template-columns: repeat(4, 1fr) !important;

        margin-top: 10px !important;

        border-radius: 7px !important;

        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .order-meta-item {
        padding: 7px 8px !important;
    }

    .order-meta-label {
        margin-bottom: 3px !important;

        font-size: 6.5px !important;
        line-height: 1.1 !important;
    }

    .order-meta-value {
        font-size: 9px !important;
        line-height: 1.15 !important;
    }

    /* --------------------------------------------------------
       TABLEAU
       -------------------------------------------------------- */

    .order-table {
        width: 100% !important;

        margin-top: 12px !important;

        border-collapse: collapse !important;

        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .order-table thead {
        display: table-header-group !important;
    }

    .order-table tr {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .order-table th {
        padding: 6px 5px !important;

        font-size: 7px !important;
        line-height: 1.1 !important;

        background: #363f58 !important;
        color: #ffffff !important;

        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .order-table td {
        padding: 7px 5px !important;

        font-size: 8.5px !important;
        line-height: 1.15 !important;
    }

    /* --------------------------------------------------------
       TOTAUX
       -------------------------------------------------------- */

    .order-summary-wrapper {
        display: flex !important;

        justify-content: flex-end !important;

        margin-top: 8px !important;

        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .order-summary {
        width: 310px !important;
    }

    .order-summary-row {
        padding: 3px 2px !important;

        font-size: 8.5px !important;
        line-height: 1.15 !important;
    }

    .order-summary-total {
        margin-top: 3px !important;

        padding: 7px 9px !important;

        border-radius: 5px !important;

        font-size: 11px !important;

        background: #696cff !important;
        color: #ffffff !important;

        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* --------------------------------------------------------
       CONDITIONS
       -------------------------------------------------------- */

    .order-conditions {
        margin-top: 10px !important;

        padding-top: 7px !important;

        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .order-conditions h4 {
        margin: 0 0 5px !important;

        font-size: 11px !important;
        line-height: 1.1 !important;
    }

    .order-condition-line {
        margin: 2px 0 !important;

        font-size: 8.5px !important;
        line-height: 1.2 !important;
    }

    /* --------------------------------------------------------
       SIGNATURES
       -------------------------------------------------------- */

    .order-signatures {
        display: grid !important;

        grid-template-columns: 1fr 1fr !important;

        gap: 60px !important;

        margin-top: 13px !important;

        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .order-signature {
        font-size: 8.5px !important;

        text-align: center !important;
    }

    .order-signature strong {
        font-size: 9px !important;
    }

    .order-signature-line {
        max-width: 170px !important;

        margin: 20px auto 4px !important;
    }

    /* --------------------------------------------------------
       CACHET
       -------------------------------------------------------- */

    .order-stamp {
        margin-top: 10px !important;

        font-size: 8.5px !important;

        text-align: center !important;
    }

    /* --------------------------------------------------------
       PIED DE PAGE
       -------------------------------------------------------- */

    .order-footer {
        margin-top: 10px !important;

        padding-top: 6px !important;

        font-size: 6.5px !important;
        line-height: 1.1 !important;

        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    /* --------------------------------------------------------
       ÉVITER LES COUPURES
       -------------------------------------------------------- */

    .order-head,
    .order-info-section,
    .order-meta,
    .order-summary-wrapper,
    .order-conditions,
    .order-signatures,
    .order-stamp,
    .order-footer {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
}


    /*
    |--------------------------------------------------------------------------
    | VÉHICULE / CLIENT - PROPRIÉTAIRE
    |--------------------------------------------------------------------------
    */

    .order-vehicle-customer-section {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 14px;
    }

    .order-vehicle-customer-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    @media(max-width: 800px) {
        .order-head,
        .order-info-section {
            grid-template-columns: 1fr;
            display: grid;
        }

        .order-title-box {
            text-align: left;
        }

        .order-meta {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>


<div class="order-page">

    <div class="order-wrapper">

        @if(session('success'))

            <div class="order-alert order-alert-success">
                {{ session('success') }}
            </div>

        @endif


        @if(session('error'))

            <div class="order-alert order-alert-error">
                {{ session('error') }}
            </div>

        @endif


        <div class="order-toolbar">

            <a
                href="{{ route('supplier-orders.index') }}"
                class="order-btn order-btn-light"
            >
                ← Liste des BC
            </a>


            <div class="order-toolbar-actions">

                <button
                    type="button"
                    onclick="window.print()"
                    class="order-btn order-btn-dark"
                >
                    Imprimer
                </button>


               {{-- ============================================================
    APPROBATION DU BON DE COMMANDE

    RÈGLE :
    - Le BC doit être au statut BROUILLON.
    - Seul un ADMINISTRATEUR peut l'approuver.
    - Le chef magasinier peut consulter le BC mais ne peut
      jamais afficher/utiliser ce bouton.
============================================================ --}}

@if(
    auth()->check()
    &&
    auth()->user()->role === 'admin'
    &&
    $supplierOrder->status === \App\Models\SupplierOrder::STATUS_DRAFT
)

    <form
        method="POST"
        action="{{
            route(
                'supplier-orders.approve',
                $supplierOrder
            )
        }}"
        class="m-0"
        id="approveSupplierOrderForm"
    >

        @csrf

        @method('PATCH')

                {{--
        |--------------------------------------------------------------------------
        | SIGNATURE ÉLECTRONIQUE DE L'APPROBATEUR
        |--------------------------------------------------------------------------
        |
        | Le JavaScript placera ici le PNG Base64 généré depuis le canvas.
        |
        --}}

        <input
            type="hidden"
            name="signature"
            id="approvalSignatureData"
            value=""
        >

        <button
            type="submit"
            class="order-btn order-btn-success"
            title="Approuver le bon de commande"
        >

            <i class="bx bx-check"></i>

            Approuver

        </button>

    </form>

@endif


{{-- ============================================================
    REJET DU BON DE COMMANDE

    RÈGLE :
    - Le BC doit être au statut BROUILLON.
    - Seul un ADMINISTRATEUR peut le rejeter.
    - Le motif du rejet sera obligatoire.
============================================================ --}}

@if(
    auth()->check()
    &&
    auth()->user()->role === 'admin'
    &&
    $supplierOrder->status === \App\Models\SupplierOrder::STATUS_DRAFT
)

    <button
        type="button"
        class="order-btn order-btn-danger"
        id="openRejectSupplierOrderModal"
        title="Rejeter le bon de commande"
    >
        <i class="bx bx-x"></i>

        Rejeter
    </button>

@endif


                @if(
                    in_array(
                        $supplierOrder->status,
                        [
                            \App\Models\SupplierOrder::STATUS_DRAFT,
                            \App\Models\SupplierOrder::STATUS_APPROVED,
                        ],
                        true
                    )
                )

                    <form
                        method="POST"
                        action="{{ route(
                            'supplier-orders.mark-as-sent',
                            $supplierOrder
                        ) }}"
                    >
                        @csrf
                        @method('PATCH')

                        <!--button
                            type="submit"
                            class="order-btn order-btn-primary"
                        >
                            Marquer envoyé
                        </button-->
                    </form>

                @endif


                @if(
                    !in_array(
                        $supplierOrder->status,
                        [
                            \App\Models\SupplierOrder::STATUS_CANCELLED,
                            \App\Models\SupplierOrder::STATUS_PARTIAL_RECEIVED,
                            \App\Models\SupplierOrder::STATUS_RECEIVED,
                        ],
                        true
                    )
                )

                    <form
                        method="POST"
                        action="{{ route(
                            'supplier-orders.cancel',
                            $supplierOrder
                        ) }}"
                        onsubmit="
                            return confirm(
                                'Annuler ce bon de commande ?'
                            );
                        "
                    >
                        @csrf
                        @method('PATCH')


                        <button
                            type="submit"
                            class="order-btn order-btn-danger"
                        >
                            Annuler
                        </button>

                    </form>

                @endif

            </div>

        </div>


        <div class="order-document">

            {{-- ENTÊTE --}}

            <div class="order-head">

              <div class="order-company">

                    <img
                        src="{{ asset('assets/img/logo/stcd.jpg') }}"
                        alt="Logo STCD Motors"
                        class="order-company-logo"
                    >

                    <div>

                        <h2>
                            STCD Motors
                        </h2>

                        <p>
                            Djibouti
                        </p>

                    </div>

                </div>


                <div class="order-title-box">

                    <h1>
                        Bon de commande
                    </h1>

                    <div class="order-number">
                        {{ $supplierOrder->order_number }}
                    </div>

                    <div class="order-status">
                        {{ $statusLabel }}
                    </div>

                </div>

            </div>


            {{-- FOURNISSEUR / LIVRAISON --}}

            <div class="order-info-section">

                <div class="order-info-box">

                    <div class="order-info-title">
                        Fournisseur
                    </div>

                    <div class="order-info-name">
                        {{ $supplierOrder->supplier?->name ?? '-' }}
                    </div>

                    @if($supplierOrder->supplier?->code)
                        <div class="order-info-line">
                            Code :
                            {{ $supplierOrder->supplier->code }}
                        </div>
                    @endif

                    @if($supplierOrder->supplier?->address)
                        <div class="order-info-line">
                            {{ $supplierOrder->supplier->address }}
                        </div>
                    @endif

                    @if($supplierOrder->supplier?->phone)
                        <div class="order-info-line">
                            Tél :
                            {{ $supplierOrder->supplier->phone }}
                        </div>
                    @endif

                    @if($supplierOrder->supplier?->email)
                        <div class="order-info-line">
                            {{ $supplierOrder->supplier->email }}
                        </div>
                    @endif

                </div>


                <div class="order-info-box">

                    <div class="order-info-title">
                        Livraison
                    </div>

                    <div class="order-info-name">
                        {{ $supplierOrder->depot?->name ?? '-' }}
                    </div>

                    @if($supplierOrder->depot?->code)
                        <div class="order-info-line">
                            Code dépôt :
                            {{ $supplierOrder->depot->code }}
                        </div>
                    @endif

                    @if($supplierOrder->depot?->address)
                        <div class="order-info-line">
                            {{ $supplierOrder->depot->address }}
                        </div>
                    @endif

                </div>

            </div>


            {{-- MÉTADONNÉES --}}


            {{--
            |--------------------------------------------------------------------------
            | VÉHICULES ET CLIENTS / PROPRIÉTAIRES
            |--------------------------------------------------------------------------
            |
            | Un bon de commande peut contenir plusieurs pièces.
            |
            | Plusieurs pièces peuvent appartenir au même véhicule.
            | Nous regroupons donc les véhicules par ID afin d'éviter
            | d'afficher plusieurs fois le même véhicule.
            |
            --}}

            @php

                $orderVehicles = $supplierOrder
                    ->items
                    ->map(
                        fn ($item) =>
                            $item
                                ->vehiclePartRequest
                                ?->vehicle
                    )
                    ->filter()
                    ->unique('id')
                    ->values();

            @endphp


            @if($orderVehicles->isNotEmpty())

                <div class="order-vehicle-customer-section">

                    @foreach($orderVehicles as $orderVehicle)

                        <div class="order-vehicle-customer-row">

                            {{-- =====================================================
                                VÉHICULE
                            ====================================================== --}}

                            <div class="order-info-box">

                                <div class="order-info-title">
                                    Véhicule
                                </div>

                                <div class="order-info-name">

                                    {{
                                        $orderVehicle->plate_number
                                        ?: 'Immatriculation non renseignée'
                                    }}

                                </div>


                                <div class="order-info-line">

                                    <strong>Marque / Modèle :</strong>

                                    {{
                                        trim(
                                            ($orderVehicle->brand ?? '')
                                            . ' '
                                            . ($orderVehicle->model ?? '')
                                        )
                                        ?: '-'
                                    }}

                                </div>


                                <div class="order-info-line">

                                    <strong>VIN / Châssis :</strong>

                                    {{
                                        $orderVehicle->vin
                                        ?: '-'
                                    }}

                                </div>


                                <div class="order-info-line">

                                    <strong>Année :</strong>

                                    {{
                                        $orderVehicle->year
                                        ?: '-'
                                    }}

                                </div>


                                <div class="order-info-line">

                                    <strong>Couleur :</strong>

                                    {{
                                        $orderVehicle->color
                                        ?: '-'
                                    }}

                                </div>

                            </div>


                            {{-- =====================================================
                                CLIENT / PROPRIÉTAIRE
                            ====================================================== --}}

                            <div class="order-info-box">

                                <div class="order-info-title">
                                    Client / Propriétaire
                                </div>


                                @if($orderVehicle->customer)

                                    <div class="order-info-name">

                                        {{
                                            $orderVehicle
                                                ->customer
                                                ->name
                                        }}

                                    </div>


                                    <div class="order-info-line">

                                        <strong>Code client :</strong>

                                        {{
                                            $orderVehicle
                                                ->customer
                                                ->code
                                            ?: '-'
                                        }}

                                    </div>


                                    <div class="order-info-line">

                                        <strong>Téléphone :</strong>

                                        {{
                                            $orderVehicle
                                                ->customer
                                                ->phone
                                            ?: '-'
                                        }}

                                    </div>


                                    <div class="order-info-line">

                                        <strong>Email :</strong>

                                        {{
                                            $orderVehicle
                                                ->customer
                                                ->email
                                            ?: '-'
                                        }}

                                    </div>

                                @else

                                    <div class="order-info-name">
                                        Client non renseigné
                                    </div>

                                    <div class="order-info-line">
                                        Aucun client ou propriétaire
                                        n'est associé à ce véhicule.
                                    </div>

                                @endif

                            </div>

                        </div>

                    @endforeach

                </div>

            @endif


            <div class="order-meta">

                <div class="order-meta-item">

                    <div class="order-meta-label">
                        Date commande
                    </div>

                    <div class="order-meta-value">
                        {{ $supplierOrder->order_date
                            ?->format('d/m/Y') }}
                    </div>

                </div>


                <div class="order-meta-item">

                    <div class="order-meta-label">
                        Livraison prévue
                    </div>

                    <div class="order-meta-value">
                        {{ $supplierOrder
                            ->expected_delivery_date
                            ?->format('d/m/Y')
                            ?? '-' }}
                    </div>

                </div>


                <div class="order-meta-item">

                    <div class="order-meta-label">
                        Devise
                    </div>

                    <div class="order-meta-value">
                        {{ $supplierOrder->currency }}
                    </div>

                </div>


                <div class="order-meta-item">

                    <div class="order-meta-label">
                        Préparé par
                    </div>

                    <div class="order-meta-value">
                        {{ $supplierOrder->creator?->name ?? '-' }}
                    </div>

                </div>

            </div>


            {{-- LIGNES --}}

            <table class="order-table">

                <thead>

                    <tr>
                        <th>#</th>
                        <th>Référence</th>
                        <th>Désignation</th>
                        <th>Unité</th>
                        <th class="text-center">
                            Qté
                        </th>
                        <th class="text-right">
                            Prix unitaire
                        </th>
                        <th class="text-right">
                            Total
                        </th>
                    </tr>

                </thead>


                <tbody>

                    @forelse(
                        $supplierOrder->items
                        as $item
                    )

                        <tr>

                            <td>
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                <strong>
                                    {{ $item->reference ?? '-' }}
                                </strong>
                            </td>

                            <td>
                                {{ $item->description }}
                            </td>

                            <td>
                                {{ $item->unit ?? '-' }}
                            </td>

                            <td class="text-center">
                                {{ number_format(
                                    (float) $item->quantity_ordered,
                                    2,
                                    ',',
                                    ' '
                                ) }}
                            </td>

                            <td class="text-right">
                                {{ number_format(
                                    (float) $item->unit_price,
                                    2,
                                    ',',
                                    ' '
                                ) }}
                            </td>

                            <td class="text-right">
                                <strong>
                                    {{ number_format(
                                        (float) $item->line_total,
                                        2,
                                        ',',
                                        ' '
                                    ) }}
                                </strong>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="7"
                                style="text-align:center;"
                            >
                                Aucune ligne.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>


            {{-- TOTAUX --}}

            <div class="order-summary-wrapper">

                <div class="order-summary">

                    <div class="order-summary-row">
                        <span>Sous-total</span>

                        <strong>
                            {{ number_format(
                                (float) $supplierOrder->subtotal,
                                2,
                                ',',
                                ' '
                            ) }}

                            {{ $supplierOrder->currency }}
                        </strong>
                    </div>


                    <div class="order-summary-row">
                        <span>
                            Remise
                            <small>
                                ({{
                                    number_format(
                                        (float) $supplierOrder->discount_rate,
                                        2,
                                        ',',
                                        ' '
                                    )
                                }} %)
                            </small>
                        </span>

                        <strong>
                            {{ number_format(
                                (float) $supplierOrder->discount,
                                2,
                                ',',
                                ' '
                            ) }}

                            {{ $supplierOrder->currency }}
                        </strong>
                    </div>


                    <div class="order-summary-row">
                        <span>Transport</span>

                        <strong>
                            {{ number_format(
                                (float) $supplierOrder->shipping_cost,
                                2,
                                ',',
                                ' '
                            ) }}

                            {{ $supplierOrder->currency }}
                        </strong>
                    </div>


                    <div class="order-summary-row">
                        <span>
                            Taxe / TVA
                            <small>
                                ({{
                                    number_format(
                                        (float) $supplierOrder->tax_rate,
                                        2,
                                        ',',
                                        ' '
                                    )
                                }} %)
                            </small>
                        </span>

                        <strong>
                            {{ number_format(
                                (float) $supplierOrder->tax_amount,
                                2,
                                ',',
                                ' '
                            ) }}

                            {{ $supplierOrder->currency }}
                        </strong>
                    </div>


                    <div
                        class="
                            order-summary-row
                            order-summary-total
                        "
                    >
                        <span>TOTAL</span>

                        <span>
                            {{ number_format(
                                (float) $supplierOrder->total,
                                2,
                                ',',
                                ' '
                            ) }}

                            {{ $supplierOrder->currency }}
                        </span>
                    </div>

                </div>

            </div>


            {{-- CONDITIONS --}}

            <div class="order-conditions">

                <h4>
                    Conditions
                </h4>

                <div class="order-condition-line">

                    <strong>
                        Paiement :
                    </strong>

                    {{ $supplierOrder->payment_terms
                        ?: 'Non spécifié' }}

                </div>


                <div class="order-condition-line">

                    <strong>
                        Livraison :
                    </strong>

                    {{ $supplierOrder->delivery_terms
                        ?: 'Non spécifié' }}

                </div>


                @if($supplierOrder->notes)

                    <div class="order-condition-line">

                        <strong>
                            Notes :
                        </strong>

                        {{ $supplierOrder->notes }}

                    </div>

                @endif

            </div>


            {{-- SIGNATURES --}}

            <div class="order-signatures">

             <div class="order-signature">

    <strong>
        Approuvé par
    </strong>


    {{--
    |--------------------------------------------------------------------------
    | SIGNATURE ÉLECTRONIQUE DE L'APPROBATEUR
    |--------------------------------------------------------------------------
    --}}

    @if($supplierOrder->approvedSignature)

        <div class="order-electronic-signature">

            <img
                src="{{
                    route(
                        'supplier-orders.signature',
                        [
                            'supplierOrder' => $supplierOrder,
                            'type' => \App\Models\SupplierOrderSignature::TYPE_APPROVED,
                        ]
                    )
                }}"
                alt="Signature électronique de l'approbateur"
                class="order-signature-image"
            >

        </div>

    @else

        <div class="order-signature-empty"></div>

    @endif


    <div class="order-signature-line"></div>


    <div class="order-signature-name">

        {{ $supplierOrder->approver?->name
            ?? 'Nom / Signature' }}

    </div>


    @if(
        $supplierOrder->approvedSignature
        &&
        $supplierOrder->approvedSignature->signed_at
    )

        <div class="order-signature-date">

            Signé le

            {{
                $supplierOrder
                    ->approvedSignature
                    ->signed_at
                    ->format('d/m/Y à H:i')
            }}

        </div>

    @endif

</div>




            </div>


            <div class="order-stamp">
                Cachet STCD Motors
            </div>


            <div class="order-footer">

                Bon de commande
                {{ $supplierOrder->order_number }}

                —

                Document généré par le système
                de gestion STCD Motors

            </div>

        </div>

    </div>

</div>


{{-- ================================================================
     MODALE DE CONFIRMATION - APPROBATION DU BON DE COMMANDE
================================================================ --}}

@if(
    auth()->check()
    &&
    auth()->user()->role === 'admin'
    &&
    $supplierOrder->status === \App\Models\SupplierOrder::STATUS_DRAFT
)

<div
    id="approveOrderModal"
    class="bc-approve-modal"
    aria-hidden="true"
>
    <div
        class="bc-approve-backdrop"
        data-close-approve-modal
    ></div>

    <div
        class="bc-approve-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="approveOrderModalTitle"
    >

        <button
            type="button"
            class="bc-approve-close"
            data-close-approve-modal
            aria-label="Fermer"
        >
            <i class="bx bx-x"></i>
        </button>


        <div class="bc-approve-icon">
            <i class="bx bx-check"></i>
        </div>


        <div class="bc-approve-content">

            <h3 id="approveOrderModalTitle">
                Approuver le bon de commande ?
            </h3>

            <div class="bc-approve-number">
                {{ $supplierOrder->order_number }}
            </div>

            <p>
                Vous êtes sur le point d'approuver officiellement
                ce bon de commande.
            </p>

            <div class="bc-approve-warning">

                <i class="bx bx-info-circle"></i>

                <span>
                    Après approbation, le document sera considéré
                    comme validé par STCD Motors.
                </span>

            </div>

                        {{--
            |--------------------------------------------------------------------------
            | ZONE DE SIGNATURE ÉLECTRONIQUE
            |--------------------------------------------------------------------------
            --}}

            <div class="bc-signature-section">

                <div class="bc-signature-header">

                    <div>

                        <strong>
                            Signature de l'approbateur
                        </strong>

                        <small>
                            Signez avec la souris ou avec votre doigt.
                        </small>

                    </div>

                    <button
                        type="button"
                        id="clearApprovalSignature"
                        class="bc-signature-clear"
                    >
                        <i class="bx bx-eraser"></i>
                        Effacer
                    </button>

                </div>


                <div
                    class="bc-signature-pad"
                    id="approvalSignaturePad"
                >

                    <canvas
                        id="approvalSignatureCanvas"
                        aria-label="Zone de signature électronique"
                    ></canvas>

                    <div
                        id="approvalSignaturePlaceholder"
                        class="bc-signature-placeholder"
                    >
                        <i class="bx bx-pen"></i>

                        <span>
                            Signez ici
                        </span>
                    </div>

                </div>


                <div
                    id="approvalSignatureError"
                    class="bc-signature-error"
                    role="alert"
                >
                    Veuillez apposer votre signature avant d'approuver.
                </div>


                <div class="bc-signature-user">

                    <i class="bx bx-user-check"></i>

                    <span>
                        Signataire :
                        <strong>
                            {{ auth()->user()->name }}
                        </strong>
                    </span>

                </div>

            </div>

        </div>


        <div class="bc-approve-actions">

            <button
                type="button"
                class="bc-approve-btn bc-approve-btn-cancel"
                data-close-approve-modal
            >
                Annuler
            </button>

            <button
                type="button"
                id="confirmApproveSupplierOrder"
                class="bc-approve-btn bc-approve-btn-confirm"
            >
                <i class="bx bx-pen"></i>

                Signer et approuver
            </button>

        </div>

    </div>
</div>



{{-- ================================================================
    MODALE DE REJET DU BON DE COMMANDE
================================================================ --}}

@if(
    auth()->check()
    &&
    auth()->user()->role === 'admin'
    &&
    $supplierOrder->status === \App\Models\SupplierOrder::STATUS_DRAFT
)

<div
    id="rejectSupplierOrderModal"
    class="bc-reject-modal"
    aria-hidden="true"
>

    <div
        class="bc-reject-backdrop"
        data-close-reject-modal
    ></div>


    <div
        class="bc-reject-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="rejectSupplierOrderTitle"
    >

        {{-- BOUTON FERMER --}}

        <button
            type="button"
            class="bc-reject-close"
            data-close-reject-modal
            aria-label="Fermer"
        >
            <i class="bx bx-x"></i>
        </button>


        {{-- ICÔNE --}}

        <div class="bc-reject-icon">
            <i class="bx bx-x-circle"></i>
        </div>


        {{-- TITRE --}}

        <h3 id="rejectSupplierOrderTitle">
            Rejeter le bon de commande
        </h3>


        <p class="bc-reject-subtitle">
            Vous êtes sur le point de rejeter
            <strong>
                {{ $supplierOrder->order_number }}
            </strong>.
        </p>


        {{-- FORMULAIRE --}}

        <form
            method="POST"
            action="{{
                route(
                    'supplier-orders.reject',
                    $supplierOrder
                )
            }}"
            id="rejectSupplierOrderForm"
        >

            @csrf

            @method('PATCH')


            <div class="bc-reject-field">

                <label for="rejectionReason">

                    Motif du rejet

                    <span class="bc-reject-required">
                        *
                    </span>

                </label>


                <textarea
                    name="rejection_reason"
                    id="rejectionReason"
                    rows="5"
                    maxlength="2000"
                    required
                    placeholder="Indiquez clairement la raison du rejet..."
                >{{ old('rejection_reason') }}</textarea>


                <div class="bc-reject-counter">

                    <span id="rejectionReasonCounter">
                        0
                    </span>

                    / 2000 caractères

                </div>


                <div
                    id="rejectionReasonError"
                    class="bc-reject-error"
                    role="alert"
                >
                    Veuillez indiquer un motif d'au moins 3 caractères.
                </div>


                @error('rejection_reason')

                    <div class="bc-reject-server-error">
                        {{ $message }}
                    </div>

                @enderror

            </div>


            {{-- INFORMATION DE TRAÇABILITÉ --}}

            <div class="bc-reject-info">

                <i class="bx bx-info-circle"></i>

                <div>

                    <strong>
                        Cette décision sera enregistrée.
                    </strong>

                    <span>
                        Le signataire administratif, la date,
                        l'heure et le motif seront conservés
                        dans l'historique du bon de commande.
                    </span>

                </div>

            </div>


            {{-- ACTIONS --}}

            <div class="bc-reject-actions">

                <button
                    type="button"
                    class="bc-reject-btn bc-reject-btn-cancel"
                    data-close-reject-modal
                >
                    Retour
                </button>


                <button
                    type="submit"
                    class="bc-reject-btn bc-reject-btn-confirm"
                    id="confirmRejectSupplierOrder"
                >
                    <i class="bx bx-x-circle"></i>

                    Confirmer le rejet
                </button>

            </div>

        </form>

    </div>

</div>

@endif


<style>

    /* ============================================================
       MODALE APPROBATION BC
    ============================================================ */

    .bc-approve-modal {
        position: fixed;
        inset: 0;

        display: flex;
        align-items: center;
        justify-content: center;

        padding: 20px;

        visibility: hidden;
        opacity: 0;

        z-index: 99999;

        transition:
            opacity .18s ease,
            visibility .18s ease;
    }


    .bc-approve-modal.is-open {
        visibility: visible;
        opacity: 1;
    }


    .bc-approve-backdrop {
        position: absolute;
        inset: 0;

        background: rgba(15, 23, 42, .58);

        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
    }


    .bc-approve-dialog {
        position: relative;

        width: 100%;
        max-width: 560px;

        background: #ffffff;

        border-radius: 18px;

        box-shadow:
            0 25px 60px rgba(15, 23, 42, .22);

        padding: 32px;

        text-align: center;

        transform: translateY(14px) scale(.97);

        transition: transform .20s ease;

        z-index: 1;
    }


    .bc-approve-modal.is-open
    .bc-approve-dialog {
        transform: translateY(0) scale(1);
    }


    .bc-approve-close {
        position: absolute;

        top: 16px;
        right: 16px;

        width: 34px;
        height: 34px;

        display: flex;
        align-items: center;
        justify-content: center;

        padding: 0;

        border: 0;
        border-radius: 9px;

        background: #f4f6f8;

        color: #64748b;

        font-size: 21px;

        cursor: pointer;

        transition: .18s ease;
    }


    .bc-approve-close:hover {
        background: #e9edf2;
        color: #334155;
    }


    .bc-approve-icon {
        width: 70px;
        height: 70px;

        margin: 0 auto 20px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background: #eaf8ef;

        color: #28a745;

        font-size: 38px;
    }


    .bc-approve-content h3 {
        margin: 0 0 9px;

        color: #27324a;

        font-size: 21px;
        font-weight: 700;
    }


    .bc-approve-number {
        display: inline-flex;

        margin-bottom: 18px;

        padding: 6px 12px;

        border-radius: 8px;

        background: #f0edff;

        color: #6954e8;

        font-size: 12px;
        font-weight: 800;

        letter-spacing: .4px;
    }


    .bc-approve-content p {
        max-width: 360px;

        margin: 0 auto 20px;

        color: #718096;

        font-size: 14px;
        line-height: 1.65;
    }


    .bc-approve-warning {
        display: flex;
        align-items: flex-start;

        gap: 10px;

        padding: 13px 15px;

        border: 1px solid #dcefe2;
        border-radius: 10px;

        background: #f6fcf8;

        color: #56705e;

        text-align: left;

        font-size: 12px;
        line-height: 1.5;
    }


    .bc-approve-warning i {
        flex-shrink: 0;

        margin-top: 1px;

        color: #36a95d;

        font-size: 18px;
    }


    .bc-approve-actions {
        display: flex;
        justify-content: center;

        gap: 10px;

        margin-top: 24px;
    }


    .bc-approve-btn {
        min-width: 130px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 6px;

        padding: 10px 18px;

        border: 0;
        border-radius: 9px;

        font-size: 13px;
        font-weight: 700;

        cursor: pointer;

        transition:
            transform .15s ease,
            box-shadow .15s ease,
            background .15s ease;
    }


    .bc-approve-btn:hover {
        transform: translateY(-1px);
    }


    .bc-approve-btn-cancel {
        background: #eef1f5;
        color: #596579;
    }


    .bc-approve-btn-cancel:hover {
        background: #e4e8ee;
    }


    .bc-approve-btn-confirm {
        background: #28a745;
        color: #ffffff;

        box-shadow:
            0 6px 14px rgba(40, 167, 69, .22);
    }


    .bc-approve-btn-confirm:hover {
        background: #218838;

        box-shadow:
            0 8px 18px rgba(40, 167, 69, .28);
    }


    .bc-approve-btn-confirm:disabled {
        opacity: .65;
        cursor: not-allowed;
        transform: none;
    }

        /*
    ============================================================
    SIGNATURE ÉLECTRONIQUE
    ============================================================
    */

    .bc-signature-section {
        margin-top: 20px;
        text-align: left;
    }


    .bc-signature-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 10px;
    }


    .bc-signature-header strong {
        display: block;
        color: #27324a;
        font-size: 13px;
        font-weight: 700;
    }


    .bc-signature-header small {
        display: block;
        margin-top: 3px;
        color: #94a3b8;
        font-size: 11px;
    }


    .bc-signature-clear {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;

        padding: 6px 9px;

        border: 1px solid #e2e8f0;
        border-radius: 7px;

        background: #ffffff;
        color: #64748b;

        font-size: 11px;
        font-weight: 600;

        cursor: pointer;

        transition: .18s ease;
    }


    .bc-signature-clear:hover {
        background: #f8fafc;
        color: #334155;
    }


    .bc-signature-pad {
        position: relative;

        width: 100%;
        height: 170px;

        overflow: hidden;

        border: 2px dashed #cbd5e1;
        border-radius: 12px;

        background: #ffffff;

        transition:
            border-color .18s ease,
            box-shadow .18s ease;
    }


    .bc-signature-pad.is-drawing,
    .bc-signature-pad.has-signature {
        border-style: solid;
        border-color: #86d49b;

        box-shadow:
            0 0 0 3px rgba(40, 167, 69, .07);
    }


    .bc-signature-pad.has-error {
        border-color: #dc3545;

        box-shadow:
            0 0 0 3px rgba(220, 53, 69, .07);
    }


    #approvalSignatureCanvas {
        position: absolute;
        inset: 0;

        display: block;

        width: 100%;
        height: 100%;

        cursor: crosshair;

        touch-action: none;
    }


    .bc-signature-placeholder {
        position: absolute;
        inset: 0;

        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;

        gap: 5px;

        color: #b3bdca;

        font-size: 12px;

        pointer-events: none;

        transition: opacity .15s ease;
    }


    .bc-signature-placeholder i {
        font-size: 25px;
    }


    .bc-signature-pad.has-signature
    .bc-signature-placeholder {
        opacity: 0;
    }


    .bc-signature-error {
        display: none;

        margin-top: 7px;

        color: #dc3545;

        font-size: 11px;
        font-weight: 600;
    }


    .bc-signature-error.is-visible {
        display: block;
    }


    .bc-signature-user {
        display: flex;
        align-items: center;
        gap: 6px;

        margin-top: 9px;

        color: #64748b;

        font-size: 11px;
    }


    .bc-signature-user i {
        color: #28a745;
        font-size: 16px;
    }

    body.bc-modal-open {
        overflow: hidden;
    }


    @media (max-width: 575.98px) {

        .bc-approve-dialog {
            max-width: 100%;
            padding: 28px 20px 22px;
        }

        .bc-approve-actions {
            flex-direction: column-reverse;
        }

        .bc-approve-btn {
            width: 100%;
        }

                .bc-signature-pad {
            height: 150px;
        }


        .bc-signature-header {
            align-items: flex-start;
        }
    }

    /*
|--------------------------------------------------------------------------
| SIGNATURE ÉLECTRONIQUE AFFICHÉE SUR LE BC
|--------------------------------------------------------------------------
*/

.order-electronic-signature {
    width: 220px;
    height: 85px;

    margin: 14px auto 4px;

    display: flex;
    align-items: flex-end;
    justify-content: center;

    overflow: hidden;
}


.order-signature-image {
    display: block;

    max-width: 210px;
    max-height: 80px;

    width: auto;
    height: auto;

    object-fit: contain;
}


.order-signature-empty {
    width: 220px;
    height: 85px;

    margin: 14px auto 4px;
}


.order-signature-name {
    margin-top: 8px;

    font-size: 13px;
    font-weight: 500;

    color: #667085;
}


.order-signature-date {
    margin-top: 4px;

    font-size: 10px;

    color: #98a2b3;
}


/*
|--------------------------------------------------------------------------
| IMPRESSION
|--------------------------------------------------------------------------
*/

@media print {

    .order-signature-image {
        max-width: 190px;
        max-height: 70px;
    }

    .order-signature-date {
        font-size: 9px;
    }
}
</style>


<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ÉLÉMENTS
    |--------------------------------------------------------------------------
    */

    const form =
        document.getElementById(
            'approveSupplierOrderForm'
        );

    const modal =
        document.getElementById(
            'approveOrderModal'
        );

    const confirmButton =
        document.getElementById(
            'confirmApproveSupplierOrder'
        );

    const canvas =
        document.getElementById(
            'approvalSignatureCanvas'
        );

    const signaturePad =
        document.getElementById(
            'approvalSignaturePad'
        );

    const signatureInput =
        document.getElementById(
            'approvalSignatureData'
        );

    const clearButton =
        document.getElementById(
            'clearApprovalSignature'
        );

    const errorMessage =
        document.getElementById(
            'approvalSignatureError'
        );


    /*
    |--------------------------------------------------------------------------
    | ARRÊTER SI LA MODALE N'EXISTE PAS
    |--------------------------------------------------------------------------
    */

    if (
        !form
        ||
        !modal
        ||
        !confirmButton
        ||
        !canvas
        ||
        !signaturePad
        ||
        !signatureInput
        ||
        !clearButton
        ||
        !errorMessage
    ) {
        return;
    }


    const context =
        canvas.getContext('2d');


    /*
    |--------------------------------------------------------------------------
    | ÉTAT
    |--------------------------------------------------------------------------
    */

    let drawing = false;

    let hasSignature = false;

    let confirmed = false;

    let lastX = 0;

    let lastY = 0;


    /*
    |--------------------------------------------------------------------------
    | CONFIGURATION DU TRAIT
    |--------------------------------------------------------------------------
    */

    context.lineCap = 'round';

    context.lineJoin = 'round';

    context.strokeStyle = '#111827';

    context.lineWidth = 2.2;


    /*
    |--------------------------------------------------------------------------
    | DIMENSIONNER LE CANVAS
    |--------------------------------------------------------------------------
    |
    | Le canvas doit tenir compte du devicePixelRatio afin que la signature
    | reste nette sur les écrans haute résolution.
    |
    */

    function resizeCanvas() {

        const rectangle =
            signaturePad.getBoundingClientRect();

        const ratio =
            Math.max(
                window.devicePixelRatio || 1,
                1
            );


        /*
        |--------------------------------------------------------------------------
        | SAUVEGARDER LE DESSIN EXISTANT
        |--------------------------------------------------------------------------
        */

        let existingSignature = null;

        if (hasSignature) {

            existingSignature =
                canvas.toDataURL(
                    'image/png'
                );
        }


        canvas.width =
            Math.round(
                rectangle.width * ratio
            );

        canvas.height =
            Math.round(
                rectangle.height * ratio
            );

        canvas.style.width =
            rectangle.width + 'px';

        canvas.style.height =
            rectangle.height + 'px';


        /*
        |--------------------------------------------------------------------------
        | REMETTRE LE CONTEXTE À L'ÉCHELLE CSS
        |--------------------------------------------------------------------------
        */

        context.setTransform(
            ratio,
            0,
            0,
            ratio,
            0,
            0
        );

        context.lineCap = 'round';

        context.lineJoin = 'round';

        context.strokeStyle = '#111827';

        context.lineWidth = 2.2;


        /*
        |--------------------------------------------------------------------------
        | RESTAURER LA SIGNATURE APRÈS REDIMENSIONNEMENT
        |--------------------------------------------------------------------------
        */

        if (existingSignature) {

            const image = new Image();

            image.onload = function () {

                context.drawImage(
                    image,
                    0,
                    0,
                    rectangle.width,
                    rectangle.height
                );
            };

            image.src = existingSignature;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | POSITION DU POINTEUR
    |--------------------------------------------------------------------------
    */

    function getPointerPosition(event) {

        const rectangle =
            canvas.getBoundingClientRect();

        return {

            x:
                event.clientX
                -
                rectangle.left,

            y:
                event.clientY
                -
                rectangle.top,
        };
    }


    /*
    |--------------------------------------------------------------------------
    | COMMENCER LA SIGNATURE
    |--------------------------------------------------------------------------
    */

    function startDrawing(event) {

        if (confirmed) {
            return;
        }

        event.preventDefault();

        drawing = true;

        const position =
            getPointerPosition(
                event
            );

        lastX = position.x;

        lastY = position.y;

        context.beginPath();

        context.moveTo(
            lastX,
            lastY
        );

        /*
        |--------------------------------------------------------------------------
        | PETIT POINT
        |--------------------------------------------------------------------------
        |
        | Cela permet qu'un simple clic/toucher soit également visible.
        |
        */

        context.lineTo(
            lastX + 0.01,
            lastY + 0.01
        );

        context.stroke();

        signaturePad.classList.add(
            'is-drawing'
        );

        signaturePad.classList.remove(
            'has-error'
        );

        errorMessage.classList.remove(
            'is-visible'
        );

        try {

            canvas.setPointerCapture(
                event.pointerId
            );

        } catch (error) {

            /*
            |--------------------------------------------------------------------------
            | Certains navigateurs peuvent refuser setPointerCapture.
            |--------------------------------------------------------------------------
            */
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DESSINER
    |--------------------------------------------------------------------------
    */

    function draw(event) {

        if (!drawing) {
            return;
        }

        event.preventDefault();

        const position =
            getPointerPosition(
                event
            );

        context.lineTo(
            position.x,
            position.y
        );

        context.stroke();

        lastX = position.x;

        lastY = position.y;

        hasSignature = true;

        signaturePad.classList.add(
            'has-signature'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TERMINER LE TRAIT
    |--------------------------------------------------------------------------
    */

    function stopDrawing(event) {

        if (!drawing) {
            return;
        }

        event.preventDefault();

        drawing = false;

        context.closePath();

        signaturePad.classList.remove(
            'is-drawing'
        );

        /*
        |--------------------------------------------------------------------------
        | CONSIDÉRER AUSSI UN SIMPLE POINT COMME UNE INTERACTION
        |--------------------------------------------------------------------------
        */

        hasSignature = true;

        signaturePad.classList.add(
            'has-signature'
        );

        try {

            canvas.releasePointerCapture(
                event.pointerId
            );

        } catch (error) {

            /*
            |--------------------------------------------------------------------------
            | Aucun traitement nécessaire.
            |--------------------------------------------------------------------------
            */
        }
    }


    /*
    |--------------------------------------------------------------------------
    | EFFACER
    |--------------------------------------------------------------------------
    */

    function clearSignature() {

        const rectangle =
            canvas.getBoundingClientRect();

        context.clearRect(
            0,
            0,
            rectangle.width,
            rectangle.height
        );

        drawing = false;

        hasSignature = false;

        signatureInput.value = '';

        signaturePad.classList.remove(
            'is-drawing',
            'has-signature',
            'has-error'
        );

        errorMessage.classList.remove(
            'is-visible'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | OUVRIR LA MODALE
    |--------------------------------------------------------------------------
    */

    function openModal() {

        modal.classList.add(
            'is-open'
        );

        modal.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.classList.add(
            'bc-modal-open'
        );


        /*
        |--------------------------------------------------------------------------
        | LE CANVAS DOIT ÊTRE DIMENSIONNÉ APRÈS AFFICHAGE
        |--------------------------------------------------------------------------
        */

        requestAnimationFrame(
            function () {

                resizeCanvas();

            }
        );


        setTimeout(
            function () {

                canvas.focus();

            },
            100
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FERMER LA MODALE
    |--------------------------------------------------------------------------
    */

    function closeModal() {

        if (confirmed) {
            return;
        }

        modal.classList.remove(
            'is-open'
        );

        modal.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.classList.remove(
            'bc-modal-open'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | INTERCEPTER LE FORMULAIRE
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'submit',
        function (event) {

            if (confirmed) {
                return;
            }

            event.preventDefault();

            openModal();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | ÉVÉNEMENTS DU CANVAS
    |--------------------------------------------------------------------------
    */

    canvas.addEventListener(
        'pointerdown',
        startDrawing
    );

    canvas.addEventListener(
        'pointermove',
        draw
    );

    canvas.addEventListener(
        'pointerup',
        stopDrawing
    );

    canvas.addEventListener(
        'pointercancel',
        stopDrawing
    );


    /*
    |--------------------------------------------------------------------------
    | EFFACER LA SIGNATURE
    |--------------------------------------------------------------------------
    */

    clearButton.addEventListener(
        'click',
        clearSignature
    );


    /*
    |--------------------------------------------------------------------------
    | FERMETURE
    |--------------------------------------------------------------------------
    */

    modal
        .querySelectorAll(
            '[data-close-approve-modal]'
        )
        .forEach(
            function (element) {

                element.addEventListener(
                    'click',
                    closeModal
                );
            }
        );


    /*
    |--------------------------------------------------------------------------
    | SIGNER ET APPROUVER
    |--------------------------------------------------------------------------
    */

    confirmButton.addEventListener(
        'click',
        function () {

            /*
            |--------------------------------------------------------------------------
            | SIGNATURE OBLIGATOIRE
            |--------------------------------------------------------------------------
            */

            if (!hasSignature) {

                signaturePad.classList.add(
                    'has-error'
                );

                errorMessage.classList.add(
                    'is-visible'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | CONVERTIR LE CANVAS EN PNG BASE64
            |--------------------------------------------------------------------------
            */

            const signatureData =
                canvas.toDataURL(
                    'image/png'
                );

            if (
                !signatureData
                ||
                !signatureData.startsWith(
                    'data:image/png;base64,'
                )
            ) {

                signaturePad.classList.add(
                    'has-error'
                );

                errorMessage.textContent =
                    'Impossible de générer la signature. Veuillez recommencer.';

                errorMessage.classList.add(
                    'is-visible'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | PLACER LA SIGNATURE DANS LE FORMULAIRE
            |--------------------------------------------------------------------------
            */

            signatureInput.value =
                signatureData;


            /*
            |--------------------------------------------------------------------------
            | VERROUILLER LA SOUMISSION
            |--------------------------------------------------------------------------
            */

            confirmed = true;

            confirmButton.disabled = true;

            clearButton.disabled = true;

            confirmButton.innerHTML =
                '<i class="bx bx-loader-alt bx-spin"></i> Signature...';


            /*
            |--------------------------------------------------------------------------
            | ENVOYER LE FORMULAIRE
            |--------------------------------------------------------------------------
            */

            form.requestSubmit();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | TOUCHE ÉCHAP
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Escape'
                &&
                modal.classList.contains(
                    'is-open'
                )
            ) {

                closeModal();
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | REDIMENSIONNEMENT
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'resize',
        function () {

            if (
                modal.classList.contains(
                    'is-open'
                )
            ) {

                resizeCanvas();
            }
        }
    );
});

</script>

@endif



<style>
/* ================================================================
   MODALE DE REJET DU BON DE COMMANDE
================================================================ */

.bc-reject-modal {
    position: fixed;
    inset: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 20px;

    visibility: hidden;
    opacity: 0;

    z-index: 100000;

    transition:
        opacity .18s ease,
        visibility .18s ease;
}

.bc-reject-modal.is-open {
    visibility: visible;
    opacity: 1;
}

.bc-reject-backdrop {
    position: absolute;
    inset: 0;

    background: rgba(15, 23, 42, .62);

    backdrop-filter: blur(3px);
    -webkit-backdrop-filter: blur(3px);
}

.bc-reject-dialog {
    position: relative;

    width: 100%;
    max-width: 560px;

    padding: 32px;

    background: #ffffff;

    border-radius: 18px;

    box-shadow:
        0 25px 60px rgba(15, 23, 42, .25);

    z-index: 1;

    transform: translateY(14px) scale(.97);

    transition: transform .20s ease;
}

.bc-reject-modal.is-open .bc-reject-dialog {
    transform: translateY(0) scale(1);
}

.bc-reject-close {
    position: absolute;

    top: 15px;
    right: 15px;

    width: 34px;
    height: 34px;

    border: 0;
    border-radius: 50%;

    background: #f4f6f8;

    font-size: 21px;

    display: flex;
    align-items: center;
    justify-content: center;

    cursor: pointer;
}

.bc-reject-icon {
    width: 58px;
    height: 58px;

    margin: 0 auto 15px;

    border-radius: 50%;

    background: #fff1f1;
    color: #d92d20;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 31px;
}

.bc-reject-dialog h3 {
    margin: 0;

    text-align: center;

    font-size: 22px;
    font-weight: 700;

    color: #1d2939;
}

.bc-reject-subtitle {
    margin: 8px 0 25px;

    text-align: center;

    color: #667085;
    font-size: 14px;
}

.bc-reject-field label {
    display: block;

    margin-bottom: 8px;

    font-size: 13px;
    font-weight: 600;

    color: #344054;
}

.bc-reject-required {
    color: #d92d20;
}

.bc-reject-field textarea {
    width: 100%;

    min-height: 125px;

    padding: 13px 14px;

    border: 1px solid #d0d5dd;
    border-radius: 10px;

    resize: vertical;

    font-family: inherit;
    font-size: 14px;

    outline: none;

    transition:
        border-color .15s ease,
        box-shadow .15s ease;
}

.bc-reject-field textarea:focus {
    border-color: #d92d20;

    box-shadow:
        0 0 0 3px rgba(217, 45, 32, .08);
}

.bc-reject-counter {
    margin-top: 5px;

    text-align: right;

    color: #98a2b3;

    font-size: 11px;
}

.bc-reject-error,
.bc-reject-server-error {
    margin-top: 7px;

    color: #d92d20;

    font-size: 12px;
}

.bc-reject-error {
    display: none;
}

.bc-reject-error.is-visible {
    display: block;
}

.bc-reject-info {
    display: flex;

    gap: 10px;

    margin-top: 20px;
    padding: 13px;

    border-radius: 10px;

    background: #f9fafb;

    color: #475467;

    font-size: 12px;
}

.bc-reject-info > i {
    margin-top: 2px;

    font-size: 18px;
}

.bc-reject-info strong,
.bc-reject-info span {
    display: block;
}

.bc-reject-info span {
    margin-top: 3px;
}

.bc-reject-actions {
    display: flex;
    justify-content: flex-end;

    gap: 10px;

    margin-top: 25px;
}

.bc-reject-btn {
    min-height: 39px;

    padding: 9px 16px;

    border: 0;
    border-radius: 8px;

    font-size: 13px;
    font-weight: 600;

    cursor: pointer;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 6px;
}

.bc-reject-btn-cancel {
    background: #f2f4f7;
    color: #344054;
}

.bc-reject-btn-confirm {
    background: #d92d20;
    color: #ffffff;
}

@media (max-width: 576px) {

    .bc-reject-dialog {
        padding:
            28px
            18px
            20px;
    }

    .bc-reject-actions {
        flex-direction: column-reverse;
    }

    .bc-reject-btn {
        width: 100%;
    }
}

@media print {

    .bc-reject-modal {
        display: none !important;
    }
}
</style>


<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | MODALE DE REJET DU BON DE COMMANDE
    |--------------------------------------------------------------------------
    */

    const openButton =
        document.getElementById(
            'openRejectSupplierOrderModal'
        );

    const modal =
        document.getElementById(
            'rejectSupplierOrderModal'
        );

    const form =
        document.getElementById(
            'rejectSupplierOrderForm'
        );

    const reason =
        document.getElementById(
            'rejectionReason'
        );

    const counter =
        document.getElementById(
            'rejectionReasonCounter'
        );

    const error =
        document.getElementById(
            'rejectionReasonError'
        );


    /*
    |--------------------------------------------------------------------------
    | LA MODALE N'EXISTE PAS POUR LES UTILISATEURS NON AUTORISÉS
    |--------------------------------------------------------------------------
    */

    if (!modal) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | COMPTEUR
    |--------------------------------------------------------------------------
    */

    function updateCounter() {

        if (!reason || !counter) {
            return;
        }

        counter.textContent =
            reason.value.length;
    }


    /*
    |--------------------------------------------------------------------------
    | OUVRIR
    |--------------------------------------------------------------------------
    */

    function openModal() {

        modal.classList.add(
            'is-open'
        );

        modal.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.style.overflow =
            'hidden';

        updateCounter();

        setTimeout(function () {

            reason?.focus();

        }, 100);
    }


    /*
    |--------------------------------------------------------------------------
    | FERMER
    |--------------------------------------------------------------------------
    */

    function closeModal() {

        modal.classList.remove(
            'is-open'
        );

        modal.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.style.overflow =
            '';

        error?.classList.remove(
            'is-visible'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | BOUTON REJETER
    |--------------------------------------------------------------------------
    */

    openButton?.addEventListener(
        'click',
        openModal
    );


    /*
    |--------------------------------------------------------------------------
    | BOUTONS DE FERMETURE
    |--------------------------------------------------------------------------
    */

    modal
        .querySelectorAll(
            '[data-close-reject-modal]'
        )
        .forEach(function (button) {

            button.addEventListener(
                'click',
                closeModal
            );

        });


    /*
    |--------------------------------------------------------------------------
    | TOUCHE ÉCHAP
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Escape'
                &&
                modal.classList.contains(
                    'is-open'
                )
            ) {
                closeModal();
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | COMPTEUR EN TEMPS RÉEL
    |--------------------------------------------------------------------------
    */

    reason?.addEventListener(
        'input',
        function () {

            updateCounter();

            if (
                reason.value.trim().length >= 3
            ) {
                error?.classList.remove(
                    'is-visible'
                );
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDATION AVANT ENVOI
    |--------------------------------------------------------------------------
    */

    form?.addEventListener(
        'submit',
        function (event) {

            const value =
                reason?.value.trim() ?? '';

            if (value.length < 3) {

                event.preventDefault();

                error?.classList.add(
                    'is-visible'
                );

                reason?.focus();

                return;
            }


            /*
            |------------------------------------------------------------------
            | ÉVITER LE DOUBLE CLIC
            |------------------------------------------------------------------
            */

            const submitButton =
                document.getElementById(
                    'confirmRejectSupplierOrder'
                );

            if (submitButton) {

                submitButton.disabled = true;

                submitButton.innerHTML =
                    '<i class="bx bx-loader-alt bx-spin"></i> Rejet en cours...';
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | RÉOUVRIR AUTOMATIQUEMENT EN CAS D'ERREUR SERVEUR
    |--------------------------------------------------------------------------
    |
    | Par exemple :
    | - motif trop court ;
    | - erreur de validation Laravel.
    |
    */

    @if($errors->has('rejection_reason'))

        openModal();

    @endif

});
</script>

@endsection
