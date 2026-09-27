@extends('layouts.layoutMaster')

@section('content')

{{-- ============================================================= --}}
{{-- PRÉSENTATION COMPACTE : FILTRES ET HUIT COLONNES                 --}}
{{-- Styles limités à cette page pour préserver les autres écrans.   --}}
{{-- ============================================================= --}}
<style>
    #stock-movements-page { width: 100%; min-width: 0; border: 1px solid #e5eaf2; border-radius: 12px; box-shadow: 0 4px 18px rgba(30,41,59,.04); container-type: inline-size; container-name: stock-movements; }
    #stock-movements-page > .card-header { padding: 16px 18px 12px; }
    #stock-movements-page h4 { font-family: inherit; font-size: 19px; font-weight: 700; color: #334155; }
    #stock-movements-page > .card-body { padding: 0 18px 18px; }

    /* Trois filtres et deux petits boutons alignés dans la largeur disponible. */
    #stock-movements-page .stock-filters { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(140px, .65fr) auto auto; gap: 10px; align-items: end; margin: 4px 0 16px; padding: 12px; background: #f8fafc; border: 1px solid #e8edf4; border-radius: 9px; }
    #stock-movements-page .stock-filters > div { min-width: 0; width: auto; padding: 0; margin: 0; }
    #stock-movements-page .stock-filters .input-group { flex-wrap: nowrap; box-shadow: none !important; border: 1px solid #dce3ed; border-radius: 6px; background: white; }
    #stock-movements-page .stock-filters input { min-width: 0; height: 34px; min-height: 34px; font-size: 12px; padding: 6px 9px; background: white !important; box-shadow: none !important; }
    #stock-movements-page .stock-filters input[type="date"] { border: 1px solid #dce3ed !important; border-radius: 6px; width: 100%; }
    #stock-movements-page .input-group-text { padding: 6px 8px; background: white !important; border-radius: 6px 0 0 6px; }
    #stock-movements-page .stock-filters .btn { display: inline-flex; align-items: center; justify-content: center; width: auto !important; min-width: 0; height: 34px !important; min-height: 34px; padding: 5px 11px; font-size: 12px !important; line-height: 22px !important; font-weight: 600 !important; border-radius: 6px !important; box-shadow: none !important; transform: none !important; white-space: nowrap; }
    #stock-movements-page .stock-filters button.btn { background: #5867db !important; }
    #stock-movements-page .stock-filters a.btn { color: #52627a !important; background: white !important; border: 1px solid #dce3ed !important; }

    /* Largeurs équilibrées : aucune colonne supprimée, y compris les actions. */
    #stock-movements-page .stock-table-wrapper { width: 100%; min-width: 0; }
    #stock-movements-page .stock-table { width: 100%; min-width: 0; table-layout: fixed; margin: 0; }
    #stock-movements-page .stock-table th,
    #stock-movements-page .stock-table td { padding: 8px 7px; font-size: 12px; line-height: 1.4; vertical-align: middle; white-space: normal; overflow-wrap: anywhere; }
    #stock-movements-page .stock-table th { font-size: 10px; font-weight: 700; letter-spacing: .02em; color: #52627a; background: #f1f4f9; overflow-wrap: normal; }
    #stock-movements-page .stock-table th:nth-child(1) { width: 13%; }
    #stock-movements-page .stock-table th:nth-child(2) { width: 21%; }
    #stock-movements-page .stock-table th:nth-child(3) { width: 14%; }
    #stock-movements-page .stock-table th:nth-child(4) { width: 18%; }
    #stock-movements-page .stock-table th:nth-child(5) { width: 8%; }
    #stock-movements-page .stock-table th:nth-child(6) { width: 8%; }
    #stock-movements-page .stock-table th:nth-child(7) { width: 10%; }
    #stock-movements-page .stock-table th:nth-child(8) { width: 8%; }
    #stock-movements-page .stock-table tbody tr:hover { background: #f8faff; }
    #stock-movements-page .stock-table .badge { max-width: 100%; font-size: 10px; padding: 5px 6px; white-space: normal; }
    #stock-movements-page .stock-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 4px; }
    #stock-movements-page .stock-actions .btn { display: inline-flex; align-items: center; justify-content: center; width: 27px; height: 27px; min-width: 27px; padding: 0; border-radius: 6px !important; box-shadow: none !important; }
    #stock-movements-page .stock-actions i { font-size: 14px; }
    #stock-movements-page .stock-actions form { margin: 0; }

    /* Si la zone centrale rétrécit, placer les boutons sous les trois filtres. */
    @container stock-movements (max-width: 850px) {
        #stock-movements-page .stock-filters { grid-template-columns: repeat(6, minmax(0, 1fr)); }
        #stock-movements-page .stock-filters > div:nth-child(-n+3) { grid-column: span 2; }
        #stock-movements-page .stock-filters > div:nth-child(4) { grid-column: 3 / 5; justify-self: end; }
        #stock-movements-page .stock-filters > div:nth-child(5) { grid-column: 5 / 7; justify-self: end; }
    }

    /* Sur téléphone : fiches lisibles contenant les huit informations. */
    @container stock-movements (max-width: 620px) {
        #stock-movements-page .stock-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        #stock-movements-page .stock-filters > div:nth-child(-n+3) { grid-column: 1 / -1; }
        #stock-movements-page .stock-filters > div:nth-child(4) { grid-column: 1; }
        #stock-movements-page .stock-filters > div:nth-child(5) { grid-column: 2; justify-self: start; }
        #stock-movements-page .stock-table thead { display: none; }
        #stock-movements-page .stock-table,
        #stock-movements-page .stock-table tbody { display: block; }
        #stock-movements-page .stock-table tr { display: block; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px; margin-bottom: 10px; }
        #stock-movements-page .stock-table td { display: grid; grid-template-columns: 110px minmax(0, 1fr); width: 100%; gap: 10px; border: 0; }
        #stock-movements-page .stock-table td::before { content: attr(data-label); font-size: 11px; font-weight: 600; color: #64748b; }
        #stock-movements-page .stock-table td[colspan] { display: block; }
        #stock-movements-page .stock-table td[colspan]::before { content: none; }
        #stock-movements-page .stock-actions { justify-content: flex-start; }
    }
</style>


<div id="stock-movements-page" class="card">

    <div class="card-header d-flex justify-content-between align-items-center">

        <h4 class="mb-0">

            Mouvements de stock

        </h4>

    </div>

    <div class="card-body">

        {{-- SEARCH BAR --}}

        <form method="GET"

            action="{{ url()->current() }}"

            class="stock-filters">

            {{-- REFERENCE --}}

            <div class="col-md-3">

                <div class="input-group shadow-sm">

                    <span class="input-group-text bg-light border-0">

                        <i class="bx bx-barcode text-primary"></i>

                    </span>

                    <input type="text"

                        name="reference"
                        aria-label="Référence produit"

                        class="form-control border-0 bg-light"

                        placeholder="Recherche référence..."

                        value="{{ request('reference') }}">

                </div>

            </div>

            {{-- DESIGNATION --}}

            <div class="col-md-3">

                <div class="input-group shadow-sm">

                    <span class="input-group-text bg-light border-0">

                        <i class="bx bx-package text-primary"></i>

                    </span>

                    <input type="text"

                        name="designation"
                        aria-label="Désignation"

                        class="form-control border-0 bg-light"

                        placeholder="Recherche désignation..."

                        value="{{ request('designation') }}">

                </div>

            </div>

            {{-- DATE --}}

            <div class="col-md-2">

                <input type="date"

                    name="date"
                        aria-label="Date du mouvement"

                    class="form-control bg-light border-0 shadow-sm"

                    value="{{ request('date') }}">

            </div>

            {{-- BUTTON SEARCH --}}

        <div class="col-md-2">

        <button type="submit"

                class="btn btn-primary w-100 fw-bold shadow-sm"

                style="

                    border-radius: 12px;

                    height: 48px;

                    font-size: 14px;

                    background: linear-gradient(135deg, #4f8cff, #2563eb);

                    border: none;

                    transition: 0.3s;

                "

                onmouseover="this.style.transform='translateY(-2px)'"

                onmouseout="this.style.transform='translateY(0px)'">

            Rechercher

        </button>

    </div>

    <div class="col-md-2">

        <a href="{{ url()->current() }}"

        class="btn w-100 fw-bold shadow-sm text-white"

        style="

                border-radius: 12px;

                height: 48px;

                line-height: 34px;

                font-size: 14px;

                background: linear-gradient(135deg, #64748b, #475569);

                border: none;

                transition: 0.3s;

        "

        onmouseover="this.style.transform='translateY(-2px)'"

        onmouseout="this.style.transform='translateY(0px)'">

            Réinitialiser

        </a>

        </div>

        </form>

       <div class="stock-table-wrapper">

            <table class="table table-bordered align-middle stock-table">

                <thead>

                    <tr>

                        <th>

                            Référence produit

                        </th>

                        <th>

                            Désignation

                        </th>

                        <th>

                            Document

                        </th>

                        <th>

                            Source

                        </th>

                        <th>

                            Quantité

                        </th>

                        <th>

                            Type

                        </th>

                        <th>

                            Date

                        </th>

                        <th>

                            Actions

                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($movements as $movement)

                        <tr>

                            {{-- RÉFÉRENCE PRODUIT --}}

                            <td data-label="Référence produit" class="fw-semibold">

                                {{ $movement->product->reference ?? '-' }}

                            </td>

                            {{-- DÉSIGNATION --}}

                            <td data-label="Désignation">

                                {{ $movement->product->designation ?? '-' }}

                            </td>

                            {{-- RÉFÉRENCE DOCUMENT / MOUVEMENT --}}

                            <td data-label="Document">

                                <span class="fw-semibold text-primary">

                                    {{ $movement->reference ?? '-' }}

                                </span>

                            </td>

                            {{-- SOURCE --}}

                            <td data-label="Source">

                                {{ $movement->source ?? '-' }}

                            </td>

                            {{-- QUANTITÉ --}}

                            <td data-label="Quantité">

                                <span class="fw-bold">

                                    {{ number_format($movement->quantity, 2) }}

                                </span>

                            </td>

                            {{-- TYPE --}}

                            <td data-label="Type">

                                @if($movement->type === 'in')

                                    <span class="badge bg-success">

                                        Entrée

                                    </span>

                                @elseif($movement->type === 'out')

                                    <span class="badge bg-danger">

                                        Sortie

                                    </span>

                                @else

                                    <span class="badge bg-secondary">

                                        {{ strtoupper($movement->type) }}

                                    </span>

                                @endif

                            </td>

                            {{-- DATE --}}

                            <td data-label="Date">

                                {{ optional($movement->created_at)->format('d/m/Y') }}

                            </td>

                            {{-- ACTIONS --}}

                            <td data-label="Actions">

                                <div class="stock-actions">

                                    {{-- SHOW --}}

                                    <a

                                        href="{{ route('stock-movements.show', $movement) }}"

                                        class="btn btn-info btn-sm text-white"

                                        title="Voir"

                                    >

                                        <i class="bx bx-show"></i>

                                    </a>

                                    {{-- ADMIN + CHEF MAGASINIER --}}

                                    @if(in_array(auth()->user()->role, ['admin', 'chef_magasinier']))

                                        <form

                                            action="{{ route('stock-movements.destroy', $movement) }}"

                                            method="POST"

                                            class="delete-form d-inline"

                                        >

                                            @csrf

                                            @method('DELETE')

                                            <button

                                                type="submit"

                                                class="btn btn-danger btn-sm rounded-pill shadow-sm"

                                                title="Supprimer"

                                            >

                                                <i class="bx bx-trash"></i>

                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td

                                colspan="8"

                                class="text-center py-5 text-muted"

                            >

                                Aucun mouvement trouvé.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const forms = document.querySelectorAll('.delete-form');

    forms.forEach(form => {

        form.addEventListener('submit', function (e) {

            e.preventDefault();

            Swal.fire({

                title: 'Supprimer le mouvement ?',

                text: "Cette action est irréversible.",

                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#ef4444',

                cancelButtonColor: '#64748b',

                confirmButtonText: 'Oui, supprimer',

                cancelButtonText: 'Annuler',

                background: '#0f172a',

                color: '#ffffff',

                borderRadius: '18px',

                width: '420px',

                backdrop: `

                    rgba(15,23,42,0.75)

                `

            }).then((result) => {

                if (result.isConfirmed) {

                    Swal.fire({

                        title: 'Supprimé !',

                        text: 'Le mouvement a été supprimé avec succès.',

                        icon: 'success',

                        confirmButtonColor: '#2563eb',

                        background: '#0f172a',

                        color: '#ffffff',

                        timer: 1500,

                        showConfirmButton: false

                    });

                    setTimeout(() => {

                        form.submit();

                    }, 1200);

                }

            });

        });

    });

});

</script>

@endsection
