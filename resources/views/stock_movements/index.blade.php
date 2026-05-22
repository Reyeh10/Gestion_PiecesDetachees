@extends('layouts.layoutMaster')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            Mouvements de stock
        </h4>
    </div>

    <div class="card-body">
        {{-- SEARCH BAR --}}
        <form method="GET"
            action="{{ route('stock-movements.index') }}"
            class="row g-3 align-items-center mb-4">

            {{-- REFERENCE --}}
            <div class="col-md-3">

                <div class="input-group shadow-sm">

                    <span class="input-group-text bg-light border-0">
                        <i class="bx bx-barcode text-primary"></i>
                    </span>

                    <input type="text"
                        name="reference"
                        class="form-control border-0 bg-light"
                        placeholder="Recherche référence..."
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
                        placeholder="Recherche désignation..."
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
        <div class="col-md-2">

        <button type="submit"
                class="btn btn-primary w-100 fw-bold shadow-sm"
                style="
                    border-radius: 12px;
                    height: 48px;
                    font-size: 14px;
                    background: linear-gradient(135deg, #4f8cff, #2563eb);
                    border: none;
                    transition: 0.3s;
                "
                onmouseover="this.style.transform='translateY(-2px)'"
                onmouseout="this.style.transform='translateY(0px)'">

            Rechercher

        </button>

    </div>

    <div class="col-md-2">

        <a href="{{ route('stock-movements.index') }}"
        class="btn w-100 fw-bold shadow-sm text-white"
        style="
                border-radius: 12px;
                height: 48px;
                line-height: 34px;
                font-size: 14px;
                background: linear-gradient(135deg, #64748b, #475569);
                border: none;
                transition: 0.3s;
        "
        onmouseover="this.style.transform='translateY(-2px)'"
        onmouseout="this.style.transform='translateY(0px)'">

            Réinitialiser

        </a>

        </div>

        </form>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">

                <thead>

                        <tr>

                            <th>
                                Référence
                            </th>

                            <th>
                                Désignation
                            </th>

                            <th>
                                Source
                            </th>

                            <th>
                                Quantité
                            </th>

                            <th>
                                Type
                            </th>

                            <th>
                                Date
                            </th>

                            <th width="140">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($movements as $movement)

                            <tr>

                                {{-- REFERENCE --}}
                                <td class="fw-semibold">

                                    {{ $movement->reference ?? '-' }}

                                </td>

                                {{-- DESIGNATION --}}
                                <td>

                                    {{ $movement->product->designation ?? '-' }}

                                </td>

                                {{-- SOURCE --}}
                                <td>

                                    {{ $movement->source ?? '-' }}

                                </td>

                                {{-- QUANTITE --}}
                                <td>

                                    <span class="fw-bold">

                                        {{ number_format($movement->quantity, 2) }}

                                    </span>

                                </td>

                                {{-- TYPE --}}
                                <td>

                                    @if($movement->type == 'in')

                                        <span class="badge bg-success">

                                            Entrée

                                        </span>

                                    @elseif($movement->type == 'out')

                                        <span class="badge bg-danger">

                                            Sortie

                                        </span>

                                    @else

                                        <span class="badge bg-secondary">

                                            {{ strtoupper($movement->type) }}

                                        </span>

                                    @endif

                                </td>

                                {{-- DATE --}}
                                <td>

                                    {{ $movement->created_at->format('d/m/Y') }}

                                </td>

                                {{-- ACTIONS --}}
                                <td>

                                    <div class="d-flex align-items-center gap-2">

                                        {{-- SHOW --}}
                                        <a href="{{ route('stock-movements.show', $movement) }}"
                                        class="btn btn-info btn-sm text-white">

                                            <i class="bx bx-show"></i>

                                        </a>

                                        {{-- ADMIN + CHEF MAGASINIER --}}
                                        @if(in_array(auth()->user()->role, ['admin', 'chef_magasinier']))

                                            {{-- DELETE --}}
                                            <form action="{{ route('stock-movements.destroy', $movement) }}"
                                                method="POST"
                                                class="delete-form d-inline">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
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

                                <td colspan="7"
                                    class="text-center py-5 text-muted">

                                    Aucun mouvement trouvé.

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

document.addEventListener('DOMContentLoaded', function () {

    const forms = document.querySelectorAll('.delete-form');

    forms.forEach(form => {

        form.addEventListener('submit', function (e) {

            e.preventDefault();

            Swal.fire({

                title: 'Supprimer le mouvement ?',

                text: "Cette action est irréversible.",

                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#ef4444',

                cancelButtonColor: '#64748b',

                confirmButtonText: 'Oui, supprimer',

                cancelButtonText: 'Annuler',

                background: '#0f172a',

                color: '#ffffff',

                borderRadius: '18px',

                width: '420px',

                backdrop: `
                    rgba(15,23,42,0.75)
                `

            }).then((result) => {

                if (result.isConfirmed) {

                    Swal.fire({

                        title: 'Supprimé !',

                        text: 'Le mouvement a été supprimé avec succès.',

                        icon: 'success',

                        confirmButtonColor: '#2563eb',

                        background: '#0f172a',

                        color: '#ffffff',

                        timer: 1500,

                        showConfirmButton: false

                    });

                    setTimeout(() => {

                        form.submit();

                    }, 1200);
                }
            });
        });
    });

});

</script>
@endsection
