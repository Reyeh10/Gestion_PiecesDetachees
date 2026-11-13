@extends('layouts.layoutMaster')

@section('title', 'Bus leasing / transport')

@section('content')

@php
    $peutGerer = auth()->user()->hasPermission('gerer_bt_bus');
    $qte = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ' '), '0'), ',');
@endphp

<h4 class="mb-1">Bus leasing / transport public</h4>
<p class="text-muted mb-3">
    Les pièces des bus sortent d'ici : chaque sortie fait un bon de transfert, envoyé à l'Atelier pour le récap du mois.
</p>

@foreach(['success' => 'success', 'error' => 'danger', 'avertissement' => 'warning'] as $cle => $classe)
    @if(session($cle))
        <div class="alert alert-{{ $classe }}">{{ session($cle) }}</div>
    @endif
@endforeach
@if(session('erreurs_import'))
    <div class="alert alert-warning">
        <ul class="mb-0">@foreach(session('erreurs_import') as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

{{-- Actions --}}
@if($peutGerer)
    <div class="row g-3 mb-3">
        <div class="col-lg-4">
            <div class="card h-100"><div class="card-body">
                <div class="fw-bold mb-1"><i class="bx bx-bus"></i> Un bus</div>
                <div class="small text-muted mb-3">Choisir le bus puis les pièces, à l'écran.</div>
                <a href="{{ route('bus.create') }}" class="btn btn-primary w-100"><i class="bx bx-plus"></i> Nouvelle sortie</a>
            </div></div>
        </div>
        <div class="col-lg-8">
            <form method="POST" action="{{ route('bus.import.apercu') }}" enctype="multipart/form-data" class="card h-100">
                @csrf
                <div class="card-body">
                    <div class="fw-bold mb-1"><i class="bx bx-spreadsheet"></i> Plusieurs bus — import Excel</div>
                    <div class="small text-muted mb-2">
                        Colonnes Immatriculation / Référence / Quantité / Dépôt (un BT par bus).
                        <a href="{{ route('bus.import.modele') }}">Télécharger le modèle</a>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-5"><input type="file" name="fichier" accept=".xlsx,.xls,.csv" class="form-control" required></div>
                        <div class="col-md-4">
                            <select name="depot_id" class="form-select" title="Dépôt si la colonne Dépôt est vide">
                                @foreach($depots as $d)
                                    <option value="{{ $d->id }}">Dépôt par défaut : {{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-grid"><button type="submit" class="btn btn-dark"><i class="bx bx-show"></i> Aperçu</button></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif

{{-- Liste des bus --}}
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="m-0">Liste des bus <span class="text-muted fs-6">({{ $bus->count() }})</span></h5>
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted">Copie de l'Atelier{{ $synchroniseLe ? ' — ' . \Illuminate\Support\Carbon::parse($synchroniseLe)->format('d/m/Y H:i') : '' }}</span>
                @if($peutGerer)
                    <form method="POST" action="{{ route('bus.synchroniser') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bx bx-refresh"></i> Actualiser</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="btn-group mb-3">
            <a href="{{ route('bus.index', array_filter(['mois' => $mois])) }}" class="btn btn-sm {{ $groupe ? 'btn-outline-primary' : 'btn-primary' }}">Tous</a>
            @foreach(\App\Models\BusAtelier::GROUPES as $cle => $libelle)
                <a href="{{ route('bus.index', array_filter(['groupe' => $cle, 'mois' => $mois])) }}" class="btn btn-sm {{ $groupe === $cle ? 'btn-primary' : 'btn-outline-primary' }}">{{ $libelle }}</a>
            @endforeach
        </div>

        @if($bus->isEmpty())
            <p class="text-muted mb-0">Aucun bus : la liste se tient dans l'Atelier (Bus leasing / Bus transport) ; cliquez sur « Actualiser ».</p>
        @else
            <div class="bus-grille">
                @foreach($bus as $b)
                    <div class="bus-carte">
                        <span class="bus-groupe bus-groupe-{{ $b->groupe }}">{{ $b->groupe_label }}</span>
                        <div class="bus-immat" title="{{ $b->immatriculation }}">{{ $b->immatriculation }}</div>
                        <div class="bus-modele">{{ $b->vehicule ?: '—' }}</div>
                        <div class="bus-compteur">{{ $b->sorties_du_mois }} BT ce mois-ci</div>
                        @if($peutGerer)
                            <a href="{{ route('bus.create', ['bus' => $b->id]) }}" class="btn btn-sm btn-outline-primary mt-auto w-100"><i class="bx bx-plus"></i> Sortie</a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- BT bus --}}
<div class="card">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="m-0">Bons de transfert des bus</h5>
            <form method="GET" class="d-flex gap-2">
                @if($groupe)<input type="hidden" name="groupe" value="{{ $groupe }}">@endif
                <select name="mois" class="form-select form-select-sm" style="min-width:160px;">
                    <option value="">Tous les mois</option>
                    @for($i = 0; $i < 12; $i++)
                        @php $m = now()->startOfMonth()->subMonths($i); @endphp
                        <option value="{{ $m->format('Y-m') }}" @selected($mois === $m->format('Y-m'))>{{ ucfirst($m->translatedFormat('F Y')) }}</option>
                    @endfor
                </select>
                <input type="text" name="search" value="{{ $recherche }}" class="form-control form-control-sm" placeholder="N° BT, immatriculation, pièce…">
                <button class="btn btn-sm btn-primary"><i class="bx bx-search"></i></button>
            </form>
        </div>

        <table class="table align-middle">
            <thead>
                <tr>
                    <th>N° BT</th>
                    <th>Bus</th>
                    <th>Groupe</th>
                    <th>Date</th>
                    <th class="text-end">Pièces</th>
                    <th class="text-end">Total HT</th>
                    <th>Atelier</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bts as $bt)
                    <tr>
                        <td><strong>{{ $bt->numero }}</strong></td>
                        <td>{{ $bt->bonCommande->vehicule_immatriculation }}</td>
                        <td><span class="bus-groupe bus-groupe-{{ $bt->bonCommande->groupe_bus }}">{{ \App\Models\BusAtelier::GROUPES[$bt->bonCommande->groupe_bus] ?? '—' }}</span></td>
                        <td>{{ $bt->created_at->format('d/m/Y') }}</td>
                        <td class="text-end">{{ $bt->lignes_count }}</td>
                        <td class="text-end">{{ number_format((float) $bt->total, 0, ',', ' ') }} FDJ</td>
                        <td>
                            @if($bt->envoye_atelier_at)
                                <span class="bus-statut bus-statut-ok" title="{{ $bt->atelier_bc_numero }}">Envoyé{{ $bt->atelier_bc_numero ? ' — ' . $bt->atelier_bc_numero : '' }}</span>
                            @elseif($bt->envoi_atelier_erreur)
                                <span class="bus-statut bus-statut-ko" title="{{ $bt->envoi_atelier_erreur }}">Échec — à renvoyer</span>
                            @else
                                <span class="bus-statut bus-statut-attente">Non envoyé</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('bons-transfert.show', $bt) }}" class="btn btn-sm btn-primary" title="Voir"><i class="bx bx-show"></i></a>
                            @if($peutGerer)
                                <a href="{{ route('bus.edit', $bt) }}" class="btn btn-sm btn-outline-secondary" title="Modifier"><i class="bx bx-edit"></i></a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Aucun bon de transfert de bus.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $bts->links() }}
    </div>
</div>

<style>
    .bus-grille { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 12px; }
    .bus-carte {
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding: 12px;
        border: 1px solid var(--stcd-border, #E6EAED);
        border-radius: 10px;
    }
    .bus-groupe {
        align-self: flex-start;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.5;
        white-space: nowrap;
    }
    .bus-groupe-bus { color: #b45309; background: #fff3e0; }
    .bus-groupe-transport { color: #0e7490; background: #e0f7fa; }
    .bus-immat {
        overflow: hidden;
        font-size: 15px;
        font-weight: 700;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    .bus-modele { font-size: 12.5px; color: #64748b; }
    .bus-compteur { margin-bottom: 6px; font-size: 12.5px; }
    .bus-statut { padding: 2px 8px; border-radius: 6px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
    .bus-statut-ok { color: #15803d; background: #dcfce7; }
    .bus-statut-ko { color: #b91c1c; background: #fee2e2; }
    .bus-statut-attente { color: #475569; background: #f1f5f9; }
</style>

@endsection
