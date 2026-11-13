@extends('layouts.layoutMaster')

@section('title', 'Demande ' . $demande->numero)

@section('content')

@php
    $peutGerer = auth()->user()?->hasPermission('gerer_demandes_pieces');
    $modifiables = [\App\Models\VehiclePartRequest::STATUS_SEARCHING, \App\Models\VehiclePartRequest::STATUS_TO_ORDER, \App\Models\VehiclePartRequest::STATUS_NOT_FOUND];
    $terminee = $demande->estTerminee();
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <h4 class="mb-1">
            Demande {{ $demande->numero }}
            <span class="badge bg-label-{{ $demande->couleurEtape() }} align-middle">{{ $demande->libelleEtape() }}</span>
            @php [$recues, $total] = $demande->avancement(); @endphp
            <span class="badge bg-label-secondary align-middle">{{ $recues }}/{{ $total }} reçue(s)</span>
        </h4>
        <div class="text-muted">
            {{ \App\Models\Demande::SOURCES[$demande->source] ?? $demande->source }}
            · le {{ $demande->created_at->format('d/m/Y à H:i') }}
            @if($demande->creator) par {{ $demande->creator->name }} @endif
            @if($demande->bonCommandeGarage)
                · <a href="{{ route('fournisseur-commandes.show', $demande->bonCommandeGarage) }}">BC garage {{ $demande->bonCommandeGarage->numero }}</a>
            @endif
        </div>
    </div>
    <a href="{{ route('demandes.index') }}" class="btn btn-outline-secondary btn-sm">← Demandes</a>
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

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small text-uppercase">Véhicule</div>
            @if($demande->vehicle)
                <div class="fw-semibold">{{ $demande->vehicle->plate_number }}</div>
                <div class="small">{{ trim($demande->vehicle->brand . ' ' . $demande->vehicle->model) }}{{ $demande->vehicle->customer ? ' — ' . $demande->vehicle->customer->name : '' }}</div>
            @else
                <div class="fw-semibold">Pour le stock</div>
            @endif
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small text-uppercase">Dépôt de réception</div>
            @if($demande->depot)
                <div class="fw-semibold">{{ $demande->depot->name }}</div>
            @elseif($peutGerer)
                <form method="POST" action="{{ route('demandes.depot', $demande) }}" class="d-flex gap-2 mt-1">
                    @csrf @method('PATCH')
                    <select name="depot_id" class="form-select form-select-sm" required>
                        <option value="">— Choisir —</option>
                        @foreach($depots as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                    </select>
                    <button class="btn btn-sm btn-primary">OK</button>
                </form>
                <div class="small text-danger mt-1">Sans dépôt de réception, les bons de commande ne peuvent pas se créer.</div>
            @else
                <div class="text-danger">À indiquer</div>
            @endif
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small text-uppercase">Remarque</div>
            <div>{{ $demande->notes ?: '—' }}</div>
        </div></div>
    </div>
</div>

{{-- BONS DE COMMANDE DE LA DEMANDE --}}
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Bons de commande ({{ $demande->bonsCommande->count() }})</h5>
        <span class="text-muted small">Un par fournisseur ; une pièce ajoutée après l'envoi va sur un bon complémentaire.</span>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 no-cartes">
            <thead><tr><th>N°</th><th>Fournisseur</th><th>Lignes</th><th class="text-end">Total</th><th>Statut</th><th>Signatures</th><th></th></tr></thead>
            <tbody>
            @forelse($demande->bonsCommande as $bc)
                <tr>
                    <td class="fw-semibold">{{ $bc->order_number }}</td>
                    <td>{{ $bc->supplier?->name }}</td>
                    <td>{{ $bc->items->count() }}</td>
                    <td class="text-end">{{ number_format((float) $bc->total, 0, ',', ' ') }} {{ $bc->currency }}</td>
                    <td>
                        <span class="badge bg-label-{{ $bc->couleurStatut() }}">{{ $bc->libelleStatut() }}</span>
                        @if($bc->estEnRetard()) <span class="badge bg-danger">En retard</span> @endif
                        @if($bc->estARelancer()) <span class="badge bg-label-danger">À relancer</span> @endif
                    </td>
                    <td class="small">
                        Chef : {{ $bc->validator?->name ?? '—' }}<br>
                        Admin : {{ $bc->approver?->name ?? '—' }}
                    </td>
                    <td class="text-end"><a href="{{ route('supplier-orders.show', $bc) }}" class="btn btn-sm btn-outline-primary">Ouvrir</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">Aucun bon de commande : indiquez le fournisseur des pièces ci-dessous.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- PIÈCES --}}
<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">Pièces ({{ $demande->pieces->count() }})</h5></div>
    <div class="table-responsive">
        <table class="table align-middle mb-0 no-cartes">
            <thead>
                <tr>
                    <th>Pièce</th>
                    <th class="text-center">Demandé</th>
                    <th class="text-center">Reçu</th>
                    <th>Statut</th>
                    <th style="min-width:330px;">Fournisseur / prix indicatif</th>
                    <th>Bon de commande</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach($demande->pieces as $piece)
                @php $bcPiece = $piece->ligneBcActive?->supplierOrder; @endphp
                <tr>
                    <td>
                        <a href="{{ route('vehicle-part-requests.show', $piece) }}" class="fw-semibold">{{ $piece->part_name }}</a>
                        <div class="small text-muted">{{ $piece->reference ?: 'Sans référence' }}{{ $piece->product ? '' : ' · hors catalogue' }}</div>
                    </td>
                    <td class="text-center">{{ rtrim(rtrim(number_format((float) $piece->quantity, 2, ',', ' '), '0'), ',') }} {{ $piece->unit }}</td>
                    <td class="text-center">{{ rtrim(rtrim(number_format((float) $piece->received_quantity, 2, ',', ' '), '0'), ',') }}</td>
                    <td><span class="badge bg-label-{{ $piece->status_badge }}">{{ $piece->status_label }}</span></td>
                    <td>
                        @if($peutGerer && in_array($piece->status, $modifiables, true) && (! $bcPiece || $bcPiece->status === \App\Models\SupplierOrder::STATUS_DRAFT))
                            <form method="POST" action="{{ route('demandes.pieces.fournisseur', $piece) }}" class="d-flex gap-1">
                                @csrf @method('PATCH')
                                <select name="supplier_id" class="form-select form-select-sm">
                                    <option value="">— À chercher —</option>
                                    @foreach($suppliers as $s)
                                        <option value="{{ $s->id }}" @selected($piece->supplier_id == $s->id)>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="prix" step="0.01" min="0" class="form-control form-control-sm text-end" style="width:110px;"
                                       value="{{ $piece->purchase_price ?? $piece->estimated_price }}" placeholder="Prix">
                                <button class="btn btn-sm btn-primary" title="Enregistrer : le bon de commande se crée ou se complète"><i class="bx bx-check"></i></button>
                            </form>
                        @else
                            {{ $piece->supplier?->name ?? '—' }}
                            @if($piece->purchase_price ?? $piece->estimated_price)
                                <span class="text-muted small">· {{ number_format((float) ($piece->purchase_price ?? $piece->estimated_price), 0, ',', ' ') }}</span>
                            @endif
                        @endif
                    </td>
                    <td>
                        @if($bcPiece)
                            <a href="{{ route('supplier-orders.show', $bcPiece) }}">{{ $bcPiece->order_number }}</a>
                            <div class="small text-muted">{{ $bcPiece->libelleStatut() }}</div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($peutGerer && in_array($piece->status, [\App\Models\VehiclePartRequest::STATUS_SEARCHING, \App\Models\VehiclePartRequest::STATUS_TO_ORDER], true))
                            <form method="POST" action="{{ route('vehicle-part-requests.change-status', $piece) }}"
                                  onsubmit="return confirm('Annuler la pièce « {{ addslashes($piece->part_name) }} » ?{{ $bcPiece ? ' Elle sera retirée du bon de commande ' . $bcPiece->order_number . '.' : '' }}');">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ \App\Models\VehiclePartRequest::STATUS_CANCELLED }}">
                                <input type="hidden" name="comment" value="Annulée depuis la demande {{ $demande->numero }}.">
                                <button class="btn btn-sm btn-outline-danger" title="Annuler la pièce"><i class="bx bx-x"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- AJOUTER DES PIÈCES --}}
@if($peutGerer)
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Ajouter une pièce</h5>
            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#bloc-ajout"><i class="bx bx-plus"></i> Ajouter</button>
        </div>
        <div class="collapse {{ old('lignes') ? 'show' : '' }}" id="bloc-ajout">
            <form method="POST" action="{{ route('demandes.pieces.store', $demande) }}" class="card-body">
                @csrf
                <p class="text-muted small mb-2">
                    Si le bon de commande du fournisseur est déjà validé ou envoyé, la pièce part sur un bon de commande complémentaire.
                </p>
                <div class="mb-3" style="max-width:420px;">
                    <label class="form-label" for="fournisseur-defaut">Fournisseur pour toutes les lignes</label>
                    <select id="fournisseur-defaut" class="form-select">
                        <option value="">— Chaque ligne a le sien —</option>
                        @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                    </select>
                </div>
                @include('demandes._lignes')
                <div class="text-end mt-3"><button class="btn btn-primary"><i class="bx bx-check"></i> Ajouter à la demande</button></div>
            </form>
        </div>
    </div>
@endif

@endsection
