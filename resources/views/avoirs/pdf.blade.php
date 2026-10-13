@php
    $sale = $avoir->sale;
    $fdj = fn ($v) => number_format(round((float) $v), 0, ',', ' ');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Avoir {{ $avoir->numero }}</title>

    <style>
        /*
         | AVOIR — PDF
         | Annule en totalité la facture d'origine (montants repris en négatif).
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
        .document-title { text-align: right; font-size: 22px; font-weight: bold; color: #b42318; letter-spacing: 1px; }
        .document-number { margin-top: 3px; text-align: right; font-size: 11px; font-weight: bold; color: #475569; }
        .separator { height: 2px; margin-bottom: 14px; background: #b42318; }

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

        .motif { margin-top: 8px; padding: 7px 9px; border: 1px solid #fecdca; background: #fef3f2; color: #7a271a; }

        .lines-table th {
            padding: 6px;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
            font-size: 8.5px;
            text-transform: uppercase;
            text-align: left;
            color: #334155;
        }

        .lines-table td { padding: 6px; border: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .negatif { color: #b42318; }

        .totals { width: 45%; margin-left: 55%; margin-top: 12px; }
        .totals td { padding: 5px 7px; border: 1px solid #e2e8f0; }
        .totals .grand td { font-size: 13px; font-weight: bold; border-top: 2px solid #b42318; }

        .rembourse { margin-top: 14px; padding: 8px 10px; border: 1px solid #e2e8f0; background: #f8fafc; }
        .footer { margin-top: 26px; font-size: 8px; color: #94a3b8; text-align: center; }
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
            <div class="document-title">AVOIR</div>
            <div class="document-number">N° {{ $avoir->numero }}</div>
            <div class="document-number">Date : {{ $avoir->created_at->format('d/m/Y') }}</div>
        </td>
    </tr>
</table>

<div class="separator"></div>

<table class="info-table">
    <tr>
        <td>
            <div class="info-label">Client</div>
            <div class="info-value">{{ $avoir->customer->name ?? 'Client non renseigné' }}</div>
            <div>{{ $avoir->customer->phone ?? '' }}</div>
        </td>
        <td>
            <div class="info-label">Facture annulée</div>
            <div class="info-value">{{ $sale->invoice_number ?? '—' }}</div>
            <div>du {{ optional($sale?->created_at)->format('d/m/Y') }}</div>
        </td>
        <td>
            <div class="info-label">Véhicule</div>
            <div class="info-value">{{ $sale?->vehicle?->plate_number ?? '—' }}</div>
            <div>Émis par : {{ $avoir->creePar->name ?? '—' }}</div>
        </td>
    </tr>
</table>

<div class="motif"><strong>Motif de l'annulation :</strong> {{ $avoir->motif }}</div>

<div class="section-title">Lignes annulées</div>

<table class="lines-table">
    <thead>
        <tr>
            <th style="width:5%;" class="text-center">#</th>
            <th style="width:17%;">Référence</th>
            <th style="width:35%;">Désignation</th>
            <th style="width:13%;">Dépôt</th>
            <th style="width:8%;" class="text-right">Qté</th>
            <th style="width:11%;" class="text-right">P.U.</th>
            <th style="width:11%;" class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($avoir->lignes as $i => $ligne)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $ligne->reference ?: '—' }}</td>
                <td>{{ $ligne->designation ?: '—' }}</td>
                <td>{{ $ligne->depot->name ?? '—' }}</td>
                <td class="text-right">{{ rtrim(rtrim(number_format((float) $ligne->quantite, 2, ',', ' '), '0'), ',') }}</td>
                <td class="text-right">{{ $fdj($ligne->prix_unitaire) }}</td>
                <td class="text-right negatif">- {{ $fdj($ligne->total) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr>
        <td>Sous-total</td>
        <td class="text-right negatif">- {{ $fdj($avoir->subtotal) }} FDJ</td>
    </tr>
    @if((float) $avoir->discount_amount > 0)
        <tr>
            <td>Remise ({{ number_format((float) $avoir->discount, 2, ',', ' ') }} %)</td>
            <td class="text-right">+ {{ $fdj($avoir->discount_amount) }} FDJ</td>
        </tr>
    @endif
    <tr>
        <td>TVA</td>
        <td class="text-right negatif">- {{ $fdj($avoir->tva) }} FDJ</td>
    </tr>
    <tr class="grand">
        <td>TOTAL DE L'AVOIR</td>
        <td class="text-right negatif">- {{ $fdj($avoir->total) }} FDJ</td>
    </tr>
</table>

<div class="rembourse">
    <strong>Déjà encaissé sur la facture :</strong> {{ $fdj($avoir->montant_deja_paye) }} FDJ
    @if((float) $avoir->montant_a_rembourser > 0)
        <br>
        @if($avoir->rembourse_le)
            <strong>Remboursé au client :</strong> {{ $fdj($avoir->montant_a_rembourser) }} FDJ
            le {{ $avoir->rembourse_le->format('d/m/Y') }} ({{ $avoir->getModeRemboursementLabel() }})
        @else
            <strong>À rembourser au client :</strong> {{ $fdj($avoir->montant_a_rembourser) }} FDJ
        @endif
    @endif
</div>

<div class="footer">
    Cet avoir annule en totalité la facture {{ $sale->invoice_number ?? '' }}. Les pièces ont été remises en stock.
</div>

</body>
</html>
