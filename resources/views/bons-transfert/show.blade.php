@extends('layouts.layoutMaster')

@section('content')

<style>
    /* ================================================================
       BON DE TRANSFERT — DÉTAIL
       Vue compacte du transfert Magasin -> Atelier.
       ================================================================ */

    .bt-show-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 4px 18px rgba(0, 0, 0, .04);
    }

    .bt-show-header {
        padding: 20px 24px;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .bt-show-title {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: #25364a;
    }

    .bt-show-number {
        margin-top: 3px;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
    }

    .bt-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 7px;
    }

    .bt-btn {
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .bt-body {
        padding: 20px 24px 24px;
    }

    .bt-info-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 18px;
    }

    .bt-info-box {
        min-width: 0;
        padding: 12px 14px;
        border: 1px solid #edf1f5;
        border-radius: 11px;
        background: #fbfcfe;
    }

    .bt-info-label {
        margin-bottom: 4px;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .45px;
    }

    .bt-info-value {
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .bt-info-secondary {
        margin-top: 2px;
        color: #64748b;
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .bt-section-title {
        margin: 20px 0 9px;
        color: #334155;
        font-size: 14px;
        font-weight: 700;
    }

    .bt-table-wrap {
        width: 100%;
        overflow-x: auto;
        border: 1px solid #edf1f5;
        border-radius: 11px;
    }

    .bt-table {
        width: 100%;
        margin: 0;
        font-size: 12px;
    }

    .bt-table thead th {
        padding: 9px 10px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        white-space: nowrap;
    }

    .bt-table tbody td {
        padding: 10px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .bt-table tbody tr:last-child td {
        border-bottom: none;
    }

    .bt-reference {
        color: #334155;
        font-weight: 700;
        white-space: nowrap;
    }

    .bt-designation {
        min-width: 180px;
        color: #334155;
    }

    .bt-depot {
        color: #475569;
        font-weight: 600;
        white-space: nowrap;
    }

    .bt-number-cell {
        text-align: right;
        white-space: nowrap;
    }

    .bt-total-box {
        display: flex;
        justify-content: flex-end;
        margin-top: 12px;
    }

    .bt-total-content {
        min-width: 230px;
        padding: 12px 16px;
        border-radius: 10px;
        background: #f8fafc;
        text-align: right;
    }

    .bt-total-label {
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .bt-total-value {
        margin-top: 2px;
        color: #1e293b;
        font-size: 21px;
        font-weight: 800;
    }

    .bt-status-box {
        margin-top: 18px;
        padding: 13px 15px;
        border-radius: 11px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .bt-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    .bt-status-sent {
        background: rgba(34, 197, 94, .12);
        color: #16a34a;
    }

    .bt-status-error {
        background: rgba(239, 68, 68, .12);
        color: #dc2626;
    }

    .bt-status-pending {
        background: rgba(245, 158, 11, .12);
        color: #d97706;
    }

    .bt-error-detail {
        margin-top: 8px;
        padding: 8px 10px;
        border-radius: 8px;
        background: rgba(239, 68, 68, .07);
        color: #b91c1c;
        font-size: 12px;
        overflow-wrap: anywhere;
    }

    @media (max-width: 1100px) {
        .bt-info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .bt-show-header,
        .bt-body {
            padding: 16px;
        }

        .bt-info-grid {
            grid-template-columns: 1fr;
        }

        .bt-actions {
            justify-content: flex-start;
            margin-top: 12px;
        }

        .bt-total-content {
            width: 100%;
            min-width: 0;
        }
    }
</style>

@php
    /*
    |--------------------------------------------------------------------------
    | Données principales
    |--------------------------------------------------------------------------
    |
    | Le contrôleur charge déjà le bon de commande, les lignes,
    | les produits, les dépôts et l'utilisateur.
    |
    */

    $bc = $bt->bonCommande;

    $vehicule = trim(
        ($bc?->vehicule_marque ?? '')
        . ' '
        . ($bc?->vehicule_modele ?? '')
    );
@endphp

<div class="card bt-show-card">

    {{-- ============================================================
         EN-TÊTE DU BON DE TRANSFERT
         ============================================================ --}}
    <div class="bt-show-header">

        <div class="row align-items-center">

            <div class="col-lg-5">

                <h1 class="bt-show-title">
                    Bon de transfert
                </h1>

                <div class="bt-show-number">
                    {{ $bt->numero }}
                </div>

            </div>

            <div class="col-lg-7">

                <div class="bt-actions">

                    <a
                        href="{{ route('bons-transfert.index') }}"
                        class="btn btn-outline-secondary bt-btn"
                    >
                        <i class="bx bx-arrow-back"></i>
                        Retour
                    </a>

                    @if($bc)
                        <a
                            href="{{ route('fournisseur-commandes.show', $bc) }}"
                            class="btn btn-outline-primary bt-btn"
                        >
                            <i class="bx bx-file"></i>
                            Bon de commande
                        </a>
                    @endif

                    <a
                        href="{{ route('bons-transfert.pdf', $bt) }}"
                        class="btn btn-outline-danger bt-btn"
                        target="_blank"
                    >
                        <i class="bx bxs-file-pdf"></i>
                        PDF
                    </a>

                    <form
                        method="POST"
                        action="{{ route('bons-transfert.envoyer', $bt) }}"
                        class="m-0"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="btn btn-primary bt-btn"
                        >
                            <i class="bx bx-send"></i>

                            {{ $bt->envoye_atelier_at
                                ? 'Renvoyer à l’Atelier'
                                : 'Envoyer à l’Atelier'
                            }}
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

    <div class="bt-body">

        {{-- ============================================================
             MESSAGES
             ============================================================ --}}
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        {{-- ============================================================
             INFORMATIONS PRINCIPALES
             ============================================================ --}}
        <div class="bt-info-grid">

            <div class="bt-info-box">

                <div class="bt-info-label">
                    Bon de commande
                </div>

                <div class="bt-info-value">
                    {{ $bc?->numero ?: '—' }}
                </div>

                <div class="bt-info-secondary">
                    Source :
                    {{ $bc?->source_system ?: '—' }}
                </div>

            </div>

            <div class="bt-info-box">

                <div class="bt-info-label">
                    Date du transfert
                </div>

                <div class="bt-info-value">
                    {{ $bt->created_at?->format('d/m/Y') ?: '—' }}
                </div>

                <div class="bt-info-secondary">
                    {{ $bt->created_at?->format('H:i') ?: '' }}
                </div>

            </div>

            <div class="bt-info-box">

                <div class="bt-info-label">
                    Créé par
                </div>

                <div class="bt-info-value">
                    {{ $bt->user?->name ?: '—' }}
                </div>

            </div>

            <div class="bt-info-box">

                <div class="bt-info-label">
                    Nombre de lignes
                </div>

                <div class="bt-info-value">
                    {{ $bt->lignes->count() }}
                </div>

                <div class="bt-info-secondary">
                    {{
                        $bt->lignes->count() > 1
                            ? 'pièces transférées'
                            : 'pièce transférée'
                    }}
                </div>

            </div>

            <div class="bt-info-box">

                <div class="bt-info-label">
                    Client
                </div>

                <div class="bt-info-value">
                    {{ $bc?->client_nom ?: '—' }}
                </div>

                @if($bc?->client_telephone)
                    <div class="bt-info-secondary">
                        {{ $bc->client_telephone }}
                    </div>
                @endif

            </div>

            <div class="bt-info-box">

                <div class="bt-info-label">
                    Véhicule
                </div>

                <div class="bt-info-value">
                    {{ $vehicule !== '' ? $vehicule : '—' }}
                </div>

                @if($bc?->vehicule_vin)
                    <div class="bt-info-secondary">
                        VIN : {{ $bc->vehicule_vin }}
                    </div>
                @endif

            </div>

            <div class="bt-info-box">

                <div class="bt-info-label">
                    Immatriculation
                </div>

                <div class="bt-info-value">
                    {{ $bc?->vehicule_immatriculation ?: '—' }}
                </div>

            </div>

            <div class="bt-info-box">

                <div class="bt-info-label">
                    N° Bon transfert
                </div>

                <div class="bt-info-value">
                    {{ $bt->numero }}
                </div>

            </div>

        </div>

        {{-- ============================================================
             LIGNES DU BON DE TRANSFERT
             ============================================================ --}}
        <div class="bt-section-title">
            Pièces transférées
        </div>

        <div class="bt-table-wrap">

            <table class="table bt-table align-middle">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Référence</th>
                        <th>Désignation</th>
                        <th>Dépôt</th>
                        <th class="text-end">Quantité</th>
                        <th class="text-end">Prix unitaire</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($bt->lignes as $ligne)

                        <tr>

                            <td>
                                {{ $ligne->position }}
                            </td>

                            <td class="bt-reference">
                                {{ $ligne->product?->reference ?: '—' }}
                            </td>

                            <td class="bt-designation">

                                {{
                                    $ligne->designation_garage
                                    ?: $ligne->product?->designation
                                    ?: '—'
                                }}

                            </td>

                            <td class="bt-depot">
                                {{ $ligne->depot?->name ?: '—' }}
                            </td>

                            <td class="bt-number-cell">
                                {{
                                    number_format(
                                        (float) $ligne->quantite,
                                        2,
                                        ',',
                                        ' '
                                    )
                                }}
                            </td>

                            <td class="bt-number-cell">
                                {{
                                    number_format(
                                        (float) $ligne->prix_unitaire,
                                        2,
                                        ',',
                                        ' '
                                    )
                                }}
                            </td>

                            <td class="bt-number-cell fw-bold">
                                {{
                                    number_format(
                                        (float) $ligne->total,
                                        2,
                                        ',',
                                        ' '
                                    )
                                }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="7"
                                class="text-center text-muted py-4"
                            >
                                Aucune ligne dans ce bon de transfert.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- ============================================================
             TOTAL
             ============================================================ --}}
        <div class="bt-total-box">

            <div class="bt-total-content">

                <div class="bt-total-label">
                    Total du transfert
                </div>

                <div class="bt-total-value">
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

        {{-- ============================================================
             ÉTAT DE L'ENVOI VERS APP-ATELIER
             ============================================================ --}}
        <div class="bt-status-box">

            <div class="bt-info-label">
                Synchronisation avec l'Atelier
            </div>

            @if($bt->envoye_atelier_at)

                <span class="bt-status bt-status-sent">
                    <i class="bx bx-check-circle"></i>

                    Envoyé à l'Atelier
                </span>

                <div class="bt-info-secondary mt-1">
                    Dernier envoi :
                    {{ $bt->envoye_atelier_at->format('d/m/Y H:i') }}
                </div>

            @elseif($bt->envoi_atelier_erreur)

                <span class="bt-status bt-status-error">
                    <i class="bx bx-error-circle"></i>

                    Échec de l'envoi
                </span>

            @else

                <span class="bt-status bt-status-pending">
                    <i class="bx bx-time-five"></i>

                    Non envoyé
                </span>

            @endif

            @if($bt->envoi_atelier_erreur)

                <div class="bt-error-detail">
                    <strong>Détail :</strong>
                    {{ $bt->envoi_atelier_erreur }}
                </div>

            @endif

        </div>

    </div>

</div>

@endsection
