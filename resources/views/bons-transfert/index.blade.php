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

        <div class="d-flex flex-wrap gap-2">
            <a
                href="{{ route('fournisseur-commandes.index') }}"
                class="btn btn-outline-secondary bt-btn"
            >
                <i class="bx bx-arrow-back"></i>
                Commandes garage
            </a>

            @if(auth()->user()->hasPermission('gerer_consommations_atelier'))
                <a href="{{ route('bt-direct.create') }}" class="btn btn-primary bt-btn"
                   title="BT direct pour un usage interne : consommation atelier, vente intersociété, équipe commerciale…">
                    <i class="bx bx-plus"></i>
                    BT
                </a>
            @endif
        </div>

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
                    placeholder="N° BT, N° BC, client, bénéficiaire ou immatriculation..."
                >
            </div>

            <div class="col-md-3 col-lg-3">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous les BT</option>
                    @foreach($types as $cle => $libelle)
                        <option value="{{ $cle }}" @selected($type === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-primary bt-btn">
                    <i class="bx bx-search"></i>
                    Rechercher
                </button>
            </div>

            @if($search !== '' || $type !== '')
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
                                @if($bc?->estConsommation())
                                    Consommation atelier · {{ $bc->equipement }}
                                @elseif($bc?->estInterne())
                                    {{ $bc->libelleMotif() }}{{ $bc->demande_par ? ' · demandé par ' . $bc->demande_par : '' }}
                                @else
                                {{ trim(($bc?->vehicule_marque ?? '') . ' ' . ($bc?->vehicule_modele ?? '')) ?: '—' }}

                                @if($bc?->vehicule_immatriculation)
                                    · {{ $bc->vehicule_immatriculation }}
                                @endif
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
                            @if($bc?->estInterne())

                                <span class="bt-muted">Interne — pas d'envoi</span>

                            @elseif($bt->envoye_atelier_at)

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

                        <td class="text-end text-nowrap">

                            <button
                                type="button"
                                class="btn btn-outline-dark bt-btn"
                                title="Imprimer {{ $bt->numero }}"
                                data-imprimer="{{ route('bons-transfert.imprimer', $bt) }}"
                            >
                                <i class="bx bx-printer"></i>
                            </button>

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
    @if($bons->total() > 0)
        <div class="bt-pagination">

            <form method="GET" action="{{ route('bons-transfert.index') }}" class="bt-par-page">
                @foreach(request()->except(['par_page', 'page']) as $cle => $valeur)
                    @if(is_scalar($valeur))
                        <input type="hidden" name="{{ $cle }}" value="{{ $valeur }}">
                    @endif
                @endforeach
                <label for="par_page">Afficher</label>
                <select id="par_page" name="par_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach(\App\Http\Controllers\BonTransfertController::PAR_PAGE as $nombre)
                        <option value="{{ $nombre }}" @selected($bons->perPage() === $nombre)>{{ $nombre }}</option>
                    @endforeach
                </select>
                <span>par page</span>
            </form>

            <div class="bt-par-page-info">
                {{ $bons->firstItem() }}–{{ $bons->lastItem() }} sur {{ $bons->total() }} bons de transfert
            </div>

            @if($bons->hasPages())
                <div class="bt-pagination-liens">{{ $bons->links() }}</div>
            @endif

        </div>
    @endif

    <style>
        .bt-pagination {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px 20px;
            padding: 8px 1.5rem 1.5rem;
        }

        .bt-par-page { display: flex; align-items: center; gap: 8px; margin: 0; font-size: 14px; }
        .bt-par-page label { margin: 0; }
        .bt-par-page .form-select { width: auto; min-width: 76px; }
        .bt-par-page-info { color: var(--stcd-muted, #8a9198); font-size: 14px; }
        .bt-pagination-liens { margin-left: auto; }
        .bt-pagination-liens nav p { display: none; }
        .bt-pagination-liens svg { width: 18px; height: 18px; }
    </style>

</div>

@include('bons-transfert._impression')

@endsection
