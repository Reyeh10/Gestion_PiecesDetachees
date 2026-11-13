@php
    $sale = $bl->sale;
    $qte = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ' '), '0'), ',');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de livraison {{ $bl->numero }}</title>

    <style>
        /*
         | BON DE LIVRAISON — PDF (sans prix)
         */
        @page { margin: 22px 25px; }
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.35;
            color: #263238;
        }

        table { width: 100%; border-collapse: collapse; }

        .header-table { margin-bottom: 14px; }
        .company-name { font-size: 18px; font-weight: bold; color: #1f2937; }
        .company-subtitle { margin-top: 3px; font-size: 9px; color: #64748b; }
        .document-title { text-align: right; font-size: 20px; font-weight: bold; color: #092C4C; letter-spacing: 1px; }
        .document-number { margin-top: 3px; text-align: right; font-size: 11px; font-weight: bold; color: #475569; }
        .separator { height: 2px; margin-bottom: 14px; background: #FE9F43; }

        .section-title {
            margin: 14px 0 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid #dbe2ea;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #334155;
        }

        .info-table td { width: 33%; padding: 6px 7px; border: 1px solid #e2e8f0; vertical-align: top; }
        .info-label { margin-bottom: 2px; font-size: 7.5px; font-weight: bold; text-transform: uppercase; color: #94a3b8; }
        .info-value { font-size: 9.5px; font-weight: bold; color: #334155; }

        .annule { margin-bottom: 10px; padding: 7px 9px; border: 1px solid #fecdca; background: #fef3f2; color: #b42318; font-weight: bold; text-align: center; }

        .lines-table th {
            padding: 6px;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
            font-size: 8.5px;
            text-transform: uppercase;
            text-align: left;
            color: #334155;
        }

        .lines-table td { padding: 7px 6px; border: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .notes { margin-top: 10px; padding: 7px 9px; border: 1px solid #e2e8f0; background: #f8fafc; }

        .signatures { margin-top: 28px; }
        .signatures td { width: 50%; height: 95px; padding: 8px 10px; border: 1px solid #cbd5e1; vertical-align: top; }
        .signature-titre { font-size: 9px; font-weight: bold; text-transform: uppercase; color: #334155; }
        .signature-aide { margin-top: 3px; font-size: 8px; color: #94a3b8; }

        .footer { margin-top: 22px; font-size: 8px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>

<table class="header-table">
    <tr>
        <td style="width:60%; vertical-align:middle;">
            <table>
                <tr>
                    <td style="width:70px; vertical-align:middle;">
                        <img src="{{ public_path('assets/img/logo/stcd.jpg') }}" style="width:60px;" alt="Logo">
                    </td>
                    <td style="vertical-align:middle;">
                        <div class="company-name">STCD MOTORS</div>
                        <div class="company-subtitle">
                            1667 Guelleh-Batal, Djibouti-ville · Tél. +253 77 22 93 33 · spareparts@stcd.dj
                        </div>
                    </td>
                </tr>
            </table>
        </td>
        <td style="width:40%; vertical-align:middle;">
            <div class="document-title">BON DE LIVRAISON</div>
            <div class="document-number">N° {{ $bl->numero }}</div>
            <div class="document-number">Livré le : {{ $bl->livre_le->format('d/m/Y') }}</div>
        </td>
    </tr>
</table>

<div class="separator"></div>

@if($bl->estAnnule())
    <div class="annule">BON DE LIVRAISON ANNULÉ (facture annulée)</div>
@endif

<table class="info-table">
    <tr>
        <td>
            <div class="info-label">Client</div>
            <div class="info-value">{{ $sale->customer->name ?? 'Client non renseigné' }}</div>
            <div>{{ $sale->customer->phone ?? '' }}</div>
        </td>
        <td>
            <div class="info-label">Facture</div>
            <div class="info-value">{{ $sale->invoice_number ?? '—' }}</div>
            <div>du {{ optional($sale->created_at)->format('d/m/Y') }}</div>
        </td>
        <td>
            <div class="info-label">Véhicule</div>
            <div class="info-value">{{ $sale->vehicle?->plate_number ?? '—' }}</div>
            <div>Établi par : {{ $bl->user->name ?? '—' }}</div>
        </td>
    </tr>
</table>

<div class="section-title">Pièces livrées</div>

<table class="lines-table">
    <thead>
        <tr>
            <th style="width:6%;" class="text-center">#</th>
            <th style="width:24%;">Référence</th>
            <th style="width:48%;">Désignation</th>
            <th style="width:11%;" class="text-right">Quantité</th>
            <th style="width:11%;">Unité</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bl->lignes as $i => $ligne)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $ligne->reference ?: '—' }}</td>
                <td>{{ $ligne->designation }}</td>
                <td class="text-right"><strong>{{ $qte($ligne->quantite) }}</strong></td>
                <td>{{ $ligne->unite ?: 'Pièce' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@if($bl->notes)
    <div class="notes"><strong>Observations :</strong> {{ $bl->notes }}</div>
@endif

<table class="signatures">
    <tr>
        <td>
            <div class="signature-titre">Livré par</div>
            <div class="signature-aide">STCD Motors — nom et signature</div>
        </td>
        <td>
            <div class="signature-titre">Reçu par{{ $bl->recu_par ? ' : ' . $bl->recu_par : '' }}</div>
            <div class="signature-aide">Nom, date et signature du client</div>
        </td>
    </tr>
</table>

<div class="footer">
    Document sans valeur comptable : les montants figurent sur la facture {{ $sale->invoice_number }}.
</div>

</body>
</html>
