@extends('layouts.layoutMaster')

@section('content')

<style>

    /* ============================================================
       PAGE
    ============================================================ */

    .reorder-page {
        width: 100%;
        padding: 22px 18px 45px;
        overflow-x: hidden;
    }

    .reorder-page-inner {
        width: 100%;
        max-width: 1500px;
        margin: 0 auto;
    }


    /* ============================================================
       CARTE
    ============================================================ */

    .reorder-card {
        width: 100%;
        overflow: hidden;

        background: #ffffff;

        border: 1px solid #e5e7eb;
        border-radius: 16px;

        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    }


    /* ============================================================
       HEADER
    ============================================================ */

    .reorder-header {
        padding: 24px 28px;

        border-bottom: 1px solid #edf0f4;

        background: #ffffff;
    }

    .reorder-title {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 15px;

        flex-wrap: wrap;
    }

    .reorder-title-content {
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .reorder-icon {
        width: 46px;
        height: 46px;

        display: flex;
        align-items: center;
        justify-content: center;

        flex-shrink: 0;

        font-size: 23px;

        border-radius: 11px;

        color: #ffffff;
        background: #f59e0b;
    }

    .reorder-title h4 {
        margin: 0;

        font-size: clamp(21px, 2vw, 27px);
        font-weight: 800;

        color: #334155;
    }

    .reorder-title p {
        margin: 5px 0 0;

        font-size: 13px;

        color: #64748b;
    }


    /* ============================================================
       BODY
    ============================================================ */

    .reorder-body {
        padding: 22px 28px 28px;
    }


    /* ============================================================
       INFO
    ============================================================ */

    .reorder-info {
        display: flex;
        align-items: flex-start;

        gap: 10px;

        margin-bottom: 20px;
        padding: 13px 16px;

        color: #92400e;

        background: #fff7ed;

        border: 1px solid #fed7aa;
        border-radius: 10px;
    }

    .reorder-info i {
        margin-top: 1px;
        font-size: 20px;
    }

    .reorder-info-text {
        font-size: 13px;
        line-height: 1.5;
    }


    /* ============================================================
       RECHERCHE
    ============================================================ */

    .reorder-search {
        display: flex;
        align-items: stretch;

        flex-wrap: wrap;

        gap: 12px;

        width: 100%;

        margin-bottom: 22px;
    }

    .reorder-search {
        align-items: flex-end;
    }

    .reorder-filtre {
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex: 1 1 180px;
        min-width: 170px;
    }

    .reorder-filtre-large {
        flex: 2 1 260px;
    }

    .reorder-filtre label {
        margin: 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
    }

    .reorder-search .form-select {
        min-height: 46px;
        border-radius: 9px;
    }

    .reorder-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .reorder-search .reorder-export,
    .reorder-search .reorder-export:hover {
        color: #ffffff !important;
    }

    .reorder-export-nb {
        font-weight: 500;
        opacity: .85;
    }

    .reorder-search .form-control,
    .reorder-search .btn {
        min-height: 46px;
        border-radius: 9px;
    }

    .reorder-search .form-control {
        width: 100%;
        padding: 10px 14px;

        border: 1px solid #d8dee8;

        box-shadow: none;
    }

    .reorder-search .form-control:focus {
        border-color: #5b8def;

        box-shadow:
            0 0 0 3px rgba(91, 141, 239, .12);
    }

    .reorder-search .btn {
        min-width: 150px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 7px;

        padding: 10px 18px;

        font-weight: 700;
    }


    /* ============================================================
       TABLE
    ============================================================ */

    .reorder-table-wrapper {
        width: 100%;

        overflow-x: auto;

        -webkit-overflow-scrolling: touch;

        border: 1px solid #edf0f4;
        border-radius: 11px;
    }

    .reorder-table {
        width: 100%;

        min-width: 850px;

        margin: 0;
    }

    .reorder-table thead th {
        padding: 14px 14px;

        vertical-align: middle;

        white-space: nowrap;

        font-size: 12px;
        font-weight: 800;

        letter-spacing: .04em;

        text-transform: uppercase;

        color: #52657b;

        background: #e8edf3;
    }

    .reorder-table tbody td {
        padding: 14px;

        vertical-align: middle;

        font-size: 13px;

        color: #52657b;

        border-top: 1px solid #edf0f4;
    }

    .reorder-table tbody tr:hover {
        background: #f8fafc;
    }

    .reference-cell {
        min-width: 170px;
        white-space: nowrap;
    }

    .designation-cell {
        min-width: 220px;
    }


    /* ============================================================
       QUANTITÉ
    ============================================================ */

    .quantity-badge {
        min-width: 85px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        padding: 6px 10px;

        border-radius: 7px;

        font-size: 12px;
        font-weight: 800;
    }


    /* ============================================================
       STATUS
    ============================================================ */

    .status-badge {
        min-width: 100px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        padding: 6px 10px;

        border-radius: 7px;

        font-size: 11px;
        font-weight: 800;

        text-transform: uppercase;
    }


    /* ============================================================
       PAGINATION
    ============================================================ */

    .reorder-pagination {
        display: flex;
        justify-content: center;

        margin-top: 22px;
    }

    .reorder-pagination svg {
        width: 18px !important;
        height: 18px !important;
    }


    /* ============================================================
       MOBILE
    ============================================================ */

    @media(max-width: 768px) {

        .reorder-page {
            padding: 14px 10px 32px;
        }

        .reorder-header,
        .reorder-body {
            padding: 16px;
        }

        .reorder-filtre,
        .reorder-actions,
        .reorder-search .btn {
            flex: 1 1 100%;
            width: 100%;
        }

        .reorder-title {
            align-items: flex-start;
        }

    }

</style>


<div class="reorder-page">

    <div class="reorder-page-inner">


        {{-- ============================================================
             MESSAGES
        ============================================================ --}}

        @if(session('success'))

            <div class="alert alert-success alert-dismissible fade show">

                {{ session('success') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Fermer"
                ></button>

            </div>

        @endif


        {{-- ============================================================
             CARTE
        ============================================================ --}}

        <div class="reorder-card">


            {{-- ========================================================
                 HEADER
            ======================================================== --}}

            <div class="reorder-header">

                <div class="reorder-title">

                    <div class="reorder-title-content">

                        <div class="reorder-icon">

                            <i class="bx bx-cart-add"></i>

                        </div>


                        <div>

                            <h4>
                                Pièces à commander
                            </h4>

                            <p>
                                Produits en rupture ou ayant atteint
                                leur seuil minimum de stock.
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ========================================================
                 BODY
            ======================================================== --}}

            <div class="reorder-body">


                {{-- INFORMATION --}}

                <div class="reorder-info">

                    <i class="bx bx-info-circle"></i>

                    <div class="reorder-info-text">

                        Cette liste est générée automatiquement.

                        Une pièce apparaît ici lorsque sa
                        <strong>quantité disponible</strong>
                        est inférieure ou égale à son
                        <strong>seuil minimum</strong>.

                    </div>

                </div>


                {{-- ====================================================
                     FILTRES : recherche, marque, modèle, statut
                     L'export Excel reprend exactement les mêmes filtres.
                ==================================================== --}}

                @php
                    $filtresActifs = array_filter(
                        request()->only(['search', 'brand_id', 'model_id', 'statut']),
                        fn ($v) => $v !== null && $v !== ''
                    );
                    $statuts = [
                        'rupture'            => 'Rupture',
                        'stock_faible'       => 'Stock faible',
                        'en_recherche'       => 'En recherche',
                        'en_commande'        => 'En commande',
                        'partiellement_recu' => 'Partiellement reçu',
                    ];
                @endphp

                <form
                    method="GET"
                    action="{{ route('products.to-order') }}"
                    class="reorder-search"
                    id="reorder-filtres"
                >

                    <div class="reorder-filtre reorder-filtre-large">
                        <label for="filtre-search">Référence / désignation</label>
                        <input
                            type="text"
                            id="filtre-search"
                            name="search"
                            class="form-control"
                            placeholder="Référence, désignation…"
                            value="{{ request('search') }}"
                        >
                    </div>

                    <div class="reorder-filtre">
                        <label for="filtre-marque">Marque</label>
                        <select id="filtre-marque" name="brand_id" class="form-select">
                            <option value="">Toutes les marques</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" @selected((string) request('brand_id') === (string) $brand->id)>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="reorder-filtre">
                        <label for="filtre-modele">Modèle</label>
                        <select id="filtre-modele" name="model_id" class="form-select">
                            <option value="">Tous les modèles</option>
                            @foreach($models as $model)
                                <option
                                    value="{{ $model->id }}"
                                    data-marque="{{ $model->brand_id }}"
                                    @selected((string) request('model_id') === (string) $model->id)
                                >
                                    {{ $model->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="reorder-filtre">
                        <label for="filtre-statut">Statut</label>
                        <select id="filtre-statut" name="statut" class="form-select">
                            <option value="">Tous les statuts</option>
                            @foreach($statuts as $valeur => $libelle)
                                <option value="{{ $valeur }}" @selected(request('statut') === $valeur)>
                                    {{ $libelle }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="reorder-actions">

                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-filter-alt"></i>
                            Filtrer
                        </button>

                        <a href="{{ route('products.to-order') }}" class="btn btn-secondary">
                            <i class="bx bx-reset"></i>
                            Réinitialiser
                        </a>

                        {{-- Export Excel : la liste filtrée ci-dessous, sans pagination --}}
                        <a
                            href="{{ route('products.to-order.export', $filtresActifs) }}"
                            class="btn btn-success reorder-export"
                        >
                            <i class="bx bx-download"></i>
                            Exporter Excel
                            <span class="reorder-export-nb">({{ $products->total() }})</span>
                        </a>

                    </div>

                </form>

                <script>
                    // Modèle : seulement ceux de la marque choisie. Un choix de
                    // marque, de modèle ou de statut filtre tout de suite.
                    (function () {
                        var form = document.getElementById('reorder-filtres');
                        var marque = document.getElementById('filtre-marque');
                        var modele = document.getElementById('filtre-modele');

                        function limiterModeles() {
                            Array.prototype.forEach.call(modele.options, function (opt) {
                                if (!opt.value) return;
                                var visible = !marque.value || opt.dataset.marque === marque.value;
                                opt.hidden = !visible;
                                if (!visible && opt.selected) modele.value = '';
                            });
                        }

                        limiterModeles();
                        marque.addEventListener('change', function () { limiterModeles(); form.submit(); });
                        modele.addEventListener('change', function () { form.submit(); });
                        document.getElementById('filtre-statut').addEventListener('change', function () { form.submit(); });
                    })();
                </script>


                {{-- ====================================================
                     TABLEAU
                ==================================================== --}}

                <div class="reorder-table-wrapper">

                    <table
                        class="
                            table
                            table-hover
                            align-middle
                            reorder-table
                        "
                    >

                        <thead>

                            <tr>

                                <th>
                                    Référence
                                </th>

                                <th>
                                    Désignation
                                </th>

                                <th>
                                    Marque
                                </th>

                                <th>
                                    Modèle
                                </th>

                                <th class="text-center">
                                    Quantité disponible
                                </th>

                                <th class="text-center">
                                    Statut
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($products as $product)

                                @php

                                    $availableQty =
                                        (float) $product->quantity;

                                    $minStock =
                                        (float) $product->min_stock;

                                @endphp


                                <tr>

                                    {{-- RÉFÉRENCE --}}
                                    <td class="reference-cell">

                                        <strong>
                                            {{ $product->reference }}
                                        </strong>

                                    </td>


                                    {{-- DÉSIGNATION --}}
                                    <td class="designation-cell">

                                        {{ $product->designation }}

                                    </td>


                                    {{-- MARQUE --}}
                                    <td>

                                        {{
                                            $product->brand?->name
                                            ?? 'Non définie'
                                        }}

                                    </td>


                                    {{-- MODÈLE --}}
                                    <td>

                                        {{
                                            $product->model?->name
                                            ?? 'Non défini'
                                        }}

                                    </td>


                                    {{-- QUANTITÉ DISPONIBLE --}}
                                    <td class="text-center">

                                        @if($availableQty <= 0)

                                            <span
                                                class="
                                                    quantity-badge
                                                    bg-label-danger
                                                "
                                            >

                                                {{
                                                    number_format(
                                                        $availableQty,
                                                        2,
                                                        ',',
                                                        ' '
                                                    )
                                                }}

                                                {{
                                                    $product->unit_label
                                                }}

                                            </span>

                                        @else

                                            <span
                                                class="
                                                    quantity-badge
                                                    bg-label-warning
                                                "
                                            >

                                                {{
                                                    number_format(
                                                        $availableQty,
                                                        2,
                                                        ',',
                                                        ' '
                                                    )
                                                }}

                                                {{
                                                    $product->unit_label
                                                }}

                                            </span>

                                        @endif

                                    </td>


                                    {{-- STATUT --}}

                                    <td class="text-center">

                                        @php

                                            $supplyStatus =
                                                $product->supply_status ?? null;

                                            $initialQty =
                                                (float) ($product->initial_quantity ?? 0);

                                            $receivedQty =
                                                (float) ($product->received_quantity ?? 0);

                                        @endphp


                                        {{-- EN RECHERCHE --}}
                                        @if($supplyStatus === 'en_recherche')

                                            <span class="status-badge bg-info text-white">

                                                En recherche

                                            </span>


                                        {{-- EN COMMANDE --}}
                                        @elseif($supplyStatus === 'en_commande')

                                            <span class="status-badge bg-primary text-white">

                                                En commande

                                            </span>


                                        {{-- PARTIELLEMENT REÇU --}}
                                        @elseif(
                                            $receivedQty > 0
                                            &&
                                            $receivedQty < $initialQty
                                        )

                                            <span class="status-badge bg-warning text-dark">

                                                Partiellement reçu

                                            </span>


                                        {{-- RUPTURE --}}
                                        @elseif($availableQty <= 0)

                                            <span class="status-badge bg-danger text-white">

                                                Rupture

                                            </span>


                                        {{-- STOCK FAIBLE --}}
                                        @elseif(
                                            $minStock > 0
                                            &&
                                            $availableQty <= $minStock
                                        )

                                            <span class="status-badge bg-warning text-dark">

                                                Stock faible

                                            </span>


                                        {{-- AUTRE --}}
                                        @else

                                            <span class="status-badge bg-secondary text-white">

                                                À traiter

                                            </span>

                                        @endif

                                    </td>

                                </tr>


                            @empty

                                <tr>

                                    <td
                                        colspan="6"
                                        class="
                                            text-center
                                            text-muted
                                            py-5
                                        "
                                    >

                                        <i
                                            class="
                                                bx
                                                bx-check-circle
                                                fs-1
                                                d-block
                                                mb-2
                                                text-success
                                            "
                                        ></i>

                                        <strong>
                                            Aucune pièce à commander.
                                        </strong>

                                        <div class="mt-1">

                                            Tous les produits disposent
                                            actuellement d'un stock
                                            supérieur à leur seuil minimum.

                                        </div>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- ====================================================
                     PAGINATION
                ==================================================== --}}

                @if(
                    method_exists($products, 'links')
                    && $products->hasPages()
                )

                    <div class="reorder-pagination">

                        {{ $products->links() }}

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection
