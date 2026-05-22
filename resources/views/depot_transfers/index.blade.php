@extends('layouts.layoutMaster')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Transferts entre dépôts</h4>

        @if(in_array(auth()->user()->role, [
                'admin',
                'chef_magasinier'
            ]))
            <a href="{{ route('depot-transfers.create') }}" class="btn btn-primary">
                Nouveau transfert
            </a>
        @endif
    </div>

    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Produit</th>
                    <th>Dépôt source</th>
                    <th>Dépôt destination</th>
                    <th>Quantité</th>
                    <th>Utilisateur</th>
                    <th>Note</th>
                    <th class="text-center">
                        Actions
                    </th>
                </tr>
            </thead>

            <tbody>
                @forelse($transfers as $transfer)
                    <tr>
                        <td>{{ $transfer->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $transfer->product->designation ?? $transfer->product->name ?? '-' }}</td>
                        <td>{{ $transfer->sourceDepot->name ?? '-' }}</td>
                        <td>{{ $transfer->destinationDepot->name ?? '-' }}</td>
                        <td>{{ $transfer->quantity }}</td>
                        <td>{{ $transfer->user->name ?? '-' }}</td>
                        <td>{{ $transfer->note ?? '-' }}</td>
                        <td class="text-center">

                            <div class="d-flex justify-content-center gap-2">

                                {{-- VOIR --}}
                                <a href="{{ route('depot-transfers.show', $transfer->id) }}"
                                class="btn btn-info btn-sm rounded-pill">

                                    <i class="bx bx-show"></i>

                                </a>

                                {{-- ADMIN + CHEF MAGASINIER --}}
                                @if(
                                    in_array(auth()->user()->role, [
                                        'admin',
                                        'chef_magasinier'
                                    ])
                                )

                                    {{-- MODIFIER --}}
                                    <a href="{{ route('depot-transfers.edit', $transfer->id) }}"
                                    class="btn btn-warning btn-sm rounded-pill">

                                        <i class="bx bx-edit"></i>

                                    </a>

                                    {{-- SUPPRIMER --}}
                                    <form
                                        action="{{ route('depot-transfers.destroy', $transfer->id) }}"
                                        method="POST"
                                        class="delete-form">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-danger btn-sm rounded-pill">

                                            <i class="bx bx-trash"></i>

                                        </button>

                                    </form>

                                @endif

                            </div>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            Aucun transfert trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const forms = document.querySelectorAll('.delete-form');

    forms.forEach(form => {

        form.addEventListener('submit', function (e) {

            e.preventDefault();

            Swal.fire({

                title: 'Supprimer le transfert ?',

                text: "Cette action est irréversible.",

                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#d33',

                cancelButtonColor: '#3085d6',

                confirmButtonText: 'Oui, supprimer',

                cancelButtonText: 'Annuler'

            }).then((result) => {

                if (result.isConfirmed) {

                    form.submit();

                }

            });

        });

    });

});

</script>
@endsection
