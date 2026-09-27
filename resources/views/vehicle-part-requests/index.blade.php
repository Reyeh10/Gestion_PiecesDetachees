@extends('layouts.layoutMaster')

@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | CONTEXTE
    |--------------------------------------------------------------------------
    */

    $currentList =
        $currentList ?? 'all';

    $pageTitle =
        $pageTitle
        ?? 'Suivi des pièces des véhicules';

    $pageDescription =
        $pageDescription
        ?? 'Recherche, commande et réception des pièces';

    $currentUrl =
        url()->current();

@endphp


<style>

    /*
    |--------------------------------------------------------------------------
    | PAGE
    |--------------------------------------------------------------------------
    */

    .vpr-index-page {
        width: 100%;
        padding: 22px 18px 45px;
    }

    .vpr-index-inner {
        width: 100%;
        max-width: 1600px;
        margin: 0 auto;
    }


    /*
    |--------------------------------------------------------------------------
    | CARD
    |--------------------------------------------------------------------------
    */

    .vpr-index-card {
        overflow: hidden;

        background: #ffffff;

        border: 1px solid #e5e7eb;
        border-radius: 16px;

        box-shadow:
            0 10px 30px rgba(15, 23, 42, .08);
    }


    /*
    |--------------------------------------------------------------------------
    | HEADER
    |--------------------------------------------------------------------------
    */

    .vpr-index-header {
        padding: 24px 28px;

        background: #ffffff;

        border-bottom: 1px solid #edf0f4;
    }

    .vpr-index-title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;

        gap: 16px;

        flex-wrap: wrap;
    }

    .vpr-index-title-row h3 {
        margin: 0 0 5px;

        font-size: 26px;
        font-weight: 800;

        color: #334155;
    }

    .vpr-index-title-row p {
        margin: 0;

        color: #94a3b8;
    }

    .vpr-new-button {
        min-height: 44px;

        padding: 9px 18px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 7px;

        border-radius: 9px;

        font-weight: 700;
    }


    /*
    |--------------------------------------------------------------------------
    | BODY
    |--------------------------------------------------------------------------
    */

    .vpr-index-body {
        padding: 22px 28px 28px;
    }


    /*
    |--------------------------------------------------------------------------
    | FILTRES
    |--------------------------------------------------------------------------
    */

    .vpr-filter-panel {
        margin-bottom: 22px;

        padding: 18px;

        background: #f8fafc;

        border: 1px solid #e5e7eb;
        border-radius: 12px;
    }

    .vpr-filter-panel .form-label {
        margin-bottom: 7px;

        display: block;

        font-size: 11px;
        font-weight: 800;

        color: #52657b;

        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .vpr-filter-panel .form-control,
    .vpr-filter-panel .form-select {
        min-height: 46px;

        border-color: #d8dee8;
        border-radius: 9px;

        background-color: #ffffff;

        box-shadow: none;
    }

    .vpr-filter-panel .form-control:focus,
    .vpr-filter-panel .form-select:focus {
        border-color: #696cff;

        box-shadow:
            0 0 0 3px rgba(105, 108, 255, .10);
    }


    /*
    |--------------------------------------------------------------------------
    | RECHERCHE VÉHICULE
    |--------------------------------------------------------------------------
    */

    .vehicle-search-group {
        width: 100%;
    }

    .vehicle-search-group .input-group {
        width: 100%;
    }

    .vehicle-search-group .input-group-text {
        min-width: 46px;

        display: flex;
        align-items: center;
        justify-content: center;

        background: #ffffff;

        border-color: #d8dee8;
        border-radius: 9px 0 0 9px;
    }

    .vehicle-search-group .form-control {
        border-left: 0;

        border-radius: 0 9px 9px 0;
    }


    /*
    |--------------------------------------------------------------------------
    | COMPTEUR VÉHICULE
    |--------------------------------------------------------------------------
    */

    .vehicle-search-info {
        min-height: 18px;

        margin-top: 5px;

        font-size: 11px;

        color: #64748b;
    }

    .vehicle-search-info.no-result {
        color: #dc2626;
    }


    /*
    |--------------------------------------------------------------------------
    | ACTIONS FILTRES
    |--------------------------------------------------------------------------
    */

    .vpr-filter-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;

        gap: 10px;

        margin-top: 16px;
    }

    .vpr-filter-actions .btn {
        min-width: 170px;
        min-height: 46px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 7px;

        border-radius: 9px;

        font-weight: 700;
    }


    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    .vpr-table-wrapper {
        width: 100%;

        overflow-x: auto;

        border: 1px solid #edf0f4;
        border-radius: 11px;
    }

    .vpr-table {
        width: 100%;
        min-width: 1250px;

        margin: 0;
    }

    .vpr-table thead th {
        padding: 13px 14px;

        white-space: nowrap;

        vertical-align: middle;

        background: #e8edf3;

        color: #52657b;

        font-size: 11px;
        font-weight: 800;

        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .vpr-table tbody td {
        padding: 14px;

        vertical-align: middle;

        color: #52657b;

        font-size: 13px;
    }

    .vpr-table tbody tr:hover {
        background: #f8fafc;
    }


    /*
    |--------------------------------------------------------------------------
    | VÉHICULE
    |--------------------------------------------------------------------------
    */

    .vehicle-number {
        font-weight: 800;

        color: #5b6df8;
    }


    /*
    |--------------------------------------------------------------------------
    | PIÈCE
    |--------------------------------------------------------------------------
    */

    .part-name {
        font-weight: 800;

        color: #475569;
    }


    /*
    |--------------------------------------------------------------------------
    | QUANTITÉ
    |--------------------------------------------------------------------------
    */

    .quantity-pill {
        min-width: 86px;

        padding: 5px 9px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        border-radius: 6px;

        color: #475569;
        background: #f1f5f9;

        font-weight: 700;

        white-space: nowrap;
    }


    /*
    |--------------------------------------------------------------------------
    | DATE
    |--------------------------------------------------------------------------
    */

    .date-cell {
        white-space: nowrap;
    }


    /*
    |--------------------------------------------------------------------------
    | STATUT
    |--------------------------------------------------------------------------
    */

    .vpr-status-badge {
        min-width: 92px;

        padding: 6px 9px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        border-radius: 6px;

        font-size: 11px;
        font-weight: 800;

        white-space: nowrap;
    }


    /*
    |--------------------------------------------------------------------------
    | ACTIONS
    |--------------------------------------------------------------------------
    */

    .vpr-actions {
        display: flex;
        justify-content: center;
        align-items: center;

        gap: 7px;

        white-space: nowrap;
    }

    .vpr-actions .btn {
        min-height: 34px;

        padding: 6px 11px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 5px;

        border-radius: 7px;

        font-weight: 700;
    }

    .vpr-actions form {
        margin: 0;
    }


    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */

    .vpr-pagination {
        display: flex;
        justify-content: center;

        margin-top: 22px;
    }

    .vpr-pagination svg,
    .vpr-pagination nav svg {
        width: 18px !important;
        height: 18px !important;

        max-width: 18px !important;
        max-height: 18px !important;
    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1199.98px) {

        .vpr-filter-actions {
            justify-content: stretch;
        }

        .vpr-filter-actions .btn {
            flex: 1;
        }
    }


    @media (max-width: 767.98px) {

        .vpr-index-page {
            padding: 14px 10px 32px;
        }

        .vpr-index-header,
        .vpr-index-body {
            padding: 16px;
        }

        .vpr-index-title-row {
            align-items: stretch;

            flex-direction: column;
        }

        .vpr-new-button {
            width: 100%;
        }

        .vpr-filter-actions {
            flex-direction: column;
        }

        .vpr-filter-actions .btn {
            width: 100%;
        }
    }

    /* ============================================================
   ACTIONS COMPACTES
   ============================================================ */

.vpr-actions-compact {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    flex-wrap: nowrap;
    white-space: nowrap;
}

.vpr-action-form {
    display: inline-flex;
    margin: 0;
    padding: 0;
}

.vpr-action-icon {
    width: 30px;
    height: 30px;
    min-width: 30px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 0;
    margin: 0;

    border: 0;
    border-radius: 7px;

    font-size: 15px;
    line-height: 1;

    text-decoration: none;
    cursor: pointer;

    transition:
        transform .15s ease,
        box-shadow .15s ease,
        opacity .15s ease;
}

.vpr-action-icon i {
    font-size: 16px;
    line-height: 1;
}

.vpr-action-icon:hover {
    transform: translateY(-1px);
    text-decoration: none;
}


/* VOIR */

.vpr-action-view {
    background: #dff6fb;
    color: #27a9c0;
}

.vpr-action-view:hover {
    color: #188da4;
    box-shadow: 0 3px 8px rgba(39, 169, 192, .18);
}


/* MODIFIER */

.vpr-action-edit {
    background: #fff1cf;
    color: #d99418;
}

.vpr-action-edit:hover {
    color: #b8750b;
    box-shadow: 0 3px 8px rgba(217, 148, 24, .18);
}


/* GÉNÉRER BC */

.vpr-action-bc {
    background: #e4f7e9;
    color: #35a85c;
}

.vpr-action-bc:hover {
    color: #278d4a;
    box-shadow: 0 3px 8px rgba(53, 168, 92, .18);
}


/* BC EXISTANT */

.vpr-action-bc-existing {
    width: 34px;
    min-width: 34px;

    background: #ebeaff;
    color: #696cff;
}

.vpr-bc-label {
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .2px;
}

.vpr-action-bc-existing:hover {
    color: #5558dc;
    box-shadow: 0 3px 8px rgba(105, 108, 255, .18);
}


/* SUPPRIMER */

.vpr-action-delete {
    background: #ffe4e6;
    color: #e25560;
}

.vpr-action-delete:hover {
    color: #c93d49;
    box-shadow: 0 3px 8px rgba(226, 85, 96, .18);
}


/* ============================================================
   TABLEAU PLUS COMPACT
   ============================================================ */

.vpr-table th {
    padding: 10px 12px;
}

.vpr-table td {
    padding: 9px 12px;
}


/* ACTIONS : largeur réduite */

.vpr-table th:last-child,
.vpr-table td:last-child {
    width: 150px;
    min-width: 150px;
}


/* Dates un peu plus compactes */

.vpr-table td {
    vertical-align: middle;
}


/* ============================================================
   ÉCRANS PLUS PETITS
   ============================================================ */

@media (max-width: 1400px) {

    .vpr-table th {
        padding-left: 8px;
        padding-right: 8px;
    }

    .vpr-table td {
        padding-left: 8px;
        padding-right: 8px;
    }

    .vpr-action-icon {
        width: 28px;
        height: 28px;
        min-width: 28px;
    }

    .vpr-action-icon i {
        font-size: 15px;
    }

    .vpr-actions-compact {
        gap: 4px;
    }

    .vpr-table th:last-child,
    .vpr-table td:last-child {
        width: 135px;
        min-width: 135px;
    }

    /* ============================================================
   RESPONSIVE — PETITS ÉCRANS / LAPTOP
   ============================================================ */

/*
|--------------------------------------------------------------------------
| TABLEAU
|--------------------------------------------------------------------------
| On force le tableau à utiliser toute la largeur disponible,
| sans largeur minimale excessive.
*/
.vpr-table {
    width: 100% !important;
    min-width: 0 !important;
    table-layout: fixed !important;
}


/*
|--------------------------------------------------------------------------
| CELLULES
|--------------------------------------------------------------------------
*/
.vpr-table th,
.vpr-table td {
    padding: 8px 7px !important;
    vertical-align: middle !important;
    overflow: hidden;
}


/*
|--------------------------------------------------------------------------
| LARGEUR DES COLONNES
|--------------------------------------------------------------------------
*/

/* Véhicule */
.vpr-table th:nth-child(1),
.vpr-table td:nth-child(1) {
    width: 14%;
}

/* Pièce */
.vpr-table th:nth-child(2),
.vpr-table td:nth-child(2) {
    width: 22%;
}

/* Quantité */
.vpr-table th:nth-child(3),
.vpr-table td:nth-child(3) {
    width: 11%;
}

/* Demande */
.vpr-table th:nth-child(4),
.vpr-table td:nth-child(4) {
    width: 14%;
}

/* Commande */
.vpr-table th:nth-child(5),
.vpr-table td:nth-child(5) {
    width: 14%;
}

/* Statut */
.vpr-table th:nth-child(6),
.vpr-table td:nth-child(6) {
    width: 13%;
}

/* Actions */
.vpr-table th:last-child,
.vpr-table td:last-child {
    width: 12%;
    min-width: 0 !important;
}


/*
|--------------------------------------------------------------------------
| TEXTE
|--------------------------------------------------------------------------
*/

.vpr-table th {
    font-size: 11px !important;
    white-space: nowrap;
}

.vpr-table td {
    font-size: 12px !important;
}

.vpr-table .vehicle-number {
    font-size: 12px !important;
}

.vpr-table small {
    font-size: 10px !important;
}


/*
|--------------------------------------------------------------------------
| DATES
|--------------------------------------------------------------------------
*/

.vpr-table td:nth-child(4),
.vpr-table td:nth-child(5) {
    white-space: nowrap;
    font-size: 11px !important;
}


/*
|--------------------------------------------------------------------------
| BADGE STATUT
|--------------------------------------------------------------------------
*/

.vpr-status-badge {
    font-size: 9px !important;
    padding: 5px 7px !important;
    white-space: nowrap;
}


/*
|--------------------------------------------------------------------------
| ACTIONS COMPACTES
|--------------------------------------------------------------------------
*/

.vpr-actions-compact,
.vpr-actions {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 4px !important;
    flex-wrap: nowrap !important;
}

.vpr-action-icon {
    width: 27px !important;
    height: 27px !important;
    min-width: 27px !important;

    padding: 0 !important;

    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;

    border-radius: 6px !important;
}

.vpr-action-icon i {
    font-size: 14px !important;
}


/*
|--------------------------------------------------------------------------
| ÉCRAN <= 1400px
|--------------------------------------------------------------------------
*/

@media (max-width: 1400px) {

    .vpr-table th,
    .vpr-table td {
        padding: 7px 5px !important;
    }

    .vpr-table th {
        font-size: 10px !important;
    }

    .vpr-table td {
        font-size: 11px !important;
    }

    .vpr-table .vehicle-number {
        font-size: 11px !important;
    }

    .vpr-table small {
        font-size: 9px !important;
    }

    .vpr-status-badge {
        font-size: 8px !important;
        padding: 4px 5px !important;
    }

    .vpr-action-icon {
        width: 25px !important;
        height: 25px !important;
        min-width: 25px !important;
    }

    .vpr-action-icon i {
        font-size: 13px !important;
    }
}


/*
|--------------------------------------------------------------------------
| ÉCRAN <= 1200px
|--------------------------------------------------------------------------
*/

@media (max-width: 1200px) {

    .vpr-table th,
    .vpr-table td {
        padding: 6px 4px !important;
    }

    .vpr-table th {
        font-size: 9px !important;
    }

    .vpr-table td {
        font-size: 10px !important;
    }

    /*
    | Pièce un peu plus importante
    */
    .vpr-table th:nth-child(1),
    .vpr-table td:nth-child(1) {
        width: 13%;
    }

    .vpr-table th:nth-child(2),
    .vpr-table td:nth-child(2) {
        width: 23%;
    }

    .vpr-table th:nth-child(3),
    .vpr-table td:nth-child(3) {
        width: 10%;
    }

    .vpr-table th:nth-child(4),
    .vpr-table td:nth-child(4) {
        width: 14%;
    }

    .vpr-table th:nth-child(5),
    .vpr-table td:nth-child(5) {
        width: 14%;
    }

    .vpr-table th:nth-child(6),
    .vpr-table td:nth-child(6) {
        width: 13%;
    }

    .vpr-table th:last-child,
    .vpr-table td:last-child {
        width: 13%;
    }

    .vpr-action-icon {
        width: 24px !important;
        height: 24px !important;
        min-width: 24px !important;
    }
}


/*
|--------------------------------------------------------------------------
| TRÈS PETITS ÉCRANS
|--------------------------------------------------------------------------
| À ce niveau, plutôt que couper des informations,
| on autorise le défilement horizontal uniquement du tableau.
*/
/* ============================================================
   RESPONSIVE FINAL — AUCUN DÉBORDEMENT DE PAGE
   ============================================================ */

.vpr-table-wrapper {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
}

.vpr-table {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    table-layout: fixed !important;
}


/* ============================================================
   LAPTOP / PETIT ÉCRAN
   ============================================================ */

@media (max-width: 1199.98px) {

    .vpr-table {
        width: 100% !important;
        min-width: 0 !important;
        table-layout: fixed !important;
    }

    .vpr-table th,
    .vpr-table td {
        padding: 6px 4px !important;
        font-size: 10px !important;
    }


    /* Véhicule */

    .vpr-table th:nth-child(1),
    .vpr-table td:nth-child(1) {
        width: 13% !important;
    }


    /* Pièce */

    .vpr-table th:nth-child(2),
    .vpr-table td:nth-child(2) {
        width: 22% !important;
    }


    /* Quantité */

    .vpr-table th:nth-child(3),
    .vpr-table td:nth-child(3) {
        width: 10% !important;
    }


    /* Demande */

    .vpr-table th:nth-child(4),
    .vpr-table td:nth-child(4) {
        width: 13% !important;
    }


    /* Commande */

    .vpr-table th:nth-child(5),
    .vpr-table td:nth-child(5) {
        width: 13% !important;
    }


    /* Statut */

    .vpr-table th:nth-child(6),
    .vpr-table td:nth-child(6) {
        width: 15% !important;
    }


    /* Actions */

    .vpr-table th:nth-child(7),
    .vpr-table td:nth-child(7) {
        width: 14% !important;
        min-width: 0 !important;
    }


    /* Dates */

    .vpr-table td:nth-child(4),
    .vpr-table td:nth-child(5) {
        white-space: normal !important;
        line-height: 1.25;
    }


    /* Statut */

    .vpr-status-badge {
        max-width: 100%;
        padding: 4px 5px !important;

        font-size: 8px !important;
        line-height: 1.1;

        white-space: normal !important;
        text-align: center;
    }


    /* Actions */

    .vpr-actions,
    .vpr-actions-compact {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;

        gap: 3px !important;
        flex-wrap: nowrap !important;
    }

    .vpr-action-icon {
        width: 23px !important;
        height: 23px !important;
        min-width: 23px !important;

        padding: 0 !important;
        border-radius: 5px !important;
    }

    .vpr-action-icon i {
        font-size: 12px !important;
    }


    /* Éviter qu'un texte force la largeur du tableau */

    .vpr-table td {
        overflow: hidden;
        overflow-wrap: anywhere;
    }

}


/* ============================================================
   TABLETTE / TRÈS PETIT ÉCRAN
   ============================================================ */

@media (max-width: 900px) {

    .vpr-table-wrapper {
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: hidden !important;
    }

    .vpr-table {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        table-layout: fixed !important;
    }

    .vpr-table th,
    .vpr-table td {
        padding: 5px 3px !important;
        font-size: 9px !important;
    }

    .vpr-table th {
        font-size: 8px !important;
    }

    .vpr-action-icon {
        width: 21px !important;
        height: 21px !important;
        min-width: 21px !important;
    }

    .vpr-action-icon i {
        font-size: 11px !important;
    }
}
}

</style>

{{-- ============================================================= --}}
{{-- DESIGN COMPACT : BOUTONS, EN-TÊTE ET FILTRES                    --}}
{{-- Styles limités à cette page ; les actions métier sont conservées. --}}
{{-- ============================================================= --}}
<style>
    /* Carte sobre : réduire les marges pour laisser plus de place au tableau. */
    .vpr-index-page { padding: 8px 0 24px; }
    .vpr-index-page .vpr-index-inner { max-width: 100%; }
    .vpr-index-page .vpr-index-card {
        border-color: #e6eaf1; border-radius: 12px;
        box-shadow: 0 4px 18px rgba(30,41,59,.04);
    }
    .vpr-index-page .vpr-index-header { padding: 18px 20px; }
    .vpr-index-page .vpr-index-title-row { gap: 12px; }
    .vpr-index-page .vpr-index-title-row h3 {
        font-family: inherit; font-size: 20px; font-weight: 700;
        letter-spacing: -.3px; margin-bottom: 4px; color: #27364b;
    }
    .vpr-index-page .vpr-index-title-row p { font-size: 12px; color: #7b879a; }
    .vpr-index-page .vpr-index-body { padding: 16px 20px 20px; }

    /* Boutons principaux : largeur naturelle et hauteur de 32 px. */
    .vpr-index-page .vpr-new-button,
    .vpr-index-page .vpr-filter-actions .btn {
        width: auto; min-width: 0; min-height: 32px; height: 32px;
        flex: 0 0 auto; padding: 5px 11px;
        font-size: 12px; line-height: 20px; font-weight: 600;
        border-radius: 6px; gap: 5px; white-space: nowrap;
        box-shadow: none; transition: background-color .15s ease, border-color .15s ease;
    }
    .vpr-index-page .vpr-new-button i,
    .vpr-index-page .vpr-filter-actions .btn i { font-size: 15px; }
    .vpr-index-page .vpr-new-button,
    .vpr-index-page .vpr-filter-actions .btn-primary {
        color: #fff; background: #5867db; border-color: #5867db;
    }
    .vpr-index-page .vpr-new-button:hover,
    .vpr-index-page .vpr-filter-actions .btn-primary:hover {
        background: #4655c3; border-color: #4655c3;
    }
    .vpr-index-page .vpr-filter-actions .btn-secondary {
        color: #607087; background: #fff; border: 1px solid #dce2ec;
    }
    .vpr-index-page .vpr-filter-actions .btn-secondary:hover {
        color: #334155; background: #eef2f7; border-color: #c5cedb;
    }

    /* Champs réguliers, libellés discrets et grille sans marges négatives. */
    .vpr-index-page .vpr-filter-panel {
        padding: 14px; margin-bottom: 16px; border-color: #e7ebf2;
        border-radius: 9px; background: #f8fafc;
    }
    .vpr-index-page .vpr-filter-panel > .row {
        display: grid; grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px; width: 100%; margin: 0;
    }
    .vpr-index-page .vpr-filter-panel > .row > div {
        width: auto; min-width: 0; margin: 0; padding: 0;
    }
    .vpr-index-page .vpr-filter-panel .form-label {
        font-size: 11px; font-weight: 600; text-transform: none;
        letter-spacing: 0; margin-bottom: 6px; color: #52627a;
    }
    .vpr-index-page .vpr-filter-panel .form-control,
    .vpr-index-page .vpr-filter-panel .form-select {
        height: 36px; min-height: 36px; font-size: 12px;
        padding-top: 6px; padding-bottom: 6px;
        border-radius: 6px; border-color: #dce3ed;
    }
    .vpr-index-page .vehicle-search-group .input-group { flex-wrap: nowrap; }
    .vpr-index-page .vehicle-search-group .input-group-text {
        min-width: 32px; height: 36px; padding: 6px 8px;
        border-radius: 6px 0 0 6px; border-color: #dce3ed;
    }
    .vpr-index-page .vehicle-search-group .form-control { border-radius: 0 6px 6px 0; min-width: 0; }
    .vpr-index-page .vehicle-search-info { min-height: 0; margin-top: 4px; font-size: 10px; }
    .vpr-index-page .vehicle-search-info:empty { display: none; }
    .vpr-index-page .vpr-filter-actions {
        justify-content: flex-end; gap: 7px; margin-top: 12px;
        padding-top: 10px; border-top: 1px solid #e7ebf2; flex-wrap: wrap;
    }

    /* Deux champs par rangée sur ordinateur étroit et tablette. */
    @media (max-width: 1199.98px) {
        .vpr-index-page .vpr-filter-panel > .row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    /* Sur téléphone, garder des champs lisibles et les boutons côte à côte. */
    @media (max-width: 575.98px) {
        .vpr-index-page { padding: 0 0 16px; }
        .vpr-index-page .vpr-index-header,
        .vpr-index-page .vpr-index-body { padding: 12px; }
        .vpr-index-page .vpr-index-title-row h3 { font-size: 17px; }
        .vpr-index-page .vpr-filter-panel { padding: 12px; }
        .vpr-index-page .vpr-filter-panel > .row { grid-template-columns: minmax(0, 1fr); }
        .vpr-index-page .vpr-new-button,
        .vpr-index-page .vpr-filter-actions .btn { width: auto; min-width: 0; flex: 0 0 auto; }
    }
</style>



<div class="vpr-index-page">

    <div class="vpr-index-inner">


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


        <div class="vpr-index-card">


            {{-- ================================================= --}}
            {{-- HEADER --}}
            {{-- ================================================= --}}

            <div class="vpr-index-header">

                <div class="vpr-index-title-row">

                    <div>

                        <h3>

                            {{ $pageTitle }}

                        </h3>

                        <p>

                            {{ $pageDescription }}

                        </p>

                    </div>


                    <a
                        href="{{
                            route(
                                'vehicle-part-requests.create'
                            )
                        }}"
                        class="
                            btn
                            btn-primary
                            vpr-new-button
                        "
                    >

                        <i class="bx bx-plus"></i>

                        Nouvelle commande

                    </a>

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- BODY --}}
            {{-- ================================================= --}}

            <div class="vpr-index-body">


                {{-- ============================================= --}}
                {{-- FILTRES --}}
                {{-- ============================================= --}}

                <form
                    method="GET"
                    action="{{ $currentUrl }}"
                    class="vpr-filter-panel"
                >

                    <div class="row g-3">


                        {{-- ===================================== --}}
                        {{-- RECHERCHE GÉNÉRALE --}}
                        {{-- ===================================== --}}

                        <div class="col-xl-3 col-md-6">

                            <label
                                for="search"
                                class="form-label"
                            >

                                Recherche

                            </label>

                            <input
                                type="text"
                                name="search"
                                id="search"
                                value="{{
                                    request('search')
                                }}"
                                class="form-control"
                                placeholder="Pièce, référence, VIN, immatriculation..."
                            >

                        </div>


                        {{-- ===================================== --}}
                        {{-- RECHERCHE VÉHICULE --}}
                        {{-- ===================================== --}}

                        <div class="col-xl-3 col-md-6">

                            <label
                                for="vehicle_search"
                                class="form-label"
                            >

                                Recherche véhicule

                            </label>


                            <div
                                class="
                                    vehicle-search-group
                                "
                            >

                                <div class="input-group">

                                    <span
                                        class="input-group-text"
                                    >

                                        <i
                                            class="
                                                bx
                                                bx-search
                                            "
                                        ></i>

                                    </span>

                                    <input
                                        type="text"
                                        id="vehicle_search"
                                        class="form-control"
                                        placeholder="VIN, immatriculation, marque, modèle..."
                                        autocomplete="off"
                                    >

                                </div>

                            </div>


                            <div
                                id="vehicle_search_info"
                                class="
                                    vehicle-search-info
                                "
                            ></div>

                        </div>


                        {{-- ===================================== --}}
                        {{-- VÉHICULE --}}
                        {{-- ===================================== --}}

                        <div class="col-xl-3 col-md-6">

                            <label
                                for="vehicle_id"
                                class="form-label"
                            >

                                Véhicule

                            </label>

                            <select
                                name="vehicle_id"
                                id="vehicle_id"
                                class="form-select"
                            >

                                <option value="">

                                    Tous les véhicules

                                </option>


                                @foreach($vehicles as $vehicle)

                                    <option
                                        value="{{ $vehicle->id }}"

                                        data-vin="{{
                                            $vehicle->vin
                                            ?? ''
                                        }}"

                                        data-plate="{{
                                            $vehicle->plate_number
                                            ?? ''
                                        }}"

                                        data-brand="{{
                                            $vehicle->brand
                                            ?? ''
                                        }}"

                                        data-model="{{
                                            $vehicle->model
                                            ?? ''
                                        }}"

                                        data-customer="{{
                                            $vehicle->customer->name
                                            ?? ''
                                        }}"

                                        @selected(
                                            (string)
                                            request(
                                                'vehicle_id'
                                            )
                                            ===
                                            (string)
                                            $vehicle->id
                                        )
                                    >

                                        {{
                                            $vehicle->plate_number
                                            ??
                                            $vehicle->vin
                                            ??
                                            '-'
                                        }}

                                        @if(
                                            $vehicle->brand
                                            ||
                                            $vehicle->model
                                        )

                                            -
                                            {{
                                                $vehicle->brand
                                                ?? ''
                                            }}

                                            {{
                                                $vehicle->model
                                                ?? ''
                                            }}

                                        @endif

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- ===================================== --}}
                        {{-- STATUT --}}
                        {{-- ===================================== --}}

                        <div class="col-xl-3 col-md-6">

                            <label
                                for="status"
                                class="form-label"
                            >

                                Statut

                            </label>

                            <select
                                name="status"
                                id="status"
                                class="form-select"
                            >

                                <option value="">

                                    Tous les statuts

                                </option>


                                @foreach($statuses as $value => $label)

                                    <option
                                        value="{{ $value }}"
                                        @selected(
                                            request('status')
                                            ===
                                            $value
                                        )
                                    >

                                        {{ $label }}

                                    </option>

                                @endforeach

                            </select>

                        </div>

                    </div>


                    {{-- ========================================= --}}
                    {{-- ACTIONS --}}
                    {{-- ========================================= --}}

                    <div class="vpr-filter-actions">

                        <button
                            type="submit"
                            class="
                                btn
                                btn-primary
                            "
                        >

                            <i class="bx bx-search"></i>

                            Rechercher

                        </button>


                        <a
                            href="{{ $currentUrl }}"
                            class="
                                btn
                                btn-secondary
                            "
                        >

                            <i class="bx bx-reset"></i>

                            Réinitialiser

                        </a>

                    </div>

                </form>


                {{-- ============================================= --}}
                {{-- TABLEAU --}}
                {{-- ============================================= --}}

                <div class="vpr-table-wrapper">

                    <table
                        class="
                            table
                            table-hover
                            align-middle
                            vpr-table
                        "
                    >

                        <thead>

                            <tr>

                                <th>
                                    Véhicule
                                </th>

                                <th>
                                    Pièce
                                </th>

                                <th>
                                    Quantité
                                </th>

                                <th>
                                    Demande
                                </th>

                                <th>
                                    Commande
                                </th>


                                @if(
                                    $currentList
                                    ===
                                    'received'
                                )

                                    <th>
                                        Réception
                                    </th>

                                @endif


                                <th>
                                    Statut
                                </th>

                                <th class="text-center">

                                    Actions

                                </th>

                            </tr>

                        </thead>


                        <tbody>

                          @forelse($partRequests as $partRequest)
                                <tr>
                                    {{-- ========================= --}}
                                    {{-- VÉHICULE --}}
                                    {{-- ========================= --}}

                                    <td>

                                        @if(
                                            $partRequest->vehicle
                                        )

                                            <div
                                                class="
                                                    vehicle-number
                                                "
                                            >

                                                {{
                                                    $partRequest
                                                        ->vehicle
                                                        ->plate_number

                                                    ??

                                                    $partRequest
                                                        ->vehicle
                                                        ->vin

                                                    ??

                                                    '-'
                                                }}

                                            </div>


                                            @if(
                                                $partRequest
                                                    ->vehicle
                                                    ->customer
                                            )

                                                <small
                                                    class="
                                                        text-muted
                                                        d-block
                                                    "
                                                >

                                                    Client :

                                                    {{
                                                        $partRequest
                                                            ->vehicle
                                                            ->customer
                                                            ->name
                                                    }}

                                                </small>

                                            @endif


                                            @if(
                                                $partRequest
                                                    ->vehicle
                                                    ->brand
                                                ||
                                                $partRequest
                                                    ->vehicle
                                                    ->model
                                            )

                                                <small
                                                    class="
                                                        text-muted
                                                        d-block
                                                    "
                                                >

                                                    {{
                                                        $partRequest
                                                            ->vehicle
                                                            ->brand
                                                        ?? ''
                                                    }}

                                                    {{
                                                        $partRequest
                                                            ->vehicle
                                                            ->model
                                                        ?? ''
                                                    }}

                                                </small>

                                            @endif

                                        @else

                                            <span
                                                class="
                                                    text-danger
                                                "
                                            >

                                                Véhicule supprimé

                                            </span>

                                        @endif

                                    </td>


                                    {{-- ========================= --}}
                                    {{-- PIÈCE --}}
                                    {{-- ========================= --}}

                                    <td>

                                        <div class="part-name">

                                            {{
                                                $partRequest
                                                    ->part_name
                                            }}

                                        </div>


                                        @if(
                                            $partRequest
                                                ->reference
                                        )

                                            <small
                                                class="
                                                    text-muted
                                                    d-block
                                                "
                                            >

                                                Réf. :

                                                {{
                                                    $partRequest
                                                        ->reference
                                                }}

                                            </small>

                                        @endif


                                        @if(
                                            $partRequest
                                                ->product
                                        )

                                            <small
                                                class="
                                                    text-success
                                                    d-block
                                                "
                                            >

                                                <i
                                                    class="
                                                        bx
                                                        bx-link
                                                        me-1
                                                    "
                                                ></i>

                                                Produit catalogue lié

                                            </small>

                                        @endif

                                    </td>


                                    {{-- ========================= --}}
                                    {{-- QUANTITÉ --}}
                                    {{-- ========================= --}}

                                    <td>

                                        <span
                                            class="
                                                quantity-pill
                                            "
                                        >

                                            {{
                                                number_format(
                                                    (float)
                                                    $partRequest
                                                        ->quantity,
                                                    2,
                                                    ',',
                                                    ' '
                                                )
                                            }}

                                            {{
                                                $partRequest
                                                    ->unit
                                            }}

                                        </span>

                                    </td>


                                    {{-- ========================= --}}
                                    {{-- DATE DEMANDE --}}
                                    {{-- ========================= --}}

                                    <td class="date-cell">

                                        @if(
                                            $partRequest
                                                ->requested_at
                                        )

                                            {{
                                                $partRequest
                                                    ->requested_at
                                                    ->format(
                                                        'd/m/Y H:i'
                                                    )
                                            }}

                                        @else

                                            <span
                                                class="
                                                    text-muted
                                                "
                                            >
                                                -
                                            </span>

                                        @endif

                                    </td>


                                    {{-- ========================= --}}
                                    {{-- DATE COMMANDE --}}
                                    {{-- ========================= --}}

                                    <td class="date-cell">

                                        @if(
                                            $partRequest
                                                ->ordered_at
                                        )

                                            {{
                                                $partRequest
                                                    ->ordered_at
                                                    ->format(
                                                        'd/m/Y H:i'
                                                    )
                                            }}

                                        @else

                                            <span
                                                class="
                                                    text-muted
                                                "
                                            >

                                                Non commandée

                                            </span>

                                        @endif

                                    </td>


                                    {{-- ========================= --}}
                                    {{-- DATE RÉCEPTION --}}
                                    {{-- ========================= --}}

                                    @if(
                                        $currentList
                                        ===
                                        'received'
                                    )

                                        <td class="date-cell">

                                            @if(
                                                $partRequest
                                                    ->received_at
                                            )

                                                {{
                                                    $partRequest
                                                        ->received_at
                                                        ->format(
                                                            'd/m/Y H:i'
                                                        )
                                                }}

                                            @else

                                                <span
                                                    class="
                                                        text-muted
                                                    "
                                                >
                                                    -
                                                </span>

                                            @endif

                                        </td>

                                    @endif


                                    {{-- ========================= --}}
                                    {{-- STATUT --}}
                                    {{-- ========================= --}}

                                    <td>

                                        <span
                                            class="
                                                badge
                                                bg-{{
                                                    $partRequest
                                                        ->status_badge
                                                }}
                                                vpr-status-badge
                                            "
                                        >

                                            {{
                                                $partRequest
                                                    ->status_label
                                            }}

                                        </span>

                                    </td>


                                    {{-- ========================= --}}
                                    {{-- ACTIONS COMPACTES --}}
                                    {{-- ========================= --}}

                                    <td class="text-center">

                                        <div class="vpr-actions-compact">


                                            {{-- VOIR --}}

                                            <a
                                                href="{{
                                                    route(
                                                        'vehicle-part-requests.show',
                                                        $partRequest
                                                    )
                                                }}"
                                                class="
                                                    vpr-action-icon
                                                    vpr-action-view
                                                "
                                                title="Voir"
                                                aria-label="Voir"
                                            >
                                                <i class="bx bx-show"></i>
                                            </a>


                                            {{-- MODIFIER --}}

                                            @if(
                                                in_array(
                                                    auth()->user()->role,
                                                    [
                                                        'admin',
                                                        'chef_magasinier',
                                                        'magasinier'
                                                    ],
                                                    true
                                                )
                                            )

                                                <a
                                                    href="{{
                                                        route(
                                                            'vehicle-part-requests.edit',
                                                            $partRequest
                                                        )
                                                    }}"
                                                    class="
                                                        vpr-action-icon
                                                        vpr-action-edit
                                                    "
                                                    title="Modifier"
                                                    aria-label="Modifier"
                                                >
                                                    <i class="bx bx-edit"></i>
                                                </a>

                                            @endif


                                            {{-- BON DE COMMANDE --}}

                                           @if(
                                                    $partRequest->status
                                                    ===
                                                    \App\Models\VehiclePartRequest::STATUS_ORDERED
                                                )

                                                @php
                                                    $supplierOrderItem =
                                                        $partRequest
                                                            ->latestSupplierOrderItem;

                                                    $supplierOrder =
                                                        $supplierOrderItem
                                                            ?->supplierOrder;
                                                @endphp


                                                @if($supplierOrder)

                                                    {{-- BC EXISTANT --}}

                                                    <a
                                                        href="{{
                                                            route(
                                                                'supplier-orders.show',
                                                                $supplierOrder
                                                            )
                                                        }}"
                                                        class="
                                                            vpr-action-icon
                                                            vpr-action-bc-existing
                                                        "
                                                        title="{{
                                                            'Voir '
                                                            .
                                                            $supplierOrder
                                                                ->order_number
                                                        }}"
                                                        aria-label="Voir le bon de commande"
                                                    >
                                                        <span class="vpr-bc-label">
                                                            BC
                                                        </span>
                                                    </a>

                                                @else

                                                    {{-- GÉNÉRER BC --}}

                                                    <a
                                                        href="{{
                                                            route(
                                                                'supplier-orders.create-from-part-request',
                                                                $partRequest
                                                            )
                                                        }}"
                                                        class="
                                                            vpr-action-icon
                                                            vpr-action-bc
                                                        "
                                                        title="Générer le bon de commande"
                                                        aria-label="Générer le bon de commande"
                                                    >
                                                        <i class="bx bx-file-blank"></i>
                                                    </a>

                                                @endif

                                            @endif


                                            {{-- SUPPRIMER --}}

                                            @if(
                                                auth()->user()->role
                                                ===
                                                'admin'
                                            )

                                                <form
                                                    method="POST"
                                                    action="{{
                                                        route(
                                                            'vehicle-part-requests.destroy',
                                                            $partRequest
                                                        )
                                                    }}"
                                                    class="
                                                        delete-part-request-form
                                                        vpr-action-form
                                                    "
                                                >

                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="
                                                            vpr-action-icon
                                                            vpr-action-delete
                                                        "
                                                        title="Supprimer"
                                                        aria-label="Supprimer"
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
                                        colspan="{{
                                            $currentList
                                            ===
                                            'received'
                                                ? 8
                                                : 7
                                        }}"
                                        class="
                                            text-center
                                            py-5
                                        "
                                    >

                                        <i
                                            class="
                                                bx
                                                bx-package
                                                fs-1
                                                text-muted
                                                d-block
                                                mb-2
                                            "
                                        ></i>

                                        <p
                                            class="
                                                text-muted
                                                mb-0
                                            "
                                        >

                                            Aucune pièce trouvée.

                                        </p>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- ============================================= --}}
                {{-- PAGINATION --}}
                {{-- ============================================= --}}

                @if(
                    method_exists(
                        $partRequests,
                        'links'
                    )
                    &&
                    $partRequests->hasPages()
                )

                    <div class="vpr-pagination">

                        {{
                            $partRequests
                                ->appends(
                                    request()->query()
                                )
                                ->links()
                        }}

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | RECHERCHE VÉHICULE LOCALE
        |--------------------------------------------------------------------------
        */

        const vehicleSearch =
            document.getElementById(
                'vehicle_search'
            );

        const vehicleSelect =
            document.getElementById(
                'vehicle_id'
            );

        const vehicleSearchInfo =
            document.getElementById(
                'vehicle_search_info'
            );


        /*
        |--------------------------------------------------------------------------
        | SAUVEGARDER LES OPTIONS
        |--------------------------------------------------------------------------
        */

        const vehicleOptions =
            vehicleSelect
                ? Array
                    .from(
                        vehicleSelect.options
                    )
                    .slice(1)
                    .map(
                        function (option) {

                            return option.cloneNode(
                                true
                            );
                        }
                    )
                : [];


        /*
        |--------------------------------------------------------------------------
        | NORMALISER TEXTE
        |--------------------------------------------------------------------------
        */

        function normalizeText(value) {

            return String(
                value || ''
            )
            .normalize('NFD')
            .replace(
                /[\u0300-\u036f]/g,
                ''
            )
            .toLowerCase()
            .trim();
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRER VÉHICULES
        |--------------------------------------------------------------------------
        */

        function filterVehicles() {

            if (
                !vehicleSearch
                ||
                !vehicleSelect
            ) {
                return;
            }


            const search =
                normalizeText(
                    vehicleSearch.value
                );


            const currentValue =
                vehicleSelect.value;


            /*
            |--------------------------------------------------------------------------
            | FILTRAGE
            |--------------------------------------------------------------------------
            */

            const results =
                vehicleOptions.filter(
                    function (option) {

                        if (search === '') {

                            return true;
                        }


                        const searchableText =
                            normalizeText(
                                [
                                    option.textContent,
                                    option.dataset.vin,
                                    option.dataset.plate,
                                    option.dataset.brand,
                                    option.dataset.model,
                                    option.dataset.customer
                                ].join(' ')
                            );


                        return searchableText
                            .includes(search);
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | RECONSTRUIRE SELECT
            |--------------------------------------------------------------------------
            */

            vehicleSelect.innerHTML =
                '';


            const defaultOption =
                new Option(
                    'Tous les véhicules',
                    ''
                );

            vehicleSelect.add(
                defaultOption
            );


            results.forEach(
                function (option) {

                    vehicleSelect.add(
                        option.cloneNode(
                            true
                        )
                    );
                }
            );


            /*
            |--------------------------------------------------------------------------
            | RESTAURER VALEUR
            |--------------------------------------------------------------------------
            */

            const exists =
                Array
                    .from(
                        vehicleSelect.options
                    )
                    .some(
                        function (option) {

                            return String(
                                option.value
                            )
                            ===
                            String(
                                currentValue
                            );
                        }
                    );


            if (exists) {

                vehicleSelect.value =
                    currentValue;
            }


            /*
            |--------------------------------------------------------------------------
            | COMPTEUR
            |--------------------------------------------------------------------------
            */

            if (vehicleSearchInfo) {

                vehicleSearchInfo
                    .classList
                    .remove(
                        'no-result'
                    );


                if (search === '') {

                    vehicleSearchInfo.textContent =
                        '';

                    return;
                }


                if (results.length === 0) {

                    vehicleSearchInfo.textContent =
                        'Aucun véhicule trouvé.';

                    vehicleSearchInfo
                        .classList
                        .add(
                            'no-result'
                        );

                } else {

                    vehicleSearchInfo.textContent =
                        results.length
                        +
                        (
                            results.length > 1
                                ? ' véhicules trouvés'
                                : ' véhicule trouvé'
                        );
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ÉVÉNEMENT RECHERCHE
        |--------------------------------------------------------------------------
        */

        if (vehicleSearch) {

            vehicleSearch.addEventListener(
                'input',
                filterVehicles
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SUPPRESSION DEMANDE
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '.delete-part-request-form'
            )
            .forEach(
                function (form) {

                    form.addEventListener(
                        'submit',
                        function (event) {

                            event.preventDefault();


                            /*
                            |--------------------------------------------------------------------------
                            | FALLBACK
                            |--------------------------------------------------------------------------
                            */

                            if (
                                typeof Swal
                                ===
                                'undefined'
                            ) {

                                if (
                                    window.confirm(
                                        'Voulez-vous supprimer cette demande ?'
                                    )
                                ) {

                                    form.submit();
                                }

                                return;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | SWEETALERT
                            |--------------------------------------------------------------------------
                            */

                            Swal.fire({

                                title:
                                    'Supprimer la demande ?',

                                text:
                                    'Cette action est irréversible.',

                                icon:
                                    'warning',

                                showCancelButton:
                                    true,

                                confirmButtonColor:
                                    '#ef4444',

                                cancelButtonColor:
                                    '#6b7280',

                                confirmButtonText:
                                    'Oui, supprimer',

                                cancelButtonText:
                                    'Annuler'

                            }).then(
                                function (result) {

                                    if (
                                        result.isConfirmed
                                    ) {

                                        form.submit();
                                    }
                                }
                            );
                        }
                    );
                }
            );

    }
);

</script>

@endsection
