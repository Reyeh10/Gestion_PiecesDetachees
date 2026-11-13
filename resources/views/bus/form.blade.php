@extends('layouts.layoutMaster')

@section('title', $bt ? 'Modifier ' . $bt->numero : 'Sortie de pièces — bus')

@section('content')

<h4 class="mb-1">{{ $bt ? 'Modifier le bon de transfert ' . $bt->numero : 'Sortie de pièces pour un bus' }}</h4>
<p class="text-muted mb-3">
    Prix = prix de vente du catalogue (HT), non modifiable.
    @if($bt)
        Le stock n'est corrigé que de la différence.
        @if($bt->envoye_atelier_at)
            La modification n'est gardée que si l'Atelier l'accepte (refusée si le BT est déjà dans un récap).
        @endif
    @else
        Le bon de transfert est créé et envoyé à l'Atelier dès la validation.
    @endif
</p>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ $bt ? route('bus.update', $bt) : route('bus.store') }}" class="card mb-3" id="form-sortie-bus">
    @csrf
    @if($bt) @method('PUT') @endif

    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Bus <span class="text-danger">*</span></label>
                @if($bt)
                    <div class="form-control bg-light">
                        {{ $bt->bonCommande->vehicule_immatriculation }} — {{ \App\Models\BusAtelier::GROUPES[$bt->bonCommande->groupe_bus] ?? '' }}
                    </div>
                @else
                    <select name="bus_atelier_id" class="form-select" required>
                        <option value="">— Choisir le bus —</option>
                        @foreach($busListe->groupBy('groupe') as $grp => $liste)
                            <optgroup label="{{ \App\Models\BusAtelier::GROUPES[$grp] ?? $grp }}">
                                @foreach($liste as $b)
                                    <option value="{{ $b->id }}" @selected(old('bus_atelier_id', $busId) == $b->id)>{{ $b->immatriculation }}{{ $b->vehicule ? ' — ' . $b->vehicule : '' }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

        @include('partials.lignes-pieces-catalogue')

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ $bt ? route('bons-transfert.show', $bt) : route('bus.index') }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary"><i class="bx bx-check"></i> {{ $bt ? 'Enregistrer la modification' : 'Créer le bon de transfert' }}</button>
        </div>
    </div>
</form>

@if($bt)
    <form method="POST" action="{{ route('bus.destroy', $bt) }}" class="card border-danger"
          onsubmit="return confirm('Supprimer {{ $bt->numero }} ? Il sera aussi annulé dans l\'Atelier (refusé s\'il est déjà dans un récap).');">
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
