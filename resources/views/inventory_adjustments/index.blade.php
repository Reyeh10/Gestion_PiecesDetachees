@extends('layouts.layoutMaster')

@section('content')

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">

        <h4 class="mb-0">
            Ajustements inventaire
        </h4>

        <a href="{{ route('inventory-adjustments.create') }}"
           class="btn btn-primary">

            Nouvel ajustement

        </a>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered align-middle">

                <thead>

                    <tr>

                        <th>#</th>
                        <th>Produit</th>
                        <th>Ancien stock</th>
                        <th>Nouveau stock</th>
                        <th>Différence</th>
                        <th>Raison</th>
                        <th>Date</th>
                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($adjustments as $a)

                        <tr>

                            <td>{{ $a->id }}</td>

                            <td>
                                {{ $a->product->designation ?? '-' }}
                            </td>

                            <td>{{ $a->old_qty }}</td>

                            <td>{{ $a->new_qty }}</td>

                            <td>

                                @php
                                    $diff = $a->new_qty - $a->old_qty;
                                @endphp

                                <span class="badge {{ $diff >= 0 ? 'bg-success' : 'bg-danger' }}">

                                    {{ $diff }}

                                </span>

                            </td>

                            <td>{{ $a->reason }}</td>

                            <td>

                                {{ $a->created_at->format('d/m/Y') }}

                            </td>

                            <td>

                                <div class="d-flex gap-2">

                                    {{-- VOIR --}}
                                    <a href="{{ route('inventory-adjustments.show', $a->id) }}"
                                       class="btn btn-info btn-sm">

                                        <i class="bx bx-show"></i>

                                    </a>

                                    {{-- SUPPRIMER --}}
                                    <form action="{{ route('inventory-adjustments.destroy', $a->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Supprimer ?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-danger btn-sm">

                                            <i class="bx bx-trash"></i>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8"
                                class="text-center">

                                Aucun ajustement trouvé

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
