@extends('layouts.layoutMaster')

@section('content')

<style>

    /*
    |--------------------------------------------------------------------------
    | CARD
    |--------------------------------------------------------------------------
    */

    .depots-card {

        border: none;
        border-radius: 18px;

        overflow: hidden;

        background: #fff;

        box-shadow:
            0 4px 18px rgba(0,0,0,.04);
    }

    /*
    |--------------------------------------------------------------------------
    | HEADER
    |--------------------------------------------------------------------------
    */

    .depots-header {

        padding: 25px 30px;

        border-bottom: 1px solid #f1f1f1;

        background: #fff;
    }

    .depots-title {

        font-size: 32px;
        font-weight: 700;

        color: #2c3e50;

        margin-bottom: 4px;
    }

    .depots-subtitle {

        color: #94a3b8;
        font-size: 14px;
    }

    /*
    |--------------------------------------------------------------------------
    | BUTTON
    |--------------------------------------------------------------------------
    */

    .btn-new-depot {

        background: linear-gradient(
            135deg,
            #5b8def,
            #3b82f6
        );

        border: none;

        color: white;

        padding: 12px 22px;

        border-radius: 12px;

        font-weight: 600;

        transition: .2s;
    }

    .btn-new-depot:hover {

        transform: translateY(-1px);

        color: white;

        box-shadow:
            0 8px 20px rgba(59,130,246,.25);
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    .depots-table {

        margin: 0;

        width: 100%;

        border-collapse: separate;
        border-spacing: 0 12px;
    }

    .depots-table thead th {

        border: none !important;

        background: transparent;

        color: #64748b;

        font-size: 12px;

        text-transform: uppercase;

        letter-spacing: 1px;

        font-weight: 700;

        padding: 0 20px 10px;
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE ROW
    |--------------------------------------------------------------------------
    */

    .depots-table tbody tr {

        background: #ffffff;

        transition: .2s;

        box-shadow:
            0 2px 10px rgba(0,0,0,.03);

        border-radius: 14px;
    }

    .depots-table tbody tr:hover {

        transform: translateY(-2px);

        box-shadow:
            0 8px 20px rgba(0,0,0,.05);
    }

    .depots-table tbody td {

        padding: 22px 20px;

        border: none !important;

        vertical-align: middle;

        background: white;
    }

    /*
    |--------------------------------------------------------------------------
    | ROUNDED TABLE
    |--------------------------------------------------------------------------
    */

    .depots-table tbody tr td:first-child {

        border-top-left-radius: 14px;
        border-bottom-left-radius: 14px;
    }

    .depots-table tbody tr td:last-child {

        border-top-right-radius: 14px;
        border-bottom-right-radius: 14px;
    }

    /*
    |--------------------------------------------------------------------------
    | NAME
    |--------------------------------------------------------------------------
    */

    .depot-name {

        font-weight: 700;

        color: #334155;

        font-size: 15px;
    }

    /*
    |--------------------------------------------------------------------------
    | BADGE
    |--------------------------------------------------------------------------
    */

    .badge-active {

        background: rgba(34,197,94,.12);

        color: #16a34a;

        padding: 8px 14px;

        border-radius: 30px;

        font-size: 12px;

        font-weight: 700;
    }

    .badge-inactive {

        background: rgba(239,68,68,.12);

        color: #dc2626;

        padding: 8px 14px;

        border-radius: 30px;

        font-size: 12px;

        font-weight: 700;
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIONS
    |--------------------------------------------------------------------------
    */

    .depot-actions {

        display: flex;
        align-items: center;
        gap: 10px;
    }

    .depot-actions .btn {

        width: 42px;
        height: 42px;

        border-radius: 12px;

        display: flex;
        align-items: center;
        justify-content: center;

        border: none;

        transition: .2s;
    }

    .depot-actions .btn:hover {

        transform: translateY(-2px);
    }

    /*
    |--------------------------------------------------------------------------
    | EMPTY
    |--------------------------------------------------------------------------
    */

    .empty-state {

        padding: 60px 20px;

        text-align: center;

        color: #94a3b8;
    }

</style>

    <div class="card depots-card">

        {{-- HEADER --}}
        <div class="depots-header d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>

                <div class="depots-title">

                    Liste des dépôts

                </div>

                <div class="depots-subtitle">

                    Gestion des dépôts et entrepôts

                </div>

            </div>


        @if(in_array(auth()->user()->role, [
            'admin',
            'chef_magasinier'
            ]))

            <a href="{{ route('depots.create') }}"
            class="btn btn-new-depot">

                <i class="bx bx-plus"></i>

                Nouveau dépôt

            </a>

        @endif

    </div>

    {{-- BODY --}}
    <div class="card-body p-4">

        @if(session('success'))

            <div class="alert alert-success border-0 shadow-sm">

                {{ session('success') }}

            </div>

        @endif

        <div class="table-responsive">

            <table class="table depots-table align-middle">

                <thead>

                    <tr>

                        <th>Code</th>

                        <th>Nom</th>

                        <th>Adresse</th>

                        <th>Statut</th>

                        <th width="220">Actions</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($depots as $depot)

                        <tr>

                            {{-- CODE --}}
                            
                            <td>
                                {{ $depot->code }}
                            </td>

                            {{-- NOM --}}
                            <td>

                                <div class="depot-name">

                                    {{ $depot->name }}

                                </div>

                            </td>


                            {{-- ADRESSE --}}
                            <td>

                                {{ $depot->address }}

                            </td>

                            {{-- STATUS --}}
                            <td>

                                @if($depot->is_active)

                                    <span class="badge-active">

                                        ACTIF

                                    </span>

                                @else

                                    <span class="badge-inactive">

                                        INACTIF

                                    </span>

                                @endif

                            </td>

                            {{-- ACTIONS --}}
                            <td>

                                <div class="depot-actions">

                                    {{-- SHOW --}}
                                    <a href="{{ route('depots.show', $depot) }}"
                                       class="btn btn-info text-white">

                                        <i class="bx bx-show"></i>

                                    </a>

                                   @if(in_array(auth()->user()->role, [
                                            'admin',
                                            'chef_magasinier'
                                        ]))

                                        {{-- EDIT --}}
                                        <a href="{{ route('depots.edit', $depot) }}"
                                        class="btn btn-warning text-white">

                                            <i class="bx bx-edit"></i>

                                        </a>

                                        {{-- DELETE --}}
                                        <form action="{{ route('depots.destroy', $depot) }}"
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

                            <td colspan="5">

                                <div class="empty-state">

                                    <i class="bx bx-package bx-lg mb-3"></i>

                                    <div>

                                        Aucun dépôt trouvé

                                    </div>

                                </div>

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

                title: 'Supprimer le dépôt ?',

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

                width: '420px'

            }).then((result) => {

                if (result.isConfirmed) {

                    Swal.fire({

                        title: 'Supprimé !',

                        text: 'Le dépôt a été supprimé avec succès.',

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
