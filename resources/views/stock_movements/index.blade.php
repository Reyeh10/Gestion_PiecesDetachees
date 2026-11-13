@extends('layouts.layoutMaster')

@section('title', 'Mouvements de stock')

@section('content')

{{-- V6 : page des mouvements refaite (onglets, filtres avec libellés, quantité signée,
     dépôt séparé de la source, heure et auteur). Styles limités à cette page. --}}
@php
    $route = request()->route()?->getName();
    $ongletFixe = $route === 'stock-movements.entries' ? 'in' : ($route === 'stock-movements.exits' ? 'out' : null);
    $onglets = [
        'stock-movements.index' => ['Tous', 'bx-transfer-alt'],
        'stock-movements.entries' => ['Entrées', 'bx-log-in-circle'],
        'stock-movements.exits' => ['Sorties', 'bx-log-out-circle'],
    ];
    $filtresActifs = collect(['reference', 'designation', 'date', 'type'])->filter(fn ($f) => request()->filled($f))->count();
@endphp

<style>
    #mv-page .mv-onglets { display: inline-flex; gap: 4px; padding: 4px; margin-bottom: 16px; background: var(--stcd-surface); border: 1px solid var(--stcd-border); border-radius: 10px; }
    #mv-page .mv-onglets a { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 7px; font-weight: 600; font-size: 13px; color: var(--stcd-muted); text-decoration: none; }
    #mv-page .mv-onglets a:hover { color: var(--stcd-text); background: var(--stcd-surface-2); }
    #mv-page .mv-onglets a.active { color: #fff; background: var(--stcd-primary); }

    #mv-page .mv-filtres { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1.2fr) minmax(140px, .7fr) minmax(120px, .6fr) auto; gap: 12px; align-items: end; }
    #mv-page .mv-filtres.sans-type { grid-template-columns: minmax(0, 1.2fr) minmax(0, 1.2fr) minmax(140px, .7fr) auto; }
    #mv-page .mv-filtres .form-label { margin-bottom: 4px; font-size: 12px; font-weight: 600; color: var(--stcd-muted); }
    #mv-page .mv-filtres .input-group-text { color: var(--stcd-muted); }
    #mv-page .mv-boutons { display: flex; gap: 8px; }

    #mv-page .mv-table { margin: 0; }
    #mv-page .mv-table th { font-size: 12px; white-space: nowrap; }
    #mv-page .mv-table td { font-size: 13px; vertical-align: middle; }
    #mv-page .mv-ref { font-weight: 600; color: var(--stcd-text); }
    #mv-page .mv-designation { max-width: 280px; }
    #mv-page .mv-doc { font-weight: 600; color: var(--stcd-primary); white-space: nowrap; }
    #mv-page .mv-depot { display: inline-flex; align-items: center; gap: 3px; margin-top: 3px; font-size: 12px; color: var(--stcd-muted); }
    #mv-page .mv-qte { font-weight: 700; white-space: nowrap; text-align: right; }
    #mv-page .mv-qte.entree { color: #16a34a; }
    #mv-page .mv-qte.sortie { color: #dc2626; }
    #mv-page .mv-date { white-space: nowrap; }
    #mv-page .mv-petit { font-size: 12px; color: var(--stcd-muted); }
    #mv-page .mv-actions { display: flex; justify-content: flex-end; gap: 6px; }
    #mv-page .mv-actions .btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; padding: 0; border-radius: 8px; }
    #mv-page .mv-actions form { margin: 0; }
    #mv-page .mv-vide { padding: 48px 16px; text-align: center; color: var(--stcd-muted); }
    #mv-page .mv-vide i { display: block; font-size: 36px; margin-bottom: 6px; }

    #mv-page .mv-pagination { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px 20px; padding: 14px 20px; border-top: 1px solid var(--stcd-border-light); }
    #mv-page .mv-par-page { display: flex; align-items: center; gap: 8px; margin: 0; font-size: 13px; }
    #mv-page .mv-par-page label { margin: 0; }
    #mv-page .mv-par-page .form-select { width: auto; min-width: 76px; }
    #mv-page .mv-par-page-info { color: var(--stcd-muted); font-size: 13px; }
    #mv-page .mv-pagination-liens { margin-left: auto; }
    #mv-page .mv-pagination-liens nav p { display: none; }
    #mv-page .mv-pagination-liens svg { width: 18px; height: 18px; }
    #mv-page .mv-pagination-liens .pagination { margin: 0; }

    @media (max-width: 991.98px) {
        #mv-page .mv-filtres,
        #mv-page .mv-filtres.sans-type { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        #mv-page .mv-boutons { grid-column: 1 / -1; }
    }

    /* Téléphone : chaque mouvement devient une fiche lisible. */
    @media (max-width: 640px) {
        #mv-page .mv-onglets { display: flex; }
        #mv-page .mv-onglets a { flex: 1; justify-content: center; }
        #mv-page .mv-filtres,
        #mv-page .mv-filtres.sans-type { grid-template-columns: minmax(0, 1fr); }
        #mv-page .mv-table thead { display: none; }
        #mv-page .mv-table, #mv-page .mv-table tbody, #mv-page .mv-table tr { display: block; }
        #mv-page .mv-table tr { padding: 10px 14px; border-bottom: 1px solid var(--stcd-border); }
        #mv-page .mv-table td { display: grid; grid-template-columns: 100px minmax(0, 1fr); gap: 8px; padding: 4px 0; border: 0; text-align: left; }
        #mv-page .mv-table td::before { content: attr(data-label); font-size: 12px; font-weight: 600; color: var(--stcd-muted); }
        #mv-page .mv-table td[colspan] { display: block; }
        #mv-page .mv-table td[colspan]::before { content: none; }
        #mv-page .mv-qte { text-align: left; }
        #mv-page .mv-actions { justify-content: flex-start; }
        #mv-page .mv-designation { max-width: none; }
    }
</style>

<div id="mv-page">

    {{-- ONGLETS : TOUS / ENTRÉES / SORTIES --}}
    <nav class="mv-onglets" aria-label="Type de mouvement">
        @foreach($onglets as $nomRoute => [$libelle, $icone])
            @if(auth()->user()->hasPermission(\App\Services\PermissionService::forRoute($nomRoute) ?? 'voir_stock'))
                <a href="{{ route($nomRoute) }}" class="{{ $route === $nomRoute ? 'active' : '' }}">
                    <i class="bx {{ $icone }}"></i> {{ $libelle }}
                </a>
            @endif
        @endforeach
    </nav>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- FILTRES --}}
    <form method="GET" action="{{ url()->current() }}" class="card mb-3">
        <div class="card-body">
            <div class="mv-filtres {{ $ongletFixe ? 'sans-type' : '' }}">

                <div>
                    <label class="form-label" for="mv-reference">Référence ou n° de document</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-barcode"></i></span>
                        <input type="text" id="mv-reference" name="reference" class="form-control"
                               placeholder="Ex. 90915-YZZD2, BT-2026-0505" value="{{ request('reference') }}">
                    </div>
                </div>

                <div>
                    <label class="form-label" for="mv-designation">Désignation ou origine</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-package"></i></span>
                        <input type="text" id="mv-designation" name="designation" class="form-control"
                               placeholder="Ex. filtre à huile, transfert garage" value="{{ request('designation') }}">
                    </div>
                </div>

                <div>
                    <label class="form-label" for="mv-date">Date</label>
                    <input type="date" id="mv-date" name="date" class="form-control" value="{{ request('date') }}">
                </div>

                @unless($ongletFixe)
                    <div>
                        <label class="form-label" for="mv-type">Type</label>
                        <select id="mv-type" name="type" class="form-select">
                            <option value="">Tous</option>
                            <option value="in" @selected(request('type') === 'in')>Entrées</option>
                            <option value="out" @selected(request('type') === 'out')>Sorties</option>
                        </select>
                    </div>
                @endunless

                <div class="mv-boutons">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-search me-1"></i> Rechercher
                    </button>
                    @if($filtresActifs)
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary" title="Effacer les filtres">
                            <i class="bx bx-x"></i>
                        </a>
                    @endif
                </div>

            </div>
        </div>
    </form>

    {{-- TABLEAU --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mv-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Pièce</th>
                        <th>Document</th>
                        <th>Origine</th>
                        <th>Type</th>
                        <th class="text-end">Quantité</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($movements as $movement)
                        @php
                            $entree = $movement->type === 'in';
                            $sortie = $movement->type === 'out';
                            // la source contient souvent « … | Dépôt: X » : on affiche le dépôt à part
                            $source = (string) ($movement->source ?? '');
                            $depotTexte = null;
                            if (preg_match('/^(.*?)\s*\|\s*D[ée]p[ôo]t\s*:\s*(.+)$/u', $source, $m)) {
                                $source = trim($m[1]);
                                $depotTexte = trim($m[2]);
                            }
                        @endphp

                        <tr>
                            <td data-label="Date" class="mv-date">
                                {{ optional($movement->created_at)->format('d/m/Y') }}
                                <div class="mv-petit">{{ optional($movement->created_at)->format('H:i') }}</div>
                            </td>

                            <td data-label="Pièce">
                                <div class="mv-ref">{{ $movement->product->reference ?? '—' }}</div>
                                <div class="mv-petit mv-designation">{{ $movement->product->designation ?? '' }}</div>
                            </td>

                            <td data-label="Document">
                                <span class="mv-doc">{{ $movement->reference ?: '—' }}</span>
                            </td>

                            <td data-label="Origine">
                                {{ $source !== '' ? $source : '—' }}
                                @if($depotTexte)
                                    <div><span class="mv-depot"><i class="bx bx-buildings"></i> {{ $depotTexte }}</span></div>
                                @endif
                                @if($movement->user)
                                    <div class="mv-petit">par {{ $movement->user->name }}</div>
                                @endif
                            </td>

                            <td data-label="Type">
                                @if($entree)
                                    <span class="badge bg-label-success"><i class="bx bx-down-arrow-alt"></i> Entrée</span>
                                @elseif($sortie)
                                    <span class="badge bg-label-danger"><i class="bx bx-up-arrow-alt"></i> Sortie</span>
                                @else
                                    <span class="badge bg-label-secondary">{{ ucfirst((string) $movement->type) }}</span>
                                @endif
                            </td>

                            <td data-label="Quantité" class="mv-qte {{ $entree ? 'entree' : ($sortie ? 'sortie' : '') }}">
                                {{ $entree ? '+' : ($sortie ? '−' : '') }}{{ rtrim(rtrim(number_format((float) $movement->quantity, 2, ',', ' '), '0'), ',') }}
                            </td>

                            <td data-label="Actions">
                                <div class="mv-actions">
                                    <a href="{{ route('stock-movements.show', $movement) }}" class="btn btn-outline-primary" title="Voir">
                                        <i class="bx bx-show"></i>
                                    </a>

                                    @if(auth()->user()->hasPermission('supprimer_mouvements'))
                                        <form action="{{ route('stock-movements.destroy', $movement) }}" method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="mv-vide">
                                    <i class="bx bx-transfer-alt"></i>
                                    Aucun mouvement trouvé{{ $filtresActifs ? ' avec ces filtres' : '' }}.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination + nombre par page (10, 25, 50 ou 100) --}}
        @if(method_exists($movements, 'total') && $movements->total() > 0)
            <div class="mv-pagination">
                <form method="GET" action="{{ url()->current() }}" class="mv-par-page">
                    @foreach(request()->except(['par_page', 'page']) as $cle => $valeur)
                        @if(is_scalar($valeur))
                            <input type="hidden" name="{{ $cle }}" value="{{ $valeur }}">
                        @endif
                    @endforeach
                    <label for="par_page">Afficher</label>
                    <select id="par_page" name="par_page" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach(\App\Http\Controllers\StockMovementController::PAR_PAGE as $nombre)
                            <option value="{{ $nombre }}" @selected($movements->perPage() === $nombre)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                    <span>par page</span>
                </form>

                <div class="mv-par-page-info">
                    {{ $movements->firstItem() }}–{{ $movements->lastItem() }} sur {{ number_format($movements->total(), 0, ',', ' ') }} mouvements
                </div>

                @if($movements->hasPages())
                    <div class="mv-pagination-liens">{{ $movements->links() }}</div>
                @endif
            </div>
        @endif
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#mv-page .delete-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Supprimer le mouvement ?',
                text: 'Cette action est irréversible.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>

@endsection
