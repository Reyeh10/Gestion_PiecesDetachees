@extends('layouts.layoutMaster')

@section('title', 'Avoir ' . $avoir->numero)

@section('content')

@php
    $sale = $avoir->sale;
    $fdj = fn ($v) => number_format(round((float) $v), 0, ',', ' ');
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h4 class="mb-1">Avoir {{ $avoir->numero }}</h4>
        <div class="text-muted">
            Émis le {{ $avoir->created_at->format('d/m/Y à H:i') }}
            par {{ $avoir->creePar->name ?? '—' }}
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('avoirs.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back"></i> Retour
        </a>

        @if($sale && auth()->user()->hasPermission('voir_ventes'))
            <a href="{{ route('sales.invoice', $sale) }}" class="btn btn-outline-primary">
                <i class="bx bx-file"></i> Facture {{ $sale->invoice_number }}
            </a>
        @endif

        <a href="{{ route('avoirs.pdf', $avoir) }}" class="btn btn-outline-danger">
            <i class="bx bxs-file-pdf"></i> PDF
        </a>

        <button type="button" class="btn btn-outline-dark" data-imprimer="{{ route('avoirs.imprimer', $avoir) }}">
            <i class="bx bx-printer"></i> Imprimer
        </button>
    </div>
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

<div class="row g-4">

    <div class="col-lg-8">

        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small text-uppercase fw-bold">Client</div>
                        <div class="fw-bold">{{ $avoir->customer->name ?? 'Client non renseigné' }}</div>
                        <div class="small">{{ $avoir->customer->phone ?? '' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small text-uppercase fw-bold">Facture annulée</div>
                        <div class="fw-bold">{{ $sale->invoice_number ?? '—' }}</div>
                        <div class="small">du {{ optional($sale?->created_at)->format('d/m/Y') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small text-uppercase fw-bold">Véhicule</div>
                        <div class="fw-bold">{{ $sale?->vehicle?->plate_number ?? '—' }}</div>
                    </div>
                </div>

                <div class="alert alert-danger mt-3 mb-0">
                    <strong>Motif de l'annulation :</strong> {{ $avoir->motif }}
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Lignes annulées (pièces remises en stock)</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Référence</th>
                            <th>Désignation</th>
                            <th>Dépôt</th>
                            <th class="text-end">Qté</th>
                            <th class="text-end">P.U.</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($avoir->lignes as $ligne)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $ligne->reference ?: '—' }}</td>
                                <td>{{ $ligne->designation ?: '—' }}</td>
                                <td>{{ $ligne->depot->name ?? '—' }}</td>
                                <td class="text-end">{{ rtrim(rtrim(number_format((float) $ligne->quantite, 2, ',', ' '), '0'), ',') }}</td>
                                <td class="text-end">{{ $fdj($ligne->prix_unitaire) }}</td>
                                <td class="text-end text-danger fw-bold">- {{ $fdj($ligne->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="col-lg-4">

        <div class="card mb-4">
            <div class="card-header">Montants</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Sous-total</span>
                    <span class="text-danger">- {{ $fdj($avoir->subtotal) }} FDJ</span>
                </div>
                @if((float) $avoir->discount_amount > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Remise ({{ number_format((float) $avoir->discount, 2, ',', ' ') }} %)</span>
                        <span>+ {{ $fdj($avoir->discount_amount) }} FDJ</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between mb-2">
                    <span>TVA</span>
                    <span class="text-danger">- {{ $fdj($avoir->tva) }} FDJ</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fs-5 fw-bold">
                    <span>Total de l'avoir</span>
                    <span class="text-danger">- {{ $fdj($avoir->total) }} FDJ</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Remboursement au client</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Déjà encaissé sur la facture</span>
                    <strong>{{ $fdj($avoir->montant_deja_paye) }} FDJ</strong>
                </div>

                @if((float) $avoir->montant_a_rembourser <= 0)
                    <div class="alert alert-info mb-0">Rien n'avait été encaissé : aucun remboursement à faire.</div>
                @elseif($avoir->rembourse_le)
                    <div class="alert alert-success mb-0">
                        <strong>Remboursé :</strong> {{ $fdj($avoir->montant_a_rembourser) }} FDJ
                        le {{ $avoir->rembourse_le->format('d/m/Y') }}
                        ({{ $avoir->getModeRemboursementLabel() }}).
                    </div>
                @else
                    <div class="alert alert-warning">
                        <strong>À rembourser :</strong> {{ $fdj($avoir->montant_a_rembourser) }} FDJ
                    </div>

                    @if(auth()->user()->hasPermission('rembourser_avoirs'))
                        <form method="POST" action="{{ route('avoirs.rembourser', $avoir) }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label class="form-label" for="mode_remboursement">Mode de remboursement</label>
                                <select id="mode_remboursement" name="mode_remboursement" class="form-select" required>
                                    <option value="">— Choisir —</option>
                                    @foreach(\App\Models\Avoir::MODES_REMBOURSEMENT as $cle => $libelle)
                                        <option value="{{ $cle }}" @selected(old('mode_remboursement') === $cle)>{{ $libelle }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="rembourse_le">Date du remboursement</label>
                                <input type="date" id="rembourse_le" name="rembourse_le" class="form-control"
                                       value="{{ old('rembourse_le', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-check"></i> Enregistrer le remboursement
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>

    </div>
</div>

@include('bons-transfert._impression')

@endsection
