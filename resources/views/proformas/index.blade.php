@extends('layouts.layoutMaster')

@section('content')

<div class="card border-0 shadow-sm rounded-4">

    {{-- HEADER --}}
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-4">

        <div>
            <h3 class="fw-bold mb-1 text-dark">
                Liste des proformas
            </h3>

            <small class="text-muted">
                Gestion des proformas et devis clients
            </small>
        </div>

        <a href="{{ route('proformas.create') }}"
           class="btn btn-primary rounded-pill px-4 shadow-sm">

            <i class="bx bx-plus"></i>

            Nouveau proforma

        </a>

    </div>

    {{-- BODY --}}
    <div class="card-body">

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))

            <div class="alert alert-success alert-dismissible fade show">

                {{ session('success') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"></button>

            </div>

        @endif

        {{-- SEARCH BAR --}}
       <form method="GET"
            action="{{ route('proformas.index') }}"
            class="row g-2 align-items-center mb-4">

            {{-- CLIENT --}}
            <div class="col-md-3">

                <div class="input-group shadow-sm">

                    <span class="input-group-text bg-light border-0">
                        <i class="bx bx-user text-primary"></i>
                    </span>

                    <input type="text"
                        name="client"
                        class="form-control border-0 bg-light"
                        placeholder="Recherche client..."
                        value="{{ request('client') }}">

                </div>

            </div>

            {{-- REFERENCE --}}
            <div class="col-md-2">

                <div class="input-group shadow-sm">

                    <span class="input-group-text bg-light border-0">
                        <i class="bx bx-barcode text-primary"></i>
                    </span>

                    <input type="text"
                        name="reference"
                        class="form-control border-0 bg-light"
                        placeholder="Référence..."
                        value="{{ request('reference') }}">

                </div>

            </div>

            {{-- DESIGNATION --}}
            <div class="col-md-3">

                <div class="input-group shadow-sm">

                    <span class="input-group-text bg-light border-0">
                        <i class="bx bx-package text-primary"></i>
                    </span>

                    <input type="text"
                        name="designation"
                        class="form-control border-0 bg-light"
                        placeholder="Désignation..."
                        value="{{ request('designation') }}">

                </div>

            </div>

            {{-- DATE --}}
            <div class="col-md-2">

                <input type="date"
                    name="date"
                    class="form-control bg-light border-0 shadow-sm"
                    value="{{ request('date') }}">

            </div>

            {{-- BUTTON SEARCH --}}
            <div class="col-md-1">

                <button type="submit"
                        class="btn btn-primary w-100 fw-bold shadow-sm"
                        style="height:42px;border-radius:10px;">

                    Rechercher

                </button>

            </div>

            {{-- BUTTON RESET --}}
            <div class="col-md-1">

                <a href="{{ route('proformas.index') }}"
                class="btn btn-secondary w-100 fw-bold shadow-sm d-flex align-items-center justify-content-center"
                style="height:42px;border-radius:10px;">

                    Reset

                </a>

            </div>

        </form>

        {{-- TABLE --}}
        <div class="table-responsive">

            <table class="table align-middle table-hover">

                <thead class="bg-light">

                    <tr>

                        <th class="fw-bold text-uppercase small">
                            Client
                        </th>

                        <th class="fw-bold text-uppercase small">
                            Référence
                        </th>

                        <th class="fw-bold text-uppercase small">
                            Désignation
                        </th>

                        <th class="fw-bold text-uppercase small text-end">
                            Total
                        </th>

                        <th class="fw-bold text-uppercase small">
                            Date
                        </th>

                        <th width="180"
                            class="fw-bold text-uppercase small text-center">

                            Actions

                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($sales as $proforma)

                        <tr class="border-bottom">

                            {{-- CLIENT --}}
                            <td class="fw-semibold">

                                {{ $proforma->customer->name ?? 'Vente comptoir' }}

                            </td>

                            {{-- REFERENCE --}}
                            <td>

                                <span class="badge bg-label-primary fs-6">

                                    {{ $proforma->invoice_number }}

                                </span>

                            </td>

                            {{-- DESIGNATION --}}
                            <td>

                                @if($proforma->items->count())

                                    <div class="fw-semibold text-dark">

                                        {{ $proforma->items->first()->product->designation ?? '-' }}

                                    </div>

                                    @if($proforma->items->count() > 1)

                                        <small class="text-muted">

                                            + {{ $proforma->items->count() - 1 }}
                                            autre(s) produit(s)

                                        </small>

                                    @endif

                                @else

                                    -

                                @endif

                            </td>

                            {{-- TOTAL --}}
                            <td class="text-end fw-bold text-success">

                                {{ number_format($proforma->total, 2, ',', ' ') }} $

                            </td>

                            {{-- DATE --}}
                            <td>

                                {{ optional($proforma->created_at)->format('d/m/Y H:i') }}

                            </td>

                            {{-- ACTIONS --}}
                            <td>

                                <div class="d-flex align-items-center gap-2">

                                    {{-- VOIR --}}
                                    <a href="{{ route('proformas.show', $proforma) }}"
                                    class="btn btn-info btn-sm">

                                        <i class="bx bx-show"></i>

                                    </a>

                                    {{-- ADMIN + CHEF MAGASINIER --}}
                                    @if(in_array(auth()->user()->role, ['admin', 'chef_magasinier']))

                                        {{-- EDIT --}}
                                        <a href="{{ route('proformas.edit', $proforma) }}"
                                        class="btn btn-warning btn-sm">

                                            <i class="bx bx-edit"></i>

                                        </a>

                                        {{-- DELETE --}}
                                        <form action="{{ route('proformas.destroy', $proforma) }}"
                                            method="POST"
                                            class="delete-form d-inline">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="confirmDelete(event)">

                                                <i class="bx bx-trash"></i>

                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>

                            <td colspan="6"
                                class="text-center text-muted py-5">

                                <i class="bx bx-file display-6 d-block mb-2"></i>

                                Aucun proforma trouvé

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

function confirmDelete(event)
{
    event.preventDefault();

    let form = event.target.closest('form');

    Swal.fire({

        title: 'Supprimer le proforma ?',

        text: "Cette action est irréversible.",

        icon: 'warning',

        showCancelButton: true,

        confirmButtonColor: '#d33',

        cancelButtonColor: '#6c757d',

        confirmButtonText: 'Oui, supprimer',

        cancelButtonText: 'Annuler',

        background: '#fff',

        color: '#333',

    }).then((result) => {

        if (result.isConfirmed)
        {
            form.submit();
        }

    });
}

</script>

@endsection
