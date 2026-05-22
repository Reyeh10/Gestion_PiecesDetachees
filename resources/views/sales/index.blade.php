@extends('layouts.layoutMaster')

@section('content')

<div class="card border-0 shadow-sm rounded-4">

    {{-- HEADER --}}
    <div class="card-header bg-white border-0 py-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>

                <h3 class="fw-bold text-dark mb-1">
                    Liste des ventes
                </h3>

                <p class="text-muted mb-0">
                    Gestion des ventes et factures
                </p>

            </div>

            <a href="{{ route('sales.create') }}"
               class="btn btn-primary rounded-pill px-4 shadow-sm">

                <i class="bx bx-plus"></i>

                Nouvelle vente

            </a>

        </div>

    </div>

    {{-- BODY --}}
    <div class="card-body">


        {{-- SEARCH BAR --}}
        <form method="GET"
            action="{{ route('sales.index') }}"
            class="mb-4">

            <div class="row g-2 align-items-center">

                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0">
                            <i class="bx bx-user"></i>
                        </span>
                        <input type="text"
                            name="client"
                            class="form-control border-0 bg-light shadow-none"
                            placeholder="Client ou facture..."
                            value="{{ request('client') }}">
                    </div>
                </div>

                <!--div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0">
                            <i class="bx bx-barcode"></i>
                        </span>
                        <input type="text"
                            name="reference"
                            class="form-control border-0 bg-light shadow-none"
                            placeholder="Référence..."
                            value="{ { request('reference') }}">
                    </div>
                </div-->

                <!--div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0">
                            <i class="bx bx-package"></i>
                        </span>
                        <input type="text"
                            name="designation"
                            class="form-control border-0 bg-light shadow-none"
                            placeholder="Désignation..."
                            value="{ { request('designation') }}">
                    </div>
                </div-->

                <div class="col-md-2">
                    <input type="date"
                        name="date"
                        class="form-control bg-light border-0 shadow-none"
                        value="{{ request('date') }}">
                </div>

                <div class="col-md-1">
                    <button type="submit"
                            class="btn btn-primary w-100 fw-bold shadow-sm"
                            style="height:42px;border-radius:10px;">
                        Rechercher
                    </button>
                </div>

                <div class="col-md-1">
                    <a href="{{ route('sales.index') }}"
                    class="btn btn-secondary w-100 fw-bold shadow-sm d-flex align-items-center justify-content-center"
                    style="height:42px;border-radius:10px;">
                        Reset
                    </a>
                </div>

            </div>
        </form>

        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle">

                <thead class="table-light">

                    <tr>

                        <th>
                            Client
                        </th>

                        <th>
                            Référence
                        </th>

                        <th>
                            Produits
                        </th>

                        <th>
                            Total
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($sales as $sale)

                        <tr>

                            {{-- CLIENT --}}
                            <td>

                                <strong>

                                    {{ $sale->customer->name ?? 'Comptoir' }}

                                </strong>

                            </td>

                            {{-- REFERENCE --}}
                            <td>

                                <strong class="text-primary">

                                    {{ $sale->invoice_number }}

                                </strong>

                            </td>

                            {{-- PRODUITS --}}
                            <td>

                                <span class="badge bg-label-info">

                                    {{ $sale->items->count() }}

                                    produit(s)

                                </span>

                            </td>

                            {{-- TOTAL --}}
                            <td>

                                <strong class="text-success">

                                 {{ number_format(round($sale->total), 0, ',', ' ') }}

                                </strong>

                            </td>

                            {{-- DATE --}}
                            <td>

                                {{ $sale->created_at->format('d/m/Y') }}

                            </td>

                            {{-- STATUS --}}

                            <td>

                                @if($sale->status == 'vendu')

                                    <span class="badge bg-danger">

                                        VENDU

                                    </span>

                                @elseif($sale->status == 'partiel')

                                    <span class="badge bg-warning">

                                        PARTIEL

                                    </span>

                                @elseif($sale->status == 'payé')

                                    <span class="badge bg-success">

                                        PAYÉ

                                    </span>

                                @else

                                    <span class="badge bg-secondary">

                                        INCONNU

                                    </span>

                                @endif

                            </td>

                            {{-- ACTIONS --}}
                            <td>

                                <div class="d-flex gap-2">

                                    {{-- VOIR --}}
                                    <a href="{{ route('sales.show', $sale->id) }}"
                                    class="btn btn-info btn-sm">

                                        Voir

                                    </a>

                                   {{-- DELETE ADMIN + CHEF MAGASINIER SEULEMENT --}}
                                    @if(
                                        auth()->user()->role == 'admin'
                                        ||
                                        auth()->user()->role == 'chef_magasinier'
                                    )

                                        <form action="{{ route('sales.destroy', $sale->id) }}"
                                            method="POST"
                                            class="delete-sale-form d-inline">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-danger btn-sm">

                                                Supprimer

                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7"
                                class="text-center text-muted py-4">

                                Aucune vente trouvée

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINATION --}}
        <div class="mt-4">

            {{ $sales->withQueryString()->links() }}

        </div>

    </div>

</div>

{{-- SWEETALERT --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | GET DELETE FORMS
    |--------------------------------------------------------------------------
    */

    const forms =
        document.querySelectorAll('.delete-sale-form');

    /*
    |--------------------------------------------------------------------------
    | LOOP FORMS
    |--------------------------------------------------------------------------
    */

    forms.forEach(form => {

        form.addEventListener('submit', function (e) {

            e.preventDefault();

            /*
            |--------------------------------------------------------------------------
            | CONFIRM DELETE
            |--------------------------------------------------------------------------
            */

           Swal.fire({

            title: 'Supprimer cette vente ?',

            html: `
                <div style="
                    font-size:16px;
                    color:#94a3b8;
                    margin-top:10px;
                ">
                    Cette action est irréversible.
                </div>
            `,

            icon: 'warning',

            showCancelButton: true,

            confirmButtonText:
                '<i class="bx bx-trash"></i> Oui, supprimer',

            cancelButtonText:
                '<i class="bx bx-x"></i> Annuler',

            reverseButtons: true,

            background: '#020617',

            color: '#ffffff',

            width: '520px',

            padding: '2.5rem',

            confirmButtonColor: '#ef4444',

            cancelButtonColor: '#475569',

            backdrop: `
                rgba(15,23,42,0.82)
            `,

            buttonsStyling: false,

            customClass: {

                popup:
                    'rounded-4 shadow-lg border-0',

                title:
                    'fw-bold',

                confirmButton:
                    'btn btn-danger btn-lg px-4 mx-2 rounded-3',

                cancelButton:
                    'btn btn-secondary btn-lg px-4 mx-2 rounded-3'
            },

            showClass: {

                popup:
                    'animate__animated animate__zoomIn animate__faster'
            },

            hideClass: {

                popup:
                    'animate__animated animate__zoomOut animate__faster'
            }

        }).then((result) => {

                /*
                |--------------------------------------------------------------------------
                | DELETE
                |--------------------------------------------------------------------------
                */

                if (result.isConfirmed) {

                    form.submit();
                }

            });

        });

    });

});



</script>

@endsection
