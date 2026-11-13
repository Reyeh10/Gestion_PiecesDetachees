@extends('layouts.layoutMaster')

@section('title', 'Nouvelle fiche d\'inventaire')

@section('content')

<h4 class="mb-1">Nouvelle fiche d'inventaire</h4>
<p class="text-muted mb-4">Choisissez ce qu'il faut compter : la fiche s'imprime ensuite, triée par rayon et emplacement.</p>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('inventaires.store') }}" class="card">
    @csrf

    <div class="card-body">
        <div class="row g-3">

            <div class="col-md-4">
                <label class="form-label" for="depot_id">Dépôt <span class="text-danger">*</span></label>
                <select id="depot_id" name="depot_id" class="form-select" required>
                    @foreach($depots as $d)
                        <option value="{{ $d->id }}" @selected(old('depot_id') == $d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label" for="compteur">Compté par (facultatif)</label>
                <input type="text" id="compteur" name="compteur" class="form-control" value="{{ old('compteur') }}" maxlength="150" placeholder="Nom de la personne qui compte">
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input type="hidden" name="avec_zero" value="0">
                    <input type="checkbox" id="avec_zero" name="avec_zero" value="1" class="form-check-input" @checked(old('avec_zero'))>
                    <label class="form-check-label" for="avec_zero">Inclure les pièces à 0 en stock</label>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label">Rayons à compter</label>
                <div class="small text-muted mb-2">Seulement les rayons du dépôt choisi. Aucun coché = tout le dépôt.</div>
                <div class="inv-rayons">
                    @foreach($depots as $d)
                        @if($sansRayon->get($d->id))
                            <label class="inv-rayon inv-rayon-sans" data-depot="{{ $d->id }}" title="Pièces de ce dépôt sans rayon">
                                <input type="checkbox" name="rayon_ids[]" value="sans" @checked(old('depot_id') == $d->id && in_array('sans', old('rayon_ids', []), true))>
                                <span>Non défini <small class="text-muted">({{ $sansRayon->get($d->id) }})</small></span>
                            </label>
                        @endif
                    @endforeach
                    @foreach($rayons as $r)
                        <label class="inv-rayon" data-depot="{{ $r->depot_id }}">
                            <input type="checkbox" name="rayon_ids[]" value="{{ $r->id }}" @checked(in_array((string) $r->id, old('rayon_ids', []), true))>
                            <span>{{ $r->name }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="small text-muted mt-2 d-none" id="inv-aucun-rayon">Aucun rayon dans ce dépôt.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="location_id">Ou un seul emplacement (facultatif)</label>
                <select id="location_id" name="location_id" class="form-select" data-ancien="{{ old('location_id') }}">
                    <option value="">— Tous les emplacements des rayons choisis —</option>
                </select>
            </div>

        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('inventaires.index') }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary"><i class="bx bx-list-check"></i> Créer la fiche</button>
        </div>
    </div>
</form>

<style>
    .inv-rayons { display: flex; flex-wrap: wrap; gap: 8px; }

    .inv-rayon {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        padding: 6px 12px;
        border: 1px solid var(--stcd-border, #E6EAED);
        border-radius: 20px;
        font-weight: 600;
        cursor: pointer;
        user-select: none;
    }

    .inv-rayon:has(input:checked) {
        color: var(--stcd-primary, #FE9F43);
        background: rgba(254, 159, 67, .10);
        border-color: var(--stcd-primary, #FE9F43);
    }

    .inv-rayon-sans { border-style: dashed; }
</style>

@push('scripts')
<script>
    // V6 : un rayon appartient à un dépôt ; les emplacements suivent les rayons cochés
    (function () {
        var depot = document.getElementById('depot_id');
        var emplacement = document.getElementById('location_id');
        var rayons = Array.prototype.slice.call(document.querySelectorAll('.inv-rayon'));
        @php
            $jsNomsRayons = $rayons->pluck('name', 'id');
            $jsEmplacements = $emplacements->map(fn ($e) => [$e->id, $e->name, $e->rayon_id])->values();
        @endphp
        var nomsRayons = @json($jsNomsRayons);
        var emplacements = @json($jsEmplacements);
        var choisi = emplacement.dataset.ancien;

        function majEmplacements() {
            var duDepot = {}, coches = {}, auMoinsUn = false;

            rayons.forEach(function (l) {
                var input = l.querySelector('input');
                if (l.dataset.depot !== depot.value || input.value === 'sans') { return; }
                duDepot[input.value] = true;
                if (input.checked) { coches[input.value] = true; auMoinsUn = true; }
            });

            var valeur = emplacement.value || choisi;
            choisi = '';
            emplacement.length = 1;

            emplacements.forEach(function (e) {
                var rayon = String(e[2]);
                if (!duDepot[rayon] || (auMoinsUn && !coches[rayon])) { return; }
                var opt = new Option(e[1] + ' (rayon ' + nomsRayons[rayon] + ')', e[0]);
                opt.selected = String(e[0]) === String(valeur);
                emplacement.add(opt);
            });
        }

        function majRayons() {
            var visibles = 0;

            rayons.forEach(function (l) {
                var duDepot = l.dataset.depot === depot.value;
                l.classList.toggle('d-none', !duDepot);
                l.querySelector('input').disabled = !duDepot;
                if (!duDepot) { l.querySelector('input').checked = false; } else { visibles++; }
            });

            document.getElementById('inv-aucun-rayon').classList.toggle('d-none', visibles > 0);
            majEmplacements();
        }

        depot.addEventListener('change', majRayons);
        rayons.forEach(function (l) { l.querySelector('input').addEventListener('change', majEmplacements); });
        majRayons();
    })();
</script>
@endpush

@endsection
