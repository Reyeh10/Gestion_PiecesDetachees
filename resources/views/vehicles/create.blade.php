@extends('layouts.layoutMaster')

@section('content')

<div class="card shadow-sm border-0">

    <div class="card-header border-0">

        <div class="d-flex justify-content-between align-items-center">

            <div>
                <h3 class="mb-1 fw-bold">
                    Nouveau véhicule
                </h3>

                <p class="text-muted mb-0">
                    Ajouter un véhicule dans le système.
                </p>
            </div>

            <a
                href="{{ route('vehicles.index') }}"
                class="btn btn-outline-secondary"
            >
                <i class="bx bx-arrow-back me-1"></i>
                Retour
            </a>

        </div>

    </div>

    <form
        action="{{ route('vehicles.store') }}"
        method="POST"
    >

        @csrf

        <div class="card-body">

            @include('vehicles._form')

        </div>

        <div class="card-footer bg-white text-end">

            <a
                href="{{ route('vehicles.index') }}"
                class="btn btn-outline-secondary"
            >
                Annuler
            </a>

            <button
                type="submit"
                class="btn btn-primary"
            >
                <i class="bx bx-save me-1"></i>
                Enregistrer
            </button>

        </div>

    </form>

</div>

@endsection
