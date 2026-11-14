@extends('layouts.layoutMaster')

@section('title', 'Nouveau bon de livraison')

@section('content')

@php $qte = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ' '), '0'), ','); @endphp

<h4 class="mb-1">Nouveau bon de livraison</h4>
<p class="text-muted mb-4">
    Facture <strong>{{ $sale->invoice_number }}</strong> — {{ $sale->customer->name ?? 'Client' }}.
    Indiquez les quantités livrées maintenant : vous pouvez ne livrer qu'une partie et faire un autre BL plus tard.
</p>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ route('bons-livraison.store', $sale) }}" class="card">
    @csrf

    <div class="card-body">

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label" for="livre_le">Date de livraison</label>
                <input type="date" id="livre_le" name="livre_le" class="form-control" value="{{ old('livre_le', now()->toDateString()) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="recu_par">Reçu par (facultatif)</label>
                <input type="text" id="recu_par" name="recu_par" class="form-control" value="{{ old('recu_par') }}" maxlength="150" placeholder="Nom de la personne qui reçoit">
            </div>
            <div class="col-md-5">
                <label class="form-label" for="notes">Observations (facultatif)</label>
                <input type="text" id="notes" name="notes" class="form-control" value="{{ old('notes') }}" maxlength="2000">
            </div>
        </div>

        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Désignation</th>
                    <th class="text-end">Facturé</th>
                    <th class="text-end">Déjà livré</th>
                    <th class="text-end">Reste</th>
                    <th style="width:170px;">À livrer maintenant</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lignes as $l)
                    <tr class="{{ $l['reste'] <= 0 ? 'text-muted' : '' }}">
                        <td><strong>{{ $l['reference'] ?: '—' }}</strong></td>
                        <td>{{ $l['designation'] }}</td>
                        <td class="text-end">{{ $qte($l['facture']) }} {{ $l['unite'] }}</td>
                        <td class="text-end">{{ $qte($l['livre']) }}</td>
                        <td class="text-end"><strong>{{ $qte($l['reste']) }}</strong></td>
                        <td>
                            @if($l['reste'] > 0)
                                <input type="number" name="quantites[{{ $l['item_id'] }}]" class="form-control form-control-sm"
                                       value="{{ old('quantites.' . $l['item_id'], $qte($l['reste'])) }}"
                                       min="0" max="{{ $l['reste'] }}" step="0.01">
                            @else
                                <span class="badge bg-success">Déjà livré</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary"><i class="bx bx-package"></i> Créer le bon de livraison</button>
        </div>
    </div>
</form>

@endsection
