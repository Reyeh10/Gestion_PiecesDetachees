@extends('layouts.layoutMaster')

@section('title', 'Nouvelle demande de pièces')

@section('content')

<h4 class="mb-1">Nouvelle demande de pièces</h4>
<p class="text-muted mb-3">
    Enregistrez toutes les pièces demandées ensemble. Dès qu'une pièce a son fournisseur, son bon de commande se crée
    tout seul (un par fournisseur) ; il est ensuite validé par le chef magasinier puis approuvé par l'administrateur.
</p>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('demandes.store') }}" class="card">
    @csrf
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label" for="vehicle_id">Véhicule <span class="text-muted">(facultatif)</span></label>
                <select name="vehicle_id" id="vehicle_id" class="form-select">
                    <option value="">— Pour le stock (aucun véhicule) —</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" @selected(old('vehicle_id', $vehicleId) == $v->id)>
                            {{ $v->plate_number }} — {{ trim($v->brand . ' ' . $v->model) }}{{ $v->customer ? ' — ' . $v->customer->name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="depot_id">Dépôt de réception <span class="text-danger">*</span></label>
                <select name="depot_id" id="depot_id" class="form-select" required>
                    <option value="">— Choisir —</option>
                    @foreach($depots as $d)
                        <option value="{{ $d->id }}" @selected(old('depot_id', $depotId) == $d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label" for="fournisseur-defaut">Fournisseur pour toutes les lignes <span class="text-muted">(facultatif)</span></label>
                <select id="fournisseur-defaut" class="form-select">
                    <option value="">— Chaque ligne a le sien / à chercher —</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="notes">Remarque</label>
                <input type="text" name="notes" id="notes" maxlength="2000" class="form-control" value="{{ old('notes') }}" placeholder="Urgent, demandé par…, client…">
            </div>
        </div>

        @include('demandes._lignes')

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('demandes.index') }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary"><i class="bx bx-check"></i> Enregistrer la demande</button>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#vehicle_id').select2({ width: '100%', placeholder: '— Pour le stock (aucun véhicule) —', allowClear: true });
});
</script>
@endpush
