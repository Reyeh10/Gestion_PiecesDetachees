@extends('layouts.layoutMaster')

@section('content')

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">

        <h4>Gestion des utilisateurs</h4>

        <a href="{{ route('users.create') }}"
           class="btn btn-primary">

            Nouveau utilisateur

        </a>

    </div>

    <div class="card-body">

        @if(session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif

        @if(session('error'))

            <div class="alert alert-danger">
                {{ session('error') }}
            </div>

        @endif

        <div class="table-responsive">

            <table class="table table-bordered">

                <thead>

                    <tr>

                        <th>#</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Actif</th>
                        <th width="220">Actions</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($users as $user)

                        <tr>

                            <td>{{ $user->id }}</td>

                            <td>{{ $user->name }}</td>

                            <td>{{ $user->email }}</td>

                            <td>

                                <span class="badge bg-primary">

                                    {{ $user->getRoleLabel() }}

                                </span>

                            </td>

                            <td>

                                @if($user->is_active)

                                    <span class="badge bg-success">
                                        Oui
                                    </span>

                                @else

                                    <span class="badge bg-danger">
                                        Non
                                    </span>

                                @endif

                            </td>

                            <td class="d-flex gap-2">

                                <a href="{{ route('users.show', $user->id) }}"
                                   class="btn btn-info btn-sm">

                                    Voir

                                </a>

                                {{-- Seul un administrateur gère un compte administrateur --}}
                                @if(auth()->user()->isAdmin() || ! $user->isAdmin())

                                <a href="{{ route('users.edit', $user->id) }}"
                                   class="btn btn-warning btn-sm">

                                    Modifier

                                </a>

                                {{-- Permissions personnalisées (comme l'app Atelier) ;
                                     un non-administrateur ne modifie pas les siennes --}}
                                @if(! $user->isAdmin() && (auth()->user()->isAdmin() || $user->id !== auth()->id()))
                                    <a href="{{ route('users.permissions.edit', $user->id) }}"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bx bx-shield-quarter"></i>
                                        Permissions
                                    </a>
                                @endif

                                <form action="{{ route('users.destroy', $user->id) }}"
                                    method="POST"
                                    class="delete-form">

                                    @csrf
                                    @method('DELETE')

                                    <button type="button"
                                            class="btn btn-danger btn-sm btn-delete">

                                        <i class="bx bx-trash"></i>

                                        Supprimer

                                    </button>

                                </form>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6"
                                class="text-center">

                                Aucun utilisateur trouvé

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>
<script>

document.addEventListener('DOMContentLoaded', function () {

    const deleteButtons =
        document.querySelectorAll('.btn-delete');

    deleteButtons.forEach(button => {

        button.addEventListener('click', function () {

            const form =
                this.closest('.delete-form');

            Swal.fire({

                title: 'Supprimer utilisateur ?',

                text: "Cette action est irréversible.",

                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#d33',

                cancelButtonColor: '#6c757d',

                confirmButtonText: 'Oui, supprimer',

                cancelButtonText: 'Annuler',

                reverseButtons: true

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
