@extends('layouts.layoutMaster')

@section('content')

<div class="card">

    {{-- HEADER --}}
    <div class="card-header d-flex justify-content-between align-items-center">

        <div>
            <h3 class="mb-1">
                Compte fournisseur
            </h3>

            <small class="text-muted">
                Informations et historique fournisseur
            </small>
        </div>

        <div class="d-flex gap-2">

            <a href="{{ route('suppliers.edit', $supplier->id) }}"
               class="btn btn-warning">

                <i class="bx bx-edit"></i>
                Modifier

            </a>

            <a href="{{ route('suppliers.index') }}"
               class="btn btn-secondary">

                Retour

            </a>

        </div>

    </div>

    <div class="card-body">

        {{-- ========================================================= --}}
        {{-- INFOS FOURNISSEUR --}}
        {{-- ========================================================= --}}

        <div class="row">

            {{-- NOM --}}
            <div class="col-md-6 mb-4">

                <label class="fw-bold">
                    Nom fournisseur
                </label>

                <div class="form-control">
                    {{ $supplier->name }}
                </div>

            </div>

            {{-- TELEPHONE --}}
            <div class="col-md-6 mb-4">

                <label class="fw-bold">
                    Téléphone
                </label>

                <div class="form-control">
                    {{ $supplier->phone ?? '-' }}
                </div>

            </div>

            {{-- EMAIL --}}
            <div class="col-md-6 mb-4">

                <label class="fw-bold">
                    Email
                </label>

                <div class="form-control">
                    {{ $supplier->email ?? '-' }}
                </div>

            </div>

            {{-- DEVISE --}}
            <div class="col-md-6 mb-4">

                <label class="fw-bold">
                    Devise
                </label>

                <div class="form-control">
                    {{ $supplier->currency ?? 'USD' }}
                </div>

            </div>

            {{-- ADRESSE --}}
            <div class="col-md-12 mb-4">

                <label class="fw-bold">
                    Adresse
                </label>

                <div class="form-control"
                     style="min-height:100px;">

                    {{ $supplier->address ?? '-' }}

                </div>

            </div>

        </div>

        {{-- ========================================================= --}}
        {{-- STATISTIQUES --}}
        {{-- ========================================================= --}}

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

                            {{ number_format($supplier->purchases->sum('total'), 2) }}
                            $

                        </h2>

                    </div>

                </div>

            </div>

        </div>

        {{-- ========================================================= --}}
        {{-- PRODUITS FOURNIS --}}
        {{-- ========================================================= --}}

        <div class="mt-5">

            <h4 class="mb-3">
                Produits fournis
            </h4>

            <div class="table-responsive">

                <table class="table table-bordered align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>Référence</th>
                            <th>Produit</th>
                            <th>Marque</th>
                            <th>Modèle</th>
                            <th>Prix achat</th>
                            <th>Stock</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($supplier->products as $product)

                            <tr>

                                <td>
                                    {{ $product->reference }}
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
                                        ??
                                        $product->purchase_price
                                        ??
                                        0,
                                        2
                                    ) }}

                                    $

                                </td>

                                <td>

                                    <span class="badge bg-label-success">

                                        {{ $product->quantity }}

                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6"
                                    class="text-center text-muted">

                                    Aucun produit associé

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        {{-- ========================================================= --}}
        {{-- HISTORIQUE ACHATS --}}
        {{-- ========================================================= --}}

        <div class="mt-5">

            <h4 class="mb-3">
                Historique des achats
            </h4>

            <div class="table-responsive">

                <table class="table table-bordered align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>Référence</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Livraison</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($supplier->purchases as $purchase)

                            <tr>

                                <td>

                                    <a href="{{ route('purchases.show', $purchase->id) }}"
                                       class="fw-bold text-primary">

                                        {{ $purchase->reference }}

                                    </a>

                                </td>

                                <td>
                                    {{ $purchase->created_at->format('d/m/Y') }}
                                </td>

                                <td>

                                    {{ number_format($purchase->total, 2) }}
                                    $

                                </td>

                                <td>

                                    <span class="badge bg-label-success">

                                        {{ ucfirst($purchase->status) }}

                                    </span>

                                </td>

                                <td>

                                    @if($purchase->delivery_status == 'received')

                                        <span class="badge bg-success">
                                            Reçu
                                        </span>

                                    @elseif($purchase->delivery_status == 'partial_received')

                                        <span class="badge bg-warning">
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

                                <td colspan="5"
                                    class="text-center text-muted">

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
