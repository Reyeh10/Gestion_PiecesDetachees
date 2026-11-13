@extends('layouts.layoutMaster')

@section('title', 'Bons de livraison')

@section('content')

<h4 class="mb-1">Bons de livraison</h4>
<p class="text-muted mb-4">Pièces remises aux clients après facturation (avec ou sans livraison complète).</p>

<div class="card">
    <div class="card-body">

        <form method="GET" action="{{ route('bons-livraison.index') }}" class="d-flex gap-2 mb-3">
            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="N° BL, n° facture, client ou « reçu par »…">
            <button type="submit" class="btn btn-primary"><i class="bx bx-search"></i> Rechercher</button>
            @if($search !== '')
                <a href="{{ route('bons-livraison.index') }}" class="btn btn-secondary">Réinitialiser</a>
            @endif
        </form>

        <table class="table align-middle">
            <thead>
                <tr>
                    <th>N° BL</th>
                    <th>Facture</th>
                    <th>Client</th>
                    <th>Pièces</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bons as $bl)
                    <tr>
                        <td><strong>{{ $bl->numero }}</strong></td>
                        <td>{{ $bl->sale->invoice_number ?? '—' }}</td>
                        <td>{{ $bl->sale->customer->name ?? '—' }}</td>
                        <td>{{ $bl->lignes_count }} ligne(s)</td>
                        <td>{{ $bl->livre_le->format('d/m/Y') }}</td>
                        <td>
                            @if($bl->estAnnule())
                                <span class="badge bg-danger">Annulé</span>
                            @else
                                <span class="badge bg-success">Livré</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('bons-livraison.imprimer', $bl) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Imprimer"><i class="bx bx-printer"></i></a>
                            <a href="{{ route('bons-livraison.show', $bl) }}" class="btn btn-sm btn-primary" title="Voir"><i class="bx bx-show"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Aucun bon de livraison pour le moment.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $bons->links() }}
    </div>
</div>

@endsection
