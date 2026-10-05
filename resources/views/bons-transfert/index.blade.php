@extends('layouts.layoutMaster')

@section('content')

<style>
    /* ================================================================
       BONS DE TRANSFERT — LISTE
       Design compact cohérent avec les commandes reçues du garage.
       ================================================================ */

    .bt-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 4px 18px rgba(0, 0, 0, .04);
    }

    .bt-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        background: #fff;
    }

    .bt-title {
        margin: 0;
        font-size: 25px;
        font-weight: 700;
        color: #2c3e50;
    }

    .bt-subtitle {
        margin-top: 3px;
        color: #94a3b8;
        font-size: 13px;
    }

    .bt-filter {
        padding: 16px 24px 8px;
    }

    .bt-filter .form-control {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 13px;
    }

    .bt-filter .btn {
        border-radius: 9px;
        font-size: 13px;
    }

    .bt-table-wrap {
        padding: 8px 20px 20px;
        overflow-x: auto;
    }

    .bt-table {
        width: 100%;
        margin: 0;
        font-size: 13px;
    }

    .bt-table thead th {
        padding: 10px 12px;
        border-bottom: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        white-space: nowrap;
    }

    .bt-table tbody td {
        padding: 13px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .bt-table tbody tr:hover {
        background: #f8fafc;
    }

    .bt-number {
        font-weight: 700;
        color: #334155;
        white-space: nowrap;
    }

    .bt-muted {
        color: #94a3b8;
        font-size: 12px;
    }

    .bt-total {
        font-weight: 700;
        color: #334155;
        white-space: nowrap;
    }

    .bt-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
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

    .bt-btn {
        padding: 5px 9px;
        border-radius: 8px;
        font-size: 12px;
        white-space: nowrap;
    }

    .bt-empty {
        padding: 45px 20px !important;
        text-align: center;
        color: #94a3b8;
    }

    @media (max-width: 768px) {
        .bt-header {
            padding: 18px;
        }

        .bt-filter {
            padding: 14px 18px 6px;
        }

        .bt-table-wrap {
            padding: 6px 12px 16px;
        }

        .bt-title {
            font-size: 21px;
        }
    }
</style>

<div class="card bt-card">

    {{-- ============================================================
         EN-TÊTE
         ============================================================ --}}
    <div class="bt-header d-flex justify-content-between align-items-center flex-wrap gap-2">

        <div>
            <h1 class="bt-title">Bons de transfert</h1>

            <div class="bt-subtitle">
                Pièces transférées du magasin vers le garage
            </div>
        </div>

        <a
            href="{{ route('fournisseur-commandes.index') }}"
            class="btn btn-outline-secondary bt-btn"
        >
            <i class="bx bx-arrow-back"></i>
            Commandes garage
        </a>

    </div>

    {{-- ============================================================
         MESSAGES
         ============================================================ --}}
    @if(session('success'))
        <div class="alert alert-success mx-4 mt-3 mb-0">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mx-4 mt-3 mb-0">
            {{ session('error') }}
        </div>
    @endif

    {{-- ============================================================
         RECHERCHE
         ============================================================ --}}
    <div class="bt-filter">

        <form
            method="GET"
            action="{{ route('bons-transfert.index') }}"
            class="row g-2"
        >

            <div class="col-md-6 col-lg-5">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="N° BT, N° BC, client ou immatriculation..."
                >
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-primary bt-btn">
                    <i class="bx bx-search"></i>
                    Rechercher
                </button>
            </div>

            @if($search !== '')
                <div class="col-auto">
                    <a
                        href="{{ route('bons-transfert.index') }}"
                        class="btn btn-outline-secondary bt-btn"
                    >
                        Réinitialiser
                    </a>
                </div>
            @endif

        </form>

    </div>

    {{-- ============================================================
         TABLEAU
         ============================================================ --}}
    <div class="bt-table-wrap">

        <table class="table bt-table align-middle">

            <thead>
                <tr>
                    <th>Bon transfert</th>
                    <th>Bon commande</th>
                    <th>Client / véhicule</th>
                    <th>Dépôt / lignes</th>
                    <th class="text-end">Total</th>
                    <th>Créé par</th>
                    <th>Envoi Atelier</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>

            <tbody>

                @forelse($bons as $bt)

                    @php
                        $bc = $bt->bonCommande;
                    @endphp

                    <tr>

                        <td>
                            <div class="bt-number">
                                {{ $bt->numero }}
                            </div>

                            <div class="bt-muted">
                                {{ $bt->created_at?->format('d/m/Y H:i') }}
                            </div>
                        </td>

                        <td>
                            @if($bc)
                                <a
                                    href="{{ route('fournisseur-commandes.show', $bc) }}"
                                    class="fw-semibold"
                                >
                                    {{ $bc->numero }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>
                            <div class="fw-semibold">
                                {{ $bc?->client_nom ?: '—' }}
                            </div>

                            <div class="bt-muted">
                                {{ trim(($bc?->vehicule_marque ?? '') . ' ' . ($bc?->vehicule_modele ?? '')) ?: '—' }}

                                @if($bc?->vehicule_immatriculation)
                                    · {{ $bc->vehicule_immatriculation }}
                                @endif
                            </div>
                        </td>

                        <td>
                            <span class="fw-semibold">
                                {{ $bt->lignes_count }}
                            </span>

                            <span class="bt-muted">
                                {{ $bt->lignes_count > 1 ? 'lignes' : 'ligne' }}
                            </span>
                        </td>

                        <td class="text-end bt-total">
                            {{ number_format((float) $bt->total, 2, ',', ' ') }}
                        </td>

                        <td>
                            {{ $bt->user?->name ?: '—' }}
                        </td>

                        <td>
                            @if($bt->envoye_atelier_at)

                                <span class="bt-status bt-status-sent">
                                    <i class="bx bx-check-circle"></i>
                                    Envoyé
                                </span>

                                <div class="bt-muted mt-1">
                                    {{ $bt->envoye_atelier_at->format('d/m/Y H:i') }}
                                </div>

                            @elseif($bt->envoi_atelier_erreur)

                                <span class="bt-status bt-status-error">
                                    <i class="bx bx-error-circle"></i>
                                    Échec
                                </span>

                            @else

                                <span class="bt-status bt-status-pending">
                                    <i class="bx bx-time-five"></i>
                                    Non envoyé
                                </span>

                            @endif
                        </td>

                        <td class="text-end">

                            <a
                                href="{{ route('bons-transfert.show', $bt) }}"
                                class="btn btn-primary bt-btn"
                            >
                                <i class="bx bx-show"></i>
                                Voir
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8" class="bt-empty">
                            <i class="bx bx-transfer-alt fs-2 d-block mb-2"></i>

                            Aucun bon de transfert trouvé.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    {{-- ============================================================
         PAGINATION
         ============================================================ --}}
    @if($bons->hasPages())
        <div class="px-4 pb-3">
            {{ $bons->links() }}
        </div>
    @endif

</div>

@endsection
