@extends('layouts.layoutMaster')

@section('title', 'Factures')

@section('content')

{{-- V6 : liste des factures avec les filtres de l'Atelier (statut, période, payeur)
     et la fiche de suivi de règlement en Excel (récolte de la TVA). --}}
@php
    $statut = request('statut', '');
    $periode = request('periode', '');
    $statuts = ['' => 'Toutes', 'non_payees' => 'Non payées', 'payees' => 'Payées', 'annulees' => 'Annulées'];
    $periodes = ['' => 'Toutes', 'jour' => 'Jour', 'mois' => 'Mois', 'annee' => 'Année'];
    $filtresActifs = request()->anyFilled(['statut', 'periode', 'date_ref', 'recherche', 'client', 'payeur', 'date']);
    $fdj = fn ($v) => number_format(round((float) $v), 0, ',', ' ');
    $moisNoms = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $refPeriode = null;
    if ($periode !== '') {
        try {
            $ref = request('date_ref');
            $refPeriode = $ref ? \Carbon\Carbon::parse($ref . ($periode === 'annee' ? '-01-01' : ($periode === 'mois' ? '-01' : ''))) : now();
        } catch (\Throwable $e) {
            $refPeriode = now();
        }
    }
    $libellePeriode = match ($periode) {
        'jour' => 'du ' . $refPeriode->format('d/m/Y'),
        'mois' => $moisNoms[$refPeriode->month - 1] . ' ' . $refPeriode->year,
        'annee' => 'année ' . $refPeriode->year,
        default => null,
    };
@endphp

<style>
    #fact-page .fact-entete { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
    #fact-page .fact-entete p { margin: 0; color: var(--stcd-muted); }

    #fact-page .fact-resume { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 16px; }
    #fact-page .fact-chiffre { padding: 14px 16px; background: var(--stcd-surface); border: 1px solid var(--stcd-border); border-radius: 12px; }
    #fact-page .fact-chiffre span { display: block; font-size: 12px; color: var(--stcd-muted); }
    #fact-page .fact-chiffre strong { display: block; margin-top: 2px; font-size: 19px; font-weight: 700; color: var(--stcd-text); white-space: nowrap; }
    #fact-page .fact-chiffre.rouge strong { color: #dc2626; }

    #fact-page .fact-filtres { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 14px 22px; }
    #fact-page .fact-groupe > label { display: block; margin-bottom: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--stcd-muted); }
    #fact-page .fact-recherche { display: flex; gap: 8px; flex: 1 1 320px; min-width: 260px; }
    #fact-page .fact-recherche .form-control { min-width: 0; }
    #fact-page .fact-puces { display: flex; flex-wrap: wrap; gap: 6px; }
    #fact-page .fact-puce { padding: 6px 12px; font-size: 13px; font-weight: 600; line-height: 1.4; color: var(--stcd-text-2); background: var(--stcd-surface); border: 1px solid var(--stcd-border); border-radius: 8px; text-decoration: none; cursor: pointer; }
    #fact-page .fact-puce:hover { border-color: var(--stcd-border-strong); color: var(--stcd-text); }
    #fact-page .fact-puce.active { color: var(--stcd-primary); background: var(--stcd-primary-50); border-color: var(--stcd-primary); }
    #fact-page .fact-date { display: flex; gap: 8px; }
    #fact-page .fact-date .form-control { width: 170px; }
    #fact-page .fact-effacer { align-self: center; font-size: 13px; color: var(--stcd-muted); text-decoration: none; }
    #fact-page .fact-effacer:hover { color: #dc2626; }

    #fact-page .fact-barre-liste { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 20px; border-bottom: 1px solid var(--stcd-border-light); }
    #fact-page .fact-barre-liste h6 { margin: 0; font-weight: 700; color: var(--stcd-text); }
    #fact-page .fact-table th { font-size: 12px; white-space: nowrap; }
    #fact-page .fact-table td { font-size: 13px; vertical-align: middle; }
    #fact-page .fact-num { font-weight: 700; color: var(--stcd-primary); white-space: nowrap; text-decoration: none; }
    #fact-page .fact-petit { font-size: 12px; color: var(--stcd-muted); }
    #fact-page .fact-montant { font-weight: 700; white-space: nowrap; text-align: right; }
    #fact-page .fact-actions .btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; padding: 0; border-radius: 8px; }
    #fact-page .fact-vide { padding: 48px 16px; text-align: center; color: var(--stcd-muted); }
    #fact-page .fact-vide i { display: block; font-size: 36px; margin-bottom: 6px; }
    #fact-page .fact-pagination { padding: 14px 20px; border-top: 1px solid var(--stcd-border-light); }
    #fact-page .fact-pagination nav p { display: none; }
    #fact-page .fact-pagination .pagination { margin: 0; }

    @media (max-width: 991.98px) { #fact-page .fact-resume { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px) {
        #fact-page .fact-resume { grid-template-columns: minmax(0, 1fr); }
        #fact-page .fact-table thead { display: none; }
        #fact-page .fact-table, #fact-page .fact-table tbody, #fact-page .fact-table tr { display: block; }
        #fact-page .fact-table tr { padding: 10px 14px; border-bottom: 1px solid var(--stcd-border); }
        #fact-page .fact-table td { display: grid; grid-template-columns: 100px minmax(0, 1fr); gap: 8px; padding: 4px 0; border: 0; text-align: left; }
        #fact-page .fact-table td::before { content: attr(data-label); font-size: 12px; font-weight: 600; color: var(--stcd-muted); }
        #fact-page .fact-table td[colspan] { display: block; }
        #fact-page .fact-table td[colspan]::before { content: none; }
        #fact-page .fact-montant { text-align: left; }
    }
</style>

<div id="fact-page">

    <div class="fact-entete">
        <p>Choisissez une période (par exemple un mois) puis téléchargez la fiche Excel de suivi de règlement : elle sert à la récolte de la TVA.</p>

        @if(auth()->user()->hasPermission('creer_ventes'))
            <a href="{{ route('sales.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Nouvelle vente
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- CHIFFRES DE LA SÉLECTION (hors factures annulées) --}}
    <div class="fact-resume">
        <div class="fact-chiffre">
            <span>Factures{{ $libellePeriode ? ' — ' . $libellePeriode : '' }}</span>
            <strong>{{ number_format($resume['nombre'], 0, ',', ' ') }}</strong>
        </div>
        <div class="fact-chiffre">
            <span>Total TTC (hors annulées)</span>
            <strong>{{ $fdj($resume['ttc']) }} FDJ</strong>
        </div>
        <div class="fact-chiffre">
            <span>TVA collectée</span>
            <strong>{{ $fdj($resume['tva']) }} FDJ</strong>
        </div>
        <div class="fact-chiffre {{ $resume['reste'] > 0 ? 'rouge' : '' }}">
            <span>Reste à encaisser</span>
            <strong>{{ $fdj($resume['reste']) }} FDJ</strong>
        </div>
    </div>

    {{-- FILTRES (comme l'Atelier) --}}
    <form method="GET" action="{{ route('sales.index') }}" class="card mb-3">
        <div class="card-body fact-filtres">

            {{-- le statut et la période choisis sont gardés --}}
            <input type="hidden" name="statut" value="{{ $statut }}">
            @if($periode !== '')<input type="hidden" name="periode" value="{{ $periode }}">@endif

            <div class="fact-groupe" style="flex: 1 1 320px;">
                <label for="fact-recherche">Recherche</label>
                <div class="fact-recherche">
                    <input type="text" id="fact-recherche" name="recherche" class="form-control"
                           placeholder="N° facture, client, téléphone, immatriculation, pièce…"
                           value="{{ request('recherche', request('client')) }}">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-search me-1"></i> Rechercher</button>
                </div>
            </div>

            <div class="fact-groupe">
                <label>Statut</label>
                <div class="fact-puces">
                    @foreach($statuts as $val => $lib)
                        <a href="{{ route('sales.index', array_merge(request()->except('statut', 'page'), $val !== '' ? ['statut' => $val] : [])) }}"
                           class="fact-puce {{ $statut === $val ? 'active' : '' }}">{{ $lib }}</a>
                    @endforeach
                </div>
            </div>

            <div class="fact-groupe">
                <label>Période</label>
                <div class="fact-puces">
                    @foreach($periodes as $val => $lib)
                        @if($val === '')
                            <a href="{{ route('sales.index', request()->except('periode', 'date_ref', 'page')) }}"
                               class="fact-puce {{ $periode === '' ? 'active' : '' }}">{{ $lib }}</a>
                        @else
                            <button type="submit" name="periode" value="{{ $val }}"
                                    formaction="{{ route('sales.index') }}"
                                    class="fact-puce {{ $periode === $val ? 'active' : '' }}"
                                    onclick="var d=this.form.querySelector('[name=date_ref]'); if (d && '{{ $periode }}' !== '{{ $val }}') d.disabled = true;">{{ $lib }}</button>
                        @endif
                    @endforeach
                </div>
            </div>

            @if($periode !== '')
                <div class="fact-groupe">
                    <label for="fact-date-ref">{{ $periode === 'jour' ? 'Jour' : ($periode === 'mois' ? 'Mois' : 'Année') }}</label>
                    <div class="fact-date">
                        @if($periode === 'annee')
                            <input type="number" id="fact-date-ref" name="date_ref" min="2020" max="2100" class="form-control"
                                   value="{{ request('date_ref', now()->year) }}">
                        @elseif($periode === 'mois')
                            <input type="month" id="fact-date-ref" name="date_ref" class="form-control"
                                   value="{{ request('date_ref', now()->format('Y-m')) }}">
                        @else
                            <input type="date" id="fact-date-ref" name="date_ref" class="form-control"
                                   value="{{ request('date_ref', now()->format('Y-m-d')) }}">
                        @endif
                        <button type="submit" class="btn btn-primary">Filtrer</button>
                    </div>
                </div>
            @endif

            <div class="fact-groupe">
                <label for="fact-payeur">Payeur</label>
                <select id="fact-payeur" name="payeur" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous</option>
                    <option value="client" @selected(request('payeur') === 'client')>Clients</option>
                    <option value="assurance" @selected(request('payeur') === 'assurance')>Assurances</option>
                </select>
            </div>

            @if($filtresActifs)
                <a href="{{ route('sales.index') }}" class="fact-effacer"><i class="bx bx-x"></i> Effacer</a>
            @endif

        </div>
    </form>

    {{-- LISTE --}}
    <div class="card">
        <div class="fact-barre-liste">
            <h6>{{ number_format($sales->total(), 0, ',', ' ') }} facture(s){{ $libellePeriode ? ' — ' . $libellePeriode : '' }}</h6>

            <a href="{{ route('sales.export', request()->except('page')) }}" class="btn btn-success">
                <i class="bx bx-download me-1"></i> Excel (suivi règlement / TVA)
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fact-table">
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Date</th>
                        <th>Doit</th>
                        <th>Véhicule</th>
                        <th>Pièces</th>
                        <th class="text-end">HT</th>
                        <th class="text-end">TVA</th>
                        <th class="text-end">TTC</th>
                        <th>Paiement</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        @php
                            $paye = round((float) $sale->payments->sum('amount'));
                            $total = round((float) $sale->total);
                            $reste = max(0, $total - $paye);
                            $immat = $sale->immatriculation ?: $sale->vehicle?->plate_number;
                        @endphp
                        <tr>
                            <td data-label="Numéro">
                                <a href="{{ route('sales.invoice', $sale) }}" class="fact-num">{{ $sale->invoice_number }}</a>
                            </td>
                            <td data-label="Date" class="text-nowrap">{{ $sale->created_at->format('d/m/Y') }}</td>
                            <td data-label="Doit">
                                <strong>{{ $sale->customer->name ?? 'Client comptoir' }}</strong>
                                @if($sale->customer?->est_assurance)
                                    <span class="badge bg-label-info ms-1">Assurance</span>
                                @endif
                                @if($sale->customer?->phone)
                                    <div class="fact-petit">{{ $sale->customer->phone }}</div>
                                @endif
                            </td>
                            <td data-label="Véhicule">{{ $immat ?: '—' }}</td>
                            <td data-label="Pièces">{{ $sale->items->count() }}</td>
                            <td data-label="HT" class="fact-montant">{{ $fdj((float) $sale->subtotal - (float) $sale->discount_amount) }}</td>
                            <td data-label="TVA" class="fact-montant">{{ $fdj($sale->tva) }}</td>
                            <td data-label="TTC" class="fact-montant">{{ $fdj($total) }}</td>
                            <td data-label="Paiement">
                                @if($sale->status === 'cancelled')
                                    <span class="badge bg-label-secondary">Annulée</span>
                                @elseif($sale->status === 'payé' || ($total > 0 && $reste <= 0))
                                    <span class="badge bg-label-success">Payée</span>
                                @elseif($paye > 0)
                                    <span class="badge bg-label-warning">Partielle</span>
                                    <div class="fact-petit">reste {{ $fdj($reste) }}</div>
                                @else
                                    <span class="badge bg-label-danger">Non payée</span>
                                @endif
                            </td>
                            <td data-label="" class="text-end fact-actions">
                                <a href="{{ route('sales.invoice', $sale) }}" class="btn btn-outline-primary" title="Voir la facture">
                                    <i class="bx bx-show"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="fact-vide">
                                    <i class="bx bx-receipt"></i>
                                    Aucune facture{{ $libellePeriode ? ' pour ' . $libellePeriode : '' }}{{ $filtresActifs ? ' avec ces filtres' : '' }}.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="fact-pagination">{{ $sales->links() }}</div>
        @endif
    </div>

</div>

@endsection
