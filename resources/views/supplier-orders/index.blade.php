@extends('layouts.layoutMaster')

@section('title', 'Bons de commande fournisseurs')

@section('content')

@php

    $statusLabels = [
        'draft' => 'Brouillon',
        'approved' => 'Approuvé',
        'sent' => 'Envoyé',
        'partial_received' => 'Réception partielle',
        'received' => 'Reçu',
        'cancelled' => 'Annulé',
    ];

@endphp


<style>
    .orders-page {
        padding: 24px;
    }

    .orders-container {
        max-width: 1500px;
        margin: auto;
    }

    .orders-header {
        background: white;
        border: 1px solid #e6e9f1;
        border-radius: 16px;
        padding: 23px 26px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
    }

    .orders-title {
        margin: 0;
        color: #303950;
        font-size: 27px;
        font-weight: 900;
    }

    .orders-subtitle {
        margin: 5px 0 0;
        color: #929bb0;
    }

    .orders-filter-card {
        background: white;
        border: 1px solid #e6e9f1;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .orders-filters {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto;
        gap: 14px;
        align-items: end;
    }

    .orders-label {
        display: block;
        margin-bottom: 7px;
        color: #657088;
        text-transform: uppercase;
        font-size: 10px;
        letter-spacing: .5px;
        font-weight: 900;
    }

    .orders-control {
        width: 100%;
        min-height: 44px;
        padding: 10px 13px;
        border: 1px solid #dce1eb;
        border-radius: 9px;
        color: #424b62;
        background: white;
    }

    .orders-search-btn {
        border: 0;
        background: #696cff;
        color: white;
        min-height: 44px;
        padding: 0 22px;
        border-radius: 9px;
        font-weight: 800;
        cursor: pointer;
    }

    .orders-table-card {
        background: white;
        border: 1px solid #e6e9f1;
        border-radius: 15px;
        overflow: hidden;
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
    }

    .orders-table th {
        padding: 14px 15px;
        background: #f2f4f9;
        color: #687289;
        text-transform: uppercase;
        font-size: 10px;
        letter-spacing: .6px;
        text-align: left;
    }

    .orders-table td {
        padding: 15px;
        border-bottom: 1px solid #eceef4;
        color: #4b556d;
        vertical-align: middle;
    }

    .orders-table tbody tr:hover {
        background: #fafbfe;
    }

    .orders-number {
        color: #696cff;
        font-weight: 900;
        text-decoration: none;
    }

    .orders-supplier {
        font-weight: 800;
        color: #374057;
    }

    .orders-status {
        display: inline-flex;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        background: #eef0ff;
        color: #5f62dc;
    }

    .orders-status-approved {
        background: #e9f8ee;
        color: #27864a;
    }

    .orders-status-sent {
        background: #e8f5fb;
        color: #2680a3;
    }

    .orders-status-partial_received {
        background: #fff5dd;
        color: #ad7718;
    }

    .orders-status-received {
        background: #e6f8eb;
        color: #258346;
    }

    .orders-status-cancelled {
        background: #ffebed;
        color: #c44751;
    }

    .orders-money {
        font-weight: 900;
        color: #364057;
        white-space: nowrap;
    }

    .orders-actions {
        display: flex;
        gap: 7px;
        align-items: center;
    }

    .orders-action {
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 800;
    }

    .orders-action-view {
        background: #e8f7fb;
        color: #23839c;
    }

    .orders-empty {
        padding: 65px 20px;
        text-align: center;
        color: #939bad;
    }

    .orders-pagination {
        padding: 18px;
    }

    .orders-alert {
        padding: 13px 17px;
        margin-bottom: 17px;
        border-radius: 9px;
        font-weight: 700;
    }

    .orders-alert-success {
        background: #eaf9ef;
        color: #27834a;
    }

    .orders-alert-error {
        background: #fff0f1;
        color: #bd3f4a;
    }

    @media(max-width: 900px) {
        .orders-filters {
            grid-template-columns: 1fr;
        }

        .orders-table-card {
            overflow-x: auto;
        }

        .orders-table {
            min-width: 1000px;
        }
    }
</style>


<div class="orders-page">

    <div class="orders-container">

        <div class="orders-header">

            <div>

                <h1 class="orders-title">
                    Bons de commande fournisseurs
                </h1>

                <p class="orders-subtitle">
                    Suivi des commandes fournisseurs,
                    approbations et réceptions
                </p>

            </div>

        </div>


        @if(session('success'))

            <div class="orders-alert orders-alert-success">
                {{ session('success') }}
            </div>

        @endif


        @if(session('error'))

            <div class="orders-alert orders-alert-error">
                {{ session('error') }}
            </div>

        @endif


        {{-- FILTRES --}}

        <div class="orders-filter-card">

            <form
                method="GET"
                action="{{ route('supplier-orders.index') }}"
            >

                <div class="orders-filters">

                    <div>

                        <label class="orders-label">
                            Recherche
                        </label>

                        <input
                            type="text"
                            name="search"
                            class="orders-control"
                            value="{{ request('search') }}"
                            placeholder="N° BC, fournisseur..."
                        >

                    </div>


                    <div>

                        <label class="orders-label">
                            Fournisseur
                        </label>

                        <select
                            name="supplier_id"
                            class="orders-control"
                        >

                            <option value="">
                                Tous les fournisseurs
                            </option>

                            @foreach($suppliers as $supplier)

                                <option
                                    value="{{ $supplier->id }}"
                                    @selected(
                                        request('supplier_id')
                                        == $supplier->id
                                    )
                                >
                                    {{ $supplier->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div>

                        <label class="orders-label">
                            Statut
                        </label>

                        <select
                            name="status"
                            class="orders-control"
                        >

                            <option value="">
                                Tous les statuts
                            </option>

                            @foreach(
                                $statusLabels
                                as $status => $label
                            )

                                <option
                                    value="{{ $status }}"
                                    @selected(
                                        request('status')
                                        === $status
                                    )
                                >
                                    {{ $label }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div>

                        <button
                            type="submit"
                            class="orders-search-btn"
                        >
                            Rechercher
                        </button>

                    </div>

                </div>

            </form>

        </div>


        {{-- TABLEAU --}}

        <div class="orders-table-card">

            @if($supplierOrders->count())

                <table class="orders-table">

                    <thead>

                        <tr>
                            <th>N° BC</th>
                            <th>Date</th>
                            <th>Fournisseur</th>
                            <th>Dépôt</th>
                            <th>Articles</th>
                            <th>Statut</th>
                            <th>Total</th>
                            <th>Créé par</th>
                            <th>Actions</th>
                        </tr>

                    </thead>


                    <tbody>

                        @foreach(
                            $supplierOrders
                            as $supplierOrder
                        )

                            <tr>

                                <td>

                                    <a
                                        href="{{ route(
                                            'supplier-orders.show',
                                            $supplierOrder
                                        ) }}"
                                        class="orders-number"
                                    >
                                        {{
                                            $supplierOrder
                                                ->order_number
                                        }}
                                    </a>

                                </td>


                                <td>
                                    {{
                                        $supplierOrder
                                            ->order_date
                                            ?->format('d/m/Y')
                                    }}
                                </td>


                                <td>

                                    <div class="orders-supplier">
                                        {{
                                            $supplierOrder
                                                ->supplier
                                                ?->name
                                            ?? '-'
                                        }}
                                    </div>

                                    @if(
                                        $supplierOrder
                                            ->supplier
                                            ?->code
                                    )

                                        <small>
                                            {{
                                                $supplierOrder
                                                    ->supplier
                                                    ->code
                                            }}
                                        </small>

                                    @endif

                                </td>


                                <td>
                                    {{
                                        $supplierOrder
                                            ->depot
                                            ?->name
                                        ?? '-'
                                    }}
                                </td>


                                <td>
                                    {{
                                        $supplierOrder
                                            ->items_count
                                    }}
                                </td>


                                <td>

                                    <span
                                        class="
                                            orders-status
                                            orders-status-{{
                                                $supplierOrder
                                                    ->status
                                            }}
                                        "
                                    >
                                        {{
                                            $statusLabels[
                                                $supplierOrder
                                                    ->status
                                            ]
                                            ??
                                            $supplierOrder
                                                ->status
                                        }}
                                    </span>

                                </td>


                                <td class="orders-money">

                                    {{
                                        number_format(
                                            (float)
                                            $supplierOrder
                                                ->total,
                                            2,
                                            ',',
                                            ' '
                                        )
                                    }}

                                    {{
                                        $supplierOrder
                                            ->currency
                                    }}

                                </td>


                                <td>
                                    {{
                                        $supplierOrder
                                            ->creator
                                            ?->name
                                        ?? '-'
                                    }}
                                </td>


                                <td>

                                    <div class="orders-actions">

                                        <a
                                            href="{{ route(
                                                'supplier-orders.show',
                                                $supplierOrder
                                            ) }}"
                                            class="
                                                orders-action
                                                orders-action-view
                                            "
                                        >
                                            Voir
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>


                <div class="orders-pagination">
                    {{ $supplierOrders->links() }}
                </div>

            @else

                <div class="orders-empty">

                    <h3>
                        Aucun bon de commande
                    </h3>

                    <p>
                        Les bons de commande générés
                        apparaîtront ici.
                    </p>

                </div>

            @endif

        </div>

    </div>

</div>

@endsection
