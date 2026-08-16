@extends('layouts.layoutMaster')

@section('content')

<form action="{{ route('inventory-adjustments.store') }}"
      method="POST"
      id="inventoryAdjustmentForm">

    @csrf

    <div class="card shadow-sm border-0">

        {{-- HEADER --}}
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">

            <div>

                <h4 class="mb-1 fw-bold">
                    Nouvel ajustement inventaire
                </h4>

                <small class="text-muted">
                    Corriger le stock selon la quantité réellement comptée
                </small>

            </div>

            <a href="{{ route('inventory-adjustments.index') }}"
               class="btn btn-secondary">

                <i class="bx bx-arrow-back me-1"></i>

                Retour

            </a>

        </div>

        {{-- BODY --}}
        <div class="card-body">

            @include('inventory_adjustments.form')

        </div>

        {{-- FOOTER --}}
        <div class="card-footer bg-white text-end">

            <a href="{{ route('inventory-adjustments.index') }}"
               class="btn btn-light me-2">

                Annuler

            </a>

            <button type="submit"
                    class="btn btn-primary">

                <i class="bx bx-save me-1"></i>

                Enregistrer ajustement

            </button>

        </div>

    </div>

</form>

@endsection