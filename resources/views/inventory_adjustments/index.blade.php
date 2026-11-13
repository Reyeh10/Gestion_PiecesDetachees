@extends('layouts.layoutMaster')

@section('title', 'Ajustements inventaire')

@section('content')

@php
    use App\Models\InventoryAdjustment;

    $user = auth()->user();
    $peutSaisir = $user->hasPermission('gerer_ajustements');
    $statutActif = request('statut');
    $fmtQte = fn ($q) => rtrim(rtrim(number_format((float) $q, 2, ',', ' '), '0'), ',');
    $fmtFdj = fn ($v) => $v === null ? '—' : number_format((float) $v, 0, ',', ' ');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="mb-1">Ajustements d'inventaire</h4>
        <p class="text-muted mb-0">Un ajustement corrige le stock d'une pièce dans un dépôt. Il ne touche le stock qu'après la validation du chef magasinier ou de l'admin.</p>
    </div>
    @if($peutSaisir)
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('inventory-adjustments.import') }}" class="btn btn-outline-primary"><i class="bx bx-upload me-1"></i> Import Excel</a>
            <a href="{{ route('inventory-adjustments.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Nouvel ajustement</a>
        </div>
    @endif
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

<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ ! $statutActif ? 'active' : '' }}" href="{{ route('inventory-adjustments.index') }}">Tous</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $statutActif === InventoryAdjustment::EN_ATTENTE ? 'active' : '' }}" href="{{ route('inventory-adjustments.index', ['statut' => InventoryAdjustment::EN_ATTENTE]) }}">
            À valider
            @if($nombreAValider)<span class="badge bg-danger ms-1">{{ $nombreAValider }}</span>@endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $statutActif === InventoryAdjustment::REFUSE ? 'active' : '' }}" href="{{ route('inventory-adjustments.index', ['statut' => InventoryAdjustment::REFUSE]) }}">Refusés</a>
    </li>
</ul>

<form method="GET" class="card mb-3">
    <div class="card-body row g-2 align-items-end">
        @if($statutActif)<input type="hidden" name="statut" value="{{ $statutActif }}">@endif
        <div class="col-md-3">
            <label class="form-label">Rechercher</label>
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="N°, référence, désignation, remarque">
        </div>
        <div class="col-md-2">
            <label class="form-label">Dépôt</label>
            <select name="depot_id" class="form-select">
                <option value="">Tous</option>
                @foreach($depots as $depot)
                    <option value="{{ $depot->id }}" @selected((string) request('depot_id') === (string) $depot->id)>{{ $depot->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Motif</label>
            <select name="motif" class="form-select">
                <option value="">Tous</option>
                @foreach(InventoryAdjustment::MOTIFS as $cle => $libelle)
                    <option value="{{ $cle }}" @selected(request('motif') === $cle)>{{ $libelle }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
            <label class="form-label">Origine</label>
            <select name="source" class="form-select">
                <option value="">Toutes</option>
                @foreach(InventoryAdjustment::SOURCES as $cle => $libelle)
                    <option value="{{ $cle }}" @selected(request('source') === $cle)>{{ $libelle }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
            <label class="form-label">Du</label>
            <input type="date" name="du" class="form-control" value="{{ request('du') }}">
        </div>
        <div class="col-md-1">
            <label class="form-label">Au</label>
            <input type="date" name="au" class="form-control" value="{{ request('au') }}">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-primary flex-fill"><i class="bx bx-search"></i> Filtrer</button>
            <a href="{{ route('inventory-adjustments.index', $statutActif ? ['statut' => $statutActif] : []) }}" class="btn btn-light">Effacer</a>
        </div>
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card"><div class="card-body py-3">
            <div class="text-muted small">Entrées validées (valeur au CUMP)</div>
            <div class="fs-5 fw-semibold text-success">+{{ $fmtFdj($totaux->entrees ?? 0) }} FDJ</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-body py-3">
            <div class="text-muted small">Sorties validées (valeur au CUMP)</div>
            <div class="fs-5 fw-semibold text-danger">{{ $fmtFdj($totaux->sorties ?? 0) }} FDJ</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-body py-3">
            <div class="text-muted small">Écart net validé</div>
            <div class="fs-5 fw-semibold">{{ $fmtFdj(($totaux->entrees ?? 0) + ($totaux->sorties ?? 0)) }} FDJ</div>
        </div></div>
    </div>
</div>

<form method="POST" action="{{ route('inventory-adjustments.valider-lot') }}" id="form-lot">
    @csrf
    <div class="card">
        @if($peutValider && $nombreAValider)
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="text-muted">Cochez les ajustements « À valider » puis validez-les en une fois.</span>
                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Valider les ajustements cochés ? Le stock des dépôts sera mis à jour.')">
                    <i class="bx bx-check-double me-1"></i> Valider la sélection
                </button>
            </div>
        @endif
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        @if($peutValider)
                            <th class="text-center" style="width:36px"><input type="checkbox" class="form-check-input" id="tout-cocher" title="Tout cocher"></th>
                        @endif
                        <th>N°</th>
                        <th>Date</th>
                        <th>Pièce</th>
                        <th>Dépôt</th>
                        <th class="text-end">Avant</th>
                        <th class="text-end">Après</th>
                        <th class="text-end">Écart</th>
                        <th class="text-end">Valeur (FDJ)</th>
                        <th>Motif</th>
                        <th>Statut</th>
                        <th>Saisi / validé par</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $a)
                        @php $ecart = $a->ecartQuantite(); @endphp
                        <tr>
                            @if($peutValider)
                                <td class="text-center">
                                    @if($a->estEnAttente())
                                        <input type="checkbox" name="ids[]" value="{{ $a->id }}" class="form-check-input case-lot">
                                    @endif
                                </td>
                            @endif
                            <td class="fw-semibold">
                                {{ $a->id }}
                                @if($a->corrige_id)<div class="small text-muted">corrige n° {{ $a->corrige_id }}</div>@endif
                            </td>
                            <td class="text-nowrap">{{ $a->created_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $a->product?->reference ?? '—' }}</div>
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($a->product?->designation, 40) }}</div>
                            </td>
                            <td>
                                {{ $a->depot?->name ?? '—' }}
                                @if($a->rayon || $a->location)
                                    <div class="small text-muted">{{ $a->rayon?->name }}{{ $a->location ? ' / ' . $a->location->name : '' }}</div>
                                @endif
                                @if($a->changeDePlace())
                                    <div class="small text-primary fw-semibold">→ {{ $a->nouveauRayon?->name ?? $a->rayon?->name }}{{ ($a->nouvelEmplacement ?? $a->location) ? ' / ' . ($a->nouvelEmplacement ?? $a->location)->name : '' }}</div>
                                @endif
                            </td>
                            <td class="text-end">{{ $fmtQte($a->old_qty) }}</td>
                            <td class="text-end">{{ $fmtQte($a->new_qty) }}</td>
                            <td class="text-end fw-semibold {{ $ecart > 0 ? 'text-success' : ($ecart < 0 ? 'text-danger' : '') }}">
                                {{ $ecart > 0 ? '+' : '' }}{{ $fmtQte($ecart) }}
                            </td>
                            <td class="text-end {{ $a->valeur_ecart > 0 ? 'text-success' : ($a->valeur_ecart < 0 ? 'text-danger' : '') }}">
                                {{ $a->valeur_ecart !== null && $a->valeur_ecart > 0 ? '+' : '' }}{{ $fmtFdj($a->valeur_ecart) }}
                            </td>
                            <td>
                                {{ $a->motif ? $a->libelleMotif() : '' }}
                                <div class="small text-muted" title="{{ $a->reason }}">{{ \Illuminate\Support\Str::limit($a->motif ? \Illuminate\Support\Str::after($a->reason, ' — ') : $a->reason, 35) }}</div>
                            </td>
                            <td>
                                <span class="badge bg-label-{{ $a->couleurStatut() }}">{{ $a->libelleStatut() }}</span>
                                <div class="small text-muted">{{ $a->libelleSource() }}</div>
                            </td>
                            <td class="small">
                                {{ $a->creator?->name ?? $a->approver?->name ?? '—' }}
                                @if($a->estValide() && $a->approved_by && (int) $a->approved_by !== (int) $a->created_by)
                                    <div class="text-muted">validé : {{ $a->approver?->name }}</div>
                                @elseif($a->refuseur && ! $a->estValide())
                                    <div class="text-muted">{{ $a->statut === InventoryAdjustment::REFUSE ? 'refusé' : 'annulé' }} : {{ $a->refuseur->name }}</div>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('inventory-adjustments.show', $a) }}" class="btn btn-sm btn-icon btn-outline-secondary" title="Voir"><i class="bx bx-show"></i></a>
                                @if($peutValider && $a->estEnAttente())
                                    <button type="submit" form="valider-{{ $a->id }}" class="btn btn-sm btn-icon btn-success" title="Valider"
                                            onclick="return confirm('Valider l\'ajustement n° {{ $a->id }} ? Le stock du dépôt sera mis à jour.')"><i class="bx bx-check"></i></button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="13" class="text-center text-muted py-4">Aucun ajustement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($adjustments->hasPages())
            <div class="card-footer">{{ $adjustments->links() }}</div>
        @endif
    </div>
</form>

{{-- formulaires de validation unitaire (hors du formulaire de lot) --}}
@if($peutValider)
    @foreach($adjustments as $a)
        @if($a->estEnAttente())
            <form id="valider-{{ $a->id }}" method="POST" action="{{ route('inventory-adjustments.valider', $a) }}" class="d-none">
                @csrf @method('PATCH')
            </form>
        @endif
    @endforeach
@endif

@endsection

@push('scripts')
<script>
    document.getElementById('tout-cocher')?.addEventListener('change', function () {
        document.querySelectorAll('.case-lot').forEach(c => c.checked = this.checked);
    });
</script>
@endpush
