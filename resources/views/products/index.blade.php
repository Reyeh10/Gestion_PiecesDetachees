@extends('layouts.layoutMaster')

@section('content')

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

<div class="card shadow-sm border-0">

    {{-- HEADER --}}
    <div class="card-header bg-white border-0">

        {{-- TOP --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

            <h4 class="mb-0">
                {{ $pageTitle ?? 'Liste des produits' }}
            </h4>

            @if(request()->routeIs('products.available'))

                <a href="{{ route('sales.create') }}"
                   class="btn btn-primary">

                    <i class="bx bx-cart"></i>

                    Nouvelle vente

                </a>

            @endif

        </div>

        {{-- IMPORT FORM --}}
        @if(
            !$hideButtons
            &&
            in_array(auth()->user()->role, [
                'admin',
                'chef_magasinier',
                'magasinier'
            ])
        )

            <form action="{{ route('products.preview') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="row g-3 align-items-end">

                    {{-- FOURNISSEUR --}}
                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Fournisseur
                        </label>

                        <select name="supplier_id"
                                class="form-select"
                                required>

                            <option value="">
                                Sélectionner fournisseur
                            </option>

                            @foreach($suppliers as $supplier)

                                <option value="{{ $supplier->id }}">
                                    {{ $supplier->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- DEPOT --}}
                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Dépôt
                        </label>

                        <select name="depot_id"
                                class="form-select"
                                required>

                            <option value="">
                                Sélectionner dépôt
                            </option>

                            @foreach($depots as $depot)

                                <option value="{{ $depot->id }}">
                                    {{ $depot->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- FICHIER --}}
                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Fichier Excel
                        </label>

                        <input type="file"
                               name="file"
                               class="form-control"
                               accept=".xlsx,.xls,.csv"
                               required>

                    </div>

                    {{-- IMPORT --}}
                    <div class="col-md-2">

                        <button type="submit"
                                class="btn btn-success w-100">

                            <i class="bx bx-upload"></i>

                            Importer

                        </button>

                    </div>

                    {{-- AJOUT --}}
                    <div class="col-md-1">

                        <a href="{{ route('products.create') }}"
                           class="btn btn-primary w-100">

                            <i class="bx bx-plus"></i>

                        </a>

                    </div>

                </div>

            </form>

        @endif

    </div>

    {{-- BODY --}}
    <div class="card-body pt-4">

        {{-- SEARCH --}}
        <form method="GET" class="mb-4">

            <div class="row align-items-center g-3">

                <div class="col-md-4">

                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Recherche produit..."
                           value="{{ request('search') }}">

                </div>

                <div class="col-md-2">

                    <button class="btn btn-primary w-100">

                        <i class="bx bx-search"></i>

                        Rechercher

                    </button>

                </div>

                <div class="col-md-2">

                    <a href="{{ route('products.index') }}"
                       class="btn btn-secondary w-100">

                        Reset

                    </a>

                </div>

            </div>

        </form>

        {{-- TABLE --}}
        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead class="table-light">

                    <tr>

                        <th>Référence</th>
                        <th>Désignation</th>
                        <th>Marque</th>
                        <th>Modèle</th>
                        <th>Famille</th>
                        <th>Rayon</th>
                        <th>Emplacement</th>

                        @if(request()->routeIs('products.index'))

                            <th>Qté initiale</th>
                            <th>Qté disponible</th>
                            <th>Qté vendue</th>

                        @endif

                        @if(request()->routeIs('products.available'))

                            <th>Qté disponible</th>

                        @endif

                        @if(request()->routeIs('products.sold'))

                            <th>Qté vendue</th>

                        @endif

                        <th>Min</th>
                        <th>Max</th>
                        <th>Prix Achat</th>
                        <th>Prix Vente</th>

                        @if(request()->routeIs('products.index'))

                            <th>Status</th>

                        @endif

                        <th width="180">
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($products as $product)

                     @php

                        /*
                        |--------------------------------------------------------------------------
                        | QUANTITE VENDUE
                        |--------------------------------------------------------------------------
                        */

                        $soldQty = $product->saleItems
                            ->sum('quantity');

                        /*
                        |--------------------------------------------------------------------------
                        | QUANTITE DISPONIBLE
                        |--------------------------------------------------------------------------
                        */

                        $availableQty = $product->quantity;

                        /*
                        |--------------------------------------------------------------------------
                        | TOTAL INITIAL
                        |--------------------------------------------------------------------------
                        */

                        $initialQty =
                            $availableQty + $soldQty;

                    @endphp

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
                                {{ $product->brand?->name ?? '-' }}
                            </td>

                            <td>
                                {{ $product->model?->name ?? '-' }}
                            </td>

                            <td>
                                {{ $product->family?->name ?? '-' }}
                            </td>

                            <td>
                                {{ $product->rayon?->name ?? '-' }}
                            </td>

                            <td>
                                {{ $product->location?->name ?? '-' }}
                            </td>

                          @if(request()->routeIs('products.index'))

                                {{-- QTE INITIALE --}}
                                <td>

                                    <span class="badge bg-label-primary">

                                        {{ $initialQty }}
                                        {{ $product->unit_label }}

                                    </span>

                                </td>

                                {{-- DISPONIBLE --}}
                                <td>

                                    <span class="badge bg-label-success">

                                        {{ $availableQty }}
                                        {{ $product->unit_label }}

                                    </span>

                                </td>

                                {{-- VENDU --}}
                                <td>

                                    <span class="badge bg-label-danger">

                                        {{ $soldQty }}
                                        {{ $product->unit_label }}

                                    </span>

                                </td>

                            @endif

                            @if(request()->routeIs('products.available'))

                                <td>

                                    <span class="badge bg-label-success">

                                        {{ $availableQty }}
                                        {{ $product->unit_label }}

                                    </span>

                                </td>

                            @endif

                            @if(request()->routeIs('products.sold'))

                                <td>

                                    <span class="badge bg-label-danger">

                                        {{ $soldQty }}

                                    </span>

                                </td>

                            @endif

                            <td>
                                {{ $product->min_stock }}
                            </td>

                            <td>
                                {{ $product->max_stock }}
                            </td>

                            <td>
                                {{ number_format($product->purchase_price, 2) }}
                            </td>

                            <td>

                                <strong>
                                    {{ number_format($product->sale_price, 2) }}
                                </strong>

                            </td>

                            @if(request()->routeIs('products.index'))

                                <td>

                                    @if($availableQty <= 0)

                                        <span class="badge bg-danger">
                                            Rupture
                                        </span>

                                    @elseif($availableQty <= $product->min_stock)

                                        <span class="badge bg-warning text-dark">
                                            Stock faible
                                        </span>

                                    @else

                                        <span class="badge bg-success">
                                            Disponible
                                        </span>

                                    @endif

                                </td>

                            @endif

                           <td>

                                <div class="d-flex align-items-center gap-2">

                                    {{-- SHOW --}}
                                    <a href="{{ route('products.show', $product) }}"
                                    class="btn btn-info btn-sm">

                                        <i class="bx bx-show"></i>

                                    </a>

                                    {{-- EDIT --}}
                                    @if(
                                        in_array(auth()->user()->role, [
                                            'admin',
                                            'chef_magasinier'
                                        ])
                                    )

                                        <a href="{{ route('products.edit', $product) }}"
                                        class="btn btn-warning btn-sm">

                                            <i class="bx bx-edit"></i>

                                        </a>

                                    @endif

                                    {{-- DELETE --}}
                                    @if(
                                        in_array(auth()->user()->role, [
                                            'admin',
                                            'chef_magasinier'
                                        ])
                                    )

                                        <form action="{{ route('products.destroy', $product) }}"
                                            method="POST"
                                            class="delete-form mb-0">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-danger btn-sm">

                                                <i class="bx bx-trash"></i>

                                            </button>

                                        </form>

                                    @endif
                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>

                            <td colspan="14"
                                class="text-center text-muted py-4">

                                Aucun produit trouvé

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINATION --}}
        @if(method_exists($products, 'links'))

            <div class="mt-4">

                {{ $products->withQueryString()->links() }}

            </div>

        @endif

    </div>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | DELETE SWEET ALERT
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.delete-form').forEach(form => {

        form.addEventListener('submit', function (e) {

            e.preventDefault();

            Swal.fire({

                title: 'Supprimer le produit ?',

                text: 'Cette action est irréversible.',

                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#ef4444',

                cancelButtonColor: '#6b7280',

                confirmButtonText: 'Oui, supprimer',

                cancelButtonText: 'Annuler',

                background: '#0f172a',

                color: '#ffffff',

                borderRadius: '15px'

            }).then((result) => {

                if (result.isConfirmed) {

                    form.submit();

                }

            });

        });

    });

});

</script>

@endsection
