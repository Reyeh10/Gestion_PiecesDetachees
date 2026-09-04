@extends('layouts/layoutMaster')

@section('title', 'Prévisualisation import inventaire')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">

        <div class="card-header d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <h4 class="mb-1">
                    Prévisualisation de l'inventaire
                </h4>

                <div class="text-muted">
                    Dépôt :
                    <strong>{{ $depot->name }}</strong>

                    @if($depot->code)
                        ({{ $depot->code }})
                    @endif
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 align-items-start">
                <span class="badge bg-success fs-6">
                    {{ $validCount }} valide(s)
                </span>

                <span class="badge {{ $errorCount > 0 ? 'bg-danger' : 'bg-secondary' }} fs-6">
                    {{ $errorCount }} erreur(s)
                </span>
            </div>
        </div>

        <div class="card-body">

            @if($errorCount > 0)
                <div class="alert alert-danger">
                    <strong>Import bloqué.</strong>
                    Corrigez les lignes en erreur puis relancez la prévisualisation.
                </div>
            @else
                <div class="alert alert-success">
                    Toutes les lignes sont valides.
                    Vous pouvez enregistrer les ajustements.
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Ligne</th>
                            <th>Référence</th>
                            <th>Désignation</th>
                            <th>Marque / Modèle</th>

                            <th>Rayon actuel</th>
                            <th>Rayon Excel</th>

                            <th>Emplacement actuel</th>
                            <th>Emplacement Excel</th>

                            <th>Action localisation</th>

                            <th class="text-end">
                                Stock dépôt
                            </th>

                            <th class="text-end">
                                Compté
                            </th>

                            <th class="text-end">
                                Écart
                            </th>

                            <th>Type</th>
                            <th>Raison</th>
                            <th>Diagnostic</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($rows as $row)
                            <tr class="{{ $row['error'] ? 'table-danger' : '' }}">
                                <td>
                                    {{ $row['excel_line'] }}
                                </td>

                                <td class="fw-semibold">
                                    {{ $row['reference'] ?: '-' }}
                                </td>

                                <td>
                                    {{ $row['designation'] ?: '-' }}
                                </td>

                                <td>
                                    {{ $row['brand_name'] ?: '-' }}

                                    @if($row['model_name'])
                                        / {{ $row['model_name'] }}
                                    @endif
                                </td>

                                <td>
                                    {{ $row['current_rayon_name'] ?: 'Non défini' }}
                                </td>

                                <td>
                                    @if($row['excel_rayon_name'])
                                        <span class="badge bg-info">
                                            {{ $row['excel_rayon_name'] }}
                                        </span>
                                    @else
                                        <span class="text-muted">
                                            Non renseigné
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $row['current_location_name'] ?: 'Non défini' }}
                                </td>

                                <td>
                                    @if($row['excel_location_name'])
                                        <span class="badge bg-dark">
                                            {{ $row['excel_location_name'] }}
                                        </span>
                                    @else
                                        <span class="text-muted">
                                            Non renseigné
                                        </span>
                                    @endif
                                </td>

                                <td style="min-width: 250px;">
                                    {{ $row['location_action'] }}
                                </td>

                                <td class="text-end">
                                    {{ number_format((float) $row['old_qty'], 2, ',', ' ') }}
                                </td>

                                <td class="text-end">
                                    @if($row['new_qty'] !== null)
                                        {{ number_format((float) $row['new_qty'], 2, ',', ' ') }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="text-end">
                                    @if($row['difference'] !== null)
                                        @if($row['difference'] > 0)
                                            <span class="text-success fw-semibold">
                                                +{{ number_format((float) $row['difference'], 2, ',', ' ') }}
                                            </span>
                                        @elseif($row['difference'] < 0)
                                            <span class="text-danger fw-semibold">
                                                {{ number_format((float) $row['difference'], 2, ',', ' ') }}
                                            </span>
                                        @else
                                            0,00
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    @if($row['type'] === 'Entrée')
                                        <span class="badge bg-success">
                                            Entrée
                                        </span>
                                    @elseif($row['type'] === 'Sortie')
                                        <span class="badge bg-danger">
                                            Sortie
                                        </span>
                                    @elseif($row['type'] === 'Aucun écart')
                                        <span class="badge bg-secondary">
                                            Aucun écart
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td style="min-width: 220px;">
                                    {{ $row['reason'] ?: '-' }}
                                </td>

                                <td style="min-width: 260px;">
                                    @if($row['error'])
                                        <span class="text-danger fw-semibold">
                                            {{ $row['error'] }}
                                        </span>
                                    @else
                                        <span class="text-success">
                                            Valide
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mt-4">
                <a
                    href="{{ route('inventory-adjustments.import') }}"
                    class="btn btn-secondary"
                >
                    <i class="bx bx-arrow-back me-1"></i>
                    Modifier le dépôt ou le fichier
                </a>

                <form
                    action="{{ route('inventory-adjustments.import.store') }}"
                    method="POST"
                    class="m-0"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="token"
                        value="{{ $token }}"
                    >

                    <button
                        type="submit"
                        class="btn btn-success"
                        @disabled($errorCount > 0)
                        onclick="return confirm('Confirmer l’enregistrement de tous les ajustements ?')"
                    >
                        <i class="bx bx-save me-1"></i>
                        Enregistrer les ajustements
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection

