<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">

    <title>
        Proforma {{ $proforma->proforma_number }}
    </title>

    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #2c3e50;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: 1px solid #d9dee3;
            padding: 7px;
            vertical-align: middle;
        }

        .no-border,
        .no-border td,
        .no-border th {
            border: none !important;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .header-table {
            margin-bottom: 22px;
        }

        .company-name {
            margin: 0;
            font-size: 25px;
            font-weight: 800;
            color: #1f3a93;
        }

        .company-info {
            margin-top: 8px;
            line-height: 1.7;
            color: #566a7f;
        }

        .title {
            font-size: 40px;
            font-weight: 800;
            color: #1f3a93;
            margin: 0;
        }

        .proforma-info {
            margin-top: 12px;
            line-height: 1.8;
        }

        .section-title {
            background: #eef1ff;
            color: #344054;
            padding: 8px;
            font-weight: bold;
            border: 1px solid #d9dee3;
        }

        .box {
            border: 1px solid #d9dee3;
            padding: 12px;
            min-height: 105px;
            line-height: 1.7;
        }

        .products-table {
            margin-top: 10px;
        }

        .products-table thead th {
            background: #f4f6f8;
            color: #566a7f;
            font-size: 10px;
            text-transform: uppercase;
        }

        .products-table tbody td {
            font-size: 10px;
        }

        .depot-name {
            font-weight: bold;
            color: #566a7f;
        }

        .unit-label {
            color: #8592a3;
            font-size: 9px;
        }

        .total-box {
            width: 340px;
            margin-left: auto;
            margin-top: 20px;
        }

        .total-box td {
            padding: 7px 5px;
        }

        .grand-total-row {
            border-top: 2px solid #696cff;
        }

        .footer-total {
            font-size: 22px;
            font-weight: 800;
            color: #696cff;
        }

        .note {
            margin-top: 25px;
            padding: 10px 12px;
            border: 1px solid #d9dee3;
            background: #f8f9fa;
            color: #566a7f;
            font-size: 10px;
            line-height: 1.6;
        }

        .status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            background: #eef1ff;
            color: #696cff;
            font-weight: bold;
        }
    </style>
</head>

<body>

{{-- ============================================================
     EN-TÊTE
============================================================ --}}
<table class="no-border header-table">
    <tr>

        {{-- LOGO --}}
        <td
            style="
                width: 130px;
                vertical-align: top;
            "
        >
            @php
                $logoPath = public_path(
                    'assets/img/logo/stcd.jpg'
                );
            @endphp

            @if(file_exists($logoPath))
                <img
                    src="{{ $logoPath }}"
                    style="width: 110px;"
                    alt="STCD Motors"
                >
            @endif
        </td>

        {{-- SOCIÉTÉ --}}
        <td
            style="
                vertical-align: top;
            "
        >
            <h1 class="company-name">
                STCD MOTORS
            </h1>

            <div class="company-info">
                Djibouti
            </div>
        </td>

        {{-- PROFORMA --}}
        <td
            style="
                width: 300px;
                text-align: right;
                vertical-align: top;
            "
        >
            <div class="title">
                PROFORMA
            </div>

            <div class="proforma-info">

                <strong>
                    N° Proforma :
                </strong>

                {{ $proforma->proforma_number }}

                <br>

                <strong>
                    Date :
                </strong>

                {{
                    optional($proforma->created_at)
                        ->format('d/m/Y')
                }}

                <br>

                <strong>
                    Statut :
                </strong>

                <span class="status">
                    {{ $proforma->status ?? 'Validé' }}
                </span>

            </div>
        </td>

    </tr>
</table>


{{-- ============================================================
     CLIENT + DÉTAILS DU PROFORMA
============================================================ --}}
<table
    class="no-border"
    style="margin-bottom: 22px;"
>
    <tr>

        {{-- CLIENT --}}
        <td
            style="
                width: 48%;
                vertical-align: top;
            "
        >
            <div class="section-title">
                Client
            </div>

            <div class="box">

                <strong>
                    {{
                        optional($proforma->customer)->name
                        ?? 'Client non renseigné'
                    }}
                </strong>

                <br><br>

                Téléphone :

                {{
                    optional($proforma->customer)->phone
                    ?? '-'
                }}

                <br>

                Email :

                {{
                    optional($proforma->customer)->email
                    ?? '-'
                }}

                <br>

                Adresse :

                {{
                    optional($proforma->customer)->address
                    ?? '-'
                }}

            </div>
        </td>

        <td style="width: 4%;"></td>

        {{-- DÉTAILS --}}
        <td
            style="
                width: 48%;
                vertical-align: top;
            "
        >
            <div class="section-title">
                Détails du proforma
            </div>

            <div class="box">

                <table class="no-border">

                    <tr>
                        <td class="fw-bold">
                            Proforma :
                        </td>

                        <td>
                            {{ $proforma->proforma_number }}
                        </td>
                    </tr>

                    <tr>
                        <td class="fw-bold">
                            Date :
                        </td>

                        <td>
                            {{
                                optional($proforma->created_at)
                                    ->format('d/m/Y')
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
                            {{
                                !empty($proforma->payment_type)
                                    ? ucfirst(
                                        $proforma->payment_type
                                    )
                                    : '-'
                            }}
                        </td>
                    </tr>

                    <tr>
                        <td class="fw-bold">
                            Immatriculation :
                        </td>

                        <td>
                            {{
                                optional($proforma->vehicle)
                                    ->registration_number
                                ??
                                optional($proforma->vehicle)
                                    ->immatriculation
                                ??
                                '-'
                            }}
                        </td>
                    </tr>

                </table>

            </div>
        </td>

    </tr>
</table>


{{-- ============================================================
     PRODUITS
============================================================ --}}
<table class="products-table">

    <thead>

        <tr>

            <th
                style="
                    width: 28px;
                "
                class="text-center"
            >
                #
            </th>

            <th>
                Référence
            </th>

            <th>
                Désignation
            </th>

            <th>
                Dépôt
            </th>

            <th
                style="width: 70px;"
                class="text-end"
            >
                Quantité
            </th>

            <th
                style="width: 90px;"
                class="text-end"
            >
                Prix unitaire
            </th>

            <th
                style="width: 95px;"
                class="text-end"
            >
                Total
            </th>

        </tr>

    </thead>

    <tbody>

        @forelse($proforma->items as $item)

            @php
                $unitLabel =
                    optional($item->product)->unit_label
                    ?? 'Pièce';

                $lineTotal =
                    (float) $item->quantity
                    *
                    (float) $item->price;
            @endphp

            <tr>

                <td class="text-center">
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{
                        optional($item->product)->reference
                        ?? '-'
                    }}
                </td>

                <td>

                    {{
                        optional($item->product)->designation
                        ?? '-'
                    }}

                    <br>

                    <span class="unit-label">
                        Unité :
                        {{ $unitLabel }}
                    </span>

                </td>

                <td>

                    @if($item->depot)

                        <span class="depot-name">
                            {{ $item->depot->name }}
                        </span>

                        @if(!empty($item->depot->code))

                            <br>

                            <span class="unit-label">
                                {{ $item->depot->code }}
                            </span>

                        @endif

                    @else

                        <span class="unit-label">
                            Non renseigné
                        </span>

                    @endif

                </td>

                <td class="text-end">

                    {{
                        number_format(
                            (float) $item->quantity,
                            2,
                            ',',
                            ' '
                        )
                    }}

                    <br>

                    <span class="unit-label">
                        {{ $unitLabel }}
                    </span>

                </td>

                <td class="text-end">

                    {{
                        number_format(
                            (float) $item->price,
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
                            $lineTotal,
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
                >
                    Aucun produit dans ce proforma.
                </td>

            </tr>

        @endforelse

    </tbody>

</table>


{{-- ============================================================
     TOTAUX
============================================================ --}}
@php
    $subtotal =
        (float) (
            $proforma->subtotal
            ?? $proforma->items->sum(
                function ($item) {
                    return
                        (float) $item->quantity
                        *
                        (float) $item->price;
                }
            )
        );

    $discountRate =
        max(
            0,
            min(
                100,
                (float) ($proforma->discount ?? 0)
            )
        );

    $discountAmount =
        (float) ($proforma->discount_amount ?? 0);

    if (
        $discountAmount <= 0
        &&
        $discountRate > 0
    ) {
        $discountAmount =
            round(
                $subtotal
                *
                $discountRate
                /
                100,
                2
            );
    }

    $taxableAmount =
        max(
            0,
            $subtotal - $discountAmount
        );

    $tva =
        (float) ($proforma->tva ?? 0);

    if ($tva <= 0) {
        $tva =
            round(
                $taxableAmount * 0.10,
                2
            );
    }

    $total =
        (float) ($proforma->total ?? 0);

    if ($total <= 0) {
        $total =
            round(
                $taxableAmount + $tva,
                2
            );
    }
@endphp


<div class="total-box">

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
                Remise ({{ number_format($discountRate, 2, ',', ' ') }} %) :
            </td>

            <td
                class="text-end"
                style="color: #dc3545;"
            >

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

            <td
                class="fw-bold"
                style="font-size: 16px;"
            >
                TOTAL :
            </td>

            <td class="text-end footer-total">

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


{{-- ============================================================
     NOTE
============================================================ --}}
<div class="note">

    <strong>Important :</strong>

    Ce document est un proforma et ne constitue pas encore
    une vente définitive.

    <br>

    La création du proforma ne diminue pas le stock.
    Le stock sera prélevé dans le dépôt sélectionné
    uniquement lors de la conversion du proforma en vente.

</div>

</body>

</html>
