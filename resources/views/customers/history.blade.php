@extends('layouts.layoutMaster')

@section('content')

{{-- ====================================================== --}}
{{-- MESSAGES --}}
{{-- ====================================================== --}}

@if(session('success'))

    <div class="alert alert-success">

        {{ session('success') }}

    </div>

@endif

@if(session('error'))

    <div class="alert alert-danger">

        {{ session('error') }}

    </div>

@endif


{{-- ====================================================== --}}
{{-- CARD PRINCIPALE --}}
{{-- ====================================================== --}}

<div class="card shadow-sm border-0">


    {{-- ================================================== --}}
    {{-- HEADER --}}
    {{-- ================================================== --}}

    <div class="card-header bg-white border-bottom">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>

                <h4 class="mb-1 fw-bold">

                    Historique des clients

                </h4>

                <small class="text-muted">

                    Véhicules, factures, paiements et soldes des clients

                </small>

            </div>


            <a
                href="{{ route('customers.index') }}"
                class="btn btn-secondary"
            >

                <i class="bx bx-arrow-back me-1"></i>

                Liste des clients

            </a>

        </div>

    </div>


    {{-- ================================================== --}}
    {{-- BODY --}}
    {{-- ================================================== --}}

    <div class="card-body">


        {{-- ================================================== --}}
        {{-- RECHERCHE --}}
        {{-- ================================================== --}}

        <form
            action="{{ route('customers.history') }}"
            method="GET"
            class="mb-4"
        >

            <div class="row g-2 align-items-end">


                <div class="col-md-8 col-lg-6">

                    <label class="form-label">

                        Rechercher un client

                    </label>

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        value="{{ request('search') }}"
                        placeholder="Code, nom, téléphone, email, VIN, véhicule ou facture..."
                    >

                </div>


                <div class="col-auto">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="bx bx-search me-1"></i>

                        Rechercher

                    </button>

                </div>


                <div class="col-auto">

                    <a
                        href="{{ route('customers.history') }}"
                        class="btn btn-outline-secondary"
                    >

                        <i class="bx bx-reset me-1"></i>

                        Réinitialiser

                    </a>

                </div>

            </div>

        </form>


        {{-- ================================================== --}}
        {{-- TABLE --}}
        {{-- ================================================== --}}

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-light">

                    <tr>

                        <th>
                            Client
                        </th>

                        <th>
                            Contact
                        </th>

                        <th class="text-center">
                            Véhicules
                        </th>

                        <th class="text-center">
                            Factures
                        </th>

                        <th class="text-end">
                            Total facturé
                        </th>

                        <th class="text-end">
                            Total payé
                        </th>

                        <th class="text-end">
                            Solde
                        </th>

                        <th
                            width="140"
                            class="text-center"
                        >
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($customers as $customer)

                        <tr>


                            {{-- ========================================= --}}
                            {{-- CLIENT --}}
                            {{-- ========================================= --}}

                            <td>

                                <div class="fw-bold">

                                    {{ $customer->name }}

                                </div>

                                <small class="text-muted">

                                    {{ $customer->code }}

                                </small>

                            </td>


                            {{-- ========================================= --}}
                            {{-- CONTACT --}}
                            {{-- ========================================= --}}

                            <td>

                                @if($customer->phone)

                                    <div>

                                        <i class="bx bx-phone me-1"></i>

                                        {{ $customer->phone }}

                                    </div>

                                @endif


                                @if($customer->email)

                                    <div class="small text-muted">

                                        <i class="bx bx-envelope me-1"></i>

                                        {{ $customer->email }}

                                    </div>

                                @endif


                                @if(!$customer->phone && !$customer->email)

                                    <span class="text-muted">

                                        -

                                    </span>

                                @endif

                            </td>


                            {{-- ========================================= --}}
                            {{-- VÉHICULES --}}
                            {{-- ========================================= --}}

                            <td class="text-center">

                                <span class="badge bg-label-info fs-6">

                                    {{ $customer->vehicles_count }}

                                </span>

                            </td>


                            {{-- ========================================= --}}
                            {{-- FACTURES --}}
                            {{-- ========================================= --}}

                            <td class="text-center">

                                <span class="badge bg-label-primary fs-6">

                                    {{ $customer->sales_count }}

                                </span>

                            </td>


                            {{-- ========================================= --}}
                            {{-- TOTAL FACTURÉ --}}
                            {{-- ========================================= --}}

                            <td class="text-end fw-semibold">

                                {{
                                    number_format(
                                        $customer->total_invoiced ?? 0,
                                        2,
                                        ',',
                                        ' '
                                    )
                                }}

                                DJF

                            </td>


                            {{-- ========================================= --}}
                            {{-- TOTAL PAYÉ --}}
                            {{-- ========================================= --}}

                            <td class="text-end text-success fw-semibold">

                                {{
                                    number_format(
                                        $customer->total_paid ?? 0,
                                        2,
                                        ',',
                                        ' '
                                    )
                                }}

                                DJF

                            </td>


                            {{-- ========================================= --}}
                            {{-- SOLDE --}}
                            {{-- ========================================= --}}

                            <td class="text-end">

                                @if(($customer->balance ?? 0) > 0)

                                    <span class="text-danger fw-bold">

                                        {{
                                            number_format(
                                                $customer->balance,
                                                2,
                                                ',',
                                                ' '
                                            )
                                        }}

                                        DJF

                                    </span>

                                @else

                                    <span class="text-success fw-bold">

                                        0,00 DJF

                                    </span>

                                @endif

                            </td>


                            {{-- ========================================= --}}
                            {{-- ACTION --}}
                            {{-- ========================================= --}}

                            <td class="text-center">

                                <a
                                    href="{{ route('customers.show', $customer) }}"
                                    class="btn btn-info btn-sm"
                                    title="Afficher l'historique du client"
                                >

                                    <i class="bx bx-show me-1"></i>

                                    Voir

                                </a>

                            </td>

                        </tr>


                        {{-- ============================================= --}}
                        {{-- LIGNE VÉHICULES --}}
                        {{-- ============================================= --}}

                        @if($customer->vehicles->count() > 0)

                            <tr class="bg-light">

                                <td colspan="8">

                                    <div class="px-2 py-2">

                                        <strong>

                                            <i class="bx bx-car me-1"></i>

                                            Véhicules :

                                        </strong>


                                        @foreach($customer->vehicles as $vehicle)

                                            <span
                                                class="badge bg-white text-dark border me-1 mb-1"
                                            >

                                                {{ $vehicle->brand ?? '' }}

                                                {{ $vehicle->model ?? '' }}

                                                @if($vehicle->vin)

                                                    -
                                                    VIN :
                                                    {{ $vehicle->vin }}

                                                @endif

                                            </span>

                                        @endforeach

                                    </div>

                                </td>

                            </tr>

                        @endif


                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="text-center py-5"
                            >

                                <i
                                    class="bx bx-user-x d-block mb-2"
                                    style="font-size:40px;"
                                ></i>

                                <strong>

                                    Aucun historique client trouvé.

                                </strong>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- ================================================== --}}
        {{-- PAGINATION --}}
        {{-- ================================================== --}}

        @if($customers->hasPages())

            <div class="mt-4">

                {{ $customers->links() }}

            </div>

        @endif

    </div>

</div>

@endsection