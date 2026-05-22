@extends('layouts.layoutMaster')

@section('content')

<div class="card shadow-sm">

    {{-- HEADER --}}
    <div class="card-header d-flex justify-content-between align-items-center">

        <h4 class="mb-0">
            Prévisualisation Importation
        </h4>

        <a href="{{ route('products.index') }}"
           class="btn btn-secondary btn-sm">

            <i class="bx bx-arrow-back"></i>

            Retour

        </a>

    </div>

    {{-- BODY --}}
    <div class="card-body">

        {{-- INFO --}}
        <div class="row mb-3">

            {{-- FOURNISSEUR --}}
            <div class="col-md-6">

                <div class="alert alert-info mb-0">

                    <strong>
                        Fournisseur sélectionné :
                    </strong>

                    {{ $supplier->name }}

                </div>

            </div>

            {{-- DEPOT --}}
            <div class="col-md-6">

                <div class="alert alert-primary mb-0">

                    <strong>
                        Dépôt sélectionné :
                    </strong>

                    {{ $depot->name }}

                </div>

            </div>

        </div>

        {{-- ERRORS --}}
        @if($errors->any())

            <div class="alert alert-danger">

                <strong>
                    Erreurs détectées :
                </strong>

                <ul class="mb-0 mt-2">

                    @foreach($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif

        {{-- FORM --}}
        <form action="{{ route('products.import.store') }}"
              method="POST">

            @csrf

            {{-- SUPPLIER --}}
            <input type="hidden"
                   name="supplier_id"
                   value="{{ $supplier->id }}">

            {{-- DEPOT --}}
            <input type="hidden"
                   name="depot_id"
                   value="{{ $depot->id }}">

            {{-- TABLE --}}
            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>Référence</th>
                            <th>Désignation</th>
                            <th>Marque</th>
                            <th>Modèle</th>
                            <th>Famille</th>
                            <th>Qté</th>
                            <th>Prix Achat</th>
                            <th>Prix Vente</th>
                            <th>Erreurs</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($data as $index => $row)

                            <tr>

                                {{-- REFERENCE --}}
                                <td>

                                    {{ $row['reference'] }}

                                </td>

                                {{-- DESIGNATION --}}
                                <td>

                                    {{ $row['designation'] }}

                                </td>

                                {{-- MARQUE --}}
                                <td>

                                    {{ $row['brand_name'] }}

                                </td>

                                {{-- MODELE --}}
                                <td>

                                    {{ $row['model_name'] }}

                                </td>

                                {{-- FAMILLE --}}
                                <td>

                                    {{ $row['family_name'] }}

                                </td>

                                {{-- QUANTITE --}}
                                <td>

                                    {{ $row['quantity'] }}

                                </td>

                                {{-- PRIX ACHAT --}}
                                <td>

                                    {{ number_format($row['purchase_price'], 2) }}

                                </td>

                                {{-- PRIX VENTE --}}
                                <td>

                                    {{ number_format($row['sale_price'], 2) }}

                                </td>

                                {{-- ERREURS --}}
                                <td>

                                    @if(count($row['errors']) > 0)

                                        @foreach($row['errors'] as $error)

                                            <span class="badge bg-danger">

                                                {{ $error }}

                                            </span>

                                        @endforeach

                                    @else

                                        <span class="badge bg-success">

                                            OK

                                        </span>

                                    @endif

                                </td>

                                {{-- HIDDEN INPUTS --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][reference]"
                                       value="{{ $row['reference'] }}">

                                <input type="hidden"
                                       name="products[{{ $index }}][designation]"
                                       value="{{ $row['designation'] }}">

                                <input type="hidden"
                                       name="products[{{ $index }}][brand_name]"
                                       value="{{ $row['brand_name'] }}">

                                <input type="hidden"
                                       name="products[{{ $index }}][model_name]"
                                       value="{{ $row['model_name'] }}">

                                <input type="hidden"
                                       name="products[{{ $index }}][family_name]"
                                       value="{{ $row['family_name'] }}">

                                <input type="hidden"
                                       name="products[{{ $index }}][subfamily_name]"
                                       value="{{ $row['subfamily_name'] }}">

                                <input type="hidden"
                                       name="products[{{ $index }}][rayon_name]"
                                       value="{{ $row['rayon_name'] }}">

                                <input type="hidden"
                                       name="products[{{ $index }}][location_name]"
                                       value="{{ $row['location_name'] }}">

                                <input type="hidden"
                                       name="products[{{ $index }}][quantity]"
                                       value="{{ $row['quantity'] }}">

                                <input type="hidden"
                                       name="products[{{ $index }}][min_stock]"
                                       value="{{ $row['min_stock'] }}">

                                <input type="hidden"
                                       name="products[{{ $index }}][max_stock]"
                                       value="{{ $row['max_stock'] }}">

                                <input type="hidden"
                                       name="products[{{ $index }}][purchase_price]"
                                       value="{{ $row['purchase_price'] }}">

                                <input type="hidden"
                                       name="products[{{ $index }}][coef_purchase]"
                                       value="{{ $row['coef_purchase'] }}">

                                <input type="hidden"
                                       name="products[{{ $index }}][coef_sale]"
                                       value="{{ $row['coef_sale'] }}">

                                <input type="hidden"
                                    name="products[{{ $index }}][unit_type]"
                                    value="{{ $row['unit_type'] }}">

                                <input type="hidden"
                                    name="products[{{ $index }}][unit_label]"
                                    value="{{ $row['unit_label'] }}">

                            </tr>

                        @empty

                            <tr>

                                <td colspan="9"
                                    class="text-center text-muted">

                                    Aucun produit trouvé

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- BUTTON --}}
            <div class="text-end mt-4">

                <button type="submit"
                        class="btn btn-success">

                    <i class="bx bx-check"></i>

                    Importer Produits

                </button>

            </div>

        </form>

    </div>

</div>

@endsection
