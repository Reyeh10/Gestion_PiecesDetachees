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
        <form
            action="{{ route('customers.index') }}"
            method="GET"
            class="mb-4"
        >

            <div class="row g-2 align-items-end">

                <div class="col-md-6">

                    <label class="form-label">

                        Rechercher un client

                    </label>

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        value="{{ request('search') }}"
                        placeholder="Code, nom, téléphone ou email..."
                    >

                </div>


                <div class="col-auto">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="bx bx-search me-1"></i>

                        Rechercher

                    </button>

                </div>


                <div class="col-auto">

                    <a
                        href="{{ route('customers.index') }}"
                        class="btn btn-outline-secondary"
                    >

                        Réinitialiser

                    </a>

                </div>

            </div>

        </form>

        <div class="table-responsive">

            <table class="table table-bordered align-middle">

                <thead class="table-light">

                    <tr>

                        <th>
                            Code
                        </th>

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
                            <td>
                                {{ $customer->code }}
                            </td>
                            
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

        {{-- PAGINATION --}}
        @if($customers->hasPages())

            <div class="mt-4">

                {{ $customers->withQueryString()->links() }}

            </div>

        @endif

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
