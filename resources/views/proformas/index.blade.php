@extends('layouts.layoutMaster')

@section('content')

<div class="card border-0 shadow-sm rounded-4">

    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}

    <div
        class="
            card-header
            bg-white
            border-0
            d-flex
            justify-content-between
            align-items-center
            py-4
        "
    >

        <div>

            <h3 class="fw-bold mb-1 text-dark">
                Liste des proformas
            </h3>

            <small class="text-muted">
                Gestion des proformas et devis clients
            </small>

        </div>


        <a
            href="{{ route('proformas.create') }}"
            class="
                btn
                btn-primary
                rounded-pill
                px-4
                shadow-sm
            "
        >

            <i class="bx bx-plus me-1"></i>

            Nouveau proforma

        </a>

    </div>



    {{-- ========================================================= --}}
    {{-- BODY --}}
    {{-- ========================================================= --}}

    <div class="card-body">


        {{-- ===================================================== --}}
        {{-- MESSAGES --}}
        {{-- ===================================================== --}}

        @if(session('success'))

            <div
                class="
                    alert
                    alert-success
                    alert-dismissible
                    fade
                    show
                "
            >

                {{ session('success') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Fermer"
                ></button>

            </div>

        @endif


        @if(session('error'))

            <div
                class="
                    alert
                    alert-danger
                    alert-dismissible
                    fade
                    show
                "
            >

                {{ session('error') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Fermer"
                ></button>

            </div>

        @endif


        @if(session('info'))

            <div
                class="
                    alert
                    alert-info
                    alert-dismissible
                    fade
                    show
                "
            >

                {{ session('info') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Fermer"
                ></button>

            </div>

        @endif



        {{-- ===================================================== --}}
        {{-- RECHERCHE / FILTRES --}}
        {{-- ===================================================== --}}

        <form
            method="GET"
            action="{{ route('proformas.index') }}"
            class="row g-3 align-items-end mb-4"
        >


            {{-- RECHERCHE --}}
            <div class="col-lg-7 col-md-6">

                <label
                    for="search"
                    class="form-label fw-semibold"
                >
                    Recherche
                </label>


                <div class="input-group shadow-sm">

                    <span
                        class="
                            input-group-text
                            bg-light
                            border-0
                        "
                    >

                        <i
                            class="
                                bx
                                bx-search
                                text-primary
                            "
                        ></i>

                    </span>


                    <input
                        type="text"
                        name="search"
                        id="search"
                        class="
                            form-control
                            border-0
                            bg-light
                        "
                        placeholder="N° proforma, client, téléphone, email, VIN, marque, modèle..."
                        value="{{ $search ?? request('search') }}"
                    >

                </div>

            </div>



            {{-- STATUT --}}
            <div class="col-lg-3 col-md-3">

                <label
                    for="status"
                    class="form-label fw-semibold"
                >
                    Statut
                </label>


                <select
                    name="status"
                    id="status"
                    class="form-select shadow-sm"
                >

                    <option value="">
                        Tous les statuts
                    </option>


                    <option
                        value="Validé"
                        {{
                            ($status ?? request('status')) === 'Validé'
                                ? 'selected'
                                : ''
                        }}
                    >
                        Validé
                    </option>


                    <option
                        value="Converti"
                        {{
                            ($status ?? request('status')) === 'Converti'
                                ? 'selected'
                                : ''
                        }}
                    >
                        Converti
                    </option>


                    <option
                        value="Annulé"
                        {{
                            ($status ?? request('status')) === 'Annulé'
                                ? 'selected'
                                : ''
                        }}
                    >
                        Annulé
                    </option>


                    <option
                        value="Expiré"
                        {{
                            ($status ?? request('status')) === 'Expiré'
                                ? 'selected'
                                : ''
                        }}
                    >
                        Expiré
                    </option>

                </select>

            </div>



            {{-- RECHERCHER --}}
            <div class="col-lg-1 col-md-2">

                <button
                    type="submit"
                    class="
                        btn
                        btn-primary
                        w-100
                        fw-semibold
                        shadow-sm
                    "
                    title="Rechercher"
                >

                    <i class="bx bx-search"></i>

                </button>

            </div>



            {{-- RESET --}}
            <div class="col-lg-1 col-md-1">

                <a
                    href="{{ route('proformas.index') }}"
                    class="
                        btn
                        btn-secondary
                        w-100
                        shadow-sm
                    "
                    title="Réinitialiser"
                >

                    <i class="bx bx-reset"></i>

                </a>

            </div>

        </form>



        {{-- ===================================================== --}}
        {{-- TABLE --}}
        {{-- ===================================================== --}}

        <div class="table-responsive">

            <table
                class="
                    table
                    align-middle
                    table-hover
                "
            >

                <thead class="table-light">

                    <tr>

                        <th
                            class="
                                fw-bold
                                text-uppercase
                                small
                            "
                        >
                            Proforma
                        </th>


                        <th
                            class="
                                fw-bold
                                text-uppercase
                                small
                            "
                        >
                            Client
                        </th>


                        <th
                            class="
                                fw-bold
                                text-uppercase
                                small
                            "
                        >
                            Véhicule
                        </th>


                        <th
                            class="
                                fw-bold
                                text-uppercase
                                small
                                text-end
                            "
                        >
                            Montant
                        </th>


                        <th
                            class="
                                fw-bold
                                text-uppercase
                                small
                                text-center
                            "
                        >
                            Statut
                        </th>


                        <th
                            class="
                                fw-bold
                                text-uppercase
                                small
                            "
                        >
                            Date
                        </th>


                        <th
                            width="110"
                            class="
                                fw-bold
                                text-uppercase
                                small
                                text-center
                            "
                        >
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($proformas as $proforma)

                        <tr>


                            {{-- ================================= --}}
                            {{-- NUMÉRO PROFORMA --}}
                            {{-- ================================= --}}

                            <td>

                                <span
                                    class="
                                        badge
                                        bg-label-primary
                                    "
                                >

                                    {{ $proforma->proforma_number }}

                                </span>

                            </td>



                            {{-- ================================= --}}
                            {{-- CLIENT --}}
                            {{-- ================================= --}}

                            <td>

                                <div
                                    class="
                                        fw-semibold
                                        text-dark
                                    "
                                >

                                    {{
                                        $proforma->customer?->name
                                        ?? 'Client non défini'
                                    }}

                                </div>


                                @if($proforma->customer?->phone)

                                    <small class="text-muted">

                                        {{
                                            $proforma
                                                ->customer
                                                ->phone
                                        }}

                                    </small>

                                @endif

                            </td>



                            {{-- ================================= --}}
                            {{-- VÉHICULE --}}
                            {{-- ================================= --}}

                            <td>

                                @if($proforma->vehicle)

                                    <div class="fw-semibold">

                                        {{
                                            $proforma
                                                ->vehicle
                                                ->plate_number
                                            ?? '-'
                                        }}

                                    </div>


                                    <small class="text-muted">

                                        {{
                                            trim(
                                                (
                                                    $proforma
                                                        ->vehicle
                                                        ->brand
                                                    ?? ''
                                                )
                                                . ' '
                                                .
                                                (
                                                    $proforma
                                                        ->vehicle
                                                        ->model
                                                    ?? ''
                                                )
                                            )
                                            ?: '-'
                                        }}

                                    </small>

                                @else

                                    <span class="text-muted">
                                        -
                                    </span>

                                @endif

                            </td>



                            {{-- ================================= --}}
                            {{-- MONTANT --}}
                            {{-- ================================= --}}

                            <td
                                class="
                                    text-end
                                    fw-bold
                                    text-success
                                "
                            >

                                {{
                                    number_format(
                                        (float) $proforma->total_amount,
                                        2,
                                        ',',
                                        ' '
                                    )
                                }}

                                FDJ

                            </td>



                            {{-- ================================= --}}
                            {{-- STATUT --}}
                            {{-- ================================= --}}

                            <td class="text-center">

                                @php

                                    $statusClass = match(
                                        $proforma->status
                                    ) {

                                        'Validé' =>
                                            'bg-label-primary',

                                        'Converti' =>
                                            'bg-label-success',

                                        'Annulé' =>
                                            'bg-label-danger',

                                        'Expiré' =>
                                            'bg-label-warning',

                                        default =>
                                            'bg-label-secondary',

                                    };

                                @endphp


                                <span
                                    class="
                                        badge
                                        {{ $statusClass }}
                                    "
                                >

                                    {{
                                        $proforma->status
                                        ?? '-'
                                    }}

                                </span>

                            </td>



                            {{-- ================================= --}}
                            {{-- DATE --}}
                            {{-- ================================= --}}

                            <td>

                                {{
                                    optional(
                                        $proforma->proforma_date
                                    )
                                    ->format(
                                        'd/m/Y H:i'
                                    )
                                    ?? '-'
                                }}

                            </td>



                            {{-- ================================= --}}
                            {{-- ACTIONS --}}
                            {{-- ================================= --}}

                            <td class="text-center">

                                <a
                                    href="{{
                                        route(
                                            'proformas.show',
                                            $proforma
                                        )
                                    }}"
                                    class="
                                        btn
                                        btn-info
                                        btn-sm
                                    "
                                    title="Voir"
                                >

                                    <i class="bx bx-show"></i>

                                </a>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="
                                    text-center
                                    text-muted
                                    py-5
                                "
                            >

                                <i
                                    class="
                                        bx
                                        bx-file
                                        display-6
                                        d-block
                                        mb-2
                                    "
                                ></i>

                                Aucun proforma trouvé.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>



        {{-- ===================================================== --}}
        {{-- PAGINATION --}}
        {{-- ===================================================== --}}

        @if($proformas->hasPages())

            <div
                class="
                    d-flex
                    justify-content-center
                    mt-4
                "
            >

                {{ $proformas->links() }}

            </div>

        @endif

    </div>

</div>

@endsection
