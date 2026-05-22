@extends('layouts.layoutMaster')

@section('content')

<div class="row">

    <div class="col-12">

        <div class="card shadow-sm border-0">

            {{-- HEADER --}}
            <div class="card-header d-flex justify-content-between align-items-center">

                <h4 class="mb-0">
                    Détails du transfert
                </h4>

                <a href="{{ route('depot-transfers.index') }}"
                   class="btn btn-secondary btn-sm">

                    <i class="bx bx-arrow-back"></i>

                    Retour

                </a>

            </div>

            {{-- BODY --}}
            <div class="card-body">

                <div class="row g-4">

                    {{-- PRODUIT --}}
                    <div class="col-md-6">

                        <label class="fw-bold">
                            Produit
                        </label>

                        <div class="form-control">

                            {{ $transfer->product->designation ?? '-' }}

                        </div>

                    </div>

                    {{-- QUANTITE --}}
                    <div class="col-md-6">

                        <label class="fw-bold">
                            Quantité
                        </label>

                        <div class="form-control">

                            {{ $transfer->quantity }}

                        </div>

                    </div>

                    {{-- DEPOT SOURCE --}}
                    <div class="col-md-6">

                        <label class="fw-bold">
                            Dépôt source
                        </label>

                        <div class="form-control">

                            {{ $transfer->sourceDepot->name ?? '-' }}

                        </div>

                    </div>

                    {{-- DEPOT DESTINATION --}}
                    <div class="col-md-6">

                        <label class="fw-bold">
                            Dépôt destination
                        </label>

                        <div class="form-control">

                            {{ $transfer->destinationDepot->name ?? '-' }}

                        </div>

                    </div>

                    {{-- UTILISATEUR --}}
                    <div class="col-md-6">

                        <label class="fw-bold">
                            Utilisateur
                        </label>

                        <div class="form-control">

                            {{ $transfer->user->name ?? '-' }}

                        </div>

                    </div>

                    {{-- DATE --}}
                    <div class="col-md-6">

                        <label class="fw-bold">
                            Date
                        </label>

                        <div class="form-control">

                            {{ $transfer->created_at->format('d/m/Y H:i') }}

                        </div>

                    </div>

                    {{-- NOTE --}}
                    <div class="col-12">

                        <label class="fw-bold">
                            Note
                        </label>

                        <textarea
                            class="form-control"
                            rows="4"
                            readonly>{{ $transfer->note }}</textarea>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
