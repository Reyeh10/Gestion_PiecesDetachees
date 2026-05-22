<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <title>Facture</title>

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

        *{
            box-sizing:border-box;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        td{
            border:1px solid #000;
            padding:6px;
            vertical-align:top;
        }

        th{
            background:#d9d9d9;
            border:1px solid #000;
            padding:8px;
            font-size:12px;
            text-transform:uppercase;
        }

        .no-border td{
            border:none !important;
        }

        .invoice-box{
            border:2px solid #000;
            padding:12px;
            min-height:auto;
        }

        .invoice-title{
            background:#d9d9d9;
            padding:8px;
            margin:-12px -12px 10px -12px;
            border-bottom:2px solid #000;
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
            margin-top:20px;
        }

        .mt-5{
            margin-top:25px;
        }

        .mb-3{
            margin-bottom:10px;
        }

        .mb-5{
            margin-bottom:25px;
        }

        .total-box td{
            border-top:3px solid #000 !important;
            font-size:24px;
            font-weight:700;
        }

        .small-line{
            line-height:1.2;
            margin:0;
        }

    </style>

</head>

<body>

<div style="width:100%; max-width:1000px; margin:auto;">

    {{-- HEADER --}}
    <table class="no-border mb-5" style="width:100%; border-collapse:collapse;">

        <tr>

            {{-- LEFT --}}
            <td style="
                width:65%;
                border:none !important;
                vertical-align:middle;
                padding:0 !important;
            ">

                <table class="no-border" style="
                    width:100%;
                    border-collapse:collapse;
                ">

                    <tr>

                        {{-- LOGO --}}
                        <td style="
                            width:170px;
                            border:none !important;
                            vertical-align:middle;
                            padding:0 15px 0 0 !important;
                        ">

                            <img src="{{ public_path('assets/img/logo/stcd.jpg') }}"
                                 alt="Logo"
                                 style="
                                    width:150px;
                                    display:block;
                                 ">

                        </td>

                        {{-- COMPANY --}}
                        <td style="
                            border:none !important;
                            vertical-align:middle;
                            padding:0 !important;
                        ">

                            <h1 style="
                                font-size:28px;
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

            {{-- RIGHT --}}
            <td style="
                width:35%;
                border:none !important;
                text-align:right;
                vertical-align:middle;
                padding:0 !important;
            ">

                <h1 style="
                    font-size:38px;
                    font-weight:900;
                    margin:0 0 10px 0;
                    color:#1f3c88;
                    letter-spacing:1px;
                ">
                    FACTURE
                </h1>

                <div class="small-line">
                    <strong>N° Facture :</strong>
                    {{ $sale->invoice_number }}
                </div>

                <div class="small-line">
                    <strong>Date :</strong>
                    {{ $sale->created_at->format('d/m/Y') }}
                </div>

            </td>

        </tr>

    </table>

    {{-- CLIENT + DETAILS --}}
    <table class="no-border mb-5">

        <tr>

            {{-- CLIENT --}}
            <td style="
                width:48%;
                border:none;
                vertical-align:top;
            ">

                <div class="invoice-box">

                    <div class="invoice-title">
                        Invoice To
                    </div>

                    <div style="
                        font-size:20px;
                        font-weight:700;
                        margin-bottom:10px;
                    ">
                        {{ $sale->customer->name ?? 'Vente comptoir' }}
                    </div>

                    <div style="line-height:1.5;">
                        <strong>Téléphone :</strong>
                        {{ $sale->customer->phone ?? '-' }}
                    </div>

                    <div style="line-height:1.5;">
                        <strong>Email :</strong>
                        {{ $sale->customer->email ?? '-' }}
                    </div>

                    <!--div style="line-height:1.5;">
                        <strong>Adresse :</strong>
                        { { $sale->customer->address ?? '-' }}
                    </div-->

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

                    <div class="invoice-title">
                        Invoice Details
                    </div>

                    <table class="no-border" style="
                        width:100%;
                        border-collapse:collapse;
                    ">

                        <tr>

                            <td style="
                                border:none;
                                padding:2px 0;
                                width:40%;
                            ">
                                <strong>Facture</strong>
                            </td>

                            <td style="
                                border:none;
                                padding:2px 0;
                            ">
                                {{ $sale->invoice_number }}
                            </td>

                        </tr>

                        <tr>

                            <td style="
                                border:none;
                                padding:2px 0;
                            ">
                                <strong>Date</strong>
                            </td>

                            <td style="
                                border:none;
                                padding:2px 0;
                            ">
                                {{ $sale->created_at->format('d/m/Y') }}
                            </td>

                        </tr>

                        <tr>

                            <td style="
                                border:none;
                                padding:2px 0;
                            ">
                                <strong>Status</strong>
                            </td>

                            <td style="
                                border:none;
                                padding:2px 0;
                            ">
                                {{ ucfirst($sale->status) }}
                            </td>

                        </tr>

                        <tr>

                            <td style="
                                border:none;
                                padding:2px 0;
                            ">
                                <strong>Paiement</strong>
                            </td>

                            <td style="
                                border:none;
                                padding:2px 0;
                            ">
                                {{ ucfirst($sale->payment_type) }}
                            </td>

                        </tr>

                    </table>

                </div>

            </td>

        </tr>

    </table>

    {{-- PRODUITS --}}
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
                        -
                    </td>

                    <td class="text-end fw-bold">
                  {{ number_format(round($lineTotal), 0, ',', ' ') }} FDJ
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

    {{-- TOTAL --}}
    <div class="mt-4">

        <table class="no-border">

            <tr>

                <td style="
                    border:none;
                    width:60%;
                "></td>

                <td style="
                    border:none;
                    width:40%;
                ">

                    <table class="no-border">

                        <tr>

                            <td style="border:none;">
                                <strong>Sous-total :</strong>
                            </td>

                            <td class="text-end" style="border:none;">
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

                            <td style="border:none;">
                                <strong>TVA (10%) :</strong>
                            </td>

                            <td class="text-end" style="border:none;">
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

                </td>

            </tr>

        </table>

    </div>

    {{-- MONTANT LETTRES --}}
    <div style="
        border:2px solid #000;
        padding:12px;
        margin-top:20px;
        font-weight:700;
    ">

        Montant en lettres :

        *********************

        {{ $totalInWords }}
    </div>

    {{-- PAIEMENTS --}}
    <div class="mt-5">

        <h3 style="
            margin-bottom:10px;
            color:#1f3c88;
        ">
            Paiements
        </h3>

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

                        <td colspan="3" class="text-center">
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

        <h4 style="margin-bottom:6px;">

            Payé :

            <strong>
               {{ number_format(round($paid), 0, ',', ' ') }} FDJ
            </strong>

        </h4>

        <h3>

            Reste :

            <strong class="{{ $remaining > 0 ? 'text-danger' : 'text-success' }}">

              {{ number_format(round($remaining), 0, ',', ' ') }} FDJ

            </strong>

        </h3>

    </div>

</div>

</body>
</html>
