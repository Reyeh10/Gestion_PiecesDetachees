@extends('layouts.layoutMaster')

@section('content')

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">

        <h4 class="mb-0">Liste des achats</h4>

        <a href="{{ route('purchases.create') }}"
           class="btn btn-primary">
            Générer un achat
        </a>

    </div>

    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive">

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Fournisseur</th>
                        <th>Produits</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Créé par</th>
                        <th>Date</th>
                    </tr>
                </thead>

                 <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td>
                                {{ $purchase->id }}
                            </td>

                            <td>

                                {{ $purchase->supplier->name ?? 'N/A' }}

                            </td>

                            <td>

                                {{ $purchase->items->count() }}

                            </td>

                            <td>

                                {{ number_format(
                                    $purchase->total,
                                    2,
                                    ',',
                                    ' '
                                ) }} $

                            </td>

                            <td>

                                <span class="badge bg-success">

                                    {{ ucfirst($purchase->status) }}

                                </span>

                            </td>

                            <td>

                                {{ $purchase->user->name ?? '-' }}

                            </td>
                            <td>
                                {{ $purchase->created_at
                                    ->format('d/m/Y H:i') }}
                            </td>
                        </tr>

                        @empty

                        <tr>
                            <td colspan="7"
                                class="text-center">
                                Aucun achat trouvé
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
