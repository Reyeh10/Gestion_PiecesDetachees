@extends('layouts.layoutMaster')

@section('content')

<form action="{{ route('inventory-adjustments.store') }}"
      method="POST">

    @csrf

    <div class="card shadow-sm border-0">

        {{-- HEADER --}}
        <div class="card-header d-flex justify-content-between align-items-center">

            <h4 class="mb-0">
                Nouvel ajustement inventaire
            </h4>

            <a href="{{ route('inventory-adjustments.index') }}"
               class="btn btn-secondary">

                <i class="bx bx-arrow-back"></i>

                Retour

            </a>

        </div>

        {{-- BODY --}}
        <div class="card-body">

            {{-- ERROR --}}
            @if(session('error'))

                <div class="alert alert-danger">

                    {{ session('error') }}

                </div>

            @endif

            {{-- VALIDATION --}}
            @if($errors->any())

                <div class="alert alert-danger">

                    <ul class="mb-0">

                        @foreach($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif

            <div class="row">

                {{-- PRODUIT --}}
                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Produit
                    </label>

                    <select name="product_id"
                            class="form-control"
                            required>

                        <option value="">
                            -- Sélectionner --
                        </option>

                        @foreach($products as $product)

                            <option value="{{ $product->id }}">

                                {{ $product->reference }}
                                -
                                {{ $product->designation }}

                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- NOUVELLE QUANTITE --}}
                <div class="col-md-3 mb-3">

                    <label class="form-label">
                        Nouvelle quantité
                    </label>

                    <input type="number"
                           min="0"
                           name="new_qty"
                           class="form-control"
                           required>

                </div>

                {{-- RAISON --}}
                <div class="col-md-12 mb-3">

                    <label class="form-label">
                        Raison de l'ajustement
                    </label>

                    <textarea name="reason"
                              rows="4"
                              class="form-control"
                              required></textarea>

                </div>

            </div>

        </div>

        {{-- FOOTER --}}
        <div class="card-footer text-end">

            <button type="submit"
                    class="btn btn-primary">

                <i class="bx bx-save"></i>

                Enregistrer ajustement

            </button>

        </div>

    </div>

</form>

@endsection
