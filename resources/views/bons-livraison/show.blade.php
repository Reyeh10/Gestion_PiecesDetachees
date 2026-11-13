@extends('layouts.layoutMaster')

@section('title', 'Bon de livraison ' . $bl->numero)

@section('content')

@php $qte = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ' '), '0'), ','); @endphp

<h4 class="mb-1">Bon de livraison {{ $bl->numero }}</h4>
<p class="text-muted mb-4">Facture {{ $bl->sale->invoice_number }} — livré le {{ $bl->livre_le->format('d/m/Y') }}</p>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($bl->estAnnule())
    <div class="alert alert-danger">
        <i class="bx bx-block"></i> Ce bon de livraison est <strong>annulé</strong> : la facture a été annulée par un avoir.
    </div>
@endif

<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('bons-livraison.imprimer', $bl) }}" target="_blank" class="btn btn-primary"><i class="bx bx-printer"></i> Imprimer</a>
    <a href="{{ route('bons-livraison.pdf', $bl) }}" class="btn btn-secondary"><i class="bx bx-download"></i> PDF</a>
    <a href="{{ route('sales.show', $bl->sale) }}" class="btn btn-light"><i class="bx bx-receipt"></i> Voir la facture</a>
    <a href="{{ route('bons-livraison.index') }}" class="btn btn-light"><i class="bx bx-arrow-back"></i> Liste des BL</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card"><div class="card-body">
            <div class="text-muted small fw-bold">CLIENT</div>
            <div class="fw-bold">{{ $bl->sale->customer->name ?? '—' }}</div>
            <div class="text-muted small">{{ $bl->sale->customer->phone ?? '' }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-body">
            <div class="text-muted small fw-bold">VÉHICULE</div>
            <div class="fw-bold">{{ $bl->sale->vehicle?->plate_number ?? '—' }}</div>
            <div class="text-muted small">{{ trim(($bl->sale->vehicle?->brand ?? '') . ' ' . ($bl->sale->vehicle?->model ?? '')) }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-body">
            <div class="text-muted small fw-bold">LIVRAISON</div>
            <div class="fw-bold">Reçu par : {{ $bl->recu_par ?: '—' }}</div>
            <div class="text-muted small">Établi par {{ $bl->user->name ?? '—' }}</div>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>#</th><th>Référence</th><th>Désignation</th><th class="text-end">Quantité livrée</th></tr>
            </thead>
            <tbody>
                @foreach($bl->lignes as $i => $ligne)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><strong>{{ $ligne->reference ?: '—' }}</strong></td>
                        <td>{{ $ligne->designation }}</td>
                        <td class="text-end"><strong>{{ $qte($ligne->quantite) }}</strong> {{ $ligne->unite }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($bl->notes)
            <p class="mt-3 mb-0"><strong>Observations :</strong> {{ $bl->notes }}</p>
        @endif
    </div>
</div>

@endsection
