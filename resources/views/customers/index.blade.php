@extends('layouts.layoutMaster')

@section('content')

{{-- SUCCESS --}}
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

{{-- ERROR --}}
@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="card shadow-sm border-0">

    {{-- HEADER --}}
    <div class="card-header d-flex justify-content-between align-items-center">

        <h4 class="mb-0">
            Clients
        </h4>

        <a href="{{ route('customers.create') }}"
           class="btn btn-primary">

            + Nouveau client

        </a>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered align-middle">

                <thead class="table-light">

                    <tr>

                        <th>
                            Nom
                        </th>

                        <th>
                            Téléphone
                        </th>

                        <th>
                            Email
                        </th>

                        <th width="250" class="text-center">
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($customers as $customer)

                        <tr>

                            {{-- NOM --}}
                            <td>
                                {{ $customer->name }}
                            </td>

                            {{-- TELEPHONE --}}
                            <td>
                                {{ $customer->phone }}
                            </td>

                            {{-- EMAIL --}}
                            <td>
                                {{ $customer->email }}
                            </td>

                            {{-- ACTIONS --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    {{-- VOIR --}}
                                    <a href="{{ route('customers.show', $customer) }}"
                                    class="btn btn-info btn-sm">
                                        <i class="bx bx-show"></i>
                                    </a>

                                    {{-- ADMIN + CHEF MAGASINIER --}}
                                    @if(in_array(auth()->user()->role, ['admin', 'chef_magasinier']))

                                        {{-- EDIT --}}
                                        <a href="{{ route('customers.edit', $customer) }}"
                                        class="btn btn-warning btn-sm">

                                            Modifier

                                        </a>

                                        {{-- DELETE --}}
                                        <form action="{{ route('customers.destroy', $customer) }}"
                                            method="POST"
                                            class="delete-form d-inline">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-danger btn-sm">

                                                Supprimer

                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="4"
                                class="text-center py-4">

                                Aucun client trouvé.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>
<script>

document.querySelectorAll('.delete-form').forEach(form => {

    form.addEventListener('submit', function(e) {

        e.preventDefault();

        Swal.fire({

            title: 'Supprimer ce client ?',

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

</script>
@endsection
