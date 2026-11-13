@extends('layouts.layoutMaster')

@section('title', 'Fiches d\'inventaire')

@section('content')

<h4 class="mb-1">Fiches d'inventaire</h4>
<p class="text-muted mb-4">Imprimez la liste d'un rayon, faites compter, saisissez le comptage puis validez les écarts.</p>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body">

        @if(auth()->user()->hasPermission('gerer_ajustements'))
            <a href="{{ route('inventaires.create') }}" class="btn btn-primary mb-3"><i class="bx bx-plus"></i> Nouvelle fiche</a>
        @endif

        <table class="table align-middle">
            <thead>
                <tr>
                    <th>N° fiche</th>
                    <th>Dépôt</th>
                    <th>Périmètre</th>
                    <th>Comptage</th>
                    <th>Statut</th>
                    <th>Créée le</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventaires as $inv)
                    <tr>
                        <td><strong>{{ $inv->numero }}</strong></td>
                        <td>{{ $inv->depot->name ?? '—' }}</td>
                        <td>{{ $inv->perimetre }}</td>
                        <td>{{ $inv->comptees_count }} / {{ $inv->lignes_count }}</td>
                        <td>
                            <span class="badge {{ $inv->statut === 'valide' ? 'bg-success' : 'bg-warning' }}">{{ $inv->getStatutLabel() }}</span>
                        </td>
                        <td>{{ $inv->created_at->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('inventaires.imprimer', $inv) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Imprimer"><i class="bx bx-printer"></i></a>
                            <a href="{{ route('inventaires.show', $inv) }}" class="btn btn-sm btn-primary" title="Voir"><i class="bx bx-show"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucune fiche d'inventaire pour le moment.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $inventaires->links() }}
    </div>
</div>

@endsection
