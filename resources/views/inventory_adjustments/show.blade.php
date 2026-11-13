@extends('layouts.layoutMaster')

@section('title', 'Ajustement n° ' . $ajustement->id)

@section('content')

@php
    $fmtQte = fn ($q) => rtrim(rtrim(number_format((float) $q, 2, ',', ' '), '0'), ',');
    $fmtFdj = fn ($v) => $v === null ? '—' : number_format((float) $v, 0, ',', ' ') . ' FDJ';
    $ecart = $ajustement->ecartQuantite();
    $p = $ajustement->product;
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="mb-1">
            Ajustement n° {{ $ajustement->id }}
            <span class="badge bg-label-{{ $ajustement->couleurStatut() }} align-middle">{{ $ajustement->libelleStatut() }}</span>
        </h4>
        <p class="text-muted mb-0">{{ $ajustement->libelleSource() }} du {{ $ajustement->created_at?->format('d/m/Y à H:i') }}</p>
    </div>
    <a href="{{ route('inventory-adjustments.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i> Retour</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

@if($ajustement->estEnAttente())
    <div class="alert alert-warning">
        <strong>À valider.</strong> Le stock n'a pas encore changé. À la validation, l'écart de
        <strong>{{ $ecart > 0 ? '+' : '' }}{{ $fmtQte($ecart) }}</strong> sera appliqué au stock du dépôt <strong>à ce moment-là</strong>
        (les ventes et transferts faits entre-temps restent comptés).
    </div>
@elseif($ajustement->statut === \App\Models\InventoryAdjustment::REFUSE)
    <div class="alert alert-danger">
        <strong>Refusé</strong> par {{ $ajustement->refuseur?->name ?? '—' }} le {{ $ajustement->refuse_le?->format('d/m/Y à H:i') }} :
        {{ $ajustement->motif_refus }}. Le stock n'a pas changé.
    </div>
@elseif($ajustement->statut === \App\Models\InventoryAdjustment::ANNULE)
    <div class="alert alert-secondary">
        <strong>Annulé</strong> par {{ $ajustement->refuseur?->name ?? '—' }} le {{ $ajustement->refuse_le?->format('d/m/Y à H:i') }}. Le stock n'a pas changé.
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Pièce</div>
                        <div class="fw-semibold">
                            @if($p)
                                <a href="{{ route('products.show', $p) }}">{{ $p->reference }}</a>
                            @else
                                —
                            @endif
                        </div>
                        <div>{{ $p?->designation }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Dépôt / rayon / emplacement</div>
                        <div class="fw-semibold">{{ $ajustement->depot?->name ?? '—' }}</div>
                        <div>{{ $ajustement->rayon?->name ?? '—' }} / {{ $ajustement->location?->name ?? '—' }}</div>
                        @if($ajustement->changeDePlace())
                            <div class="fw-semibold text-primary">
                                → {{ $ajustement->nouveauRayon?->name ?? $ajustement->rayon?->name ?? '—' }} / {{ $ajustement->nouvelEmplacement?->name ?? $ajustement->location?->name ?? '—' }}
                                <span class="badge bg-{{ $ajustement->estValide() ? 'success' : 'warning' }} ms-1">{{ $ajustement->estValide() ? 'nouvelle place appliquée' : 'nouvelle place à la validation' }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="text-muted small">{{ $ajustement->estValide() ? 'Stock avant' : 'Stock à la saisie' }}</div>
                        <div class="fs-5">{{ $fmtQte($ajustement->old_qty) }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">{{ $ajustement->estValide() ? 'Stock après' : 'Prévu après' }}</div>
                        <div class="fs-5">{{ $fmtQte($ajustement->new_qty) }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Écart</div>
                        <div class="fs-5 fw-semibold {{ $ecart > 0 ? 'text-success' : ($ecart < 0 ? 'text-danger' : '') }}">{{ $ecart > 0 ? '+' : '' }}{{ $fmtQte($ecart) }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Valeur au CUMP{{ $ajustement->cump !== null ? ' (' . number_format((float) $ajustement->cump, 0, ',', ' ') . ' / u)' : '' }}</div>
                        <div class="fs-5 fw-semibold {{ $ajustement->valeur_ecart > 0 ? 'text-success' : ($ajustement->valeur_ecart < 0 ? 'text-danger' : '') }}">{{ $fmtFdj($ajustement->valeur_ecart) }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">Motif</div>
                        <div class="fw-semibold">{{ $ajustement->motif ? $ajustement->libelleMotif() : '— (ancien ajustement)' }}</div>
                    </div>
                    <div class="col-md-8">
                        <div class="text-muted small">Raison / remarque</div>
                        <div>{{ $ajustement->reason ?: '—' }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">Saisi par</div>
                        <div>{{ $ajustement->creator?->name ?? $ajustement->approver?->name ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Validé par</div>
                        <div>
                            @if($ajustement->estValide())
                                {{ $ajustement->approver?->name ?? '—' }}
                                <span class="text-muted small">{{ $ajustement->validated_at?->format('d/m/Y H:i') }}</span>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Mouvement de stock</div>
                        <div>{{ ! $ajustement->estValide() ? 'aucun (pas validé)' : (abs($ecart) < 0.01 ? 'aucun (changement de place seulement)' : 'ADJ-' . $ajustement->id) }}</div>
                    </div>

                    @if($ajustement->corrige)
                        <div class="col-12">
                            <div class="alert alert-info mb-0">
                                Cet ajustement corrige l'<a href="{{ route('inventory-adjustments.show', $ajustement->corrige) }}">ajustement n° {{ $ajustement->corrige->id }}</a>.
                            </div>
                        </div>
                    @endif
                    @if($ajustement->corrections->isNotEmpty())
                        <div class="col-12">
                            <div class="text-muted small mb-1">Corrections faites sur cet ajustement</div>
                            <ul class="mb-0">
                                @foreach($ajustement->corrections as $c)
                                    <li>
                                        <a href="{{ route('inventory-adjustments.show', $c) }}">n° {{ $c->id }}</a>
                                        — écart {{ $c->ecartQuantite() > 0 ? '+' : '' }}{{ $fmtQte($c->ecartQuantite()) }}{{ $c->changeDePlace() ? ', nouvelle place' : '' }},
                                        {{ $c->libelleStatut() }}, par {{ $c->creator?->name ?? '—' }} le {{ $c->created_at?->format('d/m/Y') }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @if($ajustement->estEnAttente() && $peutValider)
            <div class="card mb-3">
                <div class="card-header fw-semibold">Validation</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('inventory-adjustments.valider', $ajustement) }}" class="mb-3">
                        @csrf @method('PATCH')
                        <button class="btn btn-success w-100" onclick="return confirm('Valider ? Le stock du dépôt sera mis à jour.')">
                            <i class="bx bx-check me-1"></i> Valider l'ajustement
                        </button>
                    </form>
                    <form method="POST" action="{{ route('inventory-adjustments.refuser', $ajustement) }}">
                        @csrf @method('PATCH')
                        <label class="form-label">Motif du refus</label>
                        <textarea name="motif_refus" rows="2" class="form-control mb-2" maxlength="500" required placeholder="Ex. : recompter, la quantité semble fausse"></textarea>
                        <button class="btn btn-outline-danger w-100"><i class="bx bx-x me-1"></i> Refuser</button>
                    </form>
                </div>
            </div>
        @endif

        @if($peutAnnuler)
            <form method="POST" action="{{ route('inventory-adjustments.annuler', $ajustement) }}" class="mb-3">
                @csrf @method('PATCH')
                <button class="btn btn-outline-secondary w-100" onclick="return confirm('Annuler cet ajustement ? Le stock ne changera pas.')">
                    <i class="bx bx-undo me-1"></i> Annuler l'ajustement (pas encore validé)
                </button>
            </form>
        @endif

        @if($peutCorriger)
            <div class="card">
                <div class="card-header fw-semibold">Corriger une erreur</div>
                <div class="card-body">
                    <p class="small text-muted">Un ajustement validé ne se modifie pas. Indiquez ce qui aurait dû être enregistré : un contre-ajustement de la différence sera créé (à valider), et celui-ci reste dans l'historique.</p>
                    <form method="POST" action="{{ route('inventory-adjustments.corriger', $ajustement) }}" id="form-corriger">
                        @csrf
                        <label class="form-label">Quantité correcte (au lieu de {{ $fmtQte($ajustement->new_qty) }})</label>
                        <input type="number" step="0.01" min="0" name="qte_correcte" id="corr-qte" class="form-control mb-1" value="{{ old('qte_correcte') }}">
                        <div class="text-center small text-muted mb-1">ou</div>
                        <label class="form-label">Écart correct (au lieu de {{ $ecart > 0 ? '+' : '' }}{{ $fmtQte($ecart) }})</label>
                        <input type="number" step="0.01" name="ecart_correct" id="corr-ecart" class="form-control mb-2" value="{{ old('ecart_correct') }}" placeholder="+/−">

                        <label class="form-label">Bonne place (facultatif)</label>
                        <select name="nouveau_rayon_id" id="corr-rayon" class="form-select mb-1">
                            <option value="">Rayon : inchangé</option>
                            @foreach($rayons as $r)
                                <option value="{{ $r->id }}" @selected(old('nouveau_rayon_id') == $r->id)>{{ $r->name }}</option>
                            @endforeach
                        </select>
                        @php
                            // V6 : rayon actuel de la pièce dans ce dépôt ; la liste des emplacements suit le rayon choisi
                            $rayonActuel = \App\Models\ProductDepotStock::query()->where('product_id', $ajustement->product_id)
                                ->where('depot_id', $ajustement->depot_id)->value('rayon_id');
                            $rayonListe = old('nouveau_rayon_id') ?: $rayonActuel;
                        @endphp
                        <select name="nouvel_emplacement_id" id="corr-emplacement" class="form-select mb-2" data-rayon-actuel="{{ $rayonActuel }}">
                            <option value="">{{ old('nouveau_rayon_id') && old('nouveau_rayon_id') != $rayonActuel ? 'Emplacement : non défini' : 'Emplacement : inchangé' }}</option>
                            @foreach($emplacementsParRayon[$rayonListe] ?? [] as [$idEmpl, $nomEmpl])
                                <option value="{{ $idEmpl }}" @selected(old('nouvel_emplacement_id') == $idEmpl)>{{ $nomEmpl }}</option>
                            @endforeach
                        </select>
                        <div class="small text-muted mb-2">Seulement les rayons du dépôt {{ $ajustement->depot?->name }} et les emplacements du rayon choisi.</div>

                        <label class="form-label">Remarque</label>
                        <input type="text" name="remarque" class="form-control mb-2" maxlength="500" value="{{ old('remarque') }}" placeholder="Ex. : 12 saisi au lieu de 21">
                        <button class="btn btn-primary w-100"><i class="bx bx-edit me-1"></i> Créer la correction</button>
                    </form>
                </div>
            </div>

            @push('scripts')
            <script>
                (function () {
                    var qte = document.getElementById('corr-qte'), ecart = document.getElementById('corr-ecart');
                    var rayon = document.getElementById('corr-rayon'), empl = document.getElementById('corr-emplacement');
                    // Une seule des deux cases : quantité OU écart
                    qte.addEventListener('input', function () { if (qte.value !== '') { ecart.value = ''; } });
                    ecart.addEventListener('input', function () { if (ecart.value !== '') { qte.value = ''; } });
                    var parRayon = @json($emplacementsParRayon);
                    rayon.addEventListener('change', function () {
                        var actuel = empl.dataset.rayonActuel;
                        var change = rayon.value !== '' && rayon.value !== actuel;
                        empl.length = 0;
                        empl.add(new Option(change ? 'Emplacement : non défini' : 'Emplacement : inchangé', ''));
                        (parRayon[rayon.value || actuel] || []).forEach(function (e) { empl.add(new Option(e[1], e[0])); });
                    });
                })();
            </script>
            @endpush
        @endif
    </div>
</div>

@endsection
