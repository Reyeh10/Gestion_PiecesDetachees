@extends('layouts.layoutMaster')

@section('title', 'Fiche d\'inventaire ' . $inventaire->numero)

@section('content')

@php
    $qte = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ' '), '0'), ',');
    $peutGerer = auth()->user()->hasPermission('gerer_ajustements');
    $modifiable = $inventaire->estEnCours() && $peutGerer;
    $comptees = $inventaire->lignes->filter->estComptee();
    $ecarts = $comptees->filter(fn ($l) => abs($l->ecart()) >= 0.01);
    $deplacees = $inventaire->lignes->filter->changeDePlace();
    $signalees = $inventaire->lignes->filter(fn ($l) => $l->reference_signalee);
    // V6 : $rayons = rayons du dépôt de la fiche ; $emplacementsParRayon = [rayon_id => [[id, nom], …]]
@endphp

<h4 class="mb-1">Fiche d'inventaire {{ $inventaire->numero }}</h4>
<p class="text-muted mb-3">
    {{ $inventaire->depot->name }} — {{ $inventaire->perimetre }} —
    <span class="badge {{ $inventaire->statut === 'valide' ? 'bg-success' : 'bg-warning' }}">{{ $inventaire->getStatutLabel() }}</span>
    @if($inventaire->valide_le)
        le {{ $inventaire->valide_le->format('d/m/Y H:i') }} par {{ $inventaire->validePar->name ?? '—' }}
    @endif
</p>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

{{-- Actions --}}
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('inventaires.imprimer', $inventaire) }}" target="_blank" class="btn btn-primary"><i class="bx bx-printer"></i> Imprimer la fiche</a>
    <a href="{{ route('inventaires.export', $inventaire) }}" class="btn btn-success"><i class="bx bx-spreadsheet"></i> Exporter en Excel</a>
    <a href="{{ route('inventaires.index') }}" class="btn btn-light"><i class="bx bx-arrow-back"></i> Liste</a>
</div>

@if($modifiable)
    {{-- Import de la fiche Excel remplie --}}
    <form method="POST" action="{{ route('inventaires.import', $inventaire) }}" enctype="multipart/form-data" class="card mb-3 inv-import">
        @csrf
        <div class="card-body py-3">
            <div class="fw-bold mb-1"><i class="bx bx-upload"></i> Importer le comptage depuis Excel</div>
            <div class="small text-muted mb-2">1. « Exporter en Excel » — 2. remplir les colonnes jaunes — 3. choisir le fichier ici puis « Importer ».</div>
            <div class="row g-2 align-items-center">
                <div class="col-md-8">
                    <input type="file" name="fichier" accept=".xlsx,.xls,.csv" class="form-control" required>
                </div>
                <div class="col-md-4 d-grid">
                    <button type="submit" class="btn btn-dark"><i class="bx bx-upload"></i> Importer le fichier</button>
                </div>
            </div>
        </div>
    </form>
@endif

{{-- Résumé --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3"><div class="card"><div class="card-body py-3">
        <div class="text-muted small fw-bold">PIÈCES</div><div class="fs-4 fw-bold">{{ $inventaire->lignes->count() }}</div>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card"><div class="card-body py-3">
        <div class="text-muted small fw-bold">COMPTÉES</div><div class="fs-4 fw-bold">{{ $comptees->count() }}</div>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card"><div class="card-body py-3">
        <div class="text-muted small fw-bold">ÉCARTS</div><div class="fs-4 fw-bold {{ $ecarts->count() ? 'text-danger' : '' }}">{{ $ecarts->count() }}</div>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card"><div class="card-body py-3">
        <div class="text-muted small fw-bold">RÉFÉRENCES SIGNALÉES</div><div class="fs-4 fw-bold {{ $signalees->count() ? 'text-warning' : '' }}">{{ $signalees->count() }}</div>
    </div></div></div>
</div>

{{-- Saisie du comptage --}}
<form method="POST" action="{{ route('inventaires.saisir', $inventaire) }}" class="card mb-3" id="form-comptage">
    @csrf

    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="m-0">Comptage</h5>
            @if($modifiable)
                <div class="d-flex gap-2 align-items-center">
                    <input type="text" name="compteur" value="{{ $inventaire->compteur }}" class="form-control form-control-sm" placeholder="Compté par…" style="max-width:200px;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.querySelectorAll('.inv-conforme').forEach(function (b) { b.click(); })">Tout conforme (pièces non saisies)</button>
                </div>
            @endif
        </div>

        <table class="table align-middle no-cartes inv-table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Rayon / empl.</th>
                    <th>Référence</th>
                    <th>Désignation</th>
                    <th class="text-end">Attendu</th>
                    <th style="width:150px;">Compté</th>
                    <th class="text-end" style="width:110px;">Écart</th>
                    @if($modifiable)
                        <th style="min-width:200px;">Nouvelle place</th>
                        <th>Autres</th>
                    @else
                        <th>Ajustement</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($inventaire->lignes as $l)
                    @php $ecart = $l->ecart(); @endphp
                    <tr class="{{ $ecart !== null && abs($ecart) >= 0.01 ? 'inv-ecart' : '' }}">
                        <td>{{ $l->ordre }}@if($l->hors_liste) <span class="badge bg-info">hors liste</span>@endif</td>
                        <td class="text-nowrap">{{ $l->rayon?->name ?? '—' }} / {{ $l->location?->name ?? '—' }}</td>
                        <td><strong>{{ $l->reference }}</strong></td>
                        <td>{{ $l->designation }}</td>
                        <td class="text-end">{{ $qte($l->qte_attendue) }}</td>
                        <td>
                            @if($modifiable)
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" min="0" name="lignes[{{ $l->id }}][qte_comptee]" value="{{ $l->qte_comptee !== null ? (float) $l->qte_comptee : '' }}" class="form-control inv-qte" data-attendu="{{ (float) $l->qte_attendue }}">
                                    <button type="button" class="btn btn-outline-success inv-conforme" title="Conforme : compté = attendu"
                                            onclick="var c = this.previousElementSibling; if (c.value === '') { c.value = c.dataset.attendu; c.dispatchEvent(new Event('input')); }">✓</button>
                                </div>
                            @else
                                {{ $l->qte_comptee !== null ? $qte($l->qte_comptee) : '—' }}
                            @endif
                        </td>
                        @if($modifiable)
                            <td>
                                <input type="number" step="0.01" name="lignes[{{ $l->id }}][ecart]" value="{{ $ecart !== null ? $ecart : '' }}"
                                       class="form-control form-control-sm text-end fw-bold inv-ecart-saisie" placeholder="+/−" title="Écart : compté − attendu (le compté se calcule tout seul)">
                            </td>
                            <td>
                                <div class="d-grid gap-1">
                                    <select name="lignes[{{ $l->id }}][nouveau_rayon_id]" class="form-select form-select-sm inv-rayon">
                                        <option value="">Rayon : inchangé</option>
                                        @foreach($rayons as $r)
                                            <option value="{{ $r->id }}" @selected($l->nouveau_rayon_id == $r->id)>{{ $r->name }}</option>
                                        @endforeach
                                    </select>
                                    @php $rayonDeLaLigne = $l->nouveau_rayon_id ?: $l->rayon_id; @endphp
                                    <select name="lignes[{{ $l->id }}][nouvel_emplacement_id]" class="form-select form-select-sm inv-emplacement" data-rayon-actuel="{{ $l->rayon_id }}">
                                        <option value="">{{ $l->nouveau_rayon_id && $l->nouveau_rayon_id != $l->rayon_id ? 'Emplacement : non défini' : 'Emplacement : inchangé' }}</option>
                                        @foreach($emplacementsParRayon[$rayonDeLaLigne] ?? [] as [$idEmpl, $nomEmpl])
                                            <option value="{{ $idEmpl }}" @selected($l->nouvel_emplacement_id == $idEmpl)>{{ $nomEmpl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td>
                                <details class="inv-corrections" @if($l->reference_signalee || $l->remarque) open @endif>
                                    <summary>Référence / remarque</summary>
                                    <div class="d-grid gap-1 mt-1">
                                        <input type="text" name="lignes[{{ $l->id }}][reference_signalee]" value="{{ $l->reference_signalee }}" class="form-control form-control-sm" placeholder="Référence correcte (à vérifier)" maxlength="150">
                                        <input type="text" name="lignes[{{ $l->id }}][remarque]" value="{{ $l->remarque }}" class="form-control form-control-sm" placeholder="Remarque" maxlength="500">
                                    </div>
                                </details>
                            </td>
                        @else
                            <td class="text-end fw-bold {{ $ecart > 0 ? 'text-success' : ($ecart < 0 ? 'text-danger' : '') }}">
                                {{ $ecart === null ? '' : ($ecart > 0 ? '+' : '') . $qte($ecart) }}
                                @if($l->changeDePlace())
                                    <div class="small fw-normal text-muted text-nowrap">→ {{ $l->nouveauRayon?->name ?? $l->rayon?->name ?? '—' }} / {{ $l->nouvelEmplacement?->name ?? $l->location?->name ?? '—' }}</div>
                                @endif
                            </td>
                            <td>
                                @if($l->ajustement)
                                    <a href="{{ route('inventory-adjustments.show', $l->ajustement) }}" class="badge bg-{{ $l->ajustement->couleurStatut() }} text-decoration-none">
                                        n° {{ $l->ajustement->id }} — {{ $l->ajustement->libelleStatut() }}
                                    </a>
                                @elseif($inventaire->estEnCours())
                                    —
                                @else
                                    <span class="text-muted small">aucun changement</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($modifiable)
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Enregistrer le comptage</button>
            </div>
        @endif
    </div>
</form>

@if($modifiable)
    {{-- Pièce trouvée hors liste --}}
    <form method="POST" action="{{ route('inventaires.ajouter', $inventaire) }}" class="card mb-3">
        @csrf
        <div class="card-body">
            <div class="fw-bold mb-2"><i class="bx bx-plus-circle"></i> Pièce trouvée qui n'est pas sur la fiche</div>
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Référence <span class="text-danger">*</span></label>
                    <input type="text" name="reference" class="form-control" value="{{ old('reference') }}" required maxlength="150">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quantité comptée <span class="text-danger">*</span></label>
                    <input type="number" name="qte_comptee" class="form-control" value="{{ old('qte_comptee') }}" min="0" step="0.01" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Emplacement où elle a été trouvée <span class="text-danger">*</span></label>
                    <select name="location_id" class="form-select" required>
                        <option value="">— Choisir —</option>
                        @foreach($emplacements->sortBy(fn ($e) => ($e->rayon?->name ?? 'zzz') . '|' . $e->name)->groupBy(fn ($e) => $e->rayon?->name ?? 'Sans rayon') as $nomRayon => $liste)
                            <optgroup label="Rayon {{ $nomRayon }}">
                                @foreach($liste as $e)
                                    <option value="{{ $e->id }}" @selected(old('location_id') == $e->id)>{{ $e->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-secondary"><i class="bx bx-plus"></i> Ajouter</button>
                </div>
            </div>
            <div class="small text-muted mt-2">À la validation, la pièce sera rangée à cet emplacement (et son rayon) dans ce dépôt.</div>
        </div>
    </form>
@endif

{{-- Récapitulatif avant validation --}}
<div class="card mb-3">
    <div class="card-body">
        <h5>Récapitulatif {{ $inventaire->estEnCours() ? 'avant validation' : 'de l\'inventaire' }}</h5>

        @if($ecarts->isEmpty() && $deplacees->isEmpty())
            <p class="text-muted mb-0">Aucun écart de quantité ni changement de rayon / emplacement{{ $comptees->isEmpty() ? ' (rien n\'est encore compté)' : '' }}.</p>
        @else
            <ul class="mb-2">
                @foreach($ecarts as $l)
                    <li>
                        <strong>{{ $l->reference }}</strong> — attendu {{ $qte($l->qte_attendue) }}, compté {{ $qte($l->qte_comptee) }}
                        (<span class="{{ $l->ecart() > 0 ? 'text-success' : 'text-danger' }}">{{ $l->ecart() > 0 ? '+' : '' }}{{ $qte($l->ecart()) }}</span>)
                    </li>
                @endforeach
                @foreach($deplacees as $l)
                    <li>
                        <strong>{{ $l->reference }}</strong> — déplacée :
                        {{ $l->rayon?->name ?? '—' }} / {{ $l->location?->name ?? '—' }}
                        → {{ $l->nouveauRayon?->name ?? $l->rayon?->name ?? '—' }} / {{ $l->nouvelEmplacement?->name ?? $l->location?->name ?? '—' }}
                    </li>
                @endforeach
            </ul>
            @if($inventaire->estEnCours())
                <p class="small text-muted mb-0">
                    À la validation de la fiche, un ajustement d'inventaire « {{ $inventaire->numero }} » est créé pour chaque écart
                    et chaque changement de rayon / emplacement. <strong>Le stock et les places ne changent qu'à la validation de ces
                    ajustements par le chef magasinier / l'admin</strong> (l'écart est alors appliqué au stock du dépôt du moment).
                    Les pièces non comptées ne changent pas.
                </p>
            @endif
        @endif

        @if($signalees->isNotEmpty())
            <div class="alert alert-warning mt-3 mb-0">
                <strong>Références à vérifier</strong> (signalées pendant le comptage, non modifiées automatiquement) :
                <ul class="mb-0">
                    @foreach($signalees as $l)
                        <li>
                            {{ $l->reference }} → <strong>{{ $l->reference_signalee }}</strong>
                            @if(auth()->user()->hasPermission('modifier_produits'))
                                — <a href="{{ route('products.edit', $l->product_id) }}" target="_blank">corriger la fiche produit</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($modifiable && ($comptees->isNotEmpty() || $deplacees->isNotEmpty()))
            <form method="POST" action="{{ route('inventaires.valider', $inventaire) }}" class="mt-3"
                  onsubmit="return confirm('Valider l\'inventaire {{ $inventaire->numero }} ? {{ $ecarts->count() }} écart(s) de stock et {{ $deplacees->count() }} changement(s) de place deviennent des ajustements{{ $peutValiderAjustements ? ' validés tout de suite' : ' à valider par le chef magasinier / l’admin' }}. La fiche ne sera plus modifiable.');">
                @csrf
                <button type="submit" class="btn btn-success"><i class="bx bx-check-double"></i> Valider l'inventaire</button>
            </form>
        @endif

        @if($ajustementsAValider->isNotEmpty())
            <div class="alert alert-warning mt-3 mb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <strong>{{ $ajustementsAValider->count() }} ajustement(s) de cette fiche attendent la validation</strong>
                    du chef magasinier / de l'admin : le stock et les places ne changent pas avant.
                </div>
                @if($peutValiderAjustements)
                    <form method="POST" action="{{ route('inventory-adjustments.valider-lot') }}"
                          onsubmit="return confirm('Valider les {{ $ajustementsAValider->count() }} ajustement(s) de la fiche {{ $inventaire->numero }} ? Le stock et les places du dépôt seront mis à jour.');">
                        @csrf
                        @foreach($ajustementsAValider as $a)
                            <input type="hidden" name="ids[]" value="{{ $a->id }}">
                        @endforeach
                        <button type="submit" class="btn btn-success btn-sm"><i class="bx bx-check-double"></i> Valider les ajustements de cette fiche</button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>

<style>
    .inv-table tr.inv-ecart > td { background: #fff7ed !important; }
    .inv-corrections summary { color: var(--stcd-primary, #FE9F43); font-size: 12.5px; font-weight: 700; cursor: pointer; }
    .inv-corrections { min-width: 170px; }
</style>

@if($modifiable)
    @push('scripts')
    <script>
        // Compté et écart se remplissent l'un l'autre ; la liste des emplacements suit le rayon choisi (V6)
        var emplacementsParRayon = @json($emplacementsParRayon);

        document.querySelectorAll('#form-comptage tbody tr').forEach(function (tr) {
            var qte = tr.querySelector('.inv-qte');
            var ecart = tr.querySelector('.inv-ecart-saisie');
            var rayon = tr.querySelector('.inv-rayon');
            var emplacement = tr.querySelector('.inv-emplacement');
            var arrondi = function (v) { return Math.round(v * 100) / 100; };

            if (qte && ecart) {
                var attendu = parseFloat(qte.dataset.attendu) || 0;
                qte.addEventListener('input', function () {
                    ecart.value = qte.value === '' ? '' : arrondi(parseFloat(qte.value) - attendu);
                });
                ecart.addEventListener('input', function () {
                    if (ecart.value === '' || ecart.value === '-') { return; }
                    qte.value = arrondi(attendu + parseFloat(ecart.value));
                });
            }

            if (rayon && emplacement) {
                rayon.addEventListener('change', function () {
                    var actuel = emplacement.dataset.rayonActuel;
                    var change = rayon.value !== '' && rayon.value !== actuel;
                    var liste = emplacementsParRayon[rayon.value || actuel] || [];

                    emplacement.length = 0;
                    emplacement.add(new Option(change ? 'Emplacement : non défini' : 'Emplacement : inchangé', ''));
                    liste.forEach(function (e) { emplacement.add(new Option(e[1], e[0])); });
                });
            }
        });
    </script>
    @endpush
@endif

@endsection
