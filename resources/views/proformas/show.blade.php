@extends('layouts.layoutMaster')

@section('content')

<style>

    @page {
        margin: 20px;
        size: A4 portrait;
    }

    .proforma-wrapper {
        background: #fff;
    }

    .proforma-card {
        border: 0;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(15, 23, 42, .08);
        overflow: hidden;
    }

    .proforma-card .card-body {
        max-width: 1180px;
        margin: 0 auto;
        padding: 35px;
    }

    .no-border {
        width: 100%;
        border-collapse: collapse;
    }

    .no-border td,
    .no-border th {
        border: 0 !important;
    }

    .company-title {
        margin: 0 0 6px;
        font-size: 28px;
        font-weight: 800;
        color: #1f3a93;
        line-height: 1.1;
    }

    .document-title {
        margin: 0;
        font-size: 42px;
        font-weight: 800;
        color: #1f3a93;
        line-height: 1;
    }

    .document-meta {
        margin-top: 14px;
        line-height: 1.8;
        color: #475569;
    }

    .section-title {
        background: #eef2f7;
        border: 1px solid #d5dbe5;
        border-bottom: 0;
        padding: 9px 14px;
        font-weight: 800;
        color: #334155;
    }

    .info-box {
        border: 1px solid #d5dbe5;
        padding: 14px;
        min-height: 145px;
        color: #475569;
        line-height: 1.7;
    }

    .proforma-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 22px;
    }

    .proforma-table th {
        background: #e9edf3;
        border: 1px solid #cfd6df;
        padding: 9px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #475569;
    }

    .proforma-table td {
        border: 1px solid #d7dde5;
        padding: 9px;
        vertical-align: middle;
        color: #475569;
    }

    .text-end {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .fw-bold {
        font-weight: 700;
    }

    .totals-box {
        width: 430px;
        margin-left: auto;
        margin-top: 25px;
    }

    .totals-box td {
        padding: 7px 0;
    }

    .grand-total-row td {
        border-top: 2px solid #334155 !important;
        padding-top: 14px;
        font-size: 24px;
        font-weight: 800;
    }

    .grand-total-value {
        color: #4169e1;
        font-size: 30px;
        font-weight: 800;
    }

    .text-danger-custom {
        color: #ea5455;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 11px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: #eef2ff;
        color: #4f46e5;
    }

    .status-converted {
        background: #dcfce7;
        color: #15803d;
    }

    .status-cancelled {
        background: #fee2e2;
        color: #dc2626;
    }

    .amount-words {
        margin-top: 28px;
        padding: 14px 16px;
        border: 1px solid #d7dde5;
        background: #f8fafc;
        color: #475569;
    }

    .actions-bar {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 28px;
    }

    @media print {

        .layout-navbar,
        .layout-menu,
        .print-hide,
        footer {
            display: none !important;
        }

        body,
        .content-wrapper,
        .container-xxl,
        .proforma-wrapper {
            background: #fff !important;
        }

        .proforma-card {
            box-shadow: none !important;
            border-radius: 0 !important;
        }

        .proforma-card .card-body {
            max-width: none;
            padding: 10px !important;
        }

        .document-title {
            font-size: 34px;
        }

        .proforma-table {
            font-size: 10px;
        }

        .proforma-table th,
        .proforma-table td {
            padding: 5px !important;
        }

        .info-box,
        .totals-box,
        .amount-words {
            break-inside: avoid;
        }
    }

</style>


@php

    $proformaNumber =
        $proforma->proforma_number;

    $proformaDate =
        $proforma->proforma_date
        ?? $proforma->created_at;

    $subtotal =
        (float) $proforma->calculated_subtotal;

    $discountPercent =
        (float) $proforma->discount_rate;

    $discountAmount =
        (float) $proforma->calculated_discount_amount;

    $tva =
        (float) $proforma->calculated_tva;

    $total =
        (float) $proforma->calculated_total;

    $vehicleDescription = '';

    if ($proforma->vehicle) {

        $vehicleDescription = trim(
            ($proforma->vehicle->brand ?? '')
            . ' '
            . ($proforma->vehicle->model ?? '')
        );
    }

    $isConverted =
        $proforma->status === 'Converti';

    $isCancelled =
        $proforma->status === 'Annulé';

@endphp


<div class="proforma-wrapper">

    {{-- ============================================================
        MESSAGES
    ============================================================ --}}

    @if(session('success'))

        <div class="alert alert-success print-hide">

            <i class="bx bx-check-circle me-1"></i>

            {{ session('success') }}

        </div>

    @endif


    @if(session('error'))

        <div class="alert alert-danger print-hide">

            <i class="bx bx-error-circle me-1"></i>

            {{ session('error') }}

        </div>

    @endif


    @if(session('info'))

        <div class="alert alert-info print-hide">

            <i class="bx bx-info-circle me-1"></i>

            {{ session('info') }}

        </div>

    @endif


    <div class="card proforma-card">

        <div class="card-body">

            {{-- ====================================================
                ENTÊTE
            ==================================================== --}}

            <table class="no-border">

                <tr>

                    <td style="
                        width:62%;
                        vertical-align:top;
                        padding:0;
                    ">

                        <table class="no-border">

                            <tr>

                                <td style="
                                    width:155px;
                                    vertical-align:top;
                                    padding:0 18px 0 0;
                                ">

                                    <img
                                        src="{{ asset('assets/img/logo/stcd.jpg') }}"
                                        alt="STCD Motors"
                                        style="
                                            width:135px;
                                            height:auto;
                                        "
                                    >

                                </td>


                                <td style="
                                    vertical-align:top;
                                    padding:0;
                                ">

                                    <h1 class="company-title">

                                        STCD MOTORS

                                    </h1>

                                    <div style="
                                        line-height:1.7;
                                        color:#64748b;
                                    ">

                                        1667 Guelleh-Batal,
                                        Djibouti-ville

                                        <br>

                                        Téléphone :
                                        +253 77 22 93 33

                                        <br>

                                        Fax :
                                        +253 21 35 30 09

                                        <br>

                                        Email :
                                        spareparts@stcd.dj

                                    </div>

                                </td>

                            </tr>

                        </table>

                    </td>


                    <td style="
                        width:38%;
                        text-align:right;
                        vertical-align:top;
                        padding:0;
                    ">

                        <div class="document-title">

                            PROFORMA

                        </div>


                        <div class="document-meta">

                            <div>

                                <strong>
                                    N° Proforma :
                                </strong>

                                {{ $proformaNumber }}

                            </div>


                            <div>

                                <strong>
                                    Date :
                                </strong>

                                {{
                                    $proformaDate
                                        ? $proformaDate->format('d/m/Y')
                                        : '-'
                                }}

                            </div>


                            <div style="margin-top:4px;">

                                <span
                                    class="
                                        status-badge

                                        @if($isConverted)
                                            status-converted
                                        @elseif($isCancelled)
                                            status-cancelled
                                        @endif
                                    "
                                >

                                    {{ $proforma->status ?? 'Validé' }}

                                </span>

                            </div>

                        </div>

                    </td>

                </tr>

            </table>


            {{-- ====================================================
                CLIENT / DÉTAILS
            ==================================================== --}}

            <table
                class="no-border"
                style="margin-top:28px;"
            >

                <tr>

                    <td style="
                        width:48%;
                        vertical-align:top;
                        padding:0;
                    ">

                        <div class="section-title">

                            Facturé à

                        </div>

                        <div class="info-box">

                            <strong>

                                {{
                                    $proforma->customer?->name
                                    ?? 'Client non défini'
                                }}

                            </strong>

                            <br><br>

                            <strong>
                                Téléphone :
                            </strong>

                            {{
                                $proforma->customer?->phone
                                ?? '-'
                            }}

                            <br>

                            <strong>
                                Email :
                            </strong>

                            {{
                                $proforma->customer?->email
                                ?? '-'
                            }}

                            <br>

                            <strong>
                                Adresse :
                            </strong>

                            {{
                                $proforma->customer?->address
                                ?? '-'
                            }}

                        </div>

                    </td>


                    <td style="width:4%;"></td>


                    <td style="
                        width:48%;
                        vertical-align:top;
                        padding:0;
                    ">

                        <div class="section-title">

                            Détails du proforma

                        </div>

                        <div class="info-box">

                            <table class="no-border">

                                <tr>

                                    <td
                                        class="fw-bold"
                                        style="width:40%;"
                                    >
                                        Proforma :
                                    </td>

                                    <td>
                                        {{ $proformaNumber }}
                                    </td>

                                </tr>


                                <tr>

                                    <td class="fw-bold">
                                        Date :
                                    </td>

                                    <td>

                                        {{
                                            $proformaDate
                                                ? $proformaDate->format('d/m/Y')
                                                : '-'
                                        }}

                                    </td>

                                </tr>


                                <tr>

                                    <td class="fw-bold">
                                        Statut :
                                    </td>

                                    <td>

                                        {{ $proforma->status ?? 'Validé' }}

                                    </td>

                                </tr>


                                <tr>

                                    <td class="fw-bold">
                                        Paiement :
                                    </td>

                                    <td>

                                        {{ $proforma->payment_type ?? '-' }}

                                    </td>

                                </tr>


                                <tr>

                                    <td class="fw-bold">
                                        Immatriculation :
                                    </td>

                                    <td>

                                        {{
                                            $proforma->vehicle?->plate_number
                                            ?? '-'
                                        }}

                                    </td>

                                </tr>


                                @if($vehicleDescription !== '')

                                    <tr>

                                        <td class="fw-bold">
                                            Véhicule :
                                        </td>

                                        <td>
                                            {{ $vehicleDescription }}
                                        </td>

                                    </tr>

                                @endif

                            </table>

                        </div>

                    </td>

                </tr>

            </table>


            {{-- ====================================================
                PRODUITS
            ==================================================== --}}

            <table class="proforma-table">

                <thead>

                    <tr>

                        <th
                            style="width:5%;"
                            class="text-center"
                        >
                            #
                        </th>

                        <th style="width:15%;">
                            Référence
                        </th>

                        <th style="width:28%;">
                            Désignation
                        </th>

                        <th
                            style="width:12%;"
                            class="text-end"
                        >
                            Quantité
                        </th>

                        <th
                            style="width:15%;"
                            class="text-end"
                        >
                            Prix unitaire
                        </th>

                        <th
                            style="width:12%;"
                            class="text-end"
                        >
                            TVA
                        </th>

                        <th
                            style="width:13%;"
                            class="text-end"
                        >
                            Total HT
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($proforma->items as $item)

                        @php

                            $quantity =
                                (float) $item->quantity;

                            $price =
                                (float) $item->price;

                            $lineSubtotal =
                                round(
                                    $quantity * $price,
                                    2
                                );

                            $lineDiscount =
                                round(
                                    (
                                        $lineSubtotal
                                        * $discountPercent
                                    ) / 100,
                                    2
                                );

                            $lineTaxable =
                                max(
                                    0,
                                    round(
                                        $lineSubtotal
                                        - $lineDiscount,
                                        2
                                    )
                                );

                            $lineTva =
                                $proforma->invoice_type === 'without_tax'
                                    ? 0
                                    : round(
                                        $lineTaxable * 0.10,
                                        2
                                    );

                            $unit =
                                $item->product?->unit_label
                                ?? 'Pièce';

                        @endphp


                        <tr>

                            <td class="text-center">

                                {{ $loop->iteration }}

                            </td>


                            <td>

                                {{
                                    $item->product?->reference
                                    ?? '-'
                                }}

                            </td>


                            <td>

                                {{
                                    $item->product?->designation
                                    ?? 'Produit supprimé'
                                }}

                                <br>

                                <small style="color:#94a3b8;">

                                    Unité :
                                    {{ $unit }}

                                </small>

                            </td>


                            <td class="text-end">

                                {{
                                    number_format(
                                        $quantity,
                                        2,
                                        ',',
                                        ' '
                                    )
                                }}

                                {{ $unit }}

                            </td>


                            <td class="text-end">

                                {{
                                    number_format(
                                        $price,
                                        2,
                                        ',',
                                        ' '
                                    )
                                }}

                                FDJ

                            </td>


                            <td class="text-end">

                                {{
                                    number_format(
                                        $lineTva,
                                        2,
                                        ',',
                                        ' '
                                    )
                                }}

                                FDJ

                            </td>


                            <td class="text-end fw-bold">

                                {{
                                    number_format(
                                        $lineSubtotal,
                                        2,
                                        ',',
                                        ' '
                                    )
                                }}

                                FDJ

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="text-center"
                                style="
                                    padding:25px;
                                    color:#94a3b8;
                                "
                            >

                                Aucun produit enregistré sur ce proforma.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>


            {{-- ====================================================
                TOTAUX
            ==================================================== --}}

            <div class="totals-box">

                <table class="no-border">

                    <tr>

                        <td class="fw-bold">
                            Sous-total :
                        </td>

                        <td class="text-end">

                            {{
                                number_format(
                                    $subtotal,
                                    2,
                                    ',',
                                    ' '
                                )
                            }}

                            FDJ

                        </td>

                    </tr>


                    <tr>

                        <td class="fw-bold">

                            Remise

                            @if($discountPercent > 0)

                                (
                                    {{
                                        number_format(
                                            $discountPercent,
                                            2,
                                            ',',
                                            ' '
                                        )
                                    }}
                                    %
                                )

                            @endif

                            :

                        </td>


                        <td class="text-end text-danger-custom">

                            -

                            {{
                                number_format(
                                    $discountAmount,
                                    2,
                                    ',',
                                    ' '
                                )
                            }}

                            FDJ

                        </td>

                    </tr>


                    <tr>

                        <td class="fw-bold">
                            TVA (10 %) :
                        </td>

                        <td class="text-end">

                            {{
                                number_format(
                                    $tva,
                                    2,
                                    ',',
                                    ' '
                                )
                            }}

                            FDJ

                        </td>

                    </tr>


                    <tr class="grand-total-row">

                        <td>
                            TOTAL :
                        </td>

                        <td class="text-end grand-total-value">

                            {{
                                number_format(
                                    $total,
                                    2,
                                    ',',
                                    ' '
                                )
                            }}

                            FDJ

                        </td>

                    </tr>

                </table>

            </div>


            {{-- ====================================================
                MONTANT EN LETTRES
            ==================================================== --}}

            @if(isset($totalInWords) && $totalInWords !== '')

                <div class="amount-words">

                    <strong>
                        Montant en lettres :
                    </strong>

                    {{ $totalInWords }}

                </div>

            @endif


            {{-- ====================================================
                ACTIONS
            ==================================================== --}}

            <div class="actions-bar print-hide">

                {{-- RETOUR --}}
                <a
                    href="{{ route('proformas.index') }}"
                    class="btn btn-secondary"
                >

                    <i class="bx bx-arrow-back me-1"></i>

                    Retour

                </a>


                {{-- PDF PROFORMA --}}
                @if(Route::has('proformas.pdf'))

                    <a
                        href="{{ route(
                            'proformas.pdf',
                            $proforma
                        ) }}"
                        class="btn btn-danger"
                    >

                        <i class="bx bxs-file-pdf me-1"></i>

                        Télécharger PDF

                    </a>

                @endif


                {{-- IMPRIMER --}}
                <button
                    type="button"
                    class="btn btn-dark"
                    onclick="window.print()"
                >

                    <i class="bx bx-printer me-1"></i>

                    Imprimer

                </button>


                {{-- =================================================
                    PROFORMA DÉJÀ CONVERTI
                ================================================= --}}

                @if($isConverted && $proforma->sale_id)

                    <a
                        href="{{ route(
                            'sales.invoice',
                            $proforma->sale_id
                        ) }}"
                        class="btn btn-primary"
                    >

                        <i class="bx bx-receipt me-1"></i>

                        Voir la facture

                    </a>


                {{-- =================================================
                    CONVERTIR EN VENTE
                ================================================= --}}

                @elseif(
                    !$isCancelled
                    && Route::has('proformas.convert-sale')
                )

                    <form
                        action="{{ route(
                            'proformas.convert-sale',
                            $proforma
                        ) }}"
                        method="POST"
                        class="d-inline"
                        id="convertProformaForm"
                    >

                        @csrf


                        <button
                            type="submit"
                            class="btn btn-success"
                            id="convertProformaButton"
                        >

                            <i class="bx bx-transfer me-1"></i>

                            Convertir en vente

                        </button>

                    </form>

                @endif

            </div>

        </div>

    </div>

</div>


{{-- ================================================================
    CONVERSION : BLOQUER DOUBLE CLIC
================================================================ --}}

@if(!$isConverted && !$isCancelled)

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const form =
            document.getElementById(
                'convertProformaForm'
            );

        const button =
            document.getElementById(
                'convertProformaButton'
            );


        if (!form || !button) {
            return;
        }


        form.addEventListener(
            'submit',
            function () {

                /*
                |--------------------------------------------------------------------------
                | DÉSACTIVER IMMÉDIATEMENT
                |--------------------------------------------------------------------------
                */

                button.disabled = true;


                /*
                |--------------------------------------------------------------------------
                | CACHER LE BOUTON VERT
                |--------------------------------------------------------------------------
                */

                button.innerHTML = `
                    <span
                        class="spinner-border spinner-border-sm me-1"
                        role="status"
                        aria-hidden="true"
                    ></span>

                    Conversion...
                `;


                /*
                |--------------------------------------------------------------------------
                | ÉVITER PLUSIEURS SOUMISSIONS
                |--------------------------------------------------------------------------
                */

                setTimeout(
                    function () {

                        button.style.pointerEvents =
                            'none';

                    },
                    50
                );

            }
        );

    }
);

</script>

@endif

@endsection