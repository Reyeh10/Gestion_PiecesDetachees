@extends('layouts.layoutMaster')

@section('content')

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">

        <h4 class="mb-0">
            Liste des fournisseurs
        </h4>

        @if(in_array(auth()->user()->role, [
                'admin',
                'chef_magasinier'
            ]))
            <a href="{{ route('suppliers.create') }}"
            class="btn btn-primary">

                Nouveau fournisseur

            </a>
        @endif

    </div>

    <div class="card-body">

        @if(session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead>

                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <th>Adresse</th>
                        <th>Devise</th>
                        <th width="220">Actions</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse($suppliers as $supplier)

                        <tr>
                            <td>
                                {{ $supplier->code }}
                            </td>
                            <td>
                                {{ $supplier->name }}
                            </td>

                            <td>
                                {{ $supplier->phone ?? '-' }}
                            </td>

                            <td>
                                {{ $supplier->email ?? '-' }}
                            </td>

                            <td>
                                {{ $supplier->address ?? '-' }}
                            </td>

                            <td>
                                {{ $supplier->currency ?? 'XAF' }}
                            </td>

                           {{-- ACTIONS --}}
                            <td>

                                <div class="d-flex align-items-center gap-2">

                                    {{-- VOIR --}}
                                    <a href="{{ route('suppliers.show', $supplier) }}"
                                    class="btn btn-info btn-sm">

                                        <i class="bx bx-show"></i>

                                    </a>

                                    {{-- ADMIN SEULEMENT --}}
                                    @if(in_array(auth()->user()->role, ['admin', 'chef_magasinier']))

                                        {{-- EDIT --}}
                                        <a href="{{ route('suppliers.edit', $supplier) }}"
                                        class="btn btn-warning btn-sm">

                                            <i class="bx bx-edit"></i>

                                        </a>

                                        {{-- DELETE --}}
                                        <form action="{{ route('suppliers.destroy', $supplier) }}"
                                            method="POST"
                                            class="delete-form d-inline">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-danger btn-sm">

                                                <i class="bx bx-trash"></i>

                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                           <td colspan="7" class="text-center">

                                Aucun fournisseur trouvé

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

    const forms = document.querySelectorAll('.delete-form');

    forms.forEach(form => {

        form.addEventListener('submit', function (e) {

            e.preventDefault();

            Swal.fire({

                title: 'Supprimer fournisseur ?',

                text: "Cette action est irréversible.",

                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#ef4444',

                cancelButtonColor: '#6b7280',

                confirmButtonText: 'Oui, supprimer',

                cancelButtonText: 'Annuler',

                background: '#1e293b',

                color: '#fff',

                borderRadius: '15px'

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
