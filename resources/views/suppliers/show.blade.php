@extends('layouts.layoutMaster')

@section('content')

@php

    $user = auth()->user();

    /*
    |--------------------------------------------------------------------------
    | DROITS
    |--------------------------------------------------------------------------
    |
    | Modification autorisée uniquement à :
    | - admin
    | - chef_magasinier
    |
    */

    $canEditSupplier = $user && in_array(
        $user->role,
        [
            'admin',
            'chef_magasinier',
        ],
        true
    );

@endphp


<div class="card shadow-sm border-0">

    {{-- =========================================================
        HEADER
    ========================================================= --}}
    <div class="card-header bg-white border-bottom">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>

                <h3 class="mb-1 fw-bold">
                    Compte fournisseur
                </h3>

                <small class="text-muted">
                    Informations et historique fournisseur
                </small>

            </div>


            <div class="d-flex gap-2">

                {{-- =================================================
                    MODIFIER
                    ADMIN + CHEF MAGASINIER UNIQUEMENT
                ================================================= --}}

                @if($canEditSupplier)

                    <a
                        href="{{ route('suppliers.edit', $supplier->id) }}"
                        class="btn btn-warning"
                    >

                        <i class="bx bx-edit me-1"></i>

                        Modifier

                    </a>

                @endif


                {{-- RETOUR --}}

                <a
                    href="{{ route('suppliers.index') }}"
                    class="btn btn-secondary"
                >

                    <i class="bx bx-arrow-back me-1"></i>

                    Retour

                </a>

            </div>

        </div>

    </div>


    <div class="card-body">

        {{-- =========================================================
            INFORMATIONS FOURNISSEUR
        ========================================================= --}}

        <div class="row">

            {{-- NOM --}}
            <div class="col-md-6 mb-4">

                <label class="fw-bold">
                    Nom fournisseur
                </label>

                <div class="form-control bg-light">

                    {{ $supplier->name }}

                </div>

            </div>


            {{-- TÉLÉPHONE --}}
            <div class="col-md-6 mb-4">

                <label class="fw-bold">
                    Téléphone
                </label>

                <div class="form-control bg-light">

                    {{ $supplier->phone ?? '-' }}

                </div>

            </div>


            {{-- EMAIL --}}
            <div class="col-md-6 mb-4">

                <label class="fw-bold">
                    Email
                </label>

                <div class="form-control bg-light">

                    {{ $supplier->email ?? '-' }}

                </div>

            </div>


            {{-- DEVISE --}}
            <div class="col-md-6 mb-4">

                <label class="fw-bold">
                    Devise
                </label>

                <div class="form-control bg-light">

                    {{ $supplier->currency ?? 'USD' }}

                </div>

            </div>


            {{-- ADRESSE --}}
            <div class="col-md-12 mb-4">

                <label class="fw-bold">
                    Adresse
                </label>

                <div
                    class="form-control bg-light"
                    style="min-height:100px;"
                >

                    {{ $supplier->address ?? '-' }}

                </div>

            </div>

        </div>


        {{-- =========================================================
            STATISTIQUES
        ========================================================= --}}

        <div class="row mt-4">

            {{-- NOMBRE PRODUITS --}}
            <div class="col-md-4 mb-3">

                <div class="card border shadow-sm">

                    <div class="card-body text-center">

                        <h6 class="text-muted">
                            Produits fournis
                        </h6>

                        <h2 class="text-primary">

                            {{ $supplier->products->count() }}

                        </h2>

                    </div>

                </div>

            </div>


            {{-- NOMBRE ACHATS --}}
            <div class="col-md-4 mb-3">

                <div class="card border shadow-sm">

                    <div class="card-body text-center">

                        <h6 class="text-muted">
                            Nombre achats
                        </h6>

                        <h2 class="text-success">

                            {{ $supplier->purchases->count() }}

                        </h2>

                    </div>

                </div>

            </div>


            {{-- TOTAL ACHATS --}}
            <div class="col-md-4 mb-3">

                <div class="card border shadow-sm">

                    <div class="card-body text-center">

                        <h6 class="text-muted">
                            Montant total achats
                        </h6>

                        <h2 class="text-danger">

                            {{ number_format(
                                $supplier->purchases->sum('total'),
                                2
                            ) }}
                            $

                        </h2>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
            PRODUITS FOURNIS
        ========================================================= --}}

        <div class="mt-5">

            <h4 class="mb-3">
                Produits fournis
            </h4>


            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>
                                Référence
                            </th>

                            <th>
                                Produit
                            </th>

                            <th>
                                Marque
                            </th>

                            <th>
                                Modèle
                            </th>

                            <th>
                                Prix achat
                            </th>

                            <th>
                                Stock
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($supplier->products as $product)

                            <tr>

                                <td>

                                    <strong>
                                        {{ $product->reference }}
                                    </strong>

                                </td>


                                <td>

                                    {{ $product->designation }}

                                </td>


                                <td>

                                    {{ $product->brand->name ?? '-' }}

                                </td>


                                <td>

                                    {{ $product->model->name ?? '-' }}

                                </td>


                                <td>

                                    {{ number_format(
                                        $product->pivot->purchase_price
                                            ?? $product->purchase_price
                                            ?? 0,
                                        2
                                    ) }}
                                    $

                                </td>


                                <td>

                                    @php
                                        $quantity = (float) ($product->quantity ?? 0);
                                    @endphp

                                    @if($quantity <= 0)

                                        <span class="badge bg-danger">

                                            {{ number_format($quantity, 2) }}

                                        </span>

                                    @elseif($quantity <= 5)

                                        <span class="badge bg-warning text-dark">

                                            {{ number_format($quantity, 2) }}

                                        </span>

                                    @else

                                        <span class="badge bg-success">

                                            {{ number_format($quantity, 2) }}

                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="text-center text-muted py-4"
                                >

                                    Aucun produit associé

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- =========================================================
            HISTORIQUE ACHATS
        ========================================================= --}}

        <div class="mt-5">

            <h4 class="mb-3">
                Historique des achats
            </h4>


            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>
                                Référence
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Statut
                            </th>

                            <th>
                                Livraison
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($supplier->purchases as $purchase)

                            <tr>

                                <td>

                                    @if(Route::has('purchases.show'))

                                        <a
                                            href="{{ route(
                                                'purchases.show',
                                                $purchase->id
                                            ) }}"
                                            class="fw-bold text-primary"
                                        >

                                            {{ $purchase->reference }}

                                        </a>

                                    @else

                                        <strong>
                                            {{ $purchase->reference }}
                                        </strong>

                                    @endif

                                </td>


                                <td>

                                    {{ optional(
                                        $purchase->created_at
                                    )->format('d/m/Y') }}

                                </td>


                                <td>

                                    {{ number_format(
                                        $purchase->total ?? 0,
                                        2
                                    ) }}
                                    $

                                </td>


                                <td>

                                    @php
                                        $status = $purchase->status ?? 'inconnu';
                                    @endphp

                                    <span class="badge bg-label-success">

                                        {{ ucfirst($status) }}

                                    </span>

                                </td>


                                <td>

                                    @if(
                                        $purchase->delivery_status
                                        === 'received'
                                    )

                                        <span class="badge bg-success">

                                            Reçu

                                        </span>

                                    @elseif(
                                        $purchase->delivery_status
                                        === 'partial_received'
                                    )

                                        <span class="badge bg-warning text-dark">

                                            Partiel

                                        </span>

                                    @else

                                        <span class="badge bg-secondary">

                                            En attente

                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="5"
                                    class="text-center text-muted py-4"
                                >

                                    Aucun achat trouvé

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection