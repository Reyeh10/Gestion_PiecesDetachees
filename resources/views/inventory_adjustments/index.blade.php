@extends('layouts.layoutMaster')

@section('content')

@php
    $user = auth()->user();

    /*
    |--------------------------------------------------------------------------
    | PERMISSIONS
    |--------------------------------------------------------------------------
    |
    | Admin + chef magasinier :
    | - Voir
    | - Créer
    |
    | Magasinier :
    | - Voir
    | - Créer
    |
    | Vendeur / caissier :
    | - Voir uniquement
    |
    | IMPORTANT :
    | La suppression d'un ajustement reste interdite côté contrôleur
    | afin de préserver la traçabilité.
    |
    */

    $canCreateAdjustment = in_array($user->role, [
        'admin',
        'chef_magasinier',
        'magasinier',
    ], true);
@endphp

<div class="card shadow-sm border-0">

    {{-- ============================================================
        HEADER
    ============================================================ --}}
    <div class="card-header bg-white border-bottom">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>
                <h4 class="mb-1 fw-bold">
                    Ajustements inventaire
                </h4>

                <small class="text-muted">
                    Historique des corrections de la quantité disponible
                </small>
            </div>

            @if($canCreateAdjustment)
                <a
                    href="{{ route('inventory-adjustments.create') }}"
                    class="btn btn-primary"
                >
                    <i class="bx bx-plus me-1"></i>
                    Nouvel ajustement
                </a>
            @endif

        </div>

    </div>


    <div class="card-body">

        {{-- ============================================================
            MESSAGES
        ============================================================ --}}
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


        {{-- ============================================================
            INFORMATION
        ============================================================ --}}
        <div class="alert alert-info d-flex align-items-start gap-2 mb-4">

            <i class="bx bx-info-circle fs-4 mt-1"></i>

            <div>
                <div class="fw-bold mb-1">
                    Fonctionnement
                </div>

                <div>
                    Un ajustement inventaire corrige uniquement la
                    <strong>quantité disponible</strong>.
                    Il ne doit pas modifier la
                    <strong>quantité initiale</strong>
                    ni la
                    <strong>quantité vendue</strong>.
                </div>
            </div>

        </div>


        {{-- ============================================================
            RECHERCHE
        ============================================================ --}}
        <form
            method="GET"
            action="{{ route('inventory-adjustments.index') }}"
            class="row g-3 mb-4"
        >

            <div class="col-md-8 col-lg-6">

                <label
                    for="search"
                    class="form-label fw-semibold"
                >
                    Rechercher
                </label>

                <div class="input-group">

                    <span class="input-group-text">
                        <i class="bx bx-search"></i>
                    </span>

                    <input
                        type="text"
                        id="search"
                        name="search"
                        class="form-control"
                        value="{{ request('search') }}"
                        placeholder="Référence ou désignation du produit..."
                    >

                </div>

            </div>

            <div class="col-md-auto d-flex align-items-end">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="bx bx-search me-1"></i>
                    Rechercher
                </button>

            </div>

            <div class="col-md-auto d-flex align-items-end">

                <a
                    href="{{ route('inventory-adjustments.index') }}"
                    class="btn btn-secondary"
                >
                    <i class="bx bx-reset me-1"></i>
                    Réinitialiser
                </a>

            </div>

        </form>


        {{-- ============================================================
            TABLEAU
        ============================================================ --}}
        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th
                            class="text-center"
                            style="width: 70px;"
                        >
                            #
                        </th>

                        <th>
                            Référence
                        </th>

                        <th>
                            Désignation
                        </th>

                        <th>
                            Marque
                        </th>

                        <th>
                            Modèle
                        </th>

                        <th class="text-center">
                            Qté disponible avant
                        </th>

                        <th class="text-center">
                            Qté disponible après
                        </th>

                        <th class="text-center">
                            Différence
                        </th>

                        <th class="text-center">
                            Type
                        </th>

                        <th>
                            Raison
                        </th>

                        <th>
                            Effectué par
                        </th>

                        <th>
                            Date
                        </th>

                        <th
                            class="text-center"
                            style="width: 100px;"
                        >
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($adjustments as $a)

                        @php
                            $oldQty = (float) ($a->old_qty ?? 0);
                            $newQty = (float) ($a->new_qty ?? 0);

                            $diff = round(
                                $newQty - $oldQty,
                                2
                            );

                            $unit =
                                $a->product->unit_label
                                ?? 'Pièce';

                            if ($diff > 0) {
                                $typeLabel = 'Entrée';
                                $typeClass = 'bg-success';
                                $typeIcon = 'bx-plus-circle';
                            } elseif ($diff < 0) {
                                $typeLabel = 'Sortie';
                                $typeClass = 'bg-danger';
                                $typeIcon = 'bx-minus-circle';
                            } else {
                                $typeLabel = 'Aucun';
                                $typeClass = 'bg-secondary';
                                $typeIcon = 'bx-minus';
                            }
                        @endphp


                        <tr>

                            {{-- ID --}}
                            <td class="text-center">
                                {{ $a->id }}
                            </td>


                            {{-- RÉFÉRENCE --}}
                            <td>
                                <strong>
                                    {{ $a->product->reference ?? '-' }}
                                </strong>
                            </td>


                            {{-- DÉSIGNATION --}}
                            <td>
                                {{ $a->product->designation ?? 'Produit supprimé' }}
                            </td>


                            {{-- MARQUE --}}
                            <td>
                                {{ $a->product->brand?->name ?? '-' }}
                            </td>


                            {{-- MODÈLE --}}
                            <td>
                                {{ $a->product->model?->name ?? '-' }}
                            </td>


                            {{-- AVANT --}}
                            <td class="text-center">

                                <span class="badge bg-label-secondary">

                                    {{ number_format(
                                        $oldQty,
                                        2,
                                        ',',
                                        ' '
                                    ) }}

                                    {{ $unit }}

                                </span>

                            </td>


                            {{-- APRÈS --}}
                            <td class="text-center">

                                <span class="badge bg-label-primary">

                                    {{ number_format(
                                        $newQty,
                                        2,
                                        ',',
                                        ' '
                                    ) }}

                                    {{ $unit }}

                                </span>

                            </td>


                            {{-- DIFFÉRENCE --}}
                            <td class="text-center">

                                @if($diff > 0)

                                    <span class="badge bg-success">

                                        +{{ number_format(
                                            $diff,
                                            2,
                                            ',',
                                            ' '
                                        ) }}

                                        {{ $unit }}

                                    </span>

                                @elseif($diff < 0)

                                    <span class="badge bg-danger">

                                        {{ number_format(
                                            $diff,
                                            2,
                                            ',',
                                            ' '
                                        ) }}

                                        {{ $unit }}

                                    </span>

                                @else

                                    <span class="badge bg-secondary">
                                        0 {{ $unit }}
                                    </span>

                                @endif

                            </td>


                            {{-- TYPE --}}
                            <td class="text-center">

                                <span class="badge {{ $typeClass }}">

                                    <i class="bx {{ $typeIcon }} me-1"></i>

                                    {{ $typeLabel }}

                                </span>

                            </td>


                            {{-- RAISON --}}
                            <td style="min-width: 220px;">
                                {{ $a->reason ?? '-' }}
                            </td>


                            {{-- UTILISATEUR --}}
                            <td>
                                {{ $a->approver->name ?? '-' }}
                            </td>


                            {{-- DATE --}}
                            <td style="white-space: nowrap;">
                                {{ optional($a->created_at)->format('d/m/Y H:i') }}
                            </td>


                            {{-- ACTION --}}
                            <td class="text-center">

                                <a
                                    href="{{ route(
                                        'inventory-adjustments.show',
                                        $a->id
                                    ) }}"
                                    class="btn btn-info btn-sm"
                                    title="Voir"
                                >
                                    <i class="bx bx-show"></i>
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="13"
                                class="text-center py-5 text-muted"
                            >

                                <i class="bx bx-package fs-1 d-block mb-2"></i>

                                Aucun ajustement trouvé.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- ============================================================
            PAGINATION
        ============================================================ --}}
        @if(
            method_exists($adjustments, 'links')
            && $adjustments->hasPages()
        )

            <div class="mt-3 d-flex justify-content-center">

                {{ $adjustments
                    ->withQueryString()
                    ->links()
                }}

            </div>

        @endif

    </div>

</div>

@endsection