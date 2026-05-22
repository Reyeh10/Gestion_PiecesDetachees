@extends('layouts.layoutMaster')

@section('content')

<div class="card shadow-sm border-0">

    {{-- HEADER --}}
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>

            <h2 class="mb-1 fw-bold">

                Compte dépôt

            </h2>

            <small class="text-muted">

                Informations et gestion du dépôt

            </small>

        </div>

        <div class="d-flex gap-2">

            <a href="{{ route('depots.edit', $depot) }}"
               class="btn btn-warning">

                <i class="bx bx-edit"></i>

                Modifier

            </a>

            <a href="{{ route('depots.index') }}"
               class="btn btn-secondary">

                Retour

            </a>

        </div>

    </div>





    <div class="card-body">

        {{-- INFOS --}}
        <div class="row mb-5">

            <div class="col-md-4 mb-3">

                <label class="fw-bold">
                    Nom dépôt
                </label>

                <input type="text"
                       class="form-control"
                       value="{{ $depot->name }}"
                       readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">
                    Code
                </label>

                <input type="text"
                       class="form-control"
                       value="{{ $depot->code }}"
                       readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">
                    Statut
                </label>

                <input type="text"
                       class="form-control"
                       value="{{ $depot->is_active ? 'Actif' : 'Inactif' }}"
                       readonly>

            </div>

            <div class="col-md-12">

                <label class="fw-bold">
                    Adresse
                </label>

                <textarea class="form-control"
                          rows="3"
                          readonly>{{ $depot->address }}</textarea>

            </div>

        </div>





        {{-- KPI --}}
        <div class="row mb-5">

            <div class="col-md-3">

                <div class="card border shadow-sm">

                    <div class="card-body text-center">

                        <h6 class="text-muted">
                            Produits stockés
                        </h6>

                        <h1 class="text-primary">

                            {{ $totalProducts }}

                        </h1>

                    </div>

                </div>

            </div>





            <div class="col-md-3">

                <div class="card border shadow-sm">

                    <div class="card-body text-center">

                        <h6 class="text-muted">
                            Quantité totale
                        </h6>

                        <h1 class="text-success">

                            {{ number_format($totalQuantity, 2) }}

                        </h1>

                    </div>

                </div>

            </div>





            <div class="col-md-3">

                <div class="card border shadow-sm">

                    <div class="card-body text-center">

                        <h6 class="text-muted">
                            Stock faible
                        </h6>

                        <h1 class="text-warning">

                            {{ $lowStocks }}

                        </h1>

                    </div>

                </div>

            </div>





            <div class="col-md-3">

                <div class="card border shadow-sm">

                    <div class="card-body text-center">

                        <h6 class="text-muted">
                            Ruptures
                        </h6>

                        <h1 class="text-danger">

                            {{ $ruptures }}

                        </h1>

                    </div>

                </div>

            </div>

        </div>





        {{-- VALEUR --}}
        <div class="card border shadow-sm mb-5">

            <div class="card-body text-center">

                <h5 class="text-muted">

                    Valeur totale stock

                </h5>

                <h1 class="text-primary">

                    {{ number_format($totalValue, 2) }} $

                </h1>

            </div>

        </div>





        {{-- PRODUITS --}}
        <h3 class="mb-3">

            Produits stockés

        </h3>

        <div class="table-responsive mb-5">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-light">

                    <tr>

                        <th>Référence</th>
                        <th>Produit</th>
                        <th>Marque</th>
                        <th>Modèle</th>
                        <th>Stock</th>
                        <th>Prix vente</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($stocks->where('quantity', '>', 0) as $stock)

                        <tr>

                            <td>

                                {{ $stock->product->reference }}

                            </td>

                            <td>

                                {{ $stock->product->designation }}

                            </td>

                            <td>

                                {{ $stock->product->brand->name ?? '-' }}

                            </td>

                            <td>

                                {{ $stock->product->model->name ?? '-' }}

                            </td>

                            <td>

                                @if($stock->quantity <= 0)

                                    <span class="badge bg-danger">

                                        Rupture

                                    </span>

                                @elseif(
                                    $stock->quantity <=
                                    ($stock->product->min_stock ?? 0)
                                )

                                    <span class="badge bg-warning">

                                        {{ $stock->quantity }}

                                    </span>

                                @else

                                    <span class="badge bg-success">

                                        {{ $stock->quantity }}

                                    </span>

                                @endif

                            </td>

                            <td>

                                {{ number_format($stock->product->sale_price, 2) }} $

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6"
                                class="text-center text-muted">

                                Aucun produit trouvé.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>





        {{-- TRANSFERTS --}}
        <h3 class="mb-3">

            Historique transferts

        </h3>

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-light">

                    <tr>

                        <th>Date</th>
                        <th>Produit</th>
                        <th>Source</th>
                        <th>Destination</th>
                        <th>Qté</th>
                        <th>Utilisateur</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($transfers as $transfer)

                        <tr>

                            <td>

                                {{ $transfer->created_at->format('d/m/Y H:i') }}

                            </td>

                            <td>

                                {{ $transfer->product->designation ?? '-' }}

                            </td>

                            <td>

                                {{ $transfer->sourceDepot->name ?? '-' }}

                            </td>

                            <td>

                                {{ $transfer->destinationDepot->name ?? '-' }}

                            </td>

                            <td>

                                {{ $transfer->quantity }}

                            </td>

                            <td>

                                {{ $transfer->user->name ?? '-' }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6"
                                class="text-center text-muted">

                                Aucun transfert trouvé.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
