@extends('layouts.layoutMaster')

@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | QUANTITÉS
    |--------------------------------------------------------------------------
    */

    $initialQty =
        (float) ($product->initial_quantity ?? 0);

    $receivedQty =
        (float) ($product->received_quantity ?? 0);

    $availableQty =
        (float) (
            $availableQuantity
            ?? $product->quantity
            ?? 0
        );

    $unavailableQty =
        max(
            0,
            $initialQty - $receivedQty
        );

    $soldQty =
        (float) ($soldQuantity ?? 0);

    $unitLabel =
        $product->unit_label
        ?: 'Pièce';


    /*
    |--------------------------------------------------------------------------
    | STATUT AFFICHÉ
    |--------------------------------------------------------------------------
    */

    if ($product->status === 'vendu') {

        $displayStatus =
            'Vendu';

        $statusClass =
            'status-sold';

    } elseif (
        $product->supply_status
        ===
        'en_recherche'
    ) {

        $displayStatus =
            'En recherche';

        $statusClass =
            'status-searching';

    } elseif (
        $product->supply_status
        ===
        'en_commande'
    ) {

        $displayStatus =
            'En commande';

        $statusClass =
            'status-ordered';

    } elseif (
        $receivedQty > 0
        &&
        $receivedQty < $initialQty
    ) {

        $displayStatus =
            'Partiellement reçu';

        $statusClass =
            'status-partial';

    } elseif (
        $receivedQty <= 0
        ||
        $availableQty <= 0
    ) {

        $displayStatus =
            'Rupture';

        $statusClass =
            'status-unavailable';

    } elseif (
        $initialQty > 0
        &&
        $receivedQty >= $initialQty
    ) {

        $displayStatus =
            'Reçu';

        $statusClass =
            'status-available';

    } else {

        $displayStatus =
            'Disponible';

        $statusClass =
            'status-available';
    }

@endphp

<style>
    .product-show-page {
        width: 100%;
        padding: 0;
    }

    .product-show-inner {
        width: 100%;
        margin: 0;
    }

    .product-show-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        padding: 22px 24px;
        margin-bottom: 22px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .07);
    }

    .product-show-title h3 {
        margin: 0 0 5px;
        font-size: 26px;
        font-weight: 800;
        color: #1f2937;
    }

    .product-show-title p {
        margin: 0;
        font-size: 14px;
        color: #64748b;
    }

    .product-show-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .product-show-actions .btn {
        min-height: 42px;
        padding: 9px 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-weight: 700;
        border-radius: 8px;
    }

    .product-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
        margin-bottom: 22px;
    }

    .product-section-card {
        overflow: hidden;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
    }

    .product-section-header {
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 9px;
        color: #1f2937;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        font-size: 16px;
        font-weight: 800;
    }

    .product-section-body {
        padding: 20px;
    }

    .product-details-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }

    .product-details-table tr:not(:last-child) {
        border-bottom: 1px solid #eef2f7;
    }

    .product-details-table th,
    .product-details-table td {
        padding: 11px 8px;
        vertical-align: middle;
    }

    .product-details-table th {
        width: 42%;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .product-details-table td {
        color: #111827;
        font-size: 14px;
        font-weight: 600;
    }

    .stock-badge {
        min-width: 105px;
        padding: 7px 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 800;
        border-radius: 999px;
    }

    .qty-initial {
        color: #4f46e5;
        background: #eef2ff;
    }

    .qty-received {
        color: #0369a1;
        background: #e0f2fe;
    }

    .qty-available {
        color: #15803d;
        background: #dcfce7;
    }

    .qty-unavailable {
        color: #b45309;
        background: #fef3c7;
    }

    .qty-sold {
        color: #b91c1c;
        background: #fee2e2;
    }

    .status-available {
        color: #166534;
        background: #dcfce7;
    }

    .status-unavailable {
        color: #991b1b;
        background: #fee2e2;
    }

    .status-partial {
        color: #92400e;
        background: #fef3c7;
    }

    .status-sold {
        color: #475569;
        background: #e2e8f0;
    }

    .status-searching {
    color: #0369a1;
    background: #e0f2fe;
    }

    .status-ordered {
        color: #854d0e;
        background: #fef9c3;
    }

    .stock-summary-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .stock-summary-box {
        min-height: 105px;
        padding: 16px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, .05);
    }

    .stock-summary-label {
        display: block;
        margin-bottom: 8px;
        font-size: 11px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
    }

    .stock-summary-value {
        font-size: 18px;
        font-weight: 800;
        color: #111827;
    }

    .price-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .price-box {
        min-height: 105px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 11px;
    }

    .price-box-label {
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 700;
        color: #64748b;
    }

    .price-box-value {
        font-size: 18px;
        font-weight: 800;
        color: #111827;
    }

    .supplier-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .supplier-table {
        width: 100%;
        min-width: 700px;
        margin: 0;
    }

    .supplier-table thead th {
        padding: 12px 14px;
        white-space: nowrap;
        font-size: 13px;
        font-weight: 800;
        color: #334155;
        background: #f1f5f9;
    }

    .supplier-table tbody td {
        padding: 13px 14px;
        vertical-align: middle;
        font-size: 14px;
    }

    .empty-supplier {
        padding: 18px;
        text-align: center;
        color: #92400e;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 10px;
    }

    @media (max-width: 1199.98px) {
        .stock-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .price-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .product-info-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .product-show-page {
            padding: 16px 10px 32px;
        }

        .product-show-header {
            align-items: stretch;
            padding: 18px 16px;
        }

        .product-show-actions {
            width: 100%;
            flex-direction: column;
        }

        .product-show-actions .btn {
            width: 100%;
        }

        .stock-summary-grid,
        .price-grid {
            grid-template-columns: 1fr;
        }

        .product-section-body {
            padding: 15px;
        }

        .product-details-table th,
        .product-details-table td {
            display: block;
            width: 100%;
        }

        .product-details-table th {
            padding-bottom: 2px;
        }

        .product-details-table td {
            padding-top: 2px;
            padding-bottom: 11px;
        }
    }
</style>

<style>
    /* ============================================================
       FICHE PRODUIT COMPACTE (V6) : tout tient sur un écran
       1. barre : statut / réception à gauche, actions à droite
       2. chiffres : 5 quantités + 4 prix sur une ligne
       3. trois colonnes : fiche, stock par dépôt, fournisseurs
    ============================================================ */
    .pfc { display: flex; flex-direction: column; gap: 16px; }

    .pfc-barre {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .pfc-etats { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }

    .pfc-ref {
        padding: 4px 10px;
        color: var(--stcd-text, #212B36);
        background: var(--stcd-surface, #fff);
        border: 1px solid var(--stcd-border, #E6EAED);
        border-radius: 6px;
        font-size: 13px;
        font-weight: 700;
    }

    .pfc-actions { display: flex; gap: 8px; }

    .pfc-chiffres {
        display: grid;
        grid-template-columns: repeat(9, minmax(0, 1fr));
        gap: 10px;
    }

    .pfc-chiffre {
        padding: 10px 12px;
        background: var(--stcd-surface, #fff);
        border: 1px solid var(--stcd-border, #E6EAED);
        border-left: 3px solid var(--pfc-c, #94a3b8);
        border-radius: 8px;
    }

    .pfc-chiffre span {
        display: block;
        color: var(--stcd-muted, #8a9198);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .pfc-chiffre strong {
        display: block;
        margin-top: 2px;
        color: var(--stcd-text, #212B36);
        font-size: 17px;
        font-weight: 800;
        white-space: nowrap;
    }

    .pfc-chiffre small { color: var(--stcd-muted, #8a9198); font-size: 12px; font-weight: 600; }

    .pfc-separateur { background: var(--stcd-surface-2, #F9FAFB); }

    .pfc-actions .btn { display: inline-flex; align-items: center; gap: 6px; }

    .pfc-colonnes {
        display: grid;
        grid-template-columns: 1.25fr 1fr 1fr;
        gap: 16px;
        align-items: start;
    }

    .pfc-carte {
        overflow: hidden;
        background: var(--stcd-surface, #fff);
        border: 1px solid var(--stcd-border, #E6EAED);
        border-radius: 8px;
    }

    .pfc-carte h6 {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        padding: 12px 16px;
        color: var(--stcd-text, #212B36);
        border-bottom: 1px solid var(--stcd-border, #E6EAED);
        font-size: 14.5px;
        font-weight: 800;
    }

    .pfc-carte h6 i { color: var(--stcd-primary, #FE9F43); font-size: 18px; }

    .pfc-fiche {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin: 0;
    }

    .pfc-fiche > div {
        padding: 9px 16px;
        border-bottom: 1px solid var(--stcd-border, #E6EAED);
    }

    .pfc-fiche > div:nth-child(odd) { border-right: 1px solid var(--stcd-border, #E6EAED); }

    .pfc-fiche dt {
        color: var(--stcd-muted, #8a9198);
        font-size: 11.5px;
        font-weight: 700;
    }

    .pfc-fiche dd {
        margin: 1px 0 0;
        overflow: hidden;
        color: var(--stcd-text, #212B36);
        font-size: 13.5px;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pfc-fiche .pfc-large { grid-column: 1 / -1; border-right: 0 !important; }
    .pfc-fiche .pfc-large dd { white-space: normal; }

    .pfc-table { width: 100%; margin: 0; font-size: 13px; }

    .pfc-table th {
        padding: 8px 12px;
        color: var(--stcd-muted, #8a9198);
        background: var(--stcd-surface-2, #F9FAFB);
        font-size: 11.5px;
        font-weight: 800;
        white-space: nowrap;
    }

    .pfc-table td {
        padding: 8px 12px;
        border-top: 1px solid var(--stcd-border, #E6EAED);
        vertical-align: middle;
    }

    .pfc-table .pfc-num { text-align: right; white-space: nowrap; }

    .pfc-vide {
        padding: 16px;
        color: var(--stcd-muted, #8a9198);
        font-size: 13px;
        text-align: center;
    }

    .pfc-qte-depot {
        display: inline-block;
        min-width: 44px;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 800;
        text-align: center;
    }

    .pfc-qte-depot.ok { color: #15803d; background: #dcfce7; }
    .pfc-qte-depot.zero { color: #b91c1c; background: #fee2e2; }

    .pfc-historique {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        align-items: start;
    }

    .pfc-table td.pfc-date { color: var(--stcd-muted, #8a9198); white-space: nowrap; }

    .pfc-sens {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11.5px;
        font-weight: 800;
        white-space: nowrap;
    }

    .pfc-sens.entree { color: #15803d; background: #dcfce7; }
    .pfc-sens.sortie { color: #b91c1c; background: #fee2e2; }
    .pfc-sens.autre { color: #475569; background: #e2e8f0; }

    .pfc-carte h6 .pfc-compte {
        margin-left: auto;
        color: var(--stcd-muted, #8a9198);
        font-size: 12px;
        font-weight: 700;
    }

    @media (max-width: 1199.98px) {
        .pfc-historique { grid-template-columns: 1fr; }
    }

    @media (max-width: 1399.98px) {
        .pfc-chiffres { grid-template-columns: repeat(5, minmax(0, 1fr)); }
    }

    @media (max-width: 1199.98px) {
        .pfc-colonnes { grid-template-columns: 1fr 1fr; }
        .pfc-colonnes > :first-child { grid-column: 1 / -1; }
    }

    @media (max-width: 767.98px) {
        .pfc-chiffres { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .pfc-colonnes { grid-template-columns: 1fr; }
        .pfc-fiche { grid-template-columns: 1fr; }
        .pfc-fiche > div:nth-child(odd) { border-right: 0; }
    }
</style>

@php
    $fmt = fn ($n) => number_format((float) $n, 2, ',', ' ');
    $receptionLibelle = $initialQty <= 0 ? 'Réception : non définie'
        : ($receivedQty <= 0 ? 'À recevoir'
        : ($receivedQty < $initialQty ? 'Réception partielle' : 'Réception complète'));
    $receptionClasse = $initialQty <= 0 ? 'bg-secondary'
        : ($receivedQty <= 0 ? 'bg-danger'
        : ($receivedQty < $initialQty ? 'bg-warning text-dark' : 'bg-success'));
@endphp

<div class="product-show-page">
    <div class="product-show-inner pfc">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-0">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif

        {{-- Titre de la page (remonté dans l'en-tête) --}}
        <div class="product-show-header">
            <div class="product-show-title">
                <h3>{{ $product->designation }}</h3>
                <p>Référence : <strong>{{ $product->reference }}</strong></p>
            </div>
        </div>

        {{-- 1. BARRE : états + actions --}}
        <div class="pfc-barre">
            <div class="pfc-etats">
                <span class="pfc-ref">{{ $product->reference }}</span>
                <span class="stock-badge {{ $statusClass }}">{{ $displayStatus }}</span>
                <span class="badge {{ $receptionClasse }}">{{ $receptionLibelle }}</span>
            </div>

            <div class="pfc-actions">
                @if(auth()->user()->hasPermission('modifier_produits'))
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">
                        <i class="bx bx-edit"></i> Modifier
                    </a>
                @endif
                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back"></i> Retour
                </a>
            </div>
        </div>

        {{-- 2. CHIFFRES : quantités puis prix --}}
        <div class="pfc-chiffres">
            <div class="pfc-chiffre" style="--pfc-c:#6366f1"><span>Qté initiale</span><strong>{{ $fmt($initialQty) }} <small>{{ $unitLabel }}</small></strong></div>
            <div class="pfc-chiffre" style="--pfc-c:#0ea5e9"><span>Qté reçue</span><strong>{{ $fmt($receivedQty) }} <small>{{ $unitLabel }}</small></strong></div>
            <div class="pfc-chiffre" style="--pfc-c:#22c55e"><span>Disponible</span><strong>{{ $fmt($availableQty) }} <small>{{ $unitLabel }}</small></strong></div>
            <div class="pfc-chiffre" style="--pfc-c:#f59e0b"><span>Non disponible</span><strong>{{ $fmt($unavailableQty) }} <small>{{ $unitLabel }}</small></strong></div>
            <div class="pfc-chiffre" style="--pfc-c:#ef4444"><span>Vendue</span><strong>{{ $fmt($soldQty) }} <small>{{ $unitLabel }}</small></strong></div>
            <div class="pfc-chiffre pfc-separateur" style="--pfc-c:#092C4C"><span>Prix d’achat</span><strong>{{ $fmt($product->purchase_price) }}</strong></div>
            <div class="pfc-chiffre pfc-separateur" style="--pfc-c:#092C4C"><span>Coef. d’achat</span><strong>{{ $fmt($product->coef_purchase) }}</strong></div>
            <div class="pfc-chiffre pfc-separateur" style="--pfc-c:#092C4C"><span>Prix de revient</span><strong>{{ $fmt($product->cost_price) }}</strong></div>
            <div class="pfc-chiffre pfc-separateur" style="--pfc-c:#FE9F43"><span>Prix de vente</span><strong>{{ $fmt($product->sale_price) }}</strong></div>
        </div>

        {{-- 3. TROIS COLONNES --}}
        <div class="pfc-colonnes">

            <div class="pfc-carte">
                <h6><i class="bx bx-package"></i> Fiche du produit</h6>
                <dl class="pfc-fiche">
                    <div class="pfc-large"><dt>Désignation</dt><dd>{{ $product->designation }}</dd></div>
                    <div><dt>Marque</dt><dd>{{ $product->brand?->name ?? 'Non définie' }}</dd></div>
                    <div><dt>Modèle</dt><dd>{{ $product->model?->name ?? 'Non défini' }}</dd></div>
                    <div><dt>Famille</dt><dd>{{ $product->family?->name ?? 'Non définie' }}</dd></div>
                    <div><dt>Sous-famille</dt><dd>{{ $product->subfamily?->name ?? 'Non définie' }}</dd></div>
                    <div><dt>Rayon</dt><dd>{{ $product->rayon?->name ?? 'Non défini' }}</dd></div>
                    <div><dt>Emplacement</dt><dd>{{ $product->location?->name ?? 'Non défini' }}</dd></div>
                    <div><dt>Unité</dt><dd>{{ $unitLabel }} <small class="text-muted">({{ $product->unit_type ?? 'non défini' }})</small></dd></div>
                    <div><dt>Seuils min. / max.</dt><dd>{{ $fmt($product->min_stock) }} / {{ $fmt($product->max_stock) }} {{ $unitLabel }}</dd></div>
                </dl>
            </div>

            <div class="pfc-carte">
                <h6><i class="bx bx-store-alt"></i> Stock par dépôt</h6>
                @if($product->depotStocks->count() > 0)
                    <table class="pfc-table">
                        <thead>
                            <tr><th>Dépôt</th><th>Rayon / emplacement</th><th class="pfc-num">Quantité</th></tr>
                        </thead>
                        <tbody>
                            @foreach($product->depotStocks as $depotStock)
                                <tr>
                                    <td><strong>{{ $depotStock->depot?->name ?? 'Non défini' }}</strong></td>
                                    <td>{{ $depotStock->rayon?->name ?? '—' }} / {{ $depotStock->location?->name ?? '—' }}</td>
                                    <td class="pfc-num">
                                        <span class="pfc-qte-depot {{ (float) $depotStock->quantity > 0 ? 'ok' : 'zero' }}">{{ $fmt($depotStock->quantity) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="pfc-vide">Aucun stock enregistré dans les dépôts.</div>
                @endif
            </div>

            <div class="pfc-carte">
                <h6><i class="bx bx-building-house"></i> Fournisseurs liés</h6>
                @if($product->suppliers->count() > 0)
                    <table class="pfc-table">
                        <thead>
                            <tr><th>Fournisseur</th><th>Réf. fournisseur</th><th class="pfc-num">Prix achat</th></tr>
                        </thead>
                        <tbody>
                            @foreach($product->suppliers as $supplier)
                                <tr>
                                    <td>
                                        <strong>{{ $supplier->name }}</strong>
                                        @if($supplier->phone || $supplier->email)
                                            <div class="text-muted small">{{ collect([$supplier->phone, $supplier->email])->filter()->implode(' · ') }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $supplier->pivot->supplier_reference ?? '—' }}</td>
                                    <td class="pfc-num">{{ $fmt($supplier->pivot->purchase_price ?? 0) }} {{ $supplier->currency }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="pfc-vide">Aucun fournisseur lié à ce produit.</div>
                @endif
            </div>

        </div>

        {{-- 4. HISTORIQUE : derniers mouvements et dernières ventes --}}
        @php
            $mouvements = $product->stockMovements->sortByDesc('created_at')->take(8);
            $ventes = $product->saleItems->filter(fn ($l) => $l->sale)->sortByDesc(fn ($l) => $l->sale->created_at)->take(8);
            $libelleSens = fn ($type) => match (strtolower((string) $type)) {
                'in', 'entree', 'entrée', 'entry' => ['Entrée', 'entree'],
                'out', 'sortie', 'exit' => ['Sortie', 'sortie'],
                default => [ucfirst((string) $type) ?: '—', 'autre'],
            };
        @endphp

        <div class="pfc-historique">

            <div class="pfc-carte">
                <h6>
                    <i class="bx bx-transfer-alt"></i> Derniers mouvements de stock
                    <span class="pfc-compte">{{ $product->stockMovements->count() }} au total</span>
                </h6>
                @if($mouvements->isNotEmpty())
                    <table class="pfc-table">
                        <thead>
                            <tr><th>Date</th><th>Sens</th><th>Origine</th><th>Référence</th><th class="pfc-num">Quantité</th></tr>
                        </thead>
                        <tbody>
                            @foreach($mouvements as $mouvement)
                                @php [$sens, $classeSens] = $libelleSens($mouvement->type); @endphp
                                <tr>
                                    <td class="pfc-date">{{ optional($mouvement->created_at)->format('d/m/Y H:i') }}</td>
                                    <td><span class="pfc-sens {{ $classeSens }}">{{ $sens }}</span></td>
                                    <td>{{ \Illuminate\Support\Str::limit($mouvement->source ?? '—', 42) }}</td>
                                    <td>{{ $mouvement->reference ?? '—' }}</td>
                                    <td class="pfc-num"><strong>{{ $fmt($mouvement->quantity) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="pfc-vide">Aucun mouvement de stock pour cette pièce.</div>
                @endif
            </div>

            <div class="pfc-carte">
                <h6>
                    <i class="bx bx-receipt"></i> Dernières ventes
                    <span class="pfc-compte">{{ $product->saleItems->count() }} ligne(s) vendue(s)</span>
                </h6>
                @if($ventes->isNotEmpty())
                    <table class="pfc-table">
                        <thead>
                            <tr><th>Date</th><th>Facture</th><th>Client</th><th class="pfc-num">Qté</th><th class="pfc-num">Prix</th></tr>
                        </thead>
                        <tbody>
                            @foreach($ventes as $ligne)
                                <tr>
                                    <td class="pfc-date">{{ optional($ligne->sale->created_at)->format('d/m/Y') }}</td>
                                    <td>
                                        @if(auth()->user()->hasPermission('voir_ventes'))
                                            <a href="{{ route('sales.show', $ligne->sale) }}">{{ $ligne->sale->invoice_number ?? ('#' . $ligne->sale->id) }}</a>
                                        @else
                                            {{ $ligne->sale->invoice_number ?? ('#' . $ligne->sale->id) }}
                                        @endif
                                        @if($ligne->sale->status === 'cancelled')
                                            <span class="pfc-sens autre">annulée</span>
                                        @endif
                                    </td>
                                    <td>{{ $ligne->sale->customer?->name ?? '—' }}</td>
                                    <td class="pfc-num">{{ $fmt($ligne->quantity) }}</td>
                                    <td class="pfc-num">{{ $fmt($ligne->price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="pfc-vide">Cette pièce n’a encore jamais été vendue.</div>
                @endif
            </div>

        </div>

    </div>
</div>

@endsection
