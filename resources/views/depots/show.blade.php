@extends('layouts.layoutMaster')

@section('content')

<div class="card shadow-sm border-0">

    {{-- ================================================================ --}}
    {{-- HEADER --}}
    {{-- ================================================================ --}}
    <div
        class="card-header bg-white border-bottom
               d-flex justify-content-between
               align-items-center flex-wrap gap-3"
    >

        <div>

            <h2 class="mb-1 fw-bold">
                Compte dépôt
            </h2>

            <small class="text-muted">
                Informations et gestion du dépôt
            </small>

        </div>

        <div class="d-flex gap-2">

            <a
                href="{{ route('depots.edit', $depot) }}"
                class="btn btn-warning"
            >
                <i class="bx bx-edit me-1"></i>
                Modifier
            </a>

            <a
                href="{{ route('depots.index') }}"
                class="btn btn-secondary"
            >
                <i class="bx bx-arrow-back me-1"></i>
                Retour
            </a>

        </div>

    </div>


    {{-- ================================================================ --}}
    {{-- BODY --}}
    {{-- ================================================================ --}}
    <div class="card-body">

        {{-- ============================================================ --}}
        {{-- INFORMATIONS DÉPÔT --}}
        {{-- ============================================================ --}}
        <div class="row mb-5">

            {{-- NOM --}}
            <div class="col-md-4 mb-3">

                <label class="fw-bold">
                    Nom dépôt
                </label>

                <input
                    type="text"
                    class="form-control"
                    value="{{ $depot->name }}"
                    readonly
                >

            </div>

            {{-- CODE --}}
            <div class="col-md-4 mb-3">

                <label class="fw-bold">
                    Code
                </label>

                <input
                    type="text"
                    class="form-control"
                    value="{{ $depot->code ?? '-' }}"
                    readonly
                >

            </div>

            {{-- STATUT --}}
            <div class="col-md-4 mb-3">

                <label class="fw-bold">
                    Statut
                </label>

                <input
                    type="text"
                    class="form-control"
                    value="{{ $depot->is_active ? 'Actif' : 'Inactif' }}"
                    readonly
                >

            </div>

            {{-- ADRESSE --}}
            <div class="col-md-12">

                <label class="fw-bold">
                    Adresse
                </label>

                <textarea
                    class="form-control"
                    rows="3"
                    readonly
                >{{ $depot->address ?? '' }}</textarea>

            </div>

        </div>


        {{-- ============================================================ --}}
        {{-- KPI --}}
        {{-- ============================================================ --}}
        <div class="row mb-5 g-3">

            {{-- PRODUITS STOCKÉS --}}
            <div class="col-md-3">

                <div class="card border shadow-sm h-100">

                    <div class="card-body text-center">

                        <h6 class="text-muted">
                            Produits stockés
                        </h6>

                        <h1 class="text-primary mb-0">
                            {{ $totalProducts }}
                        </h1>

                    </div>

                </div>

            </div>


            {{-- QUANTITÉ TOTALE --}}
            <div class="col-md-3">

                <div class="card border shadow-sm h-100">

                    <div class="card-body text-center">

                        <h6 class="text-muted">
                            Quantité totale
                        </h6>

                        <h1 class="text-success mb-0">
                            {{ number_format($totalQuantity, 2, ',', ' ') }}
                        </h1>

                    </div>

                </div>

            </div>


            {{-- STOCK FAIBLE --}}
            <div class="col-md-3">

                <div class="card border shadow-sm h-100">

                    <div class="card-body text-center">

                        <h6 class="text-muted">
                            Stock faible
                        </h6>

                        <h1 class="text-warning mb-0">
                            {{ $lowStocks }}
                        </h1>

                    </div>

                </div>

            </div>


            {{-- RUPTURES --}}
            <div class="col-md-3">

                <div class="card border shadow-sm h-100">

                    <div class="card-body text-center">

                        <h6 class="text-muted">
                            Ruptures
                        </h6>

                        <h1 class="text-danger mb-0">
                            {{ $ruptures }}
                        </h1>

                    </div>

                </div>

            </div>

        </div>


        {{-- ============================================================ --}}
        {{-- VALEUR STOCK --}}
        {{-- ============================================================ --}}
        <div class="card border shadow-sm mb-5">

            <div class="card-body text-center py-4">

                <h5 class="text-muted">
                    Valeur totale stock
                </h5>

                <h1 class="text-primary mb-0">

                    {{ number_format(
                        $totalValue,
                        2,
                        '.',
                        ','
                    ) }}

                    FDJ

                </h1>

            </div>

        </div>


        {{-- ============================================================ --}}
        {{-- PRODUITS STOCKÉS --}}
        {{-- ============================================================ --}}
        <div class="card border shadow-sm mb-5">

            {{-- HEADER PRODUITS --}}
            <div class="card-header bg-white border-bottom">

                <div class="row align-items-center g-3">

                    <div class="col-md-6">

                        <h3 class="mb-1">
                            Produits stockés
                        </h3>

                        <small class="text-muted">

                            <span id="productCount">
                                {{ $stocks->where('quantity', '>', 0)->count() }}
                            </span>

                            produit(s) trouvé(s)

                        </small>

                    </div>


                    {{-- ==================================================== --}}
                    {{-- BARRE DE RECHERCHE --}}
                    {{-- ==================================================== --}}
                    <div class="col-md-6">

                        <div class="input-group">

                            <span class="input-group-text bg-light">

                                <i class="bx bx-search"></i>

                            </span>

                            <input
                                type="text"
                                id="productSearch"
                                class="form-control"
                                placeholder="Rechercher référence, produit, marque, modèle..."
                                autocomplete="off"
                            >

                            <button
                                type="button"
                                id="clearProductSearch"
                                class="btn btn-outline-secondary"
                                title="Réinitialiser la recherche"
                            >
                                <i class="bx bx-x"></i>
                            </button>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ============================================================ --}}
            {{-- TABLEAU PRODUITS --}}
            {{-- ============================================================ --}}
            <div class="card-body p-0">

                <div class="table-responsive">

                    <table
                        class="table table-bordered table-hover align-middle mb-0"
                        id="productsTable"
                    >

                        <thead class="table-light">

                            <tr>

                                <th>
                                    Référence
                                </th>

                                <th>
                                    Produit
                                </th>

                                <th>
                                    Marque
                                </th>

                                <th>
                                    Modèle
                                </th>

                                <th class="text-center">
                                    Stock
                                </th>

                                <th class="text-end">
                                    Prix vente
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse(
                                $stocks->where('quantity', '>', 0)
                                as $stock
                            )

                                <tr
                                    class="product-row"
                                    data-search="
                                        {{ mb_strtolower(
                                            trim(
                                                ($stock->product->reference ?? '')
                                                . ' '
                                                . ($stock->product->designation ?? '')
                                                . ' '
                                                . ($stock->product->brand->name ?? '')
                                                . ' '
                                                . ($stock->product->model->name ?? '')
                                            )
                                        ) }}
                                    "
                                >

                                    {{-- RÉFÉRENCE --}}
                                    <td>

                                        <strong>
                                            {{ $stock->product->reference ?? '-' }}
                                        </strong>

                                    </td>


                                    {{-- PRODUIT --}}
                                    <td>

                                        {{ $stock->product->designation ?? '-' }}

                                    </td>


                                    {{-- MARQUE --}}
                                    <td>

                                        {{ $stock->product->brand->name ?? '-' }}

                                    </td>


                                    {{-- MODÈLE --}}
                                    <td>

                                        {{ $stock->product->model->name ?? '-' }}

                                    </td>


                                    {{-- STOCK --}}
                                    <td class="text-center">

                                        @if(
                                            (float) $stock->quantity <= 0
                                        )

                                            <span class="badge bg-danger">
                                                Rupture
                                            </span>

                                        @elseif(
                                            (float) $stock->quantity
                                            <=
                                            (float) (
                                                $stock->product->min_stock
                                                ?? 0
                                            )
                                        )

                                            <span class="badge bg-warning text-dark">

                                                {{ number_format(
                                                    (float) $stock->quantity,
                                                    2,
                                                    ',',
                                                    ' '
                                                ) }}

                                            </span>

                                        @else

                                            <span class="badge bg-success">

                                                {{ number_format(
                                                    (float) $stock->quantity,
                                                    2,
                                                    ',',
                                                    ' '
                                                ) }}

                                            </span>

                                        @endif

                                    </td>


                                    {{-- PRIX VENTE --}}
                                    <td class="text-end">

                                        {{ number_format(
                                            (float) (
                                                $stock->product->sale_price
                                                ?? 0
                                            ),
                                            2,
                                            '.',
                                            ','
                                        ) }}

                                        FDJ

                                    </td>

                                </tr>

                            @empty

                                <tr id="initialEmptyRow">

                                    <td
                                        colspan="6"
                                        class="text-center text-muted py-4"
                                    >

                                        <i
                                            class="bx bx-package d-block mb-2"
                                            style="font-size: 32px;"
                                        ></i>

                                        Aucun produit trouvé.

                                    </td>

                                </tr>

                            @endforelse


                            {{-- ================================================= --}}
                            {{-- AUCUN RÉSULTAT DE RECHERCHE --}}
                            {{-- ================================================= --}}
                            <tr
                                id="searchEmptyRow"
                                style="display: none;"
                            >

                                <td
                                    colspan="6"
                                    class="text-center text-muted py-4"
                                >

                                    <i
                                        class="bx bx-search-alt d-block mb-2"
                                        style="font-size: 32px;"
                                    ></i>

                                    Aucun produit ne correspond à votre recherche.

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- ============================================================ --}}
        {{-- HISTORIQUE TRANSFERTS --}}
        {{-- ============================================================ --}}
        <div class="card border shadow-sm">

            <div class="card-header bg-white border-bottom">

                <h3 class="mb-0">
                    Historique transferts
                </h3>

            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table
                        class="table table-bordered table-hover align-middle mb-0"
                    >

                        <thead class="table-light">

                            <tr>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Produit
                                </th>

                                <th>
                                    Source
                                </th>

                                <th>
                                    Destination
                                </th>

                                <th class="text-center">
                                    Qté
                                </th>

                                <th>
                                    Utilisateur
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($transfers as $transfer)

                                <tr>

                                    {{-- DATE --}}
                                    <td>

                                        {{
                                            optional(
                                                $transfer->created_at
                                            )->format('d/m/Y H:i')
                                            ?? '-'
                                        }}

                                    </td>


                                    {{-- PRODUIT --}}
                                    <td>

                                        {{ $transfer->product->designation ?? '-' }}

                                    </td>


                                    {{-- SOURCE --}}
                                    <td>

                                        {{ $transfer->sourceDepot->name ?? '-' }}

                                    </td>


                                    {{-- DESTINATION --}}
                                    <td>

                                        {{ $transfer->destinationDepot->name ?? '-' }}

                                    </td>


                                    {{-- QUANTITÉ --}}
                                    <td class="text-center">

                                        {{ number_format(
                                            (float) $transfer->quantity,
                                            2,
                                            ',',
                                            ' '
                                        ) }}

                                    </td>


                                    {{-- UTILISATEUR --}}
                                    <td>

                                        {{ $transfer->user->name ?? '-' }}

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="6"
                                        class="text-center text-muted py-4"
                                    >

                                        Aucun transfert trouvé.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- ====================================================================== --}}
{{-- JAVASCRIPT RECHERCHE PRODUITS --}}
{{-- ====================================================================== --}}
<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | ÉLÉMENTS
        |--------------------------------------------------------------------------
        */

        const searchInput =
            document.getElementById(
                'productSearch'
            );

        const clearButton =
            document.getElementById(
                'clearProductSearch'
            );

        const productRows =
            document.querySelectorAll(
                '#productsTable tbody .product-row'
            );

        const productCount =
            document.getElementById(
                'productCount'
            );

        const searchEmptyRow =
            document.getElementById(
                'searchEmptyRow'
            );


        /*
        |--------------------------------------------------------------------------
        | NORMALISER TEXTE
        |--------------------------------------------------------------------------
        |
        | Permet par exemple :
        |
        | "élément" => "element"
        |
        */
        function normalizeText(value)
        {
            return String(
                value ?? ''
            )
            .toLowerCase()
            .normalize('NFD')
            .replace(
                /[\u0300-\u036f]/g,
                ''
            )
            .trim();
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRER PRODUITS
        |--------------------------------------------------------------------------
        */
        function filterProducts()
        {
            if (!searchInput) {
                return;
            }

            const search =
                normalizeText(
                    searchInput.value
                );


            let visibleCount = 0;


            productRows.forEach(
                function (row) {

                    const searchableText =
                        normalizeText(
                            row.dataset.search
                            || row.textContent
                        );


                    const matches =
                        search === ''
                        ||
                        searchableText.includes(
                            search
                        );


                    if (matches) {

                        row.style.display = '';

                        visibleCount++;

                    } else {

                        row.style.display =
                            'none';

                    }

                }
            );


            /*
            |--------------------------------------------------------------------------
            | COMPTEUR
            |--------------------------------------------------------------------------
            */
            if (productCount) {

                productCount.textContent =
                    visibleCount;

            }


            /*
            |--------------------------------------------------------------------------
            | MESSAGE AUCUN RÉSULTAT
            |--------------------------------------------------------------------------
            */
            if (searchEmptyRow) {

                searchEmptyRow.style.display =
                    (
                        visibleCount === 0
                        &&
                        productRows.length > 0
                    )
                    ? ''
                    : 'none';

            }
        }


        /*
        |--------------------------------------------------------------------------
        | SAISIE RECHERCHE
        |--------------------------------------------------------------------------
        */
        if (searchInput) {

            searchInput.addEventListener(
                'input',
                filterProducts
            );

        }


        /*
        |--------------------------------------------------------------------------
        | RÉINITIALISER
        |--------------------------------------------------------------------------
        */
        if (clearButton) {

            clearButton.addEventListener(
                'click',
                function () {

                    if (!searchInput) {
                        return;
                    }

                    searchInput.value =
                        '';

                    filterProducts();

                    searchInput.focus();

                }
            );

        }

    }
);

</script>

@endsection
