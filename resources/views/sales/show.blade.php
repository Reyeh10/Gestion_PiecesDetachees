@extends('layouts.layoutMaster')

@section('content')

        <style>

            @page{
                margin:20px;
                size:auto;
            }

            body{
                font-family: DejaVu Sans, sans-serif;
                font-size:13px;
                color:#000;
                background:#fff;
            }

            .card{
                border:none !important;
                box-shadow:none !important;
            }

            table{
                width:100%;
                border-collapse:collapse;
                page-break-inside:auto;
            }

            tr{
                page-break-inside:avoid;
                page-break-after:auto;
            }

            td{
                border:1px solid #000;
                padding:8px;
                vertical-align:top;
            }

            th{
                background:#d9d9d9;
                border:1px solid #000;
                padding:10px;
                font-size:12px;
                text-transform:uppercase;
            }

            thead{
                display:table-header-group;
            }

            tfoot{
                display:table-footer-group;
            }

            .no-border td{
                border:none !important;
            }

            .invoice-box{
                border:2px solid #000;
                padding:15px;
                min-height:190px;
                page-break-inside:avoid;
            }

            .invoice-title{
                background:#d9d9d9;
                padding:8px;
                margin:-15px -15px 15px -15px;
                border-bottom:2px solid #000;
                font-weight:700;
            }

            .total-box td{
                border-top:3px solid #000 !important;
                font-size:30px;
                font-weight:700;
            }

            .text-center{
                text-align:center;
            }

            .text-end{
                text-align:right;
            }

            .text-primary{
                color:#1f3c88;
            }

            .text-danger{
                color:#dc3545;
            }

            .text-success{
                color:#28a745;
            }

            .fw-bold{
                font-weight:700;
            }

            .mt-4{
                margin-top:25px;
            }

            .mt-5{
                margin-top:40px;
            }

            .mb-3{
                margin-bottom:15px;
            }

            .mb-5{
                margin-bottom:40px;
            }

            .print-hide{
                display:block;
            }

            .table-responsive{
                overflow:visible !important;
            }

            @media print{

                html,
                body{
                    width:100%;
                    height:auto;
                    background:#fff !important;
                }

                .print-hide{
                    display:none !important;
                }

                .card{
                    border:none !important;
                    box-shadow:none !important;
                }

                .table-responsive{
                    overflow:visible !important;
                }

                table{
                    width:100% !important;
                }

                tr,
                td,
                th{
                    page-break-inside:avoid !important;
                }

                .invoice-box{
                    page-break-inside:avoid !important;
                }

            }
            /* =========================================
                CORRECTION IMPRESSION FACTURE
                ========================================= */

                .card-body{
                    width:100%;
                    max-width:1000px;
                    margin:auto;
                }

                table{
                    font-size:12px;
                }

                td,
                th{
                    padding:6px !important;
                }

                .invoice-box{
                    min-height:auto !important;
                }

                h1{
                    margin:0;
                }

                h4,
                h5{
                    margin:0 0 10px 0;
                }

                .mt-5{
                    margin-top:20px !important;
                }

                .mb-5{
                    margin-bottom:20px !important;
                }

                .total-box td{
                    font-size:20px !important;
                }

                @media print{

                    body{
                        zoom:90%;
                    }

                    .card-body{
                        padding:10px !important;
                    }

                    .invoice-box{
                        page-break-inside:avoid !important;
                    }

                    .table-responsive{
                        overflow:visible !important;
                    }

                    .mt-5{
                        margin-top:15px !important;
                    }

                    .mb-5{
                        margin-bottom:15px !important;
                    }

                    h1{
                        font-size:24px !important;
                    }

                    .total-box td{
                        font-size:18px !important;
                    }

                    table{
                        font-size:11px !important;
                    }

                    td,
                    th{
                        padding:5px !important;
                    }

                }
        </style>

        <div class="card shadow-sm">
            <div class="card-body p-5">

                {{-- HEADER --}}

                <table class="no-border mb-5" style="width:100%; border-collapse:collapse;">

                    <tr>

                        {{-- LOGO + COMPANY --}}
                        <td style="
                            width:65%;
                            border:none !important;
                            vertical-align:middle;
                            padding:0 !important;
                        ">

                            <table class="no-border" style="width:100%; border-collapse:collapse;">

                                <tr>

                                    {{-- LOGO --}}
                                    <td style="
                                        width:170px;
                                        border:none !important;
                                        vertical-align:middle;
                                        padding:0 15px 0 0 !important;
                                    ">

                                        <img src="{{ asset('assets/img/logo/stcd.jpg') }}"
                                            alt="Logo"
                                            style="
                                                width:150px;
                                                display:block;
                                            ">

                                    </td>

                                    {{-- TEXTE COMPANY --}}
                                    <td style="
                                        border:none !important;
                                        vertical-align:middle;
                                        padding:0 !important;
                                    ">

                                        <h1 style="
                                            font-size:26px;
                                            font-weight:800;
                                            margin:0 0 6px 0;
                                            line-height:1.1;
                                        ">
                                            STCD MOTORS
                                        </h1>

                                        <div style="margin:0; line-height:1.25;">
                                            1667 Guelleh-Batal, Djibouti-ville
                                        </div>

                                        <div style="margin:0; line-height:1.25;">
                                            Téléphone : +253 77 22 93 33
                                        </div>

                                        <div style="margin:0; line-height:1.25;">
                                            Fax : +253 21 35 30 09
                                        </div>

                                        <div style="margin:0; line-height:1.25;">
                                            Email : spareparts@stcd.dj
                                        </div>

                                    </td>

                                </tr>

                            </table>

                        </td>

                        {{-- FACTURE --}}
                        <td style="
                            width:35%;
                            border:none !important;
                            text-align:right;
                            vertical-align:middle;
                            padding:0 !important;
                        ">

                            <h1 style="
                                font-size:30px;
                                font-weight:800;
                                margin:0 0 8px 0;
                                line-height:1.1;
                                color:#1f3c88;
                            ">
                                FACTURE
                            </h1>

                            <div style="margin:0; line-height:1.3;">
                                <strong>N° Facture :</strong>
                                {{ $sale->invoice_number }}
                            </div>

                            <div style="margin:0; line-height:1.3;">
                                <strong>Date :</strong>
                                {{ $sale->created_at->format('d/m/Y') }}
                            </div>

                        </td>

                    </tr>

                </table>

                {{-- CLIENT + DETAILS --}}

                <table class="no-border mb-5" style="width:100%;">

                    <tr>

                        {{-- CLIENT --}}
                        <td style="
                            width:48%;
                            border:none;
                            vertical-align:top;
                            ">

                            <div class="invoice-box">

                                <!--div class="invoice-title">
                                    Invoice To
                                </div-->

                                 <div class="invoice-title">
                                    Facturé à
                                </div>

                                <table class="no-border" style="
                                    width:100%;
                                    border-collapse:collapse;
                                ">

                                    <td style="
                                        border:none;
                                        vertical-align:top;
                                        ">

                                        <div>
                                            <strong>
                                            {{ $sale->customer->name ?? 'Vente comptoir' }}
                                            </strong>

                                        </div>

                                        <div>
                                            <strong>
                                                Téléphone :
                                            </strong>
                                            {{ $sale->customer->phone ?? '-' }}
                                        </div>

                                        <div>
                                            <strong>
                                                Email :
                                            </strong>
                                            {{ $sale->customer->email ?? '-' }}
                                        </div>

                                        <!--div>
                                            <strong>
                                                Adresse :
                                            </strong>
                                            { { $sale->customer->address ?? '-' }}
                                        </div-->
                                    </td>

                                </table>

                            </div>

                        </td>

                        <td style="
                            width:4%;
                            border:none;
                        "></td>

                        {{-- DETAILS --}}
                        <td style="
                            width:48%;
                            border:none;
                            vertical-align:top;
                        ">

                            <div class="invoice-box">

                                <!--div class="invoice-title">
                                    Invoice Details
                                </div-->

                                <div class="invoice-title">
                                    Détails de la facture
                                </div>

                                <table class="no-border" style="
                                    width:100%;
                                    border-collapse:collapse;
                                ">
                                    <td style="
                                        border:none;
                                        vertical-align:top;
                                        ">

                                        <div>
                                            <strong>
                                                Facture :
                                            </strong>
                                            {{ $sale->invoice_number }}
                                        </div>

                                        <div>
                                            <strong>
                                                Date :
                                            </strong>
                                            {{ $sale->created_at->format('d/m/Y') }}
                                        </div>

                                        <!--div>
                                            <strong>
                                                Status :
                                            </strong>
                                            { { ucfirst($sale->status) }}
                                        </div-->

                                        <div>

                                            <strong>
                                                Status :
                                            </strong>

                                            @if($sale->status == 'paid')

                                                <span class="badge bg-success">
                                                    PAYÉ
                                                </span>

                                            @elseif($sale->status == 'partial')

                                                <span class="badge bg-warning">
                                                    PARTIEL
                                                </span>

                                            @elseif($sale->status == 'vendu')

                                                <span class="badge bg-primary">
                                                    VENDU
                                                </span>

                                            @elseif($sale->status == 'cancelled')

                                                <span class="badge bg-danger">
                                                    ANNULÉE
                                                </span>

                                            @else

                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($sale->status) }}
                                                </span>

                                            @endif

                                        </div>

                                        <div>
                                            <strong>
                                                Paiement :
                                            </strong>
                                            {{ ucfirst($sale->payment_type) }}
                                        </div>
                                    </td>

                                </table>

                            </div>

                        </td>

                    </tr>

                </table>

                @if($sale->status == 'cancelled')
                    <div class="alert alert-danger mb-4">
                        <strong>
                            Cette facture a été annulée.
                        </strong>

                        <br>

                        Tous les produits ont été retournés au stock.

                    </div>
                @endif
                {{-- TABLE PRODUITS --}}
                <div class="table-responsive">

                    <table>

                        <thead>

                            <tr>

                                <th class="text-center">
                                    #
                                </th>

                                <th class="text-center">
                                    Référence
                                </th>

                                <th>
                                    Désignation
                                </th>

                                <th class="text-center">
                                    Quantité
                                </th>

                                <th class="text-end">
                                    Prix Unitaire
                                </th>

                                <th class="text-end">
                                    TVA
                                </th>

                                <th class="text-end">
                                    Total
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($sale->items as $index => $item)

                                @php
                                    $lineTotal = $item->quantity * $item->price;
                                    $vat = $lineTotal * 0.10;
                                @endphp

                                <tr>

                                    <td class="text-center">
                                        {{ $index + 1 }}
                                    </td>

                                    <td class="text-center">
                                        {{ $item->product->reference ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $item->product->designation ?? '-' }}
                                    </td>

                                   <td class="text-center">

                                        {{ $item->quantity }}
                                        {{ $item->product->unit_label ?? 'Pièce' }}

                                    </td>

                                   <td class="text-end">
                                       {{ number_format(round($item->price), 0, ',', ' ') }} FDJ
                                        / {{ $item->product->unit_label ?? 'Pièce' }}
                                    </td>

                                    <td class="text-end">
                                      {{ number_format(round($vat), 0, ',', ' ') }} FDJ
                                    </td>

                                    <td class="text-end fw-bold">
                                      {{ number_format(round($lineTotal + $vat), 0, ',', ' ') }} FDJ
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

                {{-- TOTALS --}}
                <div class="row mt-4">

                    <div class="col-md-7"></div>

                    <div class="col-md-5">

                        <table class="no-border">

                            <tr>

                                <td class="fw-bold">
                                    Sous-total :
                                </td>

                                <td class="text-end">
                                {{ number_format(round($sale->subtotal), 0, ',', ' ') }} FDJ
                                </td>

                            </tr>

                            <tr>
                                <td style="padding:8px 0;font-weight:bold;">
                                    Remise :
                                </td>

                                <td style="padding:8px 0;text-align:right;color:red;">

                                    - {{ number_format(round($sale->discount_amount), 0, ',', ' ') }} FDJ
                                    ({{ number_format($sale->discount, 2, ',', ' ') }}%)

                                </td>
                            </tr>

                            <tr>

                                <td class="fw-bold">
                                    TVA (10%) :
                                </td>

                                <td class="text-end">
                                  {{ number_format(round($sale->tva), 0, ',', ' ') }} FDJ
                                </td>

                            </tr>

                            <tr class="total-box">

                                <td>
                                    TOTAL :
                                </td>

                                <td class="text-end text-primary">
                                   {{ number_format(round($sale->total), 0, ',', ' ') }} FDJ
                                </td>

                            </tr>

                        </table>

                    </div>

                </div>

                {{-- AMOUNT WORDS --}}
                <div style="
                    border:2px solid #000;
                    padding:12px;
                    margin-top:25px;
                    font-weight:700;
                ">

                    Montant en lettres :

                    *****************

                   {{ $totalInWords }}

                </div>

                {{-- PAIEMENTS --}}
                <div class="mt-5">

                    <h4 class="mb-3 fw-bold">
                        Paiements
                    </h4>

                    <table>

                        <thead>

                            <tr>

                                <th style="width:30%;">
                                    Date
                                </th>

                                <th style="width:35%;">
                                    Méthode
                                </th>

                                <th class="text-end">
                                    Montant
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($sale->payments as $payment)

                                <tr>

                                    <td>
                                        {{ $payment->created_at->format('d/m/Y') }}
                                    </td>

                                    <td>
                                        {{ ucfirst($payment->method) }}
                                    </td>

                                    <td class="text-end fw-bold">
                                       {{ number_format(round($payment->amount), 0, ',', ' ') }} FDJ
                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="3"
                                        class="text-center text-muted">

                                        Aucun paiement enregistré

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                @php
                    $paid = $sale->payments->sum('amount');
                    $remaining = $sale->total - $paid;
                @endphp

                {{-- RESUME --}}
                <div class="text-end mt-4">

                    <h5>

                        Payé :

                        <strong>
                          {{ number_format(round($paid), 0, ',', ' ') }} FDJ
                        </strong>

                    </h5>

                    <h4>

                        Reste :

                        <strong class="{{ $remaining > 0 ? 'text-danger' : 'text-success' }}">

                           {{ number_format(round($remaining), 0, ',', ' ') }} FDJ

                        </strong>

                    </h4>

                </div>

                {{-- FORMULAIRE --}}
              @if($remaining > 0 && $sale->status !== 'cancelled')

                <form action="{{ route('sales.payment', $sale) }}"
                    method="POST"
                    class="mt-4 print-hide">

                    @csrf

                    <div class="row">

                        <div class="col-md-4">

                            <input type="number"
                                step="1"
                                name="amount"
                                max="{{ $remaining }}"
                                class="form-control form-control-lg"
                                placeholder="Montant"
                                required>

                        </div>

                        <div class="col-md-4">

                            <select name="method"
                                    class="form-select form-select-lg">

                                <option value="cash">
                                    Cash
                                </option>

                                <option value="bank">
                                    Banque
                                </option>

                            </select>

                        </div>

                        <div class="col-md-4">

                            <button class="btn btn-success btn-lg w-100">

                                PAYER LA FACTURE

                            </button>

                        </div>

                    </div>

                </form>

                @endif

                {{-- ACTIONS --}}
                <div class="text-end mt-5 print-hide">

                    @if($sale->status !== 'cancelled')

                        <form action="{{ route('sales.cancel', $sale) }}"
                            method="POST"
                            class="d-inline">

                            @csrf

                            @method('PUT')

                            <button type="submit"
                                    class="btn btn-warning btn-lg"
                                    onclick="return confirm('Annuler cette facture ?')">

                                ❌ Annuler la facture

                            </button>

                        </form>

                    @endif

                    <a href="{{ route('sales.invoice', $sale) }}"
                    class="btn btn-danger btn-lg">

                        📄 Télécharger PDF

                    </a>

                    <button onclick="window.print()"
                            class="btn btn-dark btn-lg">

                        🖨 Imprimer

                    </button>

                </div>

            </div>

        </div>

@endsection
