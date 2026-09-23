@extends('layouts.layoutMaster')

@section('content')

<style>
    .vpr-page {
        width: 100%;
        padding: 22px 18px 45px;
    }

    .vpr-page-inner {
        width: 100%;
        max-width: 1450px;
        margin: 0 auto;
    }

    .vpr-card {
        overflow: hidden;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .08);
    }

    .vpr-card-header {
        padding: 24px 28px;
        background: #ffffff;
        border-bottom: 1px solid #edf0f4;
    }

    .vpr-card-header h3 {
        margin: 0 0 5px;
        font-size: 26px;
        font-weight: 800;
        color: #334155;
    }

    .vpr-card-header p {
        margin: 0;
        color: #94a3b8;
    }

    .vpr-card-body {
        padding: 28px;
    }

    /*
    |--------------------------------------------------------------------------
    | CHOIX DU MODE
    |--------------------------------------------------------------------------
    */

    .vpr-mode-selector {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 25px;
    }

    .vpr-mode-btn {
        min-height: 46px;
        padding: 10px 20px;
        border: 1px solid #dbe1ea;
        border-radius: 10px;
        background: #ffffff;
        color: #475569;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all .2s ease;
    }

    .vpr-mode-btn:hover {
        border-color: #7367f0;
        color: #7367f0;
    }

    .vpr-mode-btn.active {
        background: #7367f0;
        border-color: #7367f0;
        color: #ffffff;
        box-shadow: 0 5px 14px rgba(115, 103, 240, .25);
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT EXCEL
    |--------------------------------------------------------------------------
    */

    .vpr-import-panel {
        display: none;
    }

    .vpr-import-panel.active {
        display: block;
    }

    .vpr-manual-panel {
        display: block;
    }

    .vpr-manual-panel.hidden {
        display: none;
    }

    .vpr-import-box {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 24px;
        background: #f8fafc;
    }

    .vpr-import-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 6px;
        font-size: 18px;
        font-weight: 800;
        color: #334155;
    }

    .vpr-import-title i {
        color: #7367f0;
        font-size: 23px;
    }

    .vpr-import-description {
        color: #64748b;
        margin-bottom: 22px;
    }

    .vpr-import-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .vpr-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 800;
        color: #475569;
        text-transform: uppercase;
    }

    .vpr-required {
        color: #ea5455;
    }

    .vpr-field .form-control,
    .vpr-field .form-select {
        min-height: 48px;
        border-radius: 9px;
    }

    .vpr-help {
        display: block;
        margin-top: 6px;
        color: #94a3b8;
        font-size: 12px;
    }

    /*
    |--------------------------------------------------------------------------
    | MODÈLE EXCEL
    |--------------------------------------------------------------------------
    */

    .vpr-template-box {
        margin-top: 22px;
        padding: 18px;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }

    .vpr-template-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .vpr-template-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: #e8f8ef;
        color: #28c76f;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .vpr-template-info strong {
        display: block;
        color: #334155;
        margin-bottom: 3px;
    }

    .vpr-template-info small {
        color: #94a3b8;
    }

    /*
    |--------------------------------------------------------------------------
    | COLONNES ATTENDUES
    |--------------------------------------------------------------------------
    */

    .vpr-columns {
        margin-top: 20px;
        padding: 18px;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
    }

    .vpr-columns-title {
        font-weight: 800;
        color: #475569;
        margin-bottom: 12px;
    }

    .vpr-column-list {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .vpr-column-badge {
        padding: 6px 10px;
        border-radius: 7px;
        background: #eef2ff;
        color: #5b5bd6;
        font-size: 11px;
        font-weight: 800;
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIONS
    |--------------------------------------------------------------------------
    */

    .vpr-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 26px;
    }

    .vpr-actions .btn {
        min-width: 125px;
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 9px;
        font-weight: 700;
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 767.98px) {

        .vpr-page {
            padding: 14px 10px 32px;
        }

        .vpr-card-header,
        .vpr-card-body {
            padding: 18px 16px;
        }

        .vpr-mode-selector {
            display: grid;
            grid-template-columns: 1fr;
        }

        .vpr-mode-btn {
            width: 100%;
        }

        .vpr-import-grid {
            grid-template-columns: 1fr;
        }

        .vpr-template-box {
            flex-direction: column;
            align-items: stretch;
        }

        .vpr-template-box .btn {
            width: 100%;
        }

        .vpr-actions {
            flex-direction: column-reverse;
        }

        .vpr-actions .btn {
            width: 100%;
        }
    }
</style>


<div class="vpr-page">

    <div class="vpr-page-inner">

        <div class="vpr-card">

            {{-- ========================================================= --}}
            {{-- HEADER --}}
            {{-- ========================================================= --}}

            <div class="vpr-card-header">

                <h3>
                    Nouvelle commande de pièce
                </h3>

                <p>
                    Commander une pièce manuellement ou importer plusieurs
                    pièces depuis un fichier Excel.
                </p>

            </div>


            <div class="vpr-card-body">

                {{-- ===================================================== --}}
                {{-- SUCCÈS --}}
                {{-- ===================================================== --}}

                @if(session('success'))

                    <div
                        class="alert alert-success alert-dismissible fade show"
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


                {{-- ===================================================== --}}
                {{-- ERREUR --}}
                {{-- ===================================================== --}}

                @if(session('error'))

                    <div
                        class="alert alert-danger alert-dismissible fade show"
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


                {{-- ===================================================== --}}
                {{-- ERREURS D'IMPORT --}}
                {{-- ===================================================== --}}

                @if(session('import_errors') && count(session('import_errors')))

                    <div class="alert alert-warning">

                        <strong>
                            Certaines lignes n'ont pas pu être importées :
                        </strong>

                        <ul class="mb-0 mt-2">

                            @foreach(session('import_errors') as $importError)

                                <li>
                                    {{ $importError }}
                                </li>

                            @endforeach

                        </ul>

                    </div>

                @endif


                {{-- ===================================================== --}}
                {{-- VALIDATION --}}
                {{-- ===================================================== --}}

                @if($errors->any())

                    <div
                        class="alert alert-danger alert-dismissible fade show"
                    >

                        <strong>
                            Le formulaire contient des erreurs.
                        </strong>

                        <ul class="mb-0 mt-2">

                            @foreach($errors->all() as $error)

                                <li>
                                    {{ $error }}
                                </li>

                            @endforeach

                        </ul>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Fermer"
                        ></button>

                    </div>

                @endif


                {{-- ===================================================== --}}
                {{-- CHOIX DU MODE --}}
                {{-- ===================================================== --}}

                <div class="vpr-mode-selector">

                    <button
                        type="button"
                        class="vpr-mode-btn active"
                        id="vprManualButton"
                    >
                        <i class="bx bx-edit"></i>

                        Saisie manuelle
                    </button>


                    <button
                        type="button"
                        class="vpr-mode-btn"
                        id="vprExcelButton"
                    >
                        <i class="bx bx-spreadsheet"></i>

                        Importer Excel
                    </button>

                </div>


                {{-- ===================================================== --}}
                {{-- SAISIE MANUELLE --}}
                {{-- ===================================================== --}}

                <div
                    id="vprManualPanel"
                    class="vpr-manual-panel"
                >

                    <form
                        method="POST"
                        action="{{ route('vehicle-part-requests.store') }}"
                        autocomplete="off"
                    >

                        @csrf


                        @include(
                            'vehicle-part-requests._form'
                        )


                        <div class="vpr-actions">

                            <a
                                href="{{ route('vehicle-part-requests.index') }}"
                                class="btn btn-secondary"
                            >

                                <i class="bx bx-arrow-back"></i>

                                Annuler

                            </a>


                            <button
                                type="submit"
                                class="btn btn-primary"
                            >

                                <i class="bx bx-save"></i>

                                Enregistrer

                            </button>

                        </div>

                    </form>

                </div>


                {{-- ===================================================== --}}
                {{-- IMPORT EXCEL --}}
                {{-- ===================================================== --}}

                <div
                    id="vprExcelPanel"
                    class="vpr-import-panel"
                >

                    <div class="vpr-import-box">

                        <div class="vpr-import-title">

                            <i class="bx bx-spreadsheet"></i>

                            Importer une liste de pièces

                        </div>


                        <div class="vpr-import-description">

                            Sélectionnez le véhicule puis choisissez le fichier
                            Excel contenant les pièces à commander.

                        </div>


                        <form
                            method="POST"
                            action="{{ route('vehicle-part-requests.import-excel') }}"
                            enctype="multipart/form-data"
                        >

                            @csrf


                            <div class="vpr-import-grid">

                                {{-- ===================================== --}}
                                {{-- VÉHICULE --}}
                                {{-- ===================================== --}}

                                <div class="vpr-field">

                                    <label for="import_vehicle_id">

                                        Véhicule

                                        <span class="vpr-required">
                                            *
                                        </span>

                                    </label>


                                    <select
                                        name="vehicle_id"
                                        id="import_vehicle_id"
                                        class="form-select"
                                        required
                                    >

                                        <option value="">
                                            Sélectionner un véhicule
                                        </option>


                                        @foreach($vehicles as $vehicle)

                                            <option
                                                value="{{ $vehicle->id }}"
                                                @selected(
                                                    old('vehicle_id')
                                                    == $vehicle->id
                                                )
                                            >

                                                {{ $vehicle->registration_number ?? $vehicle->registration ?? 'Sans immatriculation' }}

                                                @if(!empty($vehicle->vin))
                                                    - VIN :
                                                    {{ $vehicle->vin }}
                                                @endif

                                                @if(
                                                    isset($vehicle->customer)
                                                    &&
                                                    $vehicle->customer
                                                )
                                                    -
                                                    {{ $vehicle->customer->name ?? '' }}
                                                @endif

                                            </option>

                                        @endforeach

                                    </select>


                                    <small class="vpr-help">
                                        Toutes les pièces du fichier seront
                                        rattachées à ce véhicule.
                                    </small>

                                </div>


                                {{-- ===================================== --}}
                                {{-- FICHIER --}}
                                {{-- ===================================== --}}

                                <div class="vpr-field">

                                    <label for="excel_file">

                                        Fichier Excel

                                        <span class="vpr-required">
                                            *
                                        </span>

                                    </label>


                                    <input
                                        type="file"
                                        name="excel_file"
                                        id="excel_file"
                                        class="form-control"
                                        accept=".xlsx,.xls,.csv"
                                        required
                                    >


                                    <small class="vpr-help">
                                        Formats acceptés :
                                        XLSX, XLS et CSV.
                                        Taille maximale : 10 Mo.
                                    </small>

                                </div>

                            </div>


                            {{-- ========================================= --}}
                            {{-- MODÈLE EXCEL --}}
                            {{-- ========================================= --}}

                            <div class="vpr-template-box">

                                <div class="vpr-template-info">

                                    <div class="vpr-template-icon">

                                        <i class="bx bx-file"></i>

                                    </div>


                                    <div>

                                        <strong>
                                            Modèle Excel
                                        </strong>

                                        <small>
                                            Utilisez le modèle pour respecter
                                            les colonnes attendues.
                                        </small>

                                    </div>

                                </div>


                                <a
                                    href="{{ route('vehicle-part-requests.import-template') }}"
                                    class="btn btn-outline-success"
                                >

                                    <i class="bx bx-download"></i>

                                    Télécharger le modèle Excel

                                </a>

                            </div>


                            {{-- ========================================= --}}
                            {{-- COLONNES --}}
                            {{-- ========================================= --}}

                            <div class="vpr-columns">

                                <div class="vpr-columns-title">
                                    Colonnes reconnues dans le fichier :
                                </div>


                                <div class="vpr-column-list">

                                    <span class="vpr-column-badge">
                                        REFERENCE
                                    </span>

                                    <span class="vpr-column-badge">
                                        DESIGNATION
                                    </span>

                                    <span class="vpr-column-badge">
                                        QUANTITE
                                    </span>

                                    <span class="vpr-column-badge">
                                        UNITE
                                    </span>

                                    <span class="vpr-column-badge">
                                        FOURNISSEUR
                                    </span>

                                    <span class="vpr-column-badge">
                                        REFERENCE_FOURNISSEUR
                                    </span>

                                    <span class="vpr-column-badge">
                                        PRIX_ESTIME
                                    </span>

                                    <span class="vpr-column-badge">
                                        DESCRIPTION
                                    </span>

                                    <span class="vpr-column-badge">
                                        NOTES
                                    </span>

                                </div>

                            </div>


                            {{-- ========================================= --}}
                            {{-- ACTIONS --}}
                            {{-- ========================================= --}}

                            <div class="vpr-actions">

                                <a
                                    href="{{ route('vehicle-part-requests.index') }}"
                                    class="btn btn-secondary"
                                >

                                    <i class="bx bx-arrow-back"></i>

                                    Annuler

                                </a>


                                <button
                                    type="submit"
                                    class="btn btn-success"
                                >

                                    <i class="bx bx-upload"></i>

                                    Importer les pièces

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const manualButton =
        document.getElementById('vprManualButton');

    const excelButton =
        document.getElementById('vprExcelButton');

    const manualPanel =
        document.getElementById('vprManualPanel');

    const excelPanel =
        document.getElementById('vprExcelPanel');


    if (
        !manualButton
        || !excelButton
        || !manualPanel
        || !excelPanel
    ) {
        return;
    }


    function showManual()
    {
        manualButton.classList.add('active');
        excelButton.classList.remove('active');

        manualPanel.classList.remove('hidden');
        excelPanel.classList.remove('active');
    }


    function showExcel()
    {
        excelButton.classList.add('active');
        manualButton.classList.remove('active');

        manualPanel.classList.add('hidden');
        excelPanel.classList.add('active');
    }


    manualButton.addEventListener(
        'click',
        showManual
    );


    excelButton.addEventListener(
        'click',
        showExcel
    );


    /*
    |--------------------------------------------------------------------------
    | APRÈS UNE ERREUR D'IMPORT
    |--------------------------------------------------------------------------
    |
    | Si Laravel renvoie une erreur sur excel_file,
    | on rouvre automatiquement l'onglet Excel.
    |
    */

    @if(
        $errors->has('excel_file')
        ||
        session('import_errors')
    )

        showExcel();

    @endif

});
</script>

@endsection
