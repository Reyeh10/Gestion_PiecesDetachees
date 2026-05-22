@extends('layouts.layoutMaster')

@section('content')

<div class="card shadow-sm border-0">

    {{-- HEADER --}}
    <div class="card-header d-flex justify-content-between align-items-center">

        <div>

            <h4 class="mb-1 fw-bold">
                Détails du mouvement
            </h4>

            <p class="text-muted mb-0">
                Informations complètes du mouvement de stock
            </p>

        </div>

        <a href="{{ route('stock-movements.index') }}"
           class="btn btn-secondary rounded-pill">

            <i class="bx bx-arrow-back"></i>

            Retour

        </a>

    </div>

    {{-- BODY --}}
    <div class="card-body">

        <div class="row">

            {{-- PRODUIT --}}
            <div class="col-md-6 mb-4">

                <div class="border rounded p-3 h-100">

                    <small class="text-muted d-block mb-1">
                        Produit
                    </small>

                    <h5 class="mb-0 fw-bold text-dark">

                        {{ $stockMovement->product->designation ?? '-' }}

                    </h5>

                </div>

            </div>

            {{-- REFERENCE --}}
            <div class="col-md-6 mb-4">

                <div class="border rounded p-3 h-100">

                    <small class="text-muted d-block mb-1">
                        Référence
                    </small>

                    <h5 class="mb-0 fw-bold text-primary">

                        {{ $stockMovement->product->reference ?? '-' }}

                    </h5>

                </div>

            </div>

            {{-- MARQUE --}}
            <div class="col-md-6 mb-4">

                <div class="border rounded p-3 h-100">

                    <small class="text-muted d-block mb-1">
                        Marque
                    </small>

                    <h6 class="mb-0">

                        {{ $stockMovement->product->brand->name ?? '-' }}

                    </h6>

                </div>

            </div>

            {{-- MODELE --}}
            <div class="col-md-6 mb-4">

                <div class="border rounded p-3 h-100">

                    <small class="text-muted d-block mb-1">
                        Modèle
                    </small>

                    <h6 class="mb-0">

                        {{ $stockMovement->product->model->name ?? '-' }}

                    </h6>

                </div>

            </div>

            {{-- TYPE --}}
            <div class="col-md-6 mb-4">

                <div class="border rounded p-3 h-100">

                    <small class="text-muted d-block mb-2">
                        Type mouvement
                    </small>

                    @if($stockMovement->type == 'in')

                        <span class="badge bg-success px-3 py-2">
                            Entrée stock
                        </span>

                    @else

                        <span class="badge bg-danger px-3 py-2">
                            Sortie stock
                        </span>

                    @endif

                </div>

            </div>

            {{-- QUANTITE --}}
            <div class="col-md-6 mb-4">

                <div class="border rounded p-3 h-100">

                    <small class="text-muted d-block mb-1">
                        Quantité
                    </small>

                    <h4 class="fw-bold text-dark mb-0">

                        {{ $stockMovement->quantity }}

                    </h4>

                </div>

            </div>

            {{-- UTILISATEUR --}}
            <div class="col-md-6 mb-4">

                <div class="border rounded p-3 h-100">

                    <small class="text-muted d-block mb-1">
                        Utilisateur
                    </small>

                    <h6 class="mb-0">

                        {{ $stockMovement->user->name ?? '-' }}

                    </h6>

                </div>

            </div>

            {{-- DATE --}}
            <div class="col-md-6 mb-4">

                <div class="border rounded p-3 h-100">

                    <small class="text-muted d-block mb-1">
                        Date
                    </small>

                    <h6 class="mb-0">

                        {{ $stockMovement->created_at->format('d/m/Y H:i') }}

                    </h6>

                </div>

            </div>

            {{-- SOURCE --}}
            <div class="col-md-6 mb-4">

                <div class="border rounded p-3 h-100">

                    <small class="text-muted d-block mb-1">
                        Source
                    </small>

                    <h6 class="mb-0">

                        {{ $stockMovement->source ?? '-' }}

                    </h6>

                </div>

            </div>

            {{-- REFERENCE MOUVEMENT --}}
            <div class="col-md-6 mb-4">

                <div class="border rounded p-3 h-100">

                    <small class="text-muted d-block mb-1">
                        Référence mouvement
                    </small>

                    <h6 class="mb-0">

                        {{ $stockMovement->reference ?? '-' }}

                    </h6>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
