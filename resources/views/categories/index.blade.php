@extends('layouts.layoutMaster')

@section('content')

<div class="row">

    <div class="col-12">

        <div class="card border-0 shadow-sm rounded-4">

            {{-- HEADER --}}
            <div class="card-header bg-white border-0 py-4">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                    <div>

                        <h2 class="fw-bold mb-1 text-dark">
                            Familles & Sous-familles
                        </h2>

                        <p class="text-muted mb-0">
                            Gestion des catégories de produits
                        </p>

                    </div>

                    {{-- BTN --}}
                    @if(in_array(auth()->user()->role, [
                            'admin',
                            'chef_magasinier'
                        ]))
                        <a href="{{ route('families.create') }}"
                        class="btn btn-primary rounded-pill px-4 shadow-sm">

                            <i class="bx bx-plus"></i>

                            Nouvelle famille

                        </a>
                    @endif
                </div>

            </div>

            {{-- BODY --}}
            <div class="card-body">

                {{-- SEARCH --}}
                <form method="GET"
                      action="{{ route('categories.index') }}"
                      class="mb-4">

                    <div class="row">

                        <div class="col-md-5">

                            <div class="input-group shadow-sm">

                                <span class="input-group-text bg-light border-0">

                                    <i class="bx bx-search"></i>

                                </span>

                                <input type="text"
                                       name="search"
                                       class="form-control border-0 bg-light"
                                       placeholder="Rechercher une famille ou sous-famille..."
                                       value="{{ request('search') }}">

                            </div>

                        </div>

                    </div>

                </form>

                {{-- TABLE --}}
                <div class="table-responsive">

                    <table class="table align-middle table-hover">

                        <thead class="table-light">

                            <tr>

                                <th width="80">
                                    #
                                </th>

                                <th>
                                    Famille
                                </th>

                                <th>
                                    Sous-familles
                                </th>

                                <th width="170">
                                    Total
                                </th>

                                <th width="220"
                                    class="text-center">

                                    Actions

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($families as $family)

                                <tr>

                                    {{-- ID --}}
                                    <td>

                                        <span class="fw-bold text-muted">

                                            #{{ $family->id }}

                                        </span>

                                    </td>

                                    {{-- FAMILY --}}
                                    <td>

                                        <div class="d-flex align-items-center gap-3">

                                            <div class="avatar-sm bg-label-primary rounded">

                                                <div class="avatar-title">

                                                    <i class="bx bx-category-alt"></i>

                                                </div>

                                            </div>

                                            <div>

                                                <h6 class="mb-0 fw-bold text-dark">

                                                    {{ $family->name }}

                                                </h6>

                                            </div>

                                        </div>

                                    </td>

                                    {{-- SUBFAMILIES --}}
                                    <td>

                                        <div class="d-flex flex-wrap gap-2">

                                            @forelse($family->subfamilies as $sub)

                                                <span class="badge bg-label-primary px-3 py-2">

                                                    {{ $sub->name }}

                                                </span>

                                            @empty

                                                <span class="text-muted">

                                                    Aucune sous-famille

                                                </span>

                                            @endforelse

                                        </div>

                                    </td>

                                    {{-- TOTAL --}}
                                    <td>

                                        <span class="badge bg-success px-3 py-2">

                                            {{ $family->subfamilies->count() }}

                                            sous-famille(s)

                                        </span>

                                    </td>

                                  {{-- ACTIONS --}}
                                    <td>

                                        <div class="d-flex justify-content-center gap-2">

                                            {{-- VOIR --}}
                                            <a href="{{ route('families.show', $family->id) }}"
                                            class="btn btn-info btn-sm rounded-pill shadow-sm">

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
                                                <a href="{{ route('families.edit', $family->id) }}"
                                                class="btn btn-warning btn-sm rounded-pill shadow-sm">

                                                    <i class="bx bx-edit"></i>

                                                </a>

                                                {{-- SUPPRIMER --}}
                                                <form
                                                    action="{{ route('families.destroy', $family->id) }}"
                                                    method="POST"
                                                    class="delete-form">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="btn btn-danger btn-sm rounded-pill shadow-sm">

                                                        <i class="bx bx-trash"></i>

                                                    </button>

                                                </form>

                                            @endif

                                        </div>

                                    </td>
                                </tr>

                            @empty

                                <tr>

                                    <td colspan="5"
                                        class="text-center py-5">

                                        <div class="d-flex flex-column align-items-center">

                                            <i class="bx bx-category display-3 text-muted mb-3"></i>

                                            <h5 class="text-muted mb-1">

                                                Aucune famille trouvée

                                            </h5>

                                            <p class="text-muted">

                                                Essayez une autre recherche.

                                            </p>

                                        </div>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                {{-- PAGINATION --}}
                <div class="mt-4">

                    {{ $families->links() }}

                </div>

            </div>

        </div>

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

                title: 'Supprimer ?',

                text: "Cette action est irréversible !",

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
