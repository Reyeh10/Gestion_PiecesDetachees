@extends('layouts.layoutMaster')

@section('content')

<div class="card">

    <div class="card-header">

        <h4>Détails utilisateur</h4>

    </div>

    <div class="card-body">

        <table class="table table-bordered">

            <tr>

                <th width="250">Nom</th>

                <td>{{ $user->name }}</td>

            </tr>

            <tr>

                <th>Email</th>

                <td>{{ $user->email }}</td>

            </tr>

            <tr>

                <th>Rôle</th>

                <td>

                    <span class="badge bg-primary">

                        {{ ucfirst($user->role) }}

                    </span>

                </td>

            </tr>

            <tr>

                <th>Compte actif</th>

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

            </tr>

            <tr>

                <th>Date création</th>

                <td>

                    {{ $user->created_at }}

                </td>

            </tr>

        </table>

        <div class="mt-3">

            <a href="{{ route('users.edit', $user->id) }}"
               class="btn btn-warning">

                Modifier

            </a>

            <a href="{{ route('users.index') }}"
               class="btn btn-secondary">

                Retour

            </a>

        </div>

    </div>

</div>

@endsection
