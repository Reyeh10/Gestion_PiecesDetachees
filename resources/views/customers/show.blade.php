@extends('layouts.layoutMaster')

@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | CALCULS DU DOSSIER CLIENT
    |--------------------------------------------------------------------------
    */

    $totalFacture = $customer->sales->sum(function ($sale) {

        return (float) (
            $sale->total
            ?? $sale->total_amount
            ?? $sale->grand_total
            ?? 0
        );
    });

    $totalPaye = $customer->sales->sum(function ($sale) {

        return $sale->payments->sum(function ($payment) {

            return (float) (
                $payment->amount
                ?? $payment->paid_amount
                ?? 0
            );

        });

    });

    $solde = $totalFacture - $totalPaye;

@endphp


{{-- ====================================================== --}}
{{-- HEADER --}}
{{-- ====================================================== --}}

<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">

        <div>

            <h4 class="mb-1 fw-bold">

                Dossier client

            </h4>

            <div class="text-muted">

                {{ $customer->code }}
                -
                {{ $customer->name }}

            </div>

        </div>

        <div class="d-flex gap-2">

            <a
                href="{{ route('customers.index') }}"
                class="btn btn-secondary btn-sm"
            >

                <i class="bx bx-arrow-back me-1"></i>

                Retour

            </a>

            @if(in_array(auth()->user()->role, ['admin', 'chef_magasinier']))

                <a
                    href="{{ route('customers.edit', $customer) }}"
                    class="btn btn-warning btn-sm"
                >

                    <i class="bx bx-edit me-1"></i>

                    Modifier

                </a>

            @endif

        </div>

    </div>

</div>


{{-- ====================================================== --}}
{{-- INFORMATIONS CLIENT --}}
{{-- ====================================================== --}}

<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        <h5 class="mb-0">

            <i class="bx bx-user me-2"></i>

            Informations du client

        </h5>

    </div>

    <div class="card-body">

        <div class="row g-3">

            {{-- CODE --}}
            <div class="col-md-3">

                <div class="border rounded-3 p-3 h-100">

                    <small class="text-muted d-block mb-1">

                        Code client

                    </small>

                    <strong>

                        {{ $customer->code }}

                    </strong>

                </div>

            </div>


            {{-- NOM --}}
            <div class="col-md-3">

                <div class="border rounded-3 p-3 h-100">

                    <small class="text-muted d-block mb-1">

                        Nom

                    </small>

                    <strong>

                        {{ $customer->name }}

                    </strong>

                </div>

            </div>


            {{-- TELEPHONE --}}
            <div class="col-md-3">

                <div class="border rounded-3 p-3 h-100">

                    <small class="text-muted d-block mb-1">

                        Téléphone

                    </small>

                    <strong>

                        {{ $customer->phone ?? '-' }}

                    </strong>

                </div>

            </div>


            {{-- EMAIL --}}
            <div class="col-md-3">

                <div class="border rounded-3 p-3 h-100">

                    <small class="text-muted d-block mb-1">

                        Email

                    </small>

                    <strong>

                        {{ $customer->email ?? '-' }}

                    </strong>

                </div>

            </div>


            {{-- LIMITE CREDIT --}}
            <div class="col-md-6">

                <div class="border rounded-3 p-3 h-100">

                    <small class="text-muted d-block mb-1">

                        Limite de crédit

                    </small>

                    <strong>

                        {{
                            number_format(
                                $customer->credit_limit ?? 0,
                                2,
                                ',',
                                ' '
                            )
                        }}

                        DJF

                    </strong>

                </div>

            </div>


            {{-- CONDITIONS --}}
            <div class="col-md-6">

                <div class="border rounded-3 p-3 h-100">

                    <small class="text-muted d-block mb-1">

                        Conditions de paiement

                    </small>

                    <strong>

                        {{
                            $customer->payment_terms
                            ?? 'Aucune condition définie'
                        }}

                    </strong>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- ====================================================== --}}
{{-- RESUME --}}
{{-- ====================================================== --}}

<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        <h5 class="mb-0">

            <i class="bx bx-bar-chart-alt-2 me-2"></i>

            Résumé du client

        </h5>

    </div>

    <div class="card-body">

        <div class="row g-3">


            {{-- VEHICULES --}}
            <div class="col-md-3">

                <div class="border rounded-3 p-3 text-center h-100">

                    <div class="mb-2">

                        <i
                            class="bx bx-car"
                            style="font-size:32px;"
                        ></i>

                    </div>

                    <div class="text-muted">

                        Véhicules

                    </div>

                    <h4 class="mb-0 mt-1">

                        {{ $customer->vehicles->count() }}

                    </h4>

                </div>

            </div>


            {{-- FACTURES --}}
            <div class="col-md-3">

                <div class="border rounded-3 p-3 text-center h-100">

                    <div class="mb-2">

                        <i
                            class="bx bx-receipt"
                            style="font-size:32px;"
                        ></i>

                    </div>

                    <div class="text-muted">

                        Factures

                    </div>

                    <h4 class="mb-0 mt-1">

                        {{ $customer->sales->count() }}

                    </h4>

                </div>

            </div>


            {{-- TOTAL FACTURE --}}
            <div class="col-md-3">

                <div class="border rounded-3 p-3 text-center h-100">

                    <div class="text-muted mb-1">

                        Total facturé

                    </div>

                    <h5 class="mb-0">

                        {{
                            number_format(
                                $totalFacture,
                                2,
                                ',',
                                ' '
                            )
                        }}

                        DJF

                    </h5>

                </div>

            </div>


            {{-- SOLDE --}}
            <div class="col-md-3">

                <div class="border rounded-3 p-3 text-center h-100">

                    <div class="text-muted mb-1">

                        Solde restant

                    </div>

                    <h5
                        class="mb-0
                        {{ $solde > 0 ? 'text-danger' : 'text-success' }}"
                    >

                        {{
                            number_format(
                                max($solde, 0),
                                2,
                                ',',
                                ' '
                            )
                        }}

                        DJF

                    </h5>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- ====================================================== --}}
{{-- VEHICULES --}}
{{-- ====================================================== --}}

<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        <h5 class="mb-0">

            <i class="bx bx-car me-2"></i>

            Véhicules du client

        </h5>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th>
                            VIN
                        </th>

                        <th>
                            Marque
                        </th>

                        <th>
                            Modèle
                        </th>

                        <th>
                            Année
                        </th>

                        <th>
                            Immatriculation
                        </th>

                        <th>
                            Statut
                        </th>

                        <th class="text-center">
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($customer->vehicles as $vehicle)

                        <tr>

                            <td>

                                {{ $vehicle->vin ?? '-' }}

                            </td>

                            <td>

                                {{ $vehicle->brand ?? '-' }}

                            </td>

                            <td>

                                {{ $vehicle->model ?? '-' }}

                            </td>

                            <td>

                                {{
                                    $vehicle->model_year
                                    ?? $vehicle->year
                                    ?? '-'
                                }}

                            </td>

                            <td>

                                {{
                                    $vehicle->registration_number
                                    ?? $vehicle->plate_number
                                    ?? '-'
                                }}

                            </td>

                            <td>

                                <span class="badge bg-label-primary">

                                    {{ $vehicle->status ?? '-' }}

                                </span>

                            </td>

                            <td class="text-center">

                                @if(Route::has('vehicles.show'))

                                    <a
                                        href="{{ route('vehicles.show', $vehicle) }}"
                                        class="btn btn-info btn-sm"
                                    >

                                        <i class="bx bx-show"></i>

                                    </a>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-4 text-muted"
                            >

                                Aucun véhicule associé à ce client.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


{{-- ====================================================== --}}
{{-- FACTURES --}}
{{-- ====================================================== --}}

<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        <h5 class="mb-0">

            <i class="bx bx-receipt me-2"></i>

            Factures du client

        </h5>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th>
                            Facture
                        </th>

                        <th>
                            Date
                        </th>

                        <th class="text-end">
                            Total
                        </th>

                        <th class="text-end">
                            Payé
                        </th>

                        <th class="text-end">
                            Reste
                        </th>

                        <th>
                            Statut
                        </th>

                        <th class="text-center">
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($customer->sales as $sale)

                        @php

                            $saleTotal = (float) (
                                $sale->total
                                ?? $sale->total_amount
                                ?? $sale->grand_total
                                ?? 0
                            );

                            $salePaid = $sale->payments->sum(function ($payment) {

                                return (float) (
                                    $payment->amount
                                    ?? $payment->paid_amount
                                    ?? 0
                                );

                            });

                            $saleBalance =
                                $saleTotal
                                - $salePaid;

                        @endphp

                        <tr>

                            {{-- FACTURE --}}
                            <td>

                                <strong>

                                    {{
                                        $sale->invoice_number
                                        ?? $sale->reference
                                        ?? ('#' . $sale->id)
                                    }}

                                </strong>

                            </td>


                            {{-- DATE --}}
                            <td>

                                {{
                                    optional(
                                        $sale->created_at
                                    )->format('d/m/Y')
                                }}

                            </td>


                            {{-- TOTAL --}}
                            <td class="text-end">

                                {{
                                    number_format(
                                        $saleTotal,
                                        2,
                                        ',',
                                        ' '
                                    )
                                }}

                                DJF

                            </td>


                            {{-- PAYE --}}
                            <td class="text-end text-success">

                                {{
                                    number_format(
                                        $salePaid,
                                        2,
                                        ',',
                                        ' '
                                    )
                                }}

                                DJF

                            </td>


                            {{-- RESTE --}}
                            <td class="text-end">

                                @if($saleBalance > 0)

                                    <span class="text-danger fw-bold">

                                        {{
                                            number_format(
                                                $saleBalance,
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


                            {{-- STATUT --}}
                            <td>

                                @if($saleBalance <= 0)

                                    <span class="badge bg-success">

                                        Payée

                                    </span>

                                @elseif($salePaid > 0)

                                    <span class="badge bg-warning">

                                        Partiellement payée

                                    </span>

                                @else

                                    <span class="badge bg-danger">

                                        Non payée

                                    </span>

                                @endif

                            </td>


                            {{-- ACTION --}}
                            <td class="text-center">

                                @if(Route::has('sales.show'))

                                    <a
                                        href="{{ route('sales.show', $sale) }}"
                                        class="btn btn-info btn-sm"
                                        title="Voir la facture"
                                    >

                                        <i class="bx bx-show"></i>

                                    </a>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-4 text-muted"
                            >

                                Aucune facture pour ce client.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


{{-- ====================================================== --}}
{{-- PAIEMENTS --}}
{{-- ====================================================== --}}

<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        <h5 class="mb-0">

            <i class="bx bx-money me-2"></i>

            Historique des paiements

        </h5>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th>
                            Date
                        </th>

                        <th>
                            Facture
                        </th>

                        <th class="text-end">
                            Montant
                        </th>

                        <th>
                            Mode de paiement
                        </th>

                        <th>
                            Référence
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @php

                        $hasPayments = false;

                    @endphp


                    @foreach($customer->sales as $sale)

                        @foreach($sale->payments as $payment)

                            @php

                                $hasPayments = true;

                            @endphp

                            <tr>

                                {{-- DATE --}}
                                <td>

                                    {{
                                        optional(
                                            $payment->payment_date
                                            ?? $payment->created_at
                                        )->format('d/m/Y')
                                    }}

                                </td>


                                {{-- FACTURE --}}
                                <td>

                                    {{
                                        $sale->invoice_number
                                        ?? $sale->reference
                                        ?? ('#' . $sale->id)
                                    }}

                                </td>


                                {{-- MONTANT --}}
                                <td class="text-end fw-bold text-success">

                                    {{
                                        number_format(
                                            $payment->amount
                                            ?? $payment->paid_amount
                                            ?? 0,
                                            2,
                                            ',',
                                            ' '
                                        )
                                    }}

                                    DJF

                                </td>


                                {{-- MODE --}}
                                <td>

                                    {{
                                        $payment->payment_method
                                        ?? $payment->method
                                        ?? '-'
                                    }}

                                </td>


                                {{-- REFERENCE --}}
                                <td>

                                    {{
                                        $payment->reference
                                        ?? '-'
                                    }}

                                </td>

                            </tr>

                        @endforeach

                    @endforeach


                    @if(!$hasPayments)

                        <tr>

                            <td
                                colspan="5"
                                class="text-center py-4 text-muted"
                            >

                                Aucun paiement enregistré pour ce client.

                            </td>

                        </tr>

                    @endif

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection