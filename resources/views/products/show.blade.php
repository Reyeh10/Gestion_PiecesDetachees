@extends('layouts.layoutMaster')
@section('content')
<div class="card shadow-sm">

    {{-- HEADER --}}
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">

        <div>

            <h4 class="mb-1">

                {{ $product->designation }}

            </h4>

            <small class="text-muted">

                Référence :
                {{ $product->reference }}

            </small>

        </div>

        <div class="d-flex gap-2">

           @if(
                auth()->user()->role == 'admin'
                || auth()->user()->role == 'chef_magasinier'
            )

                <a href="{{ route('products.edit', $product->id) }}"
                class="btn btn-warning">

                    <i class="bx bx-edit"></i>

                    Modifier

                </a>

            @endif

            <a href="{{ route('products.index') }}"
               class="btn btn-secondary btn-sm">

                <i class="bx bx-arrow-back"></i>

                Retour

            </a>

        </div>

    </div>

    {{-- BODY --}}
    <div class="card-body">

        <div class="row">

            {{-- INFORMATIONS --}}
            <div class="col-md-6">

                <div class="card border mb-4">

                    <div class="card-header bg-light">

                        <strong>
                            Informations Produit
                        </strong>

                    </div>

                    <div class="card-body">

                        <table class="table table-sm">

                            <tr>
                                <th width="40%">Référence</th>
                                <td>{{ $product->reference }}</td>
                            </tr>

                            <tr>
                                <th>Désignation</th>
                                <td>{{ $product->designation }}</td>
                            </tr>

                            <tr>
                                <th>Marque</th>
                                <td>{{ $product->brand?->name ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Modèle</th>
                                <td>{{ $product->model?->name ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Famille</th>
                                <td>{{ $product->family?->name ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Sous-famille</th>
                                <td>{{ $product->subfamily?->name ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Rayon</th>
                                <td>{{ $product->rayon?->name ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Emplacement</th>
                                <td>{{ $product->location?->name ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Type unité</th>
                                <td>{{ $product->unit_type ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Libellé unité</th>
                                <td>{{ $product->unit_label ?? '-' }}</td>
                            </tr>

                        </table>

                    </div>

                </div>

            </div>

            {{-- STOCK --}}
            <div class="col-md-6">

                <div class="card border mb-4">

                    <div class="card-header bg-light">

                        <strong>
                            Informations Stock
                        </strong>

                    </div>

                    <div class="card-body">

                        <table class="table table-sm">

                            <tr>
                                <th width="40%">Quantité totale</th>
                                <td>

                                    <span class="badge bg-primary">

                                        {{ $product->quantity }}

                                    </span>

                                </td>
                            </tr>

                            <tr>
                                <th>Quantité vendue</th>
                                <td>

                                    <span class="badge bg-danger">

                                        {{ $soldQuantity }}

                                    </span>

                                </td>
                            </tr>

                            <tr>
                                <th>Quantité disponible</th>
                                <td>

                                    <span class="badge bg-success">

                                        {{ $availableQuantity }}

                                    </span>

                                </td>
                            </tr>

                            <tr>
                                <th>Seuil minimum</th>
                                <td>{{ $product->min_stock }}</td>
                            </tr>

                            <tr>
                                <th>Seuil maximum</th>
                                <td>{{ $product->max_stock }}</td>
                            </tr>

                            <tr>
                                <th>Status</th>

                                <td>

                                    @if($availableQuantity <= 0)

                                        <span class="badge bg-danger">

                                            Rupture

                                        </span>

                                    @elseif($availableQuantity <= $product->min_stock)

                                        <span class="badge bg-warning text-dark">

                                            Stock faible

                                        </span>

                                    @else

                                        <span class="badge bg-success">

                                            Disponible

                                        </span>

                                    @endif

                                </td>

                            </tr>

                        </table>

                    </div>

                </div>

            </div>

        </div>

        {{-- PRIX --}}
        <div class="card border mb-4">

            <div class="card-header bg-light">

                <strong>
                    Informations Prix
                </strong>

            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Prix achat
                        </label>

                        <input type="text"
                               class="form-control"
                               value="{{ number_format($product->purchase_price, 2) }}"
                               readonly>

                    </div>

                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Coef achat
                        </label>

                        <input type="text"
                               class="form-control"
                               value="{{ $product->coef_purchase }}"
                               readonly>

                    </div>

                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Prix revient
                        </label>

                        <input type="text"
                               class="form-control"
                               value="{{ number_format($product->cost_price, 2) }}"
                               readonly>

                    </div>

                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Prix vente
                        </label>

                        <input type="text"
                               class="form-control"
                               value="{{ number_format($product->sale_price, 2) }}"
                               readonly>

                    </div>

                </div>

            </div>

        </div>

        {{-- FOURNISSEURS --}}
        <div class="card border">

            <div class="card-header bg-light">

                <strong>
                    Fournisseurs liés
                </strong>

            </div>

            <div class="card-body">

                @if($product->suppliers->count() > 0)

                    <div class="table-responsive">

                        <table class="table table-bordered">

                            <thead>

                                <tr>

                                    <th>Nom</th>
                                    <th>Téléphone</th>
                                    <th>Email</th>
                                    <th>Devise</th>

                                </tr>

                            </thead>

                            <tbody>

                                @foreach($product->suppliers as $supplier)

                                    <tr>

                                        <td>

                                            {{ $supplier->name }}

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

                    <div class="alert alert-warning mb-0">

                        Aucun fournisseur lié à ce produit.

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection
