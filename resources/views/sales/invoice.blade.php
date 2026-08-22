@extends('layouts.layoutMaster')

@section('content')

@php
    $status = strtolower((string) ($sale->status ?? 'vendu'));

    /**
*    |--------------------------------------------------------------------------*
*    | MONTANTS EN FDJ — ARRONDIS À L'UNITÉ*
*    |--------------------------------------------------------------------------*
*    |*
*    | La monnaie affichée est le FDJ.*
*    | Aucun montant monétaire ne doit afficher de décimales.*
*    |*
*    */

    $paidAmount = (int) round(
        (float) $sale->payments->sum('amount')
    );

    $invoiceTotal = (int) round(
        (float) ($sale->total ?? 0)
    );

    $remainingAmount = max(
        0,
        $invoiceTotal - $paidAmount
    );

    $discountPercent = round(
        (float) ($sale->discount ?? 0),
        2
    );

    $discountAmount = (int) round(
        (float) ($sale->discount_amount ?? 0)
    );

    $subtotal = (int) round(
        (float) ($sale->subtotal ?? 0)
    );

    $tva = (int) round(
        (float) ($sale->tva ?? 0)
    );

    $statusLabel = match ($status) {
        'paid', 'payé', 'paye' => 'Payé',
        'cancelled', 'annulé', 'annule' => 'Annulé',
        default => 'Vendu',
    };

    $isCancelled = in_array(
        $status,
        ['cancelled', 'annulé', 'annule'],
        true
    );

    $isPaid = $remainingAmount <= 0
        || in_array(
            $status,
            ['paid', 'payé', 'paye'],
            true
        );
@endphp

<style>
    /* ============================================================
       AFFICHAGE ÉCRAN
   ============================================================ */
    .invoice-page {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(15, 23, 42, .08);
        overflow: hidden;
    }

    .invoice-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 24px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }

    .invoice-toolbar-left,
    .invoice-toolbar-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .invoice-toolbar-right {
        justify-content: flex-end;
        margin-left: auto;
    }

    .invoice-toolbar .btn {
        min-width: 145px;
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-weight: 600;
        border-radius: 7px;
        white-space: nowrap;
    }

    .invoice-toolbar .btn-back {
        min-width: 120px;
    }

    .invoice-toolbar .btn-status-paid {
        cursor: default;
        opacity: 1;
    }

    @media (max-width: 768px) {
        .invoice-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .invoice-toolbar-left,
        .invoice-toolbar-right {
            width: 100%;
        }

        .invoice-toolbar-right {
            margin-left: 0;
            justify-content: stretch;
        }

        .invoice-toolbar-left .btn,
        .invoice-toolbar-right .btn,
        .invoice-toolbar-right form,
        .invoice-toolbar-right form .btn {
            width: 100%;
        }

        .invoice-toolbar-right {
            display: grid;
            grid-template-columns: 1fr;
        }
    }

    .invoice-body {
        max-width: 1050px;
        margin: 0 auto;
        padding: 30px;
        color: #334155;
        background: #fff;
    }

    .invoice-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 25px;
        margin-bottom: 22px;
    }

    .company-block {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        width: 62%;
    }

    .company-logo {
        width: 125px;
        height: auto;
        object-fit: contain;
        flex: 0 0 auto;
    }

    .company-title {
        margin: 0 0 5px;
        color: #111827;
        font-size: 25px;
        line-height: 1.1;
        font-weight: 800;
    }

    .company-details {
        font-size: 13px;
        line-height: 1.45;
        color: #475569;
    }

    .invoice-heading {
        width: 38%;
        text-align: right;
    }

    .invoice-title {
        margin: 0 0 7px;
        color: #253494;
        font-size: 38px;
        line-height: 1;
        font-weight: 800;
    }

    .invoice-number-line,
    .invoice-date-line {
        font-size: 13px;
        line-height: 1.45;
    }

    .invoice-status {
        margin-top: 7px;
    }

    .invoice-info-row {
        display: flex;
        align-items: stretch;
        gap: 18px;
        margin-bottom: 18px;
    }

    .invoice-info-col {
        width: 50%;
    }

    .info-title {
        padding: 8px 12px;
        background: #e5e7eb;
        border: 1px solid #cbd5e1;
        font-size: 13px;
        font-weight: 800;
        color: #1e293b;
    }

    .info-box {
        min-height: 123px;
        padding: 11px 12px;
        border: 1px solid #cbd5e1;
        border-top: 0;
        line-height: 1.5;
        font-size: 13px;
    }

    .customer-name {
        margin: 0 0 7px;
        font-size: 16px;
        font-weight: 800;
        color: #1e293b;
    }

    .invoice-table,
    .payment-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .invoice-table th,
    .payment-table th {
        padding: 8px 7px;
        border: 1px solid #cbd5e1;
        background: #e5e7eb;
        color: #334155;
        font-size: 11px;
        line-height: 1.2;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .invoice-table td,
    .payment-table td {
        padding: 8px 7px;
        border: 1px solid #cbd5e1;
        font-size: 12px;
        line-height: 1.3;
        vertical-align: middle;
    }

    .text-center {
        text-align: center !important;
    }

    .text-end {
        text-align: right !important;
    }

    .text-primary {
        color: #253494 !important;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .text-success {
        color: #198754 !important;
    }

    .fw-bold {
        font-weight: 700 !important;
    }

    .totals-wrapper {
        display: flex;
        justify-content: flex-end;
        margin-top: 13px;
    }

    .totals-box {
        width: 42%;
    }

    .totals-table {
        width: 100%;
        border-collapse: collapse;
    }

    .totals-table td {
        padding: 4px 0;
        border: 0;
        font-size: 12px;
        line-height: 1.3;
    }

    .grand-total td {
        padding-top: 7px;
        border-top: 2px solid #334155;
        font-size: 20px;
        font-weight: 800;
    }

    .amount-words {
        margin-top: 15px;
        padding: 9px 11px;
        border: 1px solid #94a3b8;
        background: #f8fafc;
        font-size: 11px;
        line-height: 1.35;
        font-weight: 600;
    }

    .payments-section {
        margin-top: 15px;
    }

    .payments-title {
        margin: 0 0 7px;
        color: #253494;
        font-size: 14px;
        font-weight: 800;
    }

    .payment-table th,
    .payment-table td {
        padding-top: 6px;
        padding-bottom: 6px;
    }

    .payment-summary {
        margin-top: 8px;
        font-size: 12px;
        line-height: 1.5;
        text-align: right;
    }

    /* ============================================================
       IMPRESSION A4
       - Pas de zoom
       - Client et détails restent côte à côte
       - Taille lisible
       - Une seule page pour une facture normale
   ============================================================ */
    @media print {
        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            width: auto !important;
            min-width: 0 !important;
            height: auto !important;
            overflow: visible !important;
            font-family: Arial, Helvetica, sans-serif !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .layout-navbar,
        .layout-menu,
        .invoice-toolbar,
        .no-print,
        footer,
        .content-footer,
        .layout-overlay,
        .buy-now {
            display: none !important;
        }

        .layout-wrapper,
        .layout-container,
        .layout-page,
        .content-wrapper,
        .container-xxl,
        .container-fluid,
        main,
        .content-wrapper > .container-xxl,
        .content-wrapper > .container-fluid {
            width: 100% !important;
            max-width: none !important;
            min-width: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            box-shadow: none !important;
            transform: none !important;
        }

        .layout-page {
            padding-left: 0 !important;
        }

        .invoice-page {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            overflow: visible !important;
            background: #fff !important;
        }

        .invoice-body {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            color: #000 !important;
            background: #fff !important;
            font-size: 10.5pt !important;
            zoom: 1 !important;
            transform: none !important;
        }

        /* ENTÊTE */
        .invoice-header {
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            gap: 10mm !important;
            margin: 0 0 5mm 0 !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .company-block {
            display: flex !important;
            flex-direction: row !important;
            align-items: flex-start !important;
            gap: 4mm !important;
            width: 62% !important;
        }

        .company-logo {
            width: 30mm !important;
            max-width: 30mm !important;
            height: auto !important;
            max-height: 23mm !important;
            object-fit: contain !important;
        }

        .company-title {
            margin: 0 0 1mm 0 !important;
            font-size: 16pt !important;
            line-height: 1.05 !important;
            color: #000 !important;
        }

        .company-details {
            font-size: 8.5pt !important;
            line-height: 1.3 !important;
            color: #000 !important;
        }

        .invoice-heading {
            width: 38% !important;
            text-align: right !important;
        }

        .invoice-title {
            margin: 0 0 2mm 0 !important;
            font-size: 24pt !important;
            line-height: 1 !important;
            color: #253494 !important;
        }

        .invoice-number-line,
        .invoice-date-line {
            font-size: 8.5pt !important;
            line-height: 1.35 !important;
        }

        .invoice-status {
            margin-top: 1.5mm !important;
        }

        .invoice-status .badge {
            font-size: 7.5pt !important;
            padding: 1mm 2mm !important;
        }

        /* CLIENT + DÉTAILS : TOUJOURS CÔTE À CÔTE */
        .invoice-info-row {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            align-items: stretch !important;
            gap: 5mm !important;
            width: 100% !important;
            margin: 0 0 5mm 0 !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .invoice-info-col {
            display: block !important;
            flex: 1 1 0 !important;
            width: calc(50% - 2.5mm) !important;
            max-width: calc(50% - 2.5mm) !important;
            min-width: 0 !important;
        }

        .info-title {
            padding: 2mm 2.5mm !important;
            border: 0.3mm solid #777 !important;
            background: #e5e7eb !important;
            font-size: 9pt !important;
            line-height: 1.15 !important;
            color: #000 !important;
        }

        .info-box {
            min-height: 25mm !important;
            height: auto !important;
            padding: 2.5mm !important;
            border: 0.3mm solid #777 !important;
            border-top: 0 !important;
            font-size: 8.5pt !important;
            line-height: 1.35 !important;
            color: #000 !important;
        }

        .customer-name {
            margin: 0 0 1.5mm 0 !important;
            font-size: 11pt !important;
            line-height: 1.1 !important;
        }

        /* ARTICLES */
        .table-responsive {
            width: 100% !important;
            overflow: visible !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .invoice-table {
            width: 100% !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
            margin: 0 !important;
        }

        .invoice-table th {
            padding: 1.6mm 1.2mm !important;
            border: 0.25mm solid #777 !important;
            background: #e5e7eb !important;
            color: #000 !important;
            font-size: 7.2pt !important;
            line-height: 1.15 !important;
        }

        .invoice-table td {
            padding: 1.7mm 1.2mm !important;
            border: 0.25mm solid #777 !important;
            color: #000 !important;
            font-size: 7.8pt !important;
            line-height: 1.2 !important;
            overflow-wrap: anywhere !important;
        }

        .invoice-table tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        /* TOTAUX */
        .totals-wrapper {
            display: flex !important;
            justify-content: flex-end !important;
            width: 100% !important;
            margin-top: 3mm !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .totals-box {
            width: 43% !important;
        }

        .totals-table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        .totals-table td {
            padding: 1mm 0 !important;
            border: 0 !important;
            font-size: 8.5pt !important;
            line-height: 1.2 !important;
        }

        .grand-total td {
            padding-top: 1.8mm !important;
            border-top: 0.5mm solid #333 !important;
            font-size: 14pt !important;
            font-weight: 800 !important;
        }

        /* MONTANT EN LETTRES */
        .amount-words {
            margin-top: 3.5mm !important;
            padding: 2.2mm 2.5mm !important;
            border: 0.3mm solid #777 !important;
            background: #fff !important;
            font-size: 7.8pt !important;
            line-height: 1.25 !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        /* PAIEMENTS */
        .payments-section {
            margin-top: 3.5mm !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .payments-title {
            margin: 0 0 1.5mm 0 !important;
            font-size: 9.5pt !important;
            line-height: 1.1 !important;
            color: #253494 !important;
        }

        .payment-table {
            width: 100% !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
            margin: 0 !important;
        }

        .payment-table th {
            padding: 1.3mm 1.5mm !important;
            border: 0.25mm solid #777 !important;
            background: #e5e7eb !important;
            color: #000 !important;
            font-size: 7pt !important;
            line-height: 1.1 !important;
        }

        .payment-table td {
            padding: 1.3mm 1.5mm !important;
            border: 0.25mm solid #777 !important;
            color: #000 !important;
            font-size: 7.5pt !important;
            line-height: 1.1 !important;
        }

        .payment-table tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .payment-summary {
            margin-top: 2mm !important;
            font-size: 8.5pt !important;
            line-height: 1.45 !important;
            text-align: right !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        */ Évite qu'un bloc normal soit envoyé seul sur la page suivante */
        .invoice-header,
        .invoice-info-row,
        .totals-wrapper,
        .amount-words,
        .payments-section,
        .payment-summary {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    }
</style>

<div class="invoice-page">

    {{-- ============================================================
**        ACTIONS — NAVIGATEUR UNIQUEMENT**
    ============================================================ --}}
    <div class="invoice-toolbar no-print">

        {{-- NAVIGATION À GAUCHE --}}
        <div class="invoice-toolbar-left">

            <a
                href="{{ route('sales.index') }}"
                class="btn btn-secondary btn-back"
            >
                <i class="bx bx-arrow-back"></i>
                Retour
            </a>

        </div>


        {{-- ACTIONS FACTURE À DROITE --}}
        <div class="invoice-toolbar-right">

            <a
                href="{{ route('sales.invoice.download', $sale) }}"
                class="btn btn-danger"
            >
                <i class="bx bxs-file-pdf"></i>
                Télécharger PDF
            </a>


            <button
                type="button"
                class="btn btn-dark"
                onclick="window.print()"
            >
                <i class="bx bx-printer"></i>
                Imprimer
            </button>


            @if(!$isCancelled && !$isPaid)

                <button
                    type="button"
                    class="btn btn-success"
                    data-bs-toggle="modal"
                    data-bs-target="#paymentModal"
                >
                    <i class="bx bx-money"></i>
                    Payer la facture
                </button>

            @elseif($isPaid)

                <button
                    type="button"
                    class="btn btn-success btn-status-paid"
                    disabled
                >
                    <i class="bx bx-check-circle"></i>
                    Facture payée
                </button>

            @endif


            @if(
                auth()->check()
                && auth()->user()->role === 'admin'
                && !$isCancelled
            )

                <form
                    action="{{ route('sales.cancel', $sale) }}"
                    method="POST"
                    class="d-inline"
                    onsubmit="return confirm('Voulez-vous vraiment annuler cette facture ?');"
                >
                    @csrf
                    @method('PUT')

                    <button
                        type="submit"
                        class="btn btn-warning"
                    >
                        <i class="bx bx-x-circle"></i>
                        Annuler facture
                    </button>

                </form>

            @endif

        </div>

    </div>

    <div class="invoice-body">

        {{-- MESSAGES --}}
        @if(session('success'))
            <div class="alert alert-success no-print">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger no-print">
                {{ session('error') }}
            </div>
        @endif

        {{-- ========================================================
**            ENTÊTE**
        ======================================================== --}}
        <div class="invoice-header">

            <div class="company-block">

                <img
                    src="{{ asset('assets/img/logo/stcd.jpg') }}"
                    alt="Logo STCD Motors"
                    class="company-logo"
                    onerror="this.style.display='none';"
                >

                <div>
                    <h2 class="company-title">
                        STCD MOTORS
                    </h2>

                    <div class="company-details">
                        <div>1667 Guelleh-Batal, Djibouti-ville</div>
                        <div>Téléphone : +253 77 22 93 33</div>
                        <div>Fax : +253 21 35 30 09</div>
                        <div>Email : spareparts@stcd.dj</div>
                    </div>
                </div>

            </div>

            <div class="invoice-heading">

                <h1 class="invoice-title">
                    FACTURE
                </h1>

                <div class="invoice-number-line">
                    <strong>N° Facture :</strong>
                    {{ $invoiceNumber }}
                </div>

                <div class="invoice-date-line">
                    <strong>Date :</strong>
                    {{ optional($sale->created_at)->format('d/m/Y') }}
                </div>

                <div class="invoice-status">
                    @if($isCancelled)
                        <span class="badge bg-danger">
                            Annulé
                        </span>
                    @elseif($isPaid)
                        <span class="badge bg-success">
                            Payé
                        </span>
                    @else
                        <span class="badge bg-primary">
                            Vendu
                        </span>
                    @endif
                </div>

            </div>

        </div>

        {{-- ========================================================
**            CLIENT + DÉTAILS**
        ======================================================== --}}
        <div class="invoice-info-row">

            <div class="invoice-info-col">

                <div class="info-title">
                    Facturé à
                </div>

                <div class="info-box">

                    <div class="customer-name">
                        {{ $sale->customer->name ?? 'Client non renseigné' }}
                    </div>

                    <div>
                        <strong>Téléphone :</strong>
                        {{ $sale->customer->phone ?? '-' }}
                    </div>

                    <div>
                        <strong>Email :</strong>
                        {{ $sale->customer->email ?? '-' }}
                    </div>

                    <div>
                        <strong>Adresse :</strong>
                        {{ $sale->customer->address ?? '-' }}
                    </div>

                </div>

            </div>

            <div class="invoice-info-col">

                <div class="info-title">
                    Détails de la facture
                </div>

                <div class="info-box">

                    <div>
                        <strong>Facture :</strong>
                        {{ $invoiceNumber }}
                    </div>

                    <div>
                        <strong>Date :</strong>
                        {{ optional($sale->created_at)->format('d/m/Y') }}
                    </div>

                    <div>
                        <strong>Statut :</strong>
                        {{ $statusLabel }}
                    </div>

                    <div>
                        <strong>Paiement :</strong>
                        {{ $sale->payment_type ?? '-' }}
                    </div>

                    <div>
                        <strong>Immatriculation :</strong>
                        { { $sale->vehicle->plate_number ?? '-' }}
                    </div>

                   <!-- @ if($sale->vehicle)
                        <div>
                            <strong>Véhicule :</strong>
                            { {
                                trim(
                                    ($sale->vehicle->brand ?? '')
                                    . ' '
                                    . ($sale->vehicle->model ?? '')
                                ) ?: '-'
                            }}
                        </div>
                    @ endif-->

                    <div>
                        <strong>Vendeur :</strong>
                        {{ $sale->user->name ?? '-' }}
                    </div>

                </div>

            </div>

        </div>

        {{-- ========================================================
            **            ARTICLES**
        ======================================================== --}}
        <div class="table-responsive">

            <table class="invoice-table">

                <thead>
                    <tr>
                        <th class="text-center" style="width:5%;">#</th>
                        <th style="width:16%;">Référence</th>
                        <th style="width:28%;">Désignation</th>
                        <th class="text-end" style="width:14%;">Quantité</th>
                        <th class="text-end" style="width:17%;">Prix unitaire</th>
                        <th class="text-end" style="width:20%;">Total</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($sale->items as $item)

                        @php
                            /**
*                            |--------------------------------------------------------------------------*
*                            | QUANTITÉ*
*                            |--------------------------------------------------------------------------*
*                            | La quantité peut rester décimale pour les litres / kg.*
*                            */
                            $quantity = round(
                                (float) ($item->quantity ?? 0),
                                2
                            );

                            /**
*                            |--------------------------------------------------------------------------*
*                            | MONTANTS FDJ*
*                            |--------------------------------------------------------------------------*
*                            | Prix et total de ligne arrondis à l'unité.*
*                            */
                            $price = (int) round(
                                (float) ($item->price ?? 0)
                            );

                            $lineTotal = (int) round(
                                $quantity * (float) ($item->price ?? 0)
                            );

                            $unit = $item->product->unit_label ?? 'Pièce';
                        @endphp

                        <tr>

                            <td class="text-center">
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                {{ $item->product->reference ?? '-' }}
                            </td>

                            <td>
                                {{ $item->product->designation ?? 'Produit supprimé' }}
                            </td>

                            <td class="text-end">
                                {{
                                    fmod($quantity, 1.0) == 0.0
                                        ? number_format($quantity, 0, ',', ' ')
                                        : number_format($quantity, 2, ',', ' ')
                                }}
                                {{ $unit }}
                            </td>

                            <td class="text-end">
                                {{ number_format($price, 0, ',', ' ') }}
                                FDJ
                            </td>

                            <td class="text-end fw-bold">
                                {{ number_format($lineTotal, 0, ',', ' ') }}
                                FDJ
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="6"
                                class="text-center"
                            >
                                Aucun produit enregistré.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- ========================================================
**            TOTAUX**
        ======================================================== --}}
        <div class="totals-wrapper">

            <div class="totals-box">

                <table class="totals-table">

                    <tr>
                        <td class="fw-bold">
                            Sous-total :
                        </td>

                        <td class="text-end">
                            {{ number_format($subtotal, 0, ',', ' ') }}
                            FDJ
                        </td>
                    </tr>

                    <tr>
                        <td class="fw-bold">
                            Remise
                            @if($discountPercent > 0)
                                ({{ number_format($discountPercent, 2, ',', ' ') }} %)
                            @endif
                            :
                        </td>

                        <td class="text-end text-danger">
                            - {{ number_format($discountAmount, 0, ',', ' ') }}
                            FDJ
                        </td>
                    </tr>

                    <tr>
                        <td class="fw-bold">
                            TVA (10 %) :
                        </td>

                        <td class="text-end">
                            {{ number_format($tva, 0, ',', ' ') }}
                            FDJ
                        </td>
                    </tr>

                    <tr class="grand-total">
                        <td>
                            TOTAL :
                        </td>

                        <td class="text-end text-primary">
                            {{ number_format($invoiceTotal, 0, ',', ' ') }}
                            FDJ
                        </td>
                    </tr>

                </table>

            </div>

        </div>

        {{-- ========================================================
**            MONTANT EN LETTRES**
        ======================================================== --}}
        <div class="amount-words">
            <strong>Montant en lettres :</strong>
            {{ $totalInWordsRounded ?? $totalInWords ?? '-' }}
        </div>

        {{-- ========================================================
**            PAIEMENTS**
        ======================================================== --}}
        <div class="payments-section">

            <div class="payments-title">
                Paiements
            </div>

            <div class="table-responsive">

                <table class="payment-table">

                    <thead>
                        <tr>
                            <th style="width:30%;">Date</th>
                            <th style="width:35%;">Méthode</th>
                            <th class="text-end" style="width:35%;">Montant</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($sale->payments as $payment)

                            <tr>

                                <td>
                                    {{ optional($payment->created_at)->format('d/m/Y H:i') }}
                                </td>

                                <td>
                                    {{ $payment->method ?? '-' }}
                                </td>

                                <td class="text-end fw-bold">
                                    {{ number_format((int) round((float) $payment->amount), 0, ',', ' ') }}
                                    FDJ
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="3"
                                    class="text-center"
                                >
                                    Aucun paiement enregistré
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="payment-summary">

                <div>
                    <strong>Payé :</strong>
                    {{ number_format($paidAmount, 0, ',', ' ') }}
                    FDJ
                </div>

                <div class="{{ $remainingAmount > 0 ? 'text-danger' : 'text-success' }} fw-bold">
                    Reste :
                    {{ number_format($remainingAmount, 0, ',', ' ') }}
                    FDJ
                </div>

            </div>

        </div>

    </div>

</div>

{{-- ================================================================
**    MODAL PAIEMENT**
================================================================ --}}
@if(!$isCancelled && !$isPaid)

    <div
        class="modal fade"
        id="paymentModal"
        tabindex="-1"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">
                        Ajouter un paiement
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <form
                    action="{{ route('sales.payment', $sale) }}"
                    method="POST"
                >
                    @csrf

                    <div class="modal-body">

                        <div class="mb-3">

                            <label
                                for="payment_amount"
                                class="form-label"
                            >
                                Montant payé
                            </label>

                            <input
                                type="number"
                                id="payment_amount"
                                name="amount"
                                min="1"
                                max="{{ $remainingAmount }}"
                                step="1"
                                class="form-control"
                                required
                            >

                            <small class="text-muted">
                                Reste à payer :
                                {{ number_format($remainingAmount, 0, ',', ' ') }}
                                FDJ
                            </small>

                        </div>

                        <div class="mb-3">

                            <label
                                for="payment_method"
                                class="form-label"
                            >
                                Méthode
                            </label>

                            <select
                                id="payment_method"
                                name="method"
                                class="form-select"
                            >
                                <option value="Cash">
                                    Cash
                                </option>

                                <option value="Banque">
                                    Banque
                                </option>

                                <option value="Chèque">
                                    Chèque
                                </option>

                                <option value="Mobile Money">
                                    Mobile Money
                                </option>
                            </select>

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
                            type="submit"
                            class="btn btn-success"
                        >
                            <i class="bx bx-check me-1"></i>
                            Enregistrer paiement
                        </button>

                    </div>

                </form>

            </div>

        </div>
    </div>

@endif

@endsection
