@extends('layouts.layoutMaster')

@section('content')

<div class="card shadow-sm border-0">

    {{-- ============================================================
        HEADER
    ============================================================ --}}
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">

        <div>

            <h4 class="mb-1 fw-bold">
                Détail de l'ajustement inventaire
            </h4>

            <small class="text-muted">
                Consultation uniquement
            </small>

        </div>

        <a href="{{ route('inventory-adjustments.index') }}"
           class="btn btn-secondary">

            <i class="bx bx-arrow-back me-1"></i>

            Retour

        </a>

    </div>


    {{-- ============================================================
        BODY
    ============================================================ --}}
    <div class="card-body">

        @include('inventory_adjustments.form', [
            'readonly' => true
        ])

    </div>


    {{-- ============================================================
        FOOTER
    ============================================================ --}}
    <div class="card-footer bg-white text-end">

        <a href="{{ route('inventory-adjustments.index') }}"
           class="btn btn-secondary">

            <i class="bx bx-arrow-back me-1"></i>

            Retour à la liste

        </a>

    </div>

</div>

@endsection