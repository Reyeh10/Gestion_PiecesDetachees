@extends('layouts.layoutMaster')

@section('title', 'Détail de l\'ajustement inventaire')

@section('content')

<div class="card shadow-sm border-0">

    {{-- ============================================================
        HEADER
    ============================================================ --}}
    <div class="card-header bg-white border-bottom">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

            <div>
                <h4 class="mb-1 fw-bold">
                    <i class="bx bx-detail me-2"></i>
                    Détail de l'ajustement inventaire
                </h4>

                <small class="text-muted">
                    Consultation uniquement
                </small>
            </div>

            <a
                href="{{ route('inventory-adjustments.index') }}"
                class="btn btn-secondary"
            >
                <i class="bx bx-arrow-back me-1"></i>
                Retour
            </a>

        </div>
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
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-end">

            <a
                href="{{ route('inventory-adjustments.index') }}"
                class="btn btn-secondary"
            >
                <i class="bx bx-arrow-back me-1"></i>
                Retour à la liste
            </a>

        </div>
    </div>

</div>

@endsection
