@extends('layouts.layoutMaster')

@section('title', 'Import sorties bus')

@section('content')

@php $qte = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ' '), '0'), ','); @endphp

<h4 class="mb-1">Aperçu de l'import — sorties bus</h4>
<p class="text-muted mb-3">Rien n'est encore enregistré. Un bon de transfert sera créé par bus.</p>

@if($erreurs)
    <div class="alert alert-danger">
        <strong>Le fichier contient {{ count($erreurs) }} erreur(s) : corrigez-le puis refaites l'aperçu.</strong>
        <ul class="mb-0 mt-2">@foreach($erreurs as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

@foreach($groupes as $g)
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">
                {{ $g['bus']->immatriculation }}
                <span style="padding:2px 8px; border-radius:6px; font-size:12px; font-weight:700; {{ $g['bus']->groupe === 'bus' ? 'color:#b45309; background:#fff3e0;' : 'color:#0e7490; background:#e0f7fa;' }}">{{ $g['bus']->groupe_label }}</span>
                <span class="text-muted fs-6">{{ $g['bus']->vehicule }}</span>
            </h5>
            <table class="table table-sm align-middle no-cartes mb-0">
                <thead><tr><th>Ligne</th><th>Référence</th><th>Désignation</th><th>Dépôt</th><th class="text-end">Qté</th><th class="text-end">Prix HT</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                    @foreach($g['lignes'] as $l)
                        <tr>
                            <td>{{ $l['ligne'] }}</td>
                            <td><strong>{{ $l['reference'] }}</strong></td>
                            <td>{{ $l['designation'] }}</td>
                            <td>{{ $l['depot'] }}</td>
                            <td class="text-end">{{ $qte($l['quantite']) }}</td>
                            <td class="text-end">{{ number_format($l['prix'], 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($l['prix'] * $l['quantite'], 0, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach

<div class="d-flex gap-2">
    <a href="{{ route('bus.index') }}" class="btn btn-secondary">Retour</a>
    @if(! $erreurs && $groupes)
        <form method="POST" action="{{ route('bus.import.store') }}">
            @csrf
            <button type="submit" class="btn btn-success"><i class="bx bx-check"></i> Créer {{ count($groupes) }} bon(s) de transfert</button>
        </form>
    @endif
</div>

@endsection
