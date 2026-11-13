@extends('layouts.layoutMaster')

@section('title', 'Dépôts')

@section('content')

{{-- V6 : liste des dépôts en cartes, avec leurs chiffres (thème de l'Atelier) --}}
<style>
    .dp-entete { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 20px; }
    .dp-entete p { margin: 0; color: var(--stcd-muted); }

    .dp-grille { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; }

    .dp-totaux { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 20px; }
    .dp-total { display: flex; align-items: center; gap: 14px; padding: 16px 18px; background: var(--stcd-surface); border: 1px solid var(--stcd-border); border-radius: 12px; }
    .dp-total i { flex: 0 0 42px; height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 21px; background: var(--stcd-primary-50); color: var(--stcd-primary); }
    .dp-total i.vert { background: rgba(22, 163, 74, .1); color: #16a34a; }
    .dp-total i.bleu { background: rgba(37, 99, 235, .1); color: #2563eb; }
    .dp-total i.gris { background: rgba(100, 116, 139, .12); color: #64748b; }
    .dp-total strong { display: block; font-size: 20px; font-weight: 700; color: var(--stcd-text); line-height: 1.2; white-space: nowrap; }
    .dp-total span { font-size: 12px; color: var(--stcd-muted); }

    .dp-infos { display: flex; flex-wrap: wrap; gap: 8px; padding: 14px 20px 0; }
    .dp-info { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; font-size: 12px; border-radius: 20px; background: var(--stcd-surface-2); border: 1px solid var(--stcd-border-light); color: var(--stcd-text-2); }
    .dp-info.alerte { background: rgba(220, 38, 38, .07); border-color: rgba(220, 38, 38, .15); color: #b91c1c; }
    .dp-info.attention { background: rgba(217, 119, 6, .08); border-color: rgba(217, 119, 6, .18); color: #b45309; }

    .dp-rayons { padding: 14px 20px 4px; }
    .dp-rayons-titre { margin-bottom: 8px; font-size: 12px; font-weight: 600; color: var(--stcd-muted); text-transform: uppercase; letter-spacing: .04em; }
    .dp-rayons-liste { display: flex; flex-wrap: wrap; gap: 5px; max-height: 92px; overflow: hidden; }
    .dp-rayon { padding: 2px 8px; font-size: 12px; font-weight: 600; border-radius: 6px; background: var(--stcd-primary-50); color: var(--stcd-primary); }
    .dp-rayon.plus { background: transparent; border: 1px dashed var(--stcd-border-strong); color: var(--stcd-muted); }

    @media (max-width: 991.98px) { .dp-totaux { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 480px) { .dp-totaux { grid-template-columns: minmax(0, 1fr); } }

    .dp-carte { display: flex; flex-direction: column; background: var(--stcd-surface); border: 1px solid var(--stcd-border); border-radius: 12px; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); transition: box-shadow .15s, border-color .15s; }
    .dp-carte:hover { border-color: var(--stcd-primary-100); box-shadow: 0 8px 24px rgba(15, 23, 42, .07); }
    .dp-carte.inactif { opacity: .7; }

    .dp-haut { display: flex; align-items: flex-start; gap: 14px; padding: 20px 20px 16px; }
    .dp-icone { flex: 0 0 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: var(--stcd-primary-50); color: var(--stcd-primary); font-size: 22px; }
    .dp-titre { min-width: 0; flex: 1; }
    .dp-titre h5 { margin: 0; font-size: 16px; font-weight: 700; color: var(--stcd-text); }
    .dp-code { font-size: 12px; font-weight: 600; color: var(--stcd-muted); letter-spacing: .03em; }
    .dp-adresse { display: flex; align-items: center; gap: 4px; margin-top: 6px; font-size: 13px; color: var(--stcd-muted); }

    .dp-chiffres { display: grid; grid-template-columns: repeat(3, 1fr); border-top: 1px solid var(--stcd-border-light); border-bottom: 1px solid var(--stcd-border-light); }
    .dp-chiffres > div { padding: 12px 8px; text-align: center; }
    .dp-chiffres > div + div { border-left: 1px solid var(--stcd-border-light); }
    .dp-chiffres strong { display: block; white-space: nowrap; font-size: 18px; font-weight: 700; color: var(--stcd-text); line-height: 1.2; }
    .dp-chiffres span { font-size: 12px; color: var(--stcd-muted); }

    .dp-bas { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 12px 20px; margin-top: auto; border-top: 1px solid var(--stcd-border-light); }
    .dp-actions { display: flex; gap: 6px; }
    .dp-actions .btn { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; padding: 0; border-radius: 8px; }
    .dp-actions form { margin: 0; }

    .dp-vide { padding: 60px 20px; text-align: center; color: var(--stcd-muted); background: var(--stcd-surface); border: 1px dashed var(--stcd-border-strong); border-radius: 12px; }
    .dp-vide i { font-size: 40px; display: block; margin-bottom: 8px; }
</style>

@php
    $fmtQte = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ' '), '0'), ',');
@endphp

<div class="dp-entete">
    <p>Chaque rayon appartient à un seul dépôt.</p>

    @if(auth()->user()->hasPermission('gerer_depots'))
        <a href="{{ route('depots.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Nouveau dépôt
        </a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($depots->isNotEmpty())
    <div class="dp-totaux">
        <div class="dp-total">
            <i class="bx bx-buildings"></i>
            <div><strong>{{ $depots->where('is_active', true)->count() }} / {{ $depots->count() }}</strong><span>dépôts actifs</span></div>
        </div>
        <div class="dp-total">
            <i class="bx bx-package vert"></i>
            <div><strong>{{ number_format((int) $depots->sum('pieces_en_stock'), 0, ',', ' ') }}</strong><span>pièces en stock</span></div>
        </div>
        <div class="dp-total">
            <i class="bx bx-layer bleu"></i>
            <div><strong>{{ $fmtQte($depots->sum('quantite_totale')) }}</strong><span>quantité totale</span></div>
        </div>
        <div class="dp-total">
            <i class="bx bx-grid-alt gris"></i>
            <div><strong>{{ $rayonsParDepot->flatten(1)->count() }}</strong><span>rayons</span></div>
        </div>
    </div>
@endif

@if($depots->isEmpty())
    <div class="dp-vide">
        <i class="bx bx-buildings"></i>
        Aucun dépôt pour le moment.
    </div>
@else
    <div class="dp-grille">
        @foreach($depots as $depot)
            <div class="dp-carte {{ $depot->is_active ? '' : 'inactif' }}">

                <div class="dp-haut">
                    <div class="dp-icone"><i class="bx bx-buildings"></i></div>

                    <div class="dp-titre">
                        <div class="dp-code">{{ $depot->code ?: '—' }}</div>
                        <h5>{{ $depot->name }}</h5>
                        @if($depot->address)
                            <div class="dp-adresse"><i class="bx bx-map"></i> {{ $depot->address }}</div>
                        @endif
                    </div>

                    @if($depot->is_active)
                        <span class="badge bg-label-success">Actif</span>
                    @else
                        <span class="badge bg-label-danger">Inactif</span>
                    @endif
                </div>

                <div class="dp-chiffres">
                    <div>
                        <strong>{{ number_format((int) $depot->pieces_en_stock, 0, ',', ' ') }}</strong>
                        <span>pièces en stock</span>
                    </div>
                    <div>
                        <strong>{{ $fmtQte($depot->quantite_totale) }}</strong>
                        <span>quantité</span>
                    </div>
                    <div>
                        <strong>{{ ($rayonsParDepot[$depot->id] ?? collect())->count() }}</strong>
                        <span>rayons</span>
                    </div>
                </div>

                <div class="dp-infos">
                    <span class="dp-info {{ $depot->pieces_a_zero ? 'alerte' : '' }}" title="Lignes de stock à 0 dans ce dépôt">
                        <i class="bx bx-error-circle"></i> {{ number_format((int) $depot->pieces_a_zero, 0, ',', ' ') }} à 0
                    </span>
                    <span class="dp-info {{ $depot->pieces_sans_rayon ? 'attention' : '' }}" title="Pièces de ce dépôt sans rayon (Non défini)">
                        <i class="bx bx-help-circle"></i> {{ number_format((int) $depot->pieces_sans_rayon, 0, ',', ' ') }} sans rayon
                    </span>
                </div>

                @php
                    $rayonsDepot = $rayonsParDepot[$depot->id] ?? collect();
                    $maxRayons = 18;
                @endphp
                <div class="dp-rayons">
                    <div class="dp-rayons-titre">Rayons</div>
                    @if($rayonsDepot->isEmpty())
                        <span class="text-muted small">Aucun rayon.</span>
                    @else
                        <div class="dp-rayons-liste" title="{{ $rayonsDepot->pluck('name')->implode(', ') }}">
                            @foreach($rayonsDepot->take($maxRayons) as $r)
                                <span class="dp-rayon">{{ $r->name }}</span>
                            @endforeach
                            @if($rayonsDepot->count() > $maxRayons)
                                <span class="dp-rayon plus">+{{ $rayonsDepot->count() - $maxRayons }}</span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="dp-bas">
                    <a href="{{ route('depots.show', $depot) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-show me-1"></i> Voir le stock
                    </a>

                    <div class="dp-actions">
                        @if(auth()->user()->hasPermission('gerer_depots'))
                            <a href="{{ route('depots.edit', $depot) }}" class="btn btn-outline-secondary" title="Modifier">
                                <i class="bx bx-edit"></i>
                            </a>

                            @if(auth()->user()->hasPermission('supprimer_depots'))
                                <form action="{{ route('depots.destroy', $depot) }}" method="POST" class="delete-form" data-nom="{{ $depot->name }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>

            </div>
        @endforeach
    </div>
@endif

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Supprimer le dépôt « ' + (form.dataset.nom || '') + ' » ?',
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
