<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <title>
        Bon de transfert {{ $bt->numero }}
    </title>

    <style>
        /*
        |--------------------------------------------------------------------------
        | BON DE TRANSFERT — PDF
        |--------------------------------------------------------------------------
        |
        | Document transmis du magasin vers l'Atelier.
        | Le BT constate le transfert physique des pièces.
        | Ce document n'est ni une facture ni un document de paiement.
        |
        */

        @page {
            margin: 22px 25px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.35;
            color: #263238;
        }

        .header-table,
        .info-table,
        .lines-table,
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table {
            margin-bottom: 18px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
        }

        .company-subtitle {
            margin-top: 3px;
            font-size: 9px;
            color: #64748b;
        }

        .document-title {
            text-align: right;
            font-size: 19px;
            font-weight: bold;
            color: #1f2937;
        }

        .document-number {
            margin-top: 3px;
            text-align: right;
            font-size: 11px;
            font-weight: bold;
            color: #475569;
        }

        .separator {
            height: 2px;
            margin-bottom: 15px;
            background: #334155;
        }

        .section-title {
            margin: 14px 0 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid #dbe2ea;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #334155;
        }

        .info-table td {
            width: 25%;
            padding: 6px 7px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .info-label {
            margin-bottom: 2px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .info-value {
            font-size: 9.5px;
            font-weight: bold;
            color: #334155;
        }

        .info-secondary {
            margin-top: 2px;
            font-size: 8px;
            color: #64748b;
        }

        .lines-table {
            margin-top: 6px;
        }

        .lines-table th {
            padding: 6px 5px;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
            font-size: 8px;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            color: #475569;
        }

        .lines-table td {
            padding: 6px 5px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .reference {
            font-weight: bold;
        }

        .total-wrapper {
            width: 100%;
            margin-top: 10px;
            text-align: right;
        }

        .total-box {
            display: inline-block;
            min-width: 210px;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
        }

        .total-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
        }

        .total-value {
            margin-top: 2px;
            font-size: 15px;
            font-weight: bold;
            color: #1e293b;
        }

        .notice {
            margin-top: 15px;
            padding: 8px 10px;
            border: 1px solid #dbe2ea;
            background: #f8fafc;
            font-size: 8px;
            color: #64748b;
        }

        .signature-section {
            margin-top: 28px;
        }

        .footer-table td {
            width: 50%;
            padding: 0 12px;
            vertical-align: top;
            text-align: center;
        }

        .signature-title {
            font-size: 9px;
            font-weight: bold;
            color: #334155;
        }

        .signature-space {
            height: 48px;
        }

        .signature-line {
            border-top: 1px solid #94a3b8;
            padding-top: 4px;
            font-size: 8px;
            color: #64748b;
        }

        .footer-note {
            margin-top: 25px;
            padding-top: 7px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 7.5px;
            color: #94a3b8;
        }
    </style>
</head>

<body>

@php
    /*
    |--------------------------------------------------------------------------
    | Données du document
    |--------------------------------------------------------------------------
    */

    $bc = $bt->bonCommande;

    $vehicule = trim(
        ($bc?->vehicule_marque ?? '')
        . ' '
        . ($bc?->vehicule_modele ?? '')
    );

    $depots = $bt->lignes
        ->map(fn ($ligne) => $ligne->depot?->name)
        ->filter()
        ->unique()
        ->values()
        ->implode(', ');
@endphp

{{-- =====================================================================
     EN-TÊTE
     ===================================================================== --}}
<table class="header-table">
    <tr>
        <td style="width: 50%;">
            <div class="company-name">
                STCD Motors
            </div>

            <div class="company-subtitle">
                Magasin de pièces détachées
            </div>
        </td>

        <td style="width: 50%;">
            <div class="document-title">
                BON DE TRANSFERT
            </div>

            <div class="document-number">
                {{ $bt->numero }}
            </div>
        </td>
    </tr>
</table>

<div class="separator"></div>

{{-- =====================================================================
     INFORMATIONS DU TRANSFERT
     ===================================================================== --}}
<div class="section-title">
    Informations du transfert
</div>

<table class="info-table">
    <tr>
        <td>
            <div class="info-label">
                Bon de transfert
            </div>

            <div class="info-value">
                {{ $bt->numero }}
            </div>
        </td>

        <td>
            <div class="info-label">
                Bon de commande
            </div>

            <div class="info-value">
                {{ $bc?->numero ?: '—' }}
            </div>
        </td>

        <td>
            <div class="info-label">
                Date
            </div>

            <div class="info-value">
                {{ $bt->created_at?->format('d/m/Y') ?: '—' }}
            </div>

            <div class="info-secondary">
                {{ $bt->created_at?->format('H:i') ?: '' }}
            </div>
        </td>

        <td>
            <div class="info-label">
                Créé par
            </div>

            <div class="info-value">
                {{ $bt->user?->name ?: '—' }}
            </div>
        </td>
    </tr>

    <tr>
        <td>
            <div class="info-label">
                Client
            </div>

            <div class="info-value">
                {{ $bc?->client_nom ?: '—' }}
            </div>

            @if($bc?->client_telephone)
                <div class="info-secondary">
                    {{ $bc->client_telephone }}
                </div>
            @endif
        </td>

        <td>
            <div class="info-label">
                Véhicule
            </div>

            <div class="info-value">
                {{ $vehicule !== '' ? $vehicule : '—' }}
            </div>
        </td>

        <td>
            <div class="info-label">
                Immatriculation
            </div>

            <div class="info-value">
                {{ $bc?->vehicule_immatriculation ?: '—' }}
            </div>

            @if($bc?->vehicule_vin)
                <div class="info-secondary">
                    VIN : {{ $bc->vehicule_vin }}
                </div>
            @endif
        </td>

        <td>
            <div class="info-label">
                Dépôt(s)
            </div>

            <div class="info-value">
                {{ $depots !== '' ? $depots : '—' }}
            </div>
        </td>
    </tr>
</table>

{{-- =====================================================================
     PIÈCES TRANSFÉRÉES
     ===================================================================== --}}
<div class="section-title">
    Pièces transférées
</div>

<table class="lines-table">

    <thead>
        <tr>
            <th style="width: 5%;" class="text-center">
                #
            </th>

            <th style="width: 16%;">
                Référence
            </th>

            <th style="width: 29%;">
                Désignation
            </th>

            <th style="width: 16%;">
                Dépôt
            </th>

            <th style="width: 9%;" class="text-right">
                Qté
            </th>

            <th style="width: 12%;" class="text-right">
                P.U.
            </th>

            <th style="width: 13%;" class="text-right">
                Total
            </th>
        </tr>
    </thead>

    <tbody>

        @forelse($bt->lignes as $ligne)

            <tr>
                <td class="text-center">
                    {{ $ligne->position }}
                </td>

                <td class="reference">
                    {{ $ligne->product?->reference ?: '—' }}
                </td>

                <td>
                    {{
                        $ligne->designation_garage
                        ?: $ligne->product?->designation
                        ?: '—'
                    }}
                </td>

                <td>
                    {{ $ligne->depot?->name ?: '—' }}
                </td>

                <td class="text-right">
                    {{
                        number_format(
                            (float) $ligne->quantite,
                            2,
                            ',',
                            ' '
                        )
                    }}
                </td>

                <td class="text-right">
                    {{
                        number_format(
                            (float) $ligne->prix_unitaire,
                            2,
                            ',',
                            ' '
                        )
                    }}
                </td>

                <td class="text-right">
                    <strong>
                        {{
                            number_format(
                                (float) $ligne->total,
                                2,
                                ',',
                                ' '
                            )
                        }}
                    </strong>
                </td>
            </tr>

        @empty

            <tr>
                <td
                    colspan="7"
                    class="text-center"
                >
                    Aucune pièce dans ce bon de transfert.
                </td>
            </tr>

        @endforelse

    </tbody>

</table>

{{-- =====================================================================
     TOTAL
     ===================================================================== --}}
<div class="total-wrapper">

    <div class="total-box">

        <div class="total-label">
            Valeur totale des pièces transférées
        </div>

        <div class="total-value">
            {{
                number_format(
                    (float) $bt->total,
                    2,
                    ',',
                    ' '
                )
            }}
        </div>

    </div>

</div>

{{-- =====================================================================
     NOTE
     ===================================================================== --}}
<div class="notice">
    <strong>Important :</strong>
    ce bon de transfert matérialise la sortie des pièces du magasin
    et leur transfert vers l'Atelier. Il ne constitue ni une facture
    ni une preuve de paiement.
</div>

{{-- =====================================================================
     SIGNATURES
     ===================================================================== --}}
<div class="signature-section">

    <table class="footer-table">
        <tr>
            <td>
                <div class="signature-title">
                    Magasin
                </div>

                <div class="signature-space"></div>

                <div class="signature-line">
                    Nom, signature et date
                </div>
            </td>

            <td>
                <div class="signature-title">
                    Atelier / Réception
                </div>

                <div class="signature-space"></div>

                <div class="signature-line">
                    Nom, signature et date
                </div>
            </td>
        </tr>
    </table>

</div>

<div class="footer-note">
    Document généré automatiquement par le système de gestion des pièces détachées
    — {{ $bt->numero }}
</div>

</body>
</html>
