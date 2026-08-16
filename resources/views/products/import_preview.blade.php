@extends('layouts.layoutMaster')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | DETECTION GLOBALE DES ERREURS
    |--------------------------------------------------------------------------
    */
    $hasImportErrors = collect($data)->contains(function ($row) {
        return !empty($row['errors']);
    });
@endphp

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

        {{-- INFORMATIONS FOURNISSEUR / DEPOT --}}
        <div class="row g-3 mb-3">

            {{-- FOURNISSEUR --}}
            <div class="col-md-6">

                <div class="alert alert-info mb-0">
                    <strong>Fournisseur sélectionné :</strong>
                    {{ $supplier->name }}
                </div>

            </div>

            {{-- DEPOT --}}
            <div class="col-md-6">

                <div class="alert alert-primary mb-0">
                    <strong>Dépôt sélectionné :</strong>
                    {{ $depot->name }}
                </div>

            </div>

        </div>

        {{-- ERREURS LARAVEL --}}
        @if($errors->any())

            <div class="alert alert-danger">

                <strong>Erreurs détectées :</strong>

                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>

        @endif

        {{-- MESSAGE SI LE FICHIER EXCEL CONTIENT DES ERREURS --}}
        @if($hasImportErrors)

            <div class="alert alert-warning d-flex align-items-center gap-2">
                <i class="bx bx-error-circle fs-4"></i>
                <div>
                    Corrigez les champs signalés dans le fichier Excel avant de lancer l'importation.
                </div>
            </div>

        @endif

        {{-- FORMULAIRE --}}
        <form action="{{ route('products.import.store') }}"
              method="POST">

            @csrf

            {{-- FOURNISSEUR --}}
            <input type="hidden"
                   name="supplier_id"
                   value="{{ $supplier->id }}">

            {{-- DEPOT --}}
            <input type="hidden"
                   name="depot_id"
                   value="{{ $depot->id }}">

            {{-- TABLE --}}
            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th>Référence</th>
                            <th>Désignation</th>
                            <th>Marque</th>
                            <th>Modèle</th>
                            <th>Famille</th>
                            <th class="text-center">Qté</th>
                            <th class="text-end">Prix Achat</th>
                            <th class="text-end">Prix Vente</th>
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
                                <td class="text-center">
                                    {{ $row['quantity'] }}
                                </td>

                                {{-- PRIX ACHAT --}}
                                <td class="text-end">
                                    @if(is_numeric($row['purchase_price']))
                                        {{ number_format((float) $row['purchase_price'], 2) }}
                                    @endif
                                </td>

                                {{-- PRIX VENTE --}}
                                <td class="text-end">
                                    {{ number_format((float) $row['sale_price'], 2) }}
                                </td>

                                {{-- ERREURS --}}
                                <td>

                                    @if(!empty($row['errors']))

                                        @foreach($row['errors'] as $error)

                                            <div class="mb-1">
                                                <span class="badge bg-danger">
                                                    Ligne {{ $row['excel_line'] }} : {{ $error }}
                                                </span>
                                            </div>

                                        @endforeach

                                    @else

                                        <span class="badge bg-success">
                                            OK
                                        </span>

                                    @endif

                                </td>

                                {{-- NUMERO DE LIGNE EXCEL --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][excel_line]"
                                       value="{{ $row['excel_line'] }}">

                                {{-- REFERENCE --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][reference]"
                                       value="{{ $row['reference'] }}">

                                {{-- DESIGNATION --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][designation]"
                                       value="{{ $row['designation'] }}">

                                {{-- MARQUE --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][brand_name]"
                                       value="{{ $row['brand_name'] }}">

                                {{-- MODELE --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][model_name]"
                                       value="{{ $row['model_name'] }}">

                                {{-- FAMILLE --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][family_name]"
                                       value="{{ $row['family_name'] }}">

                                {{-- SOUS-FAMILLE --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][subfamily_name]"
                                       value="{{ $row['subfamily_name'] }}">

                                {{-- RAYON --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][rayon_name]"
                                       value="{{ $row['rayon_name'] }}">

                                {{-- EMPLACEMENT --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][location_name]"
                                       value="{{ $row['location_name'] }}">

                                {{-- QUANTITE --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][quantity]"
                                       value="{{ $row['quantity'] }}">

                                {{-- STOCK MINIMUM --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][min_stock]"
                                       value="{{ $row['min_stock'] }}">

                                {{-- STOCK MAXIMUM --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][max_stock]"
                                       value="{{ $row['max_stock'] }}">

                                {{-- PRIX ACHAT --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][purchase_price]"
                                       value="{{ $row['purchase_price'] }}">

                                {{-- COEFFICIENT ACHAT --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][coef_purchase]"
                                       value="{{ $row['coef_purchase'] }}">

                                {{-- COEFFICIENT VENTE --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][coef_sale]"
                                       value="{{ $row['coef_sale'] }}">

                                {{-- TYPE UNITE --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][unit_type]"
                                       value="{{ $row['unit_type'] }}">

                                {{-- LIBELLE UNITE --}}
                                <input type="hidden"
                                       name="products[{{ $index }}][unit_label]"
                                       value="{{ $row['unit_label'] }}">

                            </tr>

                        @empty

                            <tr>
                                <td colspan="9"
                                    class="text-center text-muted py-4">
                                    Aucun produit trouvé
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- BOUTONS --}}
            <div class="d-flex justify-content-between align-items-center mt-4">

                <a href="{{ route('products.index') }}"
                   class="btn btn-secondary">
                    <i class="bx bx-arrow-back"></i>
                    Retour
                </a>

                <button type="submit"
                        class="btn btn-success"
                        @if($hasImportErrors || empty($data)) disabled @endif>

                    <i class="bx bx-check"></i>
                    Importer Produits

                </button>

            </div>

        </form>

    </div>

</div>

@endsection
