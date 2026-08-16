@extends('layouts.layoutMaster')

@section('content')

@push('styles')
<style>
    /* ============================================================
       PAGE LISTE DES VENTES
    ============================================================ */

    .sales-page-card {
        overflow: hidden;
    }

    .sales-header-title {
        font-size: 2rem;
        line-height: 1.15;
    }

    .sales-header-actions .btn {
        min-height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        white-space: nowrap;
        font-weight: 600;
    }

    /* ============================================================
       FILTRES
    ============================================================ */

    .sales-filters {
        display: grid;
        grid-template-columns:
            minmax(280px, 1fr)
            minmax(180px, 220px)
            minmax(150px, 170px)
            minmax(160px, 180px);
        gap: 14px;
        align-items: end;
        margin-bottom: 24px;
    }

    .sales-filter-group {
        min-width: 0;
    }

    .sales-filter-group label {
        display: block;
        margin-bottom: 6px;
        font-size: .8rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .sales-filter-control,
    .sales-filter-btn {
        min-height: 44px;
        height: 44px;
        border-radius: 10px !important;
    }

    .sales-filter-control {
        width: 100%;
    }

    .sales-filter-btn {
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        white-space: nowrap;
        font-weight: 700;
        padding-left: 16px;
        padding-right: 16px;
        line-height: 1;
    }

    .sales-filter-btn i {
        font-size: 1.1rem;
        flex: 0 0 auto;
    }

    /* ============================================================
       TABLE
    ============================================================ */

    .sales-table th {
        white-space: nowrap;
        font-size: .78rem;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #475569;
        vertical-align: middle;
    }

    .sales-table td {
        vertical-align: middle;
    }

    .sales-table .invoice-link {
        white-space: nowrap;
        font-weight: 700;
    }

    .sales-table .amount-value {
        white-space: nowrap;
    }

    .sales-action-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .sales-action-group .btn {
        min-width: 78px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
    }

    /* ============================================================
       RESPONSIVE
    ============================================================ */

    @media (max-width: 1199.98px) {
        .sales-filters {
            grid-template-columns:
                minmax(240px, 1fr)
                minmax(180px, 220px)
                minmax(150px, 1fr)
                minmax(160px, 1fr);
        }
    }

    @media (max-width: 991.98px) {
        .sales-filters {
            grid-template-columns: 1fr 1fr;
        }

        .sales-filter-btn {
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        .sales-filters {
            grid-template-columns: 1fr;
        }

        .sales-header-actions {
            width: 100%;
        }

        .sales-header-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush



<div class="card border-0 shadow-sm rounded-4 sales-page-card">

    {{-- HEADER --}}
    <div class="card-header bg-white border-0 py-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>

                <h3 class="fw-bold text-dark mb-1 sales-header-title">
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

    </div>

    {{-- BODY --}}
    <div class="card-body">


        {{-- SEARCH BAR --}}
        <form
            method="GET"
            action="{{ route('sales.index') }}"
            class="sales-filters"
        >

            {{-- CLIENT / FACTURE --}}
            <div class="sales-filter-group">

                <label for="filter_client">
                    Client ou facture
                </label>

                <div class="input-group">

                    <span class="input-group-text bg-light border-0">
                        <i class="bx bx-user"></i>
                    </span>

                    <input
                        type="text"
                        id="filter_client"
                        name="client"
                        class="form-control bg-light border-0 shadow-none sales-filter-control"
                        placeholder="Client ou facture..."
                        value="{{ request('client') }}"
                    >

                </div>

            </div>


            {{-- DATE --}}
            <div class="sales-filter-group">

                <label for="filter_date">
                    Date
                </label>

                <input
                    type="date"
                    id="filter_date"
                    name="date"
                    class="form-control bg-light border-0 shadow-none sales-filter-control"
                    value="{{ request('date') }}"
                >

            </div>


            {{-- RECHERCHER --}}
            <div class="sales-filter-group">

                <label class="d-none d-lg-block">
                    &nbsp;
                </label>

                <button
                    type="submit"
                    class="btn btn-primary sales-filter-btn shadow-sm"
                >
                    <i class="bx bx-search"></i>
                    Rechercher
                </button>

            </div>


            {{-- RÉINITIALISER --}}
            <div class="sales-filter-group">

                <label class="d-none d-lg-block">
                    &nbsp;
                </label>

                <a
                    href="{{ route('sales.index') }}"
                    class="btn btn-secondary sales-filter-btn shadow-sm"
                >
                    <i class="bx bx-reset"></i>
                    Réinitialiser
                </a>

            </div>

        </form>

        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle sales-table">

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

                                <strong class="text-primary invoice-link">

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

                                <strong class="text-success amount-value">

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
                                @elseif($sale->status == 'cancelled')

                                    <span class="badge bg-dark">
                                        ANNULÉE
                                    </span>

                                @else

                                    <span class="badge bg-secondary">

                                        INCONNU

                                    </span>

                                @endif

                            </td>

                            {{-- ACTIONS --}}
                            <td>

                                <div class="sales-action-group">

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

    /**
*    |--------------------------------------------------------------------------*
*    | GET DELETE FORMS*
*    |--------------------------------------------------------------------------*
*    */

    const forms =
        document.querySelectorAll('.delete-sale-form');

    /**
*    |--------------------------------------------------------------------------*
*    | LOOP FORMS*
*    |--------------------------------------------------------------------------*
*    */

    forms.forEach(form => {

        form.addEventListener('submit', function (e) {

            e.preventDefault();

            /**
*            |--------------------------------------------------------------------------*
*            | CONFIRM DELETE*
*            |--------------------------------------------------------------------------*
*            */

           Swal.fire({

            title: 'Supprimer cette vente ?',

            html: \`
                <div style="
                    font-size:16px;
                    color:#94a3b8;
                    margin-top:10px;
                ">
                    Cette action est irréversible.
                </div>
            \`,

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

            backdrop: \`
                rgba(15,23,42,0.82)
            \`,

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

                /**
*                |--------------------------------------------------------------------------*
*                | DELETE*
*                |--------------------------------------------------------------------------*
*                */

                if (result.isConfirmed) {

                    form.submit();
                }

            });

        });

    });

});



</script>

@endsection
