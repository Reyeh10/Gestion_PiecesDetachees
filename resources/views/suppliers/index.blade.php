@extends('layouts.layoutMaster')

@section('content')

@php
    $user = auth()->user();

    /*
    |--------------------------------------------------------------------------
    | DROITS
    |--------------------------------------------------------------------------
    */

    $canCreateSupplier = $user && in_array(
        $user->role,
        [
            'admin',
            'chef_magasinier',
            'magasinier',
            'vendeur',
            'caissier',
        ],
        true
    );

    $canEditSupplier = $user && in_array(
        $user->role,
        [
            'admin',
            'chef_magasinier',
        ],
        true
    );

    $canDeleteSupplier = $user && in_array(
        $user->role,
        [
            'admin',
            'chef_magasinier',
        ],
        true
    );
@endphp


<div class="card">

    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}

    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">

        <div>

            <h4 class="mb-1">
                Liste des fournisseurs
            </h4>

            <small class="text-muted">
                Gestion et consultation des fournisseurs
            </small>

        </div>


        {{-- ===================================================== --}}
        {{-- NOUVEAU FOURNISSEUR --}}
        {{-- ===================================================== --}}

        @if($canCreateSupplier)

            <a
                href="{{ route('suppliers.create') }}"
                class="btn btn-primary"
            >

                <i class="bx bx-plus me-1"></i>

                Nouveau fournisseur

            </a>

        @endif

    </div>


    <div class="card-body">

        {{-- ========================================================= --}}
        {{-- MESSAGES --}}
        {{-- ========================================================= --}}

        @if(session('success'))

            <div
                class="alert alert-success alert-dismissible fade show"
                role="alert"
            >

                <i class="bx bx-check-circle me-1"></i>

                {{ session('success') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Fermer"
                ></button>

            </div>

        @endif


        @if(session('error'))

            <div
                class="alert alert-danger alert-dismissible fade show"
                role="alert"
            >

                <i class="bx bx-error-circle me-1"></i>

                {{ session('error') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Fermer"
                ></button>

            </div>

        @endif


        @if(session('info'))

            <div
                class="alert alert-info alert-dismissible fade show"
                role="alert"
            >

                <i class="bx bx-info-circle me-1"></i>

                {{ session('info') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Fermer"
                ></button>

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- TABLEAU --}}
        {{-- ========================================================= --}}

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th style="min-width: 120px;">
                            Code
                        </th>

                        <th style="min-width: 160px;">
                            Nom
                        </th>

                        <th style="min-width: 150px;">
                            Téléphone
                        </th>

                        <th style="min-width: 220px;">
                            Email
                        </th>

                        <th style="min-width: 260px;">
                            Adresse
                        </th>

                        <th style="min-width: 100px;">
                            Devise
                        </th>

                        <th
                            class="text-center"
                            style="min-width: 180px;"
                        >
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($suppliers as $supplier)

                        <tr>

                            {{-- CODE --}}
                            <td class="fw-semibold">

                                {{ $supplier->code ?? '-' }}

                            </td>


                            {{-- NOM --}}
                            <td>

                                {{ $supplier->name }}

                            </td>


                            {{-- TÉLÉPHONE --}}
                            <td>

                                {{ $supplier->phone ?? '-' }}

                            </td>


                            {{-- EMAIL --}}
                            <td>

                                {{ $supplier->email ?? '-' }}

                            </td>


                            {{-- ADRESSE --}}
                            <td>

                                {{ $supplier->address ?? '-' }}

                            </td>


                            {{-- DEVISE --}}
                            <td>

                                <span class="badge bg-label-primary">

                                    {{ $supplier->currency ?? 'FDJ' }}

                                </span>

                            </td>


                            {{-- ================================================= --}}
                            {{-- ACTIONS --}}
                            {{-- ================================================= --}}

                            <td class="text-center">

                                <div class="d-flex justify-content-center align-items-center gap-2">

                                    {{-- VOIR --}}
                                    <a
                                        href="{{ route('suppliers.show', $supplier) }}"
                                        class="btn btn-info btn-sm"
                                        title="Voir"
                                    >

                                        <i class="bx bx-show"></i>

                                    </a>


                                    {{-- MODIFIER --}}
                                    @if($canEditSupplier)

                                        <a
                                            href="{{ route('suppliers.edit', $supplier) }}"
                                            class="btn btn-warning btn-sm"
                                            title="Modifier"
                                        >

                                            <i class="bx bx-edit"></i>

                                        </a>

                                    @endif


                                    {{-- SUPPRIMER --}}
                                    @if($canDeleteSupplier)

                                        <form
                                            action="{{ route('suppliers.destroy', $supplier) }}"
                                            method="POST"
                                            class="delete-form d-inline"
                                        >

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-danger btn-sm"
                                                title="Supprimer"
                                            >

                                                <i class="bx bx-trash"></i>

                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="text-center text-muted py-5"
                            >

                                <i
                                    class="bx bx-building-house d-block mb-2"
                                    style="font-size: 38px;"
                                ></i>

                                Aucun fournisseur trouvé

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- ========================================================= --}}
        {{-- PAGINATION --}}
        {{-- ========================================================= --}}

        @if(
            method_exists($suppliers, 'hasPages')
            && $suppliers->hasPages()
        )

            <div class="mt-4">

                {{ $suppliers->links() }}

            </div>

        @endif

    </div>

</div>


{{-- ============================================================= --}}
{{-- SWEETALERT SUPPRESSION --}}
{{-- ============================================================= --}}

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const forms =
            document.querySelectorAll(
                '.delete-form'
            );

        forms.forEach(
            function (form) {

                form.addEventListener(
                    'submit',
                    function (e) {

                        e.preventDefault();


                        /*
                        |--------------------------------------------------------------------------
                        | SI SWEETALERT N'EST PAS CHARGÉ
                        |--------------------------------------------------------------------------
                        */

                        if (
                            typeof Swal ===
                            'undefined'
                        ) {

                            const confirmation =
                                window.confirm(
                                    'Voulez-vous vraiment supprimer ce fournisseur ?'
                                );

                            if (confirmation) {

                                form.submit();

                            }

                            return;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | SWEETALERT
                        |--------------------------------------------------------------------------
                        */

                        Swal.fire({

                            title:
                                'Supprimer le fournisseur ?',

                            text:
                                'Cette action est irréversible.',

                            icon:
                                'warning',

                            showCancelButton:
                                true,

                            confirmButtonColor:
                                '#ef4444',

                            cancelButtonColor:
                                '#6b7280',

                            confirmButtonText:
                                'Oui, supprimer',

                            cancelButtonText:
                                'Annuler',

                            reverseButtons:
                                true

                        }).then(
                            function (result) {

                                if (
                                    result.isConfirmed
                                ) {

                                    form.submit();

                                }

                            }
                        );

                    }
                );

            }
        );

    }
);

</script>

@endsection