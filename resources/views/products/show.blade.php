@extends('layouts.layoutMaster')

@section('content')

@php
    $initialQty = (float) ($product->initial_quantity ?? 0);
    $receivedQty = (float) ($product->received_quantity ?? 0);
    $availableQty = (float) ($availableQuantity ?? $product->quantity ?? 0);
    $unavailableQty = max(0, $initialQty - $receivedQty);
    $soldQty = (float) ($soldQuantity ?? 0);
    $unitLabel = $product->unit_label ?: 'Pièce';

    if ($product->status === 'vendu') {
        $displayStatus = 'Vendu';
        $statusClass = 'status-sold';
    } elseif ($availableQty > 0 && $unavailableQty > 0) {
        $displayStatus = 'Disponible partiellement';
        $statusClass = 'status-partial';
    } elseif ($availableQty > 0) {
        $displayStatus = 'Disponible';
        $statusClass = 'status-available';
    } else {
        $displayStatus = 'Non disponible';
        $statusClass = 'status-unavailable';
    }
@endphp

<style>
    .product-show-page {
        width: 100%;
        padding: 24px 20px 45px;
    }

    .product-show-inner {
        width: 100%;
        max-width: 1450px;
        margin: 0 auto;
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

<div class="product-show-page">
    <div class="product-show-inner">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Fermer"
                ></button>
            </div>
        @endif

        <div class="product-show-header">
            <div class="product-show-title">
                <h3>{{ $product->designation }}</h3>

                <p>
                    Référence :
                    <strong>{{ $product->reference }}</strong>
                </p>
            </div>

            <div class="product-show-actions">
                @if(
                    in_array(
                        auth()->user()->role,
                        ['admin', 'chef_magasinier'],
                        true
                    )
                )
                    <a
                        href="{{ route('products.edit', $product) }}"
                        class="btn btn-warning"
                    >
                        <i class="bx bx-edit"></i>
                        Modifier
                    </a>
                @endif

                <a
                    href="{{ route('products.index') }}"
                    class="btn btn-secondary"
                >
                    <i class="bx bx-arrow-back"></i>
                    Retour
                </a>
            </div>
        </div>

        <div class="stock-summary-grid">
            <div class="stock-summary-box">
                <span class="stock-summary-label">
                    Quantité initiale
                </span>

                <span class="stock-summary-value">
                    {{ number_format($initialQty, 2, ',', ' ') }}
                    {{ $unitLabel }}
                </span>
            </div>

            <div class="stock-summary-box">
                <span class="stock-summary-label">
                    Quantité reçue
                </span>

                <span class="stock-summary-value">
                    {{ number_format($receivedQty, 2, ',', ' ') }}
                    {{ $unitLabel }}
                </span>
            </div>

            <div class="stock-summary-box">
                <span class="stock-summary-label">
                    Quantité disponible
                </span>

                <span class="stock-summary-value">
                    {{ number_format($availableQty, 2, ',', ' ') }}
                    {{ $unitLabel }}
                </span>
            </div>

            <div class="stock-summary-box">
                <span class="stock-summary-label">
                    Quantité non disponible
                </span>

                <span class="stock-summary-value">
                    {{ number_format($unavailableQty, 2, ',', ' ') }}
                    {{ $unitLabel }}
                </span>
            </div>

            <div class="stock-summary-box">
                <span class="stock-summary-label">
                    Quantité vendue
                </span>

                <span class="stock-summary-value">
                    {{ number_format($soldQty, 2, ',', ' ') }}
                    {{ $unitLabel }}
                </span>
            </div>
        </div>

        <div class="product-info-grid">

            <div class="product-section-card">
                <div class="product-section-header">
                    <i class="bx bx-package"></i>
                    Informations du produit
                </div>

                <div class="product-section-body">
                    <table class="product-details-table">
                        <tr>
                            <th>Référence</th>
                            <td>{{ $product->reference }}</td>
                        </tr>

                        <tr>
                            <th>Désignation</th>
                            <td>{{ $product->designation }}</td>
                        </tr>

                        <tr>
                            <th>Marque</th>
                            <td>{{ $product->brand?->name ?? 'Non définie' }}</td>
                        </tr>

                        <tr>
                            <th>Modèle</th>
                            <td>{{ $product->model?->name ?? 'Non défini' }}</td>
                        </tr>

                        <tr>
                            <th>Famille</th>
                            <td>{{ $product->family?->name ?? 'Non définie' }}</td>
                        </tr>

                        <tr>
                            <th>Sous-famille</th>
                            <td>{{ $product->subfamily?->name ?? 'Non définie' }}</td>
                        </tr>

                        <tr>
                            <th>Rayon</th>
                            <td>{{ $product->rayon?->name ?? 'Non défini' }}</td>
                        </tr>

                        <tr>
                            <th>Emplacement</th>
                            <td>{{ $product->location?->name ?? 'Non défini' }}</td>
                        </tr>

                        <tr>
                            <th>Type d’unité</th>
                            <td>{{ $product->unit_type ?? 'Non défini' }}</td>
                        </tr>

                        <tr>
                            <th>Libellé de l’unité</th>
                            <td>{{ $unitLabel }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="product-section-card">
                <div class="product-section-header">
                    <i class="bx bx-cube"></i>
                    Informations du stock
                </div>

                <div class="product-section-body">
                    <table class="product-details-table">
                        <tr>
                            <th>Quantité initiale</th>
                            <td>
                                <span class="stock-badge qty-initial">
                                    {{ number_format($initialQty, 2, ',', ' ') }}
                                    {{ $unitLabel }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th>Quantité reçue</th>
                            <td>
                                <span class="stock-badge qty-received">
                                    {{ number_format($receivedQty, 2, ',', ' ') }}
                                    {{ $unitLabel }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th>Quantité disponible</th>
                            <td>
                                <span class="stock-badge qty-available">
                                    {{ number_format($availableQty, 2, ',', ' ') }}
                                    {{ $unitLabel }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th>Quantité non disponible</th>
                            <td>
                                <span class="stock-badge qty-unavailable">
                                    {{ number_format($unavailableQty, 2, ',', ' ') }}
                                    {{ $unitLabel }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th>Quantité vendue</th>
                            <td>
                                <span class="stock-badge qty-sold">
                                    {{ number_format($soldQty, 2, ',', ' ') }}
                                    {{ $unitLabel }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th>Seuil minimum</th>
                            <td>
                                {{ number_format((float) $product->min_stock, 2, ',', ' ') }}
                                {{ $unitLabel }}
                            </td>
                        </tr>

                        <tr>
                            <th>Seuil maximum</th>
                            <td>
                                {{ number_format((float) $product->max_stock, 2, ',', ' ') }}
                                {{ $unitLabel }}
                            </td>
                        </tr>

                        <tr>
                            <th>Statut produit</th>
                            <td>
                                <span class="stock-badge {{ $statusClass }}">
                                    {{ $displayStatus }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th>État de réception</th>
                            <td>
                                @if($initialQty <= 0)
                                    <span class="badge bg-secondary">
                                        Non défini
                                    </span>
                                @elseif($receivedQty <= 0)
                                    <span class="badge bg-danger">
                                        À recevoir
                                    </span>
                                @elseif($receivedQty < $initialQty)
                                    <span class="badge bg-warning text-dark">
                                        Réception partielle
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        Réception complète
                                    </span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="product-section-card mb-4">
            <div class="product-section-header">
                <i class="bx bx-money"></i>
                Informations sur les prix
            </div>

            <div class="product-section-body">
                <div class="price-grid">

                    <div class="price-box">
                        <div class="price-box-label">
                            Prix d’achat
                        </div>

                        <div class="price-box-value">
                            {{ number_format((float) $product->purchase_price, 2, ',', ' ') }}
                        </div>
                    </div>

                    <div class="price-box">
                        <div class="price-box-label">
                            Coefficient d’achat
                        </div>

                        <div class="price-box-value">
                            {{ number_format((float) $product->coef_purchase, 2, ',', ' ') }}
                        </div>
                    </div>

                    <div class="price-box">
                        <div class="price-box-label">
                            Prix de revient
                        </div>

                        <div class="price-box-value">
                            {{ number_format((float) $product->cost_price, 2, ',', ' ') }}
                        </div>
                    </div>

                    <div class="price-box">
                        <div class="price-box-label">
                            Prix de vente
                        </div>

                        <div class="price-box-value">
                            {{ number_format((float) $product->sale_price, 2, ',', ' ') }}
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="product-section-card">
            <div class="product-section-header">
                <i class="bx bx-building-house"></i>
                Fournisseurs liés
            </div>

            <div class="product-section-body">
                @if($product->suppliers->count() > 0)
                    <div class="supplier-table-wrapper">
                        <table class="table table-bordered table-hover supplier-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Référence fournisseur</th>
                                    <th>Prix achat</th>
                                    <th>Téléphone</th>
                                    <th>Email</th>
                                    <th>Devise</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($product->suppliers as $supplier)
                                    <tr>
                                        <td>
                                            <strong>{{ $supplier->name }}</strong>
                                        </td>

                                        <td>
                                            {{ $supplier->pivot->supplier_reference ?? '-' }}
                                        </td>

                                        <td>
                                            {{ number_format((float) ($supplier->pivot->purchase_price ?? 0), 2, ',', ' ') }}
                                        </td>

                                        <td>
                                            {{ $supplier->phone ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $supplier->email ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $supplier->currency ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-supplier">
                        <i class="bx bx-info-circle me-1"></i>
                        Aucun fournisseur n’est lié à ce produit.
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection