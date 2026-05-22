@extends('layouts.layoutMaster')

@section('content')

<div class="card shadow-sm border-0">

    {{-- HEADER --}}
    <div class="card-header bg-white d-flex justify-content-between align-items-center">

        <h4 class="mb-0">
            Détails du client
        </h4>

        <a href="{{ route('customers.index') }}"
           class="btn btn-secondary btn-sm">

            <i class="bx bx-arrow-back"></i>

            Retour

        </a>

    </div>

    {{-- BODY --}}
    <div class="card-body">

        <div class="row g-4">

            {{-- NOM --}}
            <div class="col-md-6">

                <div class="border rounded-3 p-3 h-100">

                    <small class="text-muted d-block mb-1">
                        Nom
                    </small>

                    <h5 class="mb-0">
                        {{ $customer->name }}
                    </h5>

                </div>

            </div>

            {{-- TELEPHONE --}}
            <div class="col-md-6">

                <div class="border rounded-3 p-3 h-100">

                    <small class="text-muted d-block mb-1">
                        Téléphone
                    </small>

                    <h5 class="mb-0">
                        {{ $customer->phone ?? '-' }}
                    </h5>

                </div>

            </div>

            {{-- EMAIL --}}
            <div class="col-md-6">

                <div class="border rounded-3 p-3 h-100">

                    <small class="text-muted d-block mb-1">
                        Email
                    </small>

                    <h5 class="mb-0">
                        {{ $customer->email ?? '-' }}
                    </h5>

                </div>

            </div>

            {{-- LIMITE CREDIT --}}
            <div class="col-md-6">

                <div class="border rounded-3 p-3 h-100">

                    <small class="text-muted d-block mb-1">
                        Limite de crédit
                    </small>

                    <h5 class="mb-0 text-success">

                        {{ number_format($customer->credit_limit ?? 0, 2, ',', ' ') }}

                    </h5>

                </div>

            </div>

            {{-- CONDITIONS PAIEMENT --}}
            <div class="col-md-12">

                <div class="border rounded-3 p-3">

                    <small class="text-muted d-block mb-1">
                        Conditions de paiement
                    </small>

                    <p class="mb-0">

                        {{ $customer->payment_terms ?? 'Aucune condition définie' }}

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
