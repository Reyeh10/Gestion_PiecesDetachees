@extends('layouts.layoutMaster')

@section('title', 'Avoirs')

@section('content')

@php
    $fdj = fn ($v) => number_format(round((float) $v), 0, ',', ' ');
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h4 class="mb-1">Avoirs</h4>
        <div class="text-muted">Factures annulées : une facture ne se supprime pas, elle est annulée par un avoir.</div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('avoirs.index') }}" class="row g-2 align-items-center">
            <div class="col-md-6">
                <input type="text" name="search" value="{{ $search }}" class="form-control"
                       placeholder="Rechercher : n° avoir, n° facture, client, motif…">
            </div>
            <div class="col-md-3">
                <select name="filtre" class="form-select">
                    <option value="">Tous les avoirs</option>
                    <option value="a_rembourser" @selected($filtre === 'a_rembourser')>Remboursement en attente</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bx bx-search"></i> Rechercher</button>
                @if($search !== '' || $filtre !== '')
                    <a href="{{ route('avoirs.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>N° avoir</th>
                    <th>Facture annulée</th>
                    <th>Client</th>
                    <th>Motif</th>
                    <th class="text-end">Montant</th>
                    <th>Remboursement</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($avoirs as $avoir)
                    <tr>
                        <td class="fw-bold">{{ $avoir->numero }}</td>
                        <td>{{ $avoir->sale->invoice_number ?? '—' }}</td>
                        <td>{{ $avoir->customer->name ?? '—' }}</td>
                        <td class="text-muted" style="max-width:260px;">{{ \Illuminate\Support\Str::limit($avoir->motif, 70) }}</td>
                        <td class="text-end text-danger fw-bold">- {{ $fdj($avoir->total) }} FDJ</td>
                        <td>
                            @if((float) $avoir->montant_a_rembourser <= 0)
                                <span class="badge bg-label-secondary">Rien à rembourser</span>
                            @elseif($avoir->rembourse_le)
                                <span class="badge bg-success">Remboursé</span>
                            @else
                                <span class="badge bg-warning text-dark">{{ $fdj($avoir->montant_a_rembourser) }} FDJ à rembourser</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $avoir->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-sm btn-outline-dark" title="Imprimer {{ $avoir->numero }}"
                                    data-imprimer="{{ route('avoirs.imprimer', $avoir) }}">
                                <i class="bx bx-printer"></i>
                            </button>
                            <a href="{{ route('avoirs.show', $avoir) }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-show"></i> Voir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bx bx-receipt fs-2 d-block mb-2"></i>
                            @if($search !== '' || $filtre !== '')
                                Aucun avoir ne correspond à cette recherche.
                            @else
                                Aucun avoir pour le moment.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $avoirs->links() }}
</div>

@include('bons-transfert._impression')

@endsection
