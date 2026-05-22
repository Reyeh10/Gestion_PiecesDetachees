@extends('layouts.layoutMaster')

@section('content')

<div class="row">

    <div class="col-12">

        <div class="card shadow-sm border-0">

            {{-- HEADER --}}
            <div class="card-header d-flex justify-content-between align-items-center">

                <h4 class="mb-0">
                    Modifier le transfert
                </h4>

                <a href="{{ route('depot-transfers.index') }}"
                   class="btn btn-secondary btn-sm">

                    <i class="bx bx-arrow-back"></i>

                    Retour

                </a>

            </div>

            {{-- BODY --}}
            <div class="card-body">

                <form
                    action="{{ route('depot-transfers.update', $transfer->id) }}"
                    method="POST">

                    @csrf
                    @method('PUT')

                    <div class="row g-4">

                        {{-- PRODUIT --}}
                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Produit
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                value="{{ $transfer->product->designation ?? '-' }}"
                                readonly>

                        </div>

                        {{-- QUANTITE --}}
                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Quantité
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                value="{{ $transfer->quantity }}"
                                readonly>

                        </div>

                        {{-- DEPOT SOURCE --}}
                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Dépôt source
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                value="{{ $transfer->sourceDepot->name ?? '-' }}"
                                readonly>

                        </div>

                        {{-- DEPOT DESTINATION --}}
                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Dépôt destination
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                value="{{ $transfer->destinationDepot->name ?? '-' }}"
                                readonly>

                        </div>

                        {{-- NOTE --}}
                        <div class="col-12">

                            <label class="form-label fw-bold">
                                Note
                            </label>

                            <textarea
                                name="note"
                                rows="5"
                                class="form-control">{{ $transfer->note }}</textarea>

                        </div>

                    </div>

                    {{-- BUTTONS --}}
                    <div class="mt-4 d-flex justify-content-end gap-2">

                        <a href="{{ route('depot-transfers.index') }}"
                           class="btn btn-secondary">

                            Annuler

                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary">

                            <i class="bx bx-save"></i>

                            Mettre à jour

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection
