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


    /* ============================================================
       DESIGN COMPACT — conserver les sept colonnes et les actions
       ============================================================ */
    #sales-page { width: 100%; min-width: 0; overflow: visible; border: 1px solid #e5eaf2 !important; border-radius: 12px !important; container-type: inline-size; container-name: sales; }
    #sales-page > .card-header { padding: 16px 18px !important; border-radius: 12px 12px 0 0; }
    #sales-page .sales-header-title { font-family: inherit; font-size: 21px; color: #334155 !important; }
    #sales-page .card-header p { font-size: 12px; }
    #sales-page > .card-body { padding: 0 18px 18px; }
    #sales-page .card-header .btn,
    #sales-page .sales-filter-btn { min-height: 34px; height: 34px; width: auto; padding: 5px 11px !important; border-radius: 6px !important; font-size: 12px; font-weight: 600; box-shadow: none !important; }
    #sales-page .btn-primary { background: #5867db; border-color: #5867db; }
    #sales-page .btn-primary:hover { background: #4655c3; }

    /* Filtres dans un panneau clair, boutons alignés sur les champs. */
    #sales-page .sales-filters { grid-template-columns: minmax(0, 1fr) minmax(145px, .55fr) auto auto; gap: 10px; padding: 12px; margin-bottom: 16px; background: #f8fafc; border: 1px solid #e5eaf2; border-radius: 9px; }
    #sales-page .sales-filter-group label { font-size: 11px; font-weight: 600; text-transform: none; letter-spacing: 0; }
    #sales-page .sales-filter-group:nth-child(n+3) label { display: none !important; }
    #sales-page .sales-filter-control { height: 34px; min-height: 34px; font-size: 12px; background: #fff !important; border: 1px solid #dce3ed !important; border-radius: 6px !important; min-width: 0; }
    #sales-page .input-group { flex-wrap: nowrap; }
    #sales-page .input-group-text { padding: 6px 9px; background: #fff !important; border: 1px solid #dce3ed !important; border-right: 0 !important; border-radius: 6px 0 0 6px; }
    #sales-page .input-group .sales-filter-control { border-left: 0 !important; border-radius: 0 6px 6px 0 !important; }
    #sales-page .sales-filter-btn i { font-size: 15px; }
    #sales-page .sales-filter-btn.btn-secondary { background: #fff; border: 1px solid #dce3ed; color: #52627a; }

    /* Textes longs sur plusieurs lignes : aucune donnée tronquée. */
    #sales-page .sales-table-wrapper { width: 100%; min-width: 0; }
    #sales-page .sales-table { width: 100%; min-width: 0; table-layout: fixed; margin: 0; }
    #sales-page .sales-table th,
    #sales-page .sales-table td { padding: 9px 7px; font-size: 12px; white-space: normal; overflow-wrap: anywhere; line-height: 1.4; }
    #sales-page .sales-table th { font-size: 10px; letter-spacing: .02em; overflow-wrap: normal; background: #f1f4f9; }
    #sales-page .sales-table th:nth-child(1) { width: 17%; }
    #sales-page .sales-table th:nth-child(2) { width: 21%; }
    #sales-page .sales-table th:nth-child(3) { width: 13%; }
    #sales-page .sales-table th:nth-child(4) { width: 14%; }
    #sales-page .sales-table th:nth-child(5) { width: 13%; }
    #sales-page .sales-table th:nth-child(6) { width: 12%; }
    #sales-page .sales-table th:nth-child(7) { width: 10%; }
    #sales-page .invoice-link,
    #sales-page .amount-value { white-space: normal; }
    #sales-page .badge { font-size: 10px; max-width: 100%; white-space: normal; padding: 5px 6px; line-height: 1.3; }
    #sales-page .sales-action-group { gap: 4px; flex-wrap: wrap; }
    #sales-page .sales-action-group .btn { width: 28px; min-width: 28px; height: 28px; padding: 0; border-radius: 6px; box-shadow: none; }
    #sales-page .sales-action-group i { font-size: 15px; }
    #sales-page .sales-action-group form { margin: 0; }
    #sales-page .pagination { flex-wrap: wrap; }

    /* Réorganiser les filtres selon la largeur réelle de la carte. */
    @container sales (max-width: 750px) {
        #sales-page .sales-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        #sales-page .sales-filter-group:nth-child(3) { justify-self: end; }
    }
    /* Petits écrans : une fiche par vente avec les sept informations. */
    @container sales (max-width: 600px) {
        #sales-page .sales-table thead { display: none; }
        #sales-page .sales-table, #sales-page .sales-table tbody { display: block; }
        #sales-page .sales-table tr { display: block; padding: 6px; margin-bottom: 10px; border: 1px solid #e5eaf2; border-radius: 8px; }
        #sales-page .sales-table td { display: grid; grid-template-columns: 90px minmax(0, 1fr); gap: 10px; width: 100%; border: 0; }
        #sales-page .sales-table td::before { content: attr(data-label); font-weight: 600; color: #64748b; }
        #sales-page .sales-table td[colspan] { display: block; }
        #sales-page .sales-table td[colspan]::before { content: none; }
    }
    @container sales (max-width: 380px) {
        #sales-page .sales-filter-group:nth-child(-n+2) { grid-column: 1 / -1; }
    }

</style>

@endpush

<div id="sales-page" class="card border-0 shadow-sm rounded-4 sales-page-card">

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

        <div class="sales-table-wrapper">

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

                            <td data-label="Client">

                                <strong>

                                    {{ $sale->customer->name ?? 'Comptoir' }}

                                </strong>

                            </td>

                            {{-- REFERENCE --}}

                            <td data-label="Référence">

                                <strong class="text-primary invoice-link">

                                    {{ $sale->invoice_number }}

                                </strong>

                            </td>

                            {{-- PRODUITS --}}

                            <td data-label="Produits">

                                <span class="badge bg-label-info">

                                    {{ $sale->items->count() }}

                                    produit(s)

                                </span>

                            </td>

                            {{-- TOTAL --}}

                            <td data-label="Total">

                                <strong class="text-success amount-value">

                                 {{ number_format(round($sale->total), 0, ',', ' ') }}

                                </strong>

                            </td>

                            {{-- DATE --}}

                            <td data-label="Date">

                                {{ $sale->created_at->format('d/m/Y') }}

                            </td>

                            {{-- STATUS --}}

                            <td data-label="Statut">

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

                            <td data-label="Actions">

                                <div class="sales-action-group">

                                    {{-- VOIR --}}

                                    <a href="{{ route('sales.show', $sale->id) }}"

                                    class="btn btn-info btn-sm" title="Voir la vente" aria-label="Voir la vente">

                                        <i class="bx bx-show" aria-hidden="true"></i>
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

                                                    class="btn btn-danger btn-sm" title="Supprimer la vente" aria-label="Supprimer la vente">

                                                <i class="bx bx-trash" aria-hidden="true"></i>
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
