@extends('layouts.layoutMaster')

@section('title', $bt ? 'Modifier ' . $bt->numero : 'Nouveau bon de transfert')

@section('content')

@php
    $conso = $motif === \App\Models\ExternalBonCommande::MOTIF_CONSO;
@endphp

<h4 class="mb-1">{{ $bt ? 'Modifier le bon de transfert ' . $bt->numero : 'Nouveau bon de transfert (usage interne)' }}</h4>
<p class="text-muted mb-3">
    BT direct, saisi comme un devis : les pièces sortent du stock dès la validation. Prix proposé au catalogue (HT), modifiable.
    @if($bt)
        Le stock n'est corrigé que de la différence.
    @else
        Seule la consommation atelier est envoyée à l'Atelier.
    @endif
</p>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('avertissement'))
    <div class="alert alert-warning">{{ session('avertissement') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ $bt ? route('bt-direct.update', $bt) : route('bt-direct.store') }}" class="card mb-3">
    @csrf
    @if($bt) @method('PUT') @endif

    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Motif <span class="text-danger">*</span></label>
                @if($bt)
                    <div class="form-control bg-light">{{ $motifs[$motif] ?? '—' }}</div>
                @else
                    <select name="motif" id="motif" class="form-select" required>
                        <option value="">— Choisir le motif —</option>
                        @foreach($motifs as $cle => $libelle)
                            <option value="{{ $cle }}" @selected(old('motif', $motif) === $cle)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- Consommation atelier : un équipement du garage --}}
            <div class="col-md-4" id="bloc-equipement" @if(! $conso && ($bt || old('motif', $motif) !== \App\Models\ExternalBonCommande::MOTIF_CONSO)) style="display:none;" @endif>
                <label class="form-label d-flex justify-content-between">
                    <span>Équipement <span class="text-danger">*</span></span>
                    @unless($bt)
                        <button type="submit" form="actualiser-equipements" class="btn btn-link btn-sm p-0" title="Recopier la liste tenue dans l'Atelier"><i class="bx bx-refresh"></i> Actualiser</button>
                    @endunless
                </label>
                @if($bt)
                    <div class="form-control bg-light">{{ $bt->bonCommande->equipement ?: '—' }}</div>
                @else
                    <select name="equipement_atelier_id" class="form-select">
                        <option value="">— Choisir l'équipement —</option>
                        @foreach($equipements as $e)
                            <option value="{{ $e->id }}" @selected(old('equipement_atelier_id', $equipementId) == $e->id)>{{ $e->nom }}</option>
                        @endforeach
                    </select>
                    @if($equipements->isEmpty())
                        <div class="form-text text-danger">Aucun équipement : ajoutez-les dans l'Atelier (Suivi des pièces → Consommation atelier) puis « Actualiser ».</div>
                    @endif
                @endif
            </div>

            {{-- Autres motifs : bénéficiaire en texte libre --}}
            <div class="col-md-4" id="bloc-beneficiaire" @if($conso || (! $bt && old('motif', $motif) === \App\Models\ExternalBonCommande::MOTIF_CONSO)) style="display:none;" @endif>
                <label class="form-label">Bénéficiaire <span class="text-danger">*</span></label>
                <input type="text" name="beneficiaire" maxlength="150" class="form-control"
                       value="{{ old('beneficiaire', $bt?->bonCommande?->beneficiaire) }}" placeholder="Société du groupe, commercial, service…">
            </div>

            <div class="col-md-4">
                <label class="form-label">Demandé par</label>
                <input type="text" name="demande_par" maxlength="150" class="form-control"
                       value="{{ old('demande_par', $bt?->bonCommande?->demande_par) }}" placeholder="Nom de la personne">
            </div>
        </div>

        @include('partials.lignes-pieces-catalogue')

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ $bt ? route('bons-transfert.show', $bt) : url()->previous(route('bons-transfert.index')) }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary"><i class="bx bx-check"></i> {{ $bt ? 'Enregistrer la modification' : 'Créer le bon de transfert' }}</button>
        </div>
    </div>
</form>

@unless($bt)
    <form method="POST" action="{{ route('bt-direct.equipements') }}" id="actualiser-equipements" class="d-none">@csrf</form>
@endunless

@if($bt)
    <form method="POST" action="{{ route('bt-direct.destroy', $bt) }}" class="card border-danger"
          onsubmit="return confirm('Supprimer {{ $bt->numero }} ?{{ $conso ? ' Il sera aussi annulé dans l\'Atelier.' : '' }}');">
        @csrf
        @method('DELETE')
        <div class="card-body d-flex flex-wrap align-items-center gap-3">
            <strong class="text-danger">Supprimer ce bon de transfert</strong>
            <div class="form-check m-0">
                <input type="checkbox" name="remettre_en_stock" value="1" id="remettre" class="form-check-input" checked>
                <label for="remettre" class="form-check-label">Les pièces sont revenues : les remettre en stock</label>
            </div>
            <button type="submit" class="btn btn-outline-danger btn-sm ms-auto"><i class="bx bx-trash"></i> Supprimer</button>
        </div>
    </form>
@endif

@endsection

@if(! $bt)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var motif = document.getElementById('motif');
    var equipement = document.getElementById('bloc-equipement');
    var beneficiaire = document.getElementById('bloc-beneficiaire');

    function afficher() {
        var conso = motif.value === @json(\App\Models\ExternalBonCommande::MOTIF_CONSO);
        equipement.style.display = conso ? '' : 'none';
        beneficiaire.style.display = conso || motif.value === '' ? 'none' : '';
    }
    motif.addEventListener('change', afficher);
    afficher();
});
</script>
@endpush
@endif
