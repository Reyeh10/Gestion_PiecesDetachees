@extends('layouts/layoutMaster')

@section('title', 'Import Excel - Ajustements inventaire')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <div class="fw-semibold mb-2">
                        Veuillez corriger les erreurs suivantes :
                    </div>

                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h4 class="mb-1">
                            <i class="bx bx-spreadsheet me-2"></i>
                            Import Excel - Ajustement inventaire
                        </h4>

                        <div class="text-muted">
                            Choisissez le dépôt puis importez les quantités réellement comptées.
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a
                            href="{{ route('inventory-adjustments.import.template') }}"
                            class="btn btn-outline-primary"
                        >
                            <i class="bx bx-download me-1"></i>
                            Télécharger le modèle
                        </a>

                        <a
                            href="{{ route('inventory-adjustments.index') }}"
                            class="btn btn-secondary"
                        >
                            <i class="bx bx-arrow-back me-1"></i>
                            Retour
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Principe :</strong>
                        la quantité est toujours ajustée dans le dépôt choisi.
                        Le rayon et l'emplacement ne sont modifiés que lorsqu'ils sont renseignés dans Excel.
                    </div>

                    <form
                        action="{{ route('inventory-adjustments.import.preview') }}"
                        method="POST"
                        enctype="multipart/form-data"
                    >
                        @csrf

                        <div class="row g-4">
                            <div class="col-12">
                                <label
                                    for="depot_id"
                                    class="form-label fw-semibold"
                                >
                                    1. Dépôt
                                    <span class="text-danger">*</span>
                                </label>

                                <select
                                    name="depot_id"
                                    id="depot_id"
                                    class="form-select @error('depot_id') is-invalid @enderror"
                                    required
                                >
                                    <option value="">
                                        -- Sélectionner un dépôt --
                                    </option>

                                    @foreach($depots as $depot)
                                        <option
                                            value="{{ $depot->id }}"
                                            @selected(old('depot_id') == $depot->id)
                                        >
                                            {{ $depot->name }}

                                            @if($depot->code)
                                                ({{ $depot->code }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>

                                @error('depot_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label
                                    for="file"
                                    class="form-label fw-semibold"
                                >
                                    2. Fichier Excel
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="file"
                                    name="file"
                                    id="file"
                                    class="form-control @error('file') is-invalid @enderror"
                                    accept=".xlsx,.xls,.csv"
                                    required
                                >

                                @error('file')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label
                                    for="global_reason"
                                    class="form-label fw-semibold"
                                >
                                    3. Raison globale
                                </label>

                                <textarea
                                    name="global_reason"
                                    id="global_reason"
                                    rows="3"
                                    maxlength="1000"
                                    class="form-control"
                                    placeholder="Ex. Inventaire physique mensuel"
                                >{{ old('global_reason') }}</textarea>

                                <div class="form-text">
                                    Utilisée lorsque la colonne « Raison » de la ligne Excel est vide.
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5>Format attendu du fichier</h5>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Colonne</th>
                                        <th>Champ</th>
                                        <th>Obligatoire</th>
                                        <th>Rôle</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td>A</td>
                                        <td>Référence</td>
                                        <td>
                                            <span class="badge bg-danger">
                                                Oui
                                            </span>
                                        </td>
                                        <td>
                                            Identifie un produit existant.
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>B</td>
                                        <td>Quantité comptée</td>
                                        <td>
                                            <span class="badge bg-danger">
                                                Oui
                                            </span>
                                        </td>
                                        <td>
                                            Nouvelle quantité physique dans le dépôt.
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>C</td>
                                        <td>Rayon</td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                Non
                                            </span>
                                        </td>
                                        <td>
                                            Si renseigné, met à jour le rayon du produit.
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>D</td>
                                        <td>Emplacement</td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                Non
                                            </span>
                                        </td>
                                        <td>
                                            Si renseigné, met à jour l'emplacement.
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>E</td>
                                        <td>Raison</td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                Non
                                            </span>
                                        </td>
                                        <td>
                                            Facultatif si une raison globale est saisie.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-warning mt-4">
                            Si vous renseignez un <strong>Emplacement</strong>,
                            vous devez également renseigner le <strong>Rayon</strong>
                            correspondant.
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                <i class="bx bx-search-alt me-1"></i>
                                Prévisualiser
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
