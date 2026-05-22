@extends('layouts.layoutMaster')

@section('content')

<div class="card shadow-sm border-0">

    {{-- HEADER --}}
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">

        <div>

            <h4 class="mb-1 fw-bold">
                Nouveau transfert entre dépôts
            </h4>

            <small class="text-muted">
                Déplacer un produit d’un dépôt vers un autre
            </small>

        </div>

        <a href="{{ route('depot-transfers.index') }}"
           class="btn btn-secondary">

            <i class="bx bx-arrow-back"></i>

            Retour

        </a>

    </div>

    {{-- BODY --}}
    <div class="card-body">

        {{-- SUCCESS --}}
        @if(session('success'))

            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center">

                <i class="bx bx-check-circle fs-3 me-2"></i>

                <div>

                    <strong>
                        Succès
                    </strong>

                    <br>

                    {{ session('success') }}

                </div>

            </div>

        @endif

        {{-- ERROR --}}
        @if(session('error'))

            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">

                <div class="d-flex align-items-center">

                    <i class="bx bx-error-circle fs-2 me-3"></i>

                    <div>

                        <strong>
                            Transfert impossible
                        </strong>

                        <br>

                        {{ session('error') }}

                    </div>

                </div>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                </button>

            </div>

        @endif

        {{-- VALIDATION ERRORS --}}
        @if($errors->any())

            <div class="alert alert-danger border-0 shadow-sm">

                <strong>
                    Erreurs détectées :
                </strong>

                <ul class="mb-0 mt-2">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif

        {{-- FORM --}}
        <form action="{{ route('depot-transfers.store') }}"
              method="POST">

            @csrf

            <div class="row">

                {{-- PRODUIT --}}
                <div class="col-md-12 mb-3">

                    <label class="form-label fw-semibold">

                        Produit

                    </label>

                    <select name="product_id"
                            class="form-select"
                            required>

                        <option value="">
                            -- Sélectionner un produit --
                        </option>

                        @foreach($products as $product)

                            <option value="{{ $product->id }}"
                                {{ old('product_id') == $product->id ? 'selected' : '' }}>

                                {{ $product->reference ?? '' }}
                                -
                                {{ $product->designation ?? $product->name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- DEPOT SOURCE --}}
                <div class="col-md-6 mb-3">

                    <label class="form-label fw-semibold">

                        Dépôt source

                    </label>

                    <select name="source_depot_id"
                            class="form-select"
                            required>

                        <option value="">
                            -- Sélectionner --
                        </option>

                        @foreach($depots as $depot)

                            <option value="{{ $depot->id }}"
                                {{ old('source_depot_id') == $depot->id ? 'selected' : '' }}>

                                {{ $depot->name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- DEPOT DESTINATION --}}
                <div class="col-md-6 mb-3">

                    <label class="form-label fw-semibold">

                        Dépôt destination

                    </label>

                    <select name="destination_depot_id"
                            class="form-select"
                            required>

                        <option value="">
                            -- Sélectionner --
                        </option>

                        @foreach($depots as $depot)

                            <option value="{{ $depot->id }}"
                                {{ old('destination_depot_id') == $depot->id ? 'selected' : '' }}>

                                {{ $depot->name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- QUANTITE --}}
                <div class="col-md-4 mb-3">

                    <label class="form-label fw-semibold">

                        Quantité à transférer

                    </label>

                    <input type="number"
                           name="quantity"
                           class="form-control"
                           min="1"
                           value="{{ old('quantity') }}"
                           required>

                </div>

                {{-- NOTE --}}
                <div class="col-md-8 mb-3">

                    <label class="form-label fw-semibold">

                        Note

                    </label>

                    <input type="text"
                           name="note"
                           class="form-control"
                           value="{{ old('note') }}"
                           placeholder="Ex : transfert urgent vers dépôt principal">

                </div>

            </div>

            {{-- BUTTONS --}}
            <div class="d-flex justify-content-end gap-2 mt-3">

                <a href="{{ route('depot-transfers.index') }}"
                   class="btn btn-secondary">

                    <i class="bx bx-x"></i>

                    Annuler

                </a>

                <button type="submit"
                        class="btn btn-primary">

                    <i class="bx bx-transfer"></i>

                    Valider le transfert

                </button>

            </div>

        </form>

    </div>

</div>

@endsection
