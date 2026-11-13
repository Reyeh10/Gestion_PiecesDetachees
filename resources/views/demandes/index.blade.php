@extends('layouts.layoutMaster')

@section('title', 'Demandes de pièces')

@section('content')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="mb-1">Demandes de pièces</h4>
        <p class="text-muted mb-0">Une demande regroupe les pièces demandées ensemble ; chaque fournisseur a son bon de commande, créé automatiquement.</p>
    </div>
    @if(auth()->user()?->hasPermission('gerer_demandes_pieces'))
        <a href="{{ route('demandes.create') }}" class="btn btn-primary"><i class="bx bx-plus"></i> Nouvelle demande</a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<ul class="nav nav-tabs mb-3 flex-nowrap overflow-auto text-nowrap">
    <li class="nav-item">
        <a class="nav-link {{ $onglet === 'toutes' ? 'active' : '' }}" href="{{ route('demandes.index') }}">Toutes</a>
    </li>
    @foreach(\App\Models\Demande::ETAPES as $cle => [$libelle, $couleur])
        <li class="nav-item">
            <a class="nav-link {{ $onglet === $cle ? 'active' : '' }}" href="{{ route('demandes.index', ['onglet' => $cle]) }}">
                {{ $cle === 'non-trouvees' ? 'Non trouvées / annulées' : ($cle === 'recues' ? 'Reçues' : ($cle === 'commandees' ? 'Commandées' : $libelle)) }}
                <span class="badge bg-label-{{ $couleur }} ms-1">{{ $comptes[$cle] ?? 0 }}</span>
            </a>
        </li>
    @endforeach
    <li class="nav-item">
        <a class="nav-link {{ $onglet === 'a-completer' ? 'active' : '' }}" href="{{ route('demandes.index', ['onglet' => 'a-completer']) }}">
            Pièces à compléter
            @if($nombreACompleter)<span class="badge bg-danger ms-1">{{ $nombreACompleter }}</span>@endif
        </a>
    </li>
</ul>

<form method="GET" class="card mb-3">
    <div class="card-body row g-2 align-items-end">
        @if($onglet !== 'toutes')<input type="hidden" name="onglet" value="{{ $onglet }}">@endif
        <div class="col-md-5">
            <label class="form-label">Rechercher</label>
            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                   placeholder="{{ $onglet !== 'a-completer' ? 'N° de demande, de BC, pièce, plaque…' : 'Référence ou désignation' }}">
        </div>
        @if($onglet !== 'a-completer')
            <div class="col-md-3">
                <label class="form-label">Origine</label>
                <select name="source" class="form-select">
                    <option value="">Toutes</option>
                    @foreach(\App\Models\Demande::SOURCES as $cle => $libelle)
                        <option value="{{ $cle }}" @selected(request('source') === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-primary w-100">Filtrer</button>
            <a href="{{ route('demandes.index', $onglet !== 'toutes' ? ['onglet' => $onglet] : []) }}" class="btn btn-outline-secondary" title="Effacer">&times;</a>
        </div>
    </div>
</form>

@if($onglet !== 'a-completer')
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr><th>N°</th><th>Date</th><th>Destination</th><th>Pièces</th><th>Bons de commande</th><th>Étape</th><th></th></tr>
                </thead>
                <tbody>
                @forelse($demandes as $d)
                    <tr>
                        <td class="fw-semibold">{{ $d->numero }}<div class="small text-muted">{{ \App\Models\Demande::SOURCES[$d->source] ?? $d->source }}</div></td>
                        <td>{{ $d->created_at->format('d/m/Y') }}<div class="small text-muted">{{ $d->creator?->name }}</div></td>
                        <td>
                            {{ $d->vehicle ? $d->vehicle->plate_number : 'Stock' }}
                            <div class="small text-muted">→ {{ $d->depot?->name ?? 'dépôt à indiquer' }}</div>
                        </td>
                        <td class="text-nowrap">
                            @php [$recues, $total] = $d->avancement(); @endphp
                            {{ $d->pieces_count }} pièce(s)
                            <div class="small {{ $total && $recues === $total ? 'text-success' : 'text-muted' }}">{{ $recues }}/{{ $total }} reçue(s)</div>
                        </td>
                        <td>
                            @forelse($d->bonsCommande as $bc)
                                <div class="small">
                                    <a href="{{ route('supplier-orders.show', $bc) }}">{{ $bc->order_number }}</a>
                                    <span class="badge bg-label-{{ $bc->couleurStatut() }}">{{ $bc->libelleStatut() }}</span>
                                </div>
                            @empty
                                <span class="text-muted small">Aucun (fournisseur à indiquer)</span>
                            @endforelse
                        </td>
                        <td>
                            <span class="badge bg-label-{{ $d->couleurEtape() }}">{{ $d->libelleEtape() }}</span>
                        </td>
                        <td class="text-end"><a href="{{ route('demandes.show', $d) }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucune demande{{ $onglet !== 'toutes' ? ' à cette étape' : '' }}.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $demandes->links() }}</div>
@else
    <div class="card">
        <div class="card-header text-muted small">
            Pièces en recherche sans fournisseur (ou sans référence ni fiche catalogue) : indiquez le fournisseur,
            le bon de commande se crée tout seul.
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 no-cartes">
                <thead>
                    <tr><th>Pièce</th><th>Demande</th><th>Depuis</th><th>Manque</th><th style="min-width:330px;">Fournisseur / prix indicatif</th></tr>
                </thead>
                <tbody>
                @forelse($pieces as $p)
                    <tr>
                        <td>
                            <a href="{{ route('vehicle-part-requests.show', $p) }}" class="fw-semibold">{{ $p->part_name }}</a>
                            <div class="small text-muted">{{ $p->reference ?: 'Sans référence' }} · {{ rtrim(rtrim(number_format((float) $p->quantity, 2, ',', ' '), '0'), ',') }} {{ $p->unit }}</div>
                        </td>
                        <td>
                            @if($p->demande)
                                <a href="{{ route('demandes.show', $p->demande) }}">{{ $p->demande->numero }}</a>
                            @else
                                <span class="text-muted small">Ancienne saisie</span>
                            @endif
                            <div class="small text-muted">{{ $p->vehicle?->plate_number ?? $p->depot?->name }}</div>
                        </td>
                        <td>{{ optional($p->requested_at ?? $p->created_at)->format('d/m/Y') }}</td>
                        <td class="small">
                            @if(! $p->supplier_id)<span class="badge bg-label-danger">Fournisseur</span>@endif
                            @if(! $p->product_id && ! $p->reference)<span class="badge bg-label-warning">Référence</span>@endif
                        </td>
                        <td>
                            @if(auth()->user()?->hasPermission('gerer_demandes_pieces'))
                                <form method="POST" action="{{ route('demandes.pieces.fournisseur', $p) }}" class="d-flex gap-1">
                                    @csrf @method('PATCH')
                                    <select name="supplier_id" class="form-select form-select-sm" required>
                                        <option value="">— Fournisseur —</option>
                                        @foreach($suppliers as $s)
                                            <option value="{{ $s->id }}" @selected($p->supplier_id == $s->id)>{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="prix" step="0.01" min="0" class="form-control form-control-sm text-end" style="width:110px;"
                                           value="{{ $p->purchase_price ?? $p->estimated_price }}" placeholder="Prix">
                                    <button class="btn btn-sm btn-primary"><i class="bx bx-check"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Rien à compléter.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $pieces->links() }}</div>
@endif

@endsection
