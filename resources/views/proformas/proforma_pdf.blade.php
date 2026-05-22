<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">

    <title>PROFORMA</title>

    <style>

        body{
            font-family: DejaVu Sans, sans-serif;
            font-size:12px;
            color:#2c3e50;
            margin:20px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        table th,
        table td{
            border:1px solid #000;
            padding:6px;
        }

        .no-border td{
            border:none;
        }

        .text-end{
            text-align:right;
        }

        .fw-bold{
            font-weight:bold;
        }

        .title{
            font-size:52px;
            font-weight:800;
            color:#1f3a93;
            margin:0;
        }

        .section-title{
            background:#e9ecef;
            padding:8px;
            font-weight:bold;
            border:1px solid #000;
        }

        .box{
            border:1px solid #000;
            padding:15px;
            min-height:110px;
        }

        .total-box{
            width:320px;
            margin-left:auto;
            margin-top:20px;
        }

        .footer-total{
            font-size:38px;
            font-weight:800;
            color:#4169e1;
        }

    </style>
</head>

<body>

{{-- HEADER --}}
<table class="no-border" style="margin-bottom:25px;">

    <tr>

        {{-- LOGO --}}
        <td style="
            width:140px;
            vertical-align:top;
        ">

            <img src="{{ public_path('assets/img/logo/stcd.jpg') }}"
                 style="width:120px;">

        </td>

        {{-- COMPANY --}}
        <td style="
            vertical-align:top;
        ">

            <h1 style="
                margin:0;
                font-size:26px;
                font-weight:800;
                color:#1f3a93;
            ">
                STCD MOTORS
            </h1>

            <div style="margin-top:10px; line-height:1.7;">

                Laval, Québec, Canada<br>

                Téléphone : +1 xxx xxx xxxx<br>

                Email : contact@stcdmotors.com

            </div>

        </td>

        {{-- PROFORMA --}}
        <td style="
            width:320px;
            text-align:right;
            vertical-align:top;
        ">

            <div class="title">
                PROFORMA
            </div>

            <div style="
                margin-top:15px;
                line-height:1.8;
            ">

                <strong>
                    N° Proforma :
                </strong>

                {{ $sale->invoice_number }}

                <br>

                <strong>
                    Date :
                </strong>

                {{ $sale->created_at->format('d/m/Y') }}

            </div>

        </td>

    </tr>

</table>

{{-- CLIENT + DETAILS --}}
<table class="no-border" style="margin-bottom:25px;">

    <tr>

        {{-- CLIENT --}}
        <td style="
            width:48%;
            vertical-align:top;
        ">

            <div class="section-title">
                Facturé à
            </div>

            <div class="box">

                <strong>
                    {{ $sale->customer->name ?? 'Vente comptoir' }}
                </strong>

                <br><br>

                Téléphone :
                {{ $sale->customer->phone ?? '-' }}

                <br>

                Email :
                {{ $sale->customer->email ?? '-' }}

                <br>

                Adresse :
                {{ $sale->customer->address ?? '-' }}

            </div>

        </td>

        <td style="width:4%;"></td>

        {{-- DETAILS --}}
        <td style="
            width:48%;
            vertical-align:top;
        ">

            <div class="section-title">
                Détails du proforma
            </div>

            <div class="box">

                <table class="no-border">

                    <tr>
                        <td class="fw-bold">Proforma :</td>
                        <td>{{ $sale->invoice_number }}</td>
                    </tr>

                    <tr>
                        <td class="fw-bold">Date :</td>
                        <td>{{ $sale->created_at->format('d/m/Y') }}</td>
                    </tr>

                    <tr>
                        <td class="fw-bold">Statut :</td>
                        <td>PROFORMA</td>
                    </tr>

                    <tr>
                        <td class="fw-bold">Paiement :</td>
                        <td>{{ ucfirst($sale->payment_type) }}</td>
                    </tr>

                </table>

            </div>

        </td>

    </tr>

</table>

{{-- TABLE PRODUITS --}}
<table>

    <thead>

        <tr style="background:#f1f1f1;">

            <th>#</th>

            <th>Référence</th>

            <th>Désignation</th>

            <th>Quantité</th>

            <th>Prix unitaire</th>

            <th>TVA</th>

            <th>Total</th>

        </tr>

    </thead>

    <tbody>

        @foreach($sale->items as $item)

            <tr>

                <td>
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{ $item->product->reference ?? '-' }}
                </td>

               <td>

                    {{ $item->product->designation ?? '-' }}

                    <br>

                    <small>

                        Unité :
                        {{ $item->product->unit_label ?? 'Pièce' }}

                    </small>

                </td>

              <td class="text-end">

                    {{ number_format($item->quantity, 2, ',', ' ') }}

                    {{ $item->product->unit_label ?? 'Pièce' }}

                </td>
                <td class="text-end">

                    {{ number_format($item->price, 2, ',', ' ') }} $

                    / {{ $item->product->unit_label ?? 'Pièce' }}

                </td>
                <td class="text-end">

                    {{ number_format(($item->quantity * $item->price) * 0.10, 2, ',', ' ') }} $

                </td>

                <td class="text-end fw-bold">

                    {{ number_format($item->quantity * $item->price, 2, ',', ' ') }} $

                </td>

            </tr>

        @endforeach

    </tbody>

</table>

{{-- TOTALS --}}
<div class="total-box">

    <table class="no-border">

        <tr>
            <td class="fw-bold">Sous-total :</td>

            <td class="text-end">
                {{ number_format($sale->subtotal, 2, ',', ' ') }} $
            </td>
        </tr>

        <tr>
            <td class="fw-bold">Remise :</td>

            <td class="text-end" style="color:red;">

                - {{ number_format($sale->discount, 2, ',', ' ') }} $

            </td>
        </tr>

        <tr>
            <td class="fw-bold">TVA (10%) :</td>

            <td class="text-end">
                {{ number_format($sale->tva, 2, ',', ' ') }} $
            </td>
        </tr>

    </table>

    <hr>

    <table class="no-border">

        <tr>

            <td class="fw-bold" style="font-size:20px;">
                TOTAL :
            </td>

            <td class="text-end footer-total">

                {{ number_format($sale->total, 2, ',', ' ') }} $

            </td>

        </tr>

    </table>

</div>

{{-- TOTAL EN LETTRES --}}
<div style="
    border:2px solid #000;
    padding:12px;
    margin-top:25px;
    font-weight:700;
">

    Montant en lettres :

    ***********

    {{ $totalInWords }}

</div>

</body>
</html>
