@extends('layouts.layoutMaster')

@section('title', 'Ajustements inventaire')

@section('content')

@php

    /*

    *--------------------------------------------------------------------------

    * UTILISATEUR & PERMISSIONS

    *--------------------------------------------------------------------------

    */
    $user = auth()->user();

    $canCreateAdjustment = in_array(

        $user->role,

        [

            'admin',

            'chef_magasinier',

        ],

        true

    );

    $canImportAdjustment = in_array(

        $user->role,

        [

            'admin',

            'chef_magasinier',

        ],

        true

    );


    /*
    |--------------------------------------------------------------------------
    | MODIFICATION / SUPPRESSION
    |--------------------------------------------------------------------------
    |
    | Règle métier :
    | - admin : peut modifier et supprimer
    | - autres rôles : consultation uniquement
    |
    */
    $canEditAdjustment =
        $user
        && $user->role === 'admin';

    $canDeleteAdjustment =
        $user
        && $user->role === 'admin';

@endphp

<style>

    /*

    *--------------------------------------------------------------------------

    * TABLEAU RESPONSIVE

    *--------------------------------------------------------------------------

    */
    .inventory-adjustments-wrapper {

        width: 100%;

        overflow-x: auto;

        -webkit-overflow-scrolling: touch;

    }

    .inventory-adjustments-table {

        width: 100%;

        min-width: 1180px;

        margin-bottom: 0;

    }

    .inventory-adjustments-table th,

    .inventory-adjustments-table td {

        vertical-align: middle;

    }

    .inventory-adjustments-table th {

        white-space: nowrap;

    }

    .inventory-adjustments-table .col-id {

        width: 60px;

        min-width: 60px;

    }

    .inventory-adjustments-table .col-reference {

        min-width: 145px;

    }

    .inventory-adjustments-table .col-designation {

        min-width: 210px;

        max-width: 260px;

    }

    .inventory-adjustments-table .col-depot {

        min-width: 140px;

    }

    .inventory-adjustments-table .col-rayon {

        min-width: 110px;

    }

    .inventory-adjustments-table .col-location {

        min-width: 135px;

    }

    .inventory-adjustments-table .col-qty {

        min-width: 120px;

        white-space: nowrap;

    }

    .inventory-adjustments-table .col-type {

        min-width: 95px;

        white-space: nowrap;

    }

    .inventory-adjustments-table .col-reason {

        min-width: 180px;

        max-width: 230px;

    }

    .inventory-adjustments-table .col-user {

        min-width: 135px;

    }

    .inventory-adjustments-table .col-date {

        min-width: 145px;

        white-space: nowrap;

    }

    .inventory-adjustments-table .col-action {

        width: 155px;

        min-width: 155px;

        white-space: nowrap;

    }

    .inventory-action-buttons {

        display: flex;

        align-items: center;

        justify-content: center;

        gap: 6px;

        flex-wrap: nowrap;

    }

    .inventory-action-buttons form {

        display: inline-block;

        margin: 0;

    }

    .inventory-reason {

        max-width: 220px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;

    }

    /*

    *--------------------------------------------------------------------------

    * ÉCRANS MOINS LARGES

    *--------------------------------------------------------------------------

    */
    @media (max-width: 1399.98px) {

        .inventory-adjustments-table {

            min-width: 1000px;

        }

    }

    @media (max-width: 1199.98px) {

        .inventory-adjustments-table {

            min-width: 900px;

        }

    }

    /*

    *--------------------------------------------------------------------------

    * HEADER / ACTIONS

    *--------------------------------------------------------------------------

    */
    .inventory-header-actions .btn {

        white-space: nowrap;

    }

</style>

<div class="card shadow-sm border-0">

    {{-- ============================================================

        HEADER

    ============================================================ --}}

    <div class="card-header bg-white border-bottom">

        <div

            class="

                d-flex

                flex-column

                flex-lg-row

                justify-content-between

                align-items-lg-center

                gap-3

            "

        >

            <div>

                <h4 class="mb-1 fw-bold">

                    <i class="bx bx-list-check me-2"></i>

                    Ajustements inventaire

                </h4>

                <small class="text-muted">

                    Historique des corrections de stock,

                    dépôts et localisations

                </small>

            </div>

            @if(
                $canCreateAdjustment
                || $canImportAdjustment
            )

                <div

                    class="

                        inventory-header-actions

                        d-flex

                        flex-wrap

                        gap-2

                    "

                >

                    {{-- IMPORT EXCEL --}}

                    @if($canImportAdjustment)

                        <a

                            href="{{ route('inventory-adjustments.import') }}"

                            class="btn btn-success"

                        >

                            <i class="bx bx-spreadsheet me-1"></i>

                            Importer Excel

                        </a>

                    @endif

                    {{-- NOUVEL AJUSTEMENT --}}

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

            @endif

        </div>

    </div>

    {{-- ============================================================

        BODY

    ============================================================ --}}

    <div class="card-body">

        {{-- ============================================================

            MESSAGE SUCCESS

        ============================================================ --}}

        @if(session('success'))

            <div

                class="

                    alert

                    alert-success

                    alert-dismissible

                    fade

                    show

                "

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

        {{-- ============================================================

            MESSAGE ERROR

        ============================================================ --}}

        @if(session('error'))

            <div

                class="

                    alert

                    alert-danger

                    alert-dismissible

                    fade

                    show

                "

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

        <div

            class="

                alert

                alert-info

                d-flex

                align-items-start

                gap-2

                mb-4

            "

        >

            <i class="bx bx-info-circle fs-4 mt-1"></i>

            <div>

                <div class="fw-bold mb-1">

                    Fonctionnement

                </div>

                <div>

                    Un ajustement inventaire corrige la

                    <strong>

                        quantité réellement disponible

                    </strong>

                    dans un dépôt.

                    <br>

                    Lors d'un import Excel :

                    <strong>

                        le rayon et l'emplacement

                    </strong>

                    sont mis à jour uniquement lorsqu'ils

                    sont renseignés dans le fichier.

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

            {{-- CHAMP RECHERCHE --}}

            <div class="col-12 col-lg-6">

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

                        placeholder="Référence, désignation, dépôt, rayon ou emplacement..."

                    >

                </div>

            </div>

            {{-- BOUTON RECHERCHER --}}

            <div

                class="

                    col-12

                    col-sm-auto

                    d-flex

                    align-items-end

                "

            >

                <button

                    type="submit"

                    class="btn btn-primary w-100"

                >

                    <i class="bx bx-search me-1"></i>

                    Rechercher

                </button>

            </div>

            {{-- BOUTON RÉINITIALISER --}}

            <div

                class="

                    col-12

                    col-sm-auto

                    d-flex

                    align-items-end

                "

            >

                <a

                    href="{{ route('inventory-adjustments.index') }}"

                    class="btn btn-secondary w-100"

                >

                    <i class="bx bx-reset me-1"></i>

                    Réinitialiser

                </a>

            </div>

        </form>

        {{-- ============================================================

            TABLEAU

        ============================================================ --}}

        <div class="inventory-adjustments-wrapper">

            <table

                class="

                    table

                    table-bordered

                    table-hover

                    align-middle

                    inventory-adjustments-table

                "

            >

                {{-- ====================================================

                    HEADER TABLE

                ==================================================== --}}

                <thead class="table-light">

                    <tr>

                        <th class="text-center col-id">

                            #

                        </th>

                        <th class="col-reference">

                            Référence

                        </th>

                        <th class="col-designation">

                            Désignation

                        </th>

                        <th class="d-none d-xxl-table-cell">

                            Marque

                        </th>

                        <th class="d-none d-xxl-table-cell">

                            Modèle

                        </th>

                        <th class="col-depot">

                            Dépôt

                        </th>

                        <th class="col-rayon">

                            Rayon

                        </th>

                        <th class="col-location">

                            Emplacement

                        </th>

                        <th class="text-center col-qty">

                            Qté avant

                        </th>

                        <th class="text-center col-qty">

                            Qté après

                        </th>

                        <th class="text-center col-qty">

                            Différence

                        </th>

                        <th class="text-center col-type">

                            Type

                        </th>

                        <th class="col-reason">

                            Raison

                        </th>

                        <th class="d-none d-xl-table-cell col-user">

                            Effectué par

                        </th>

                        <th class="d-none d-xl-table-cell col-date">

                            Date

                        </th>

                        <th class="text-center col-action">

                            Action

                        </th>

                    </tr>

                </thead>

                {{-- ====================================================

                    BODY TABLE

                ==================================================== --}}

                <tbody>

                    @forelse($adjustments as $adjustment)

                        @php

                            /*

                            *--------------------------------------------------------------------------

                            * QUANTITÉS

                            *--------------------------------------------------------------------------

                            */
                            $oldQty =

                                (float) (

                                    $adjustment->old_qty

                                    ?? 0

                                );

                            $newQty =

                                (float) (

                                    $adjustment->new_qty

                                    ?? 0

                                );

                            $difference =

                                round(

                                    $newQty - $oldQty,

                                    2

                                );

                            /*

                            *--------------------------------------------------------------------------

                            * UNITÉ

                            *--------------------------------------------------------------------------

                            */
                            $unit =

                                $adjustment->product?->unit_label

                                ?? $adjustment->product?->unit_type

                                ?? 'Pièce';

                            /*

                            *--------------------------------------------------------------------------

                            * LOCALISATION

                            *--------------------------------------------------------------------------

                            *

                            * On privilégie la localisation enregistrée

                            * dans l'ajustement.

                            *

                            * Pour les anciens ajustements qui n'avaient pas

                            * depot_id / rayon_id / location_id, on retombe

                            * sur la localisation actuelle du produit.

                            *

                            */
                            $depotName =

                                $adjustment->depot?->name;

                            $rayonName =

                                $adjustment->rayon?->name

                                ?? $adjustment->product?->rayon?->name;

                            $locationName =

                                $adjustment->location?->name

                                ?? $adjustment->product?->location?->name;

                            /*

                            *--------------------------------------------------------------------------

                            * TYPE AJUSTEMENT

                            *--------------------------------------------------------------------------

                            */
                            if ($difference > 0) {

                                $typeLabel =

                                    'Entrée';

                                $typeClass =

                                    'bg-success';

                                $typeIcon =

                                    'bx-plus-circle';

                            } elseif ($difference < 0) {

                                $typeLabel =

                                    'Sortie';

                                $typeClass =

                                    'bg-danger';

                                $typeIcon =

                                    'bx-minus-circle';

                            } else {

                                $typeLabel =

                                    'Aucun';

                                $typeClass =

                                    'bg-secondary';

                                $typeIcon =

                                    'bx-minus';

                            }

                        @endphp

                        <tr>

                            {{-- ==========================================

                                ID

                            ========================================== --}}

                            <td class="text-center col-id">
                                {{ $adjustment->id }}
                            </td>

                            {{-- ==========================================

                                RÉFÉRENCE

                            ========================================== --}}

                            <td>

                                <strong>

                                    {{

                                        $adjustment->product?->reference

                                        ?? '-'

                                    }}

                                </strong>

                            </td>

                            {{-- ==========================================

                                DÉSIGNATION

                            ========================================== --}}

                            <td>

                                <div

                                    class="text-truncate"

                                    style="max-width: 250px;"

                                    title="{{

                                        $adjustment->product?->designation

                                        ?? 'Produit supprimé'

                                    }}"

                                >

                                    {{

                                        $adjustment->product?->designation

                                        ?? 'Produit supprimé'

                                    }}

                                </div>

                            </td>

                            {{-- ==========================================

                                MARQUE

                            ========================================== --}}

                            <td class="d-none d-xxl-table-cell">

                                {{

                                    $adjustment->product?->brand?->name

                                    ?? '-'

                                }}

                            </td>

                            {{-- ==========================================

                                MODÈLE

                            ========================================== --}}

                            <td class="d-none d-xxl-table-cell">

                                {{

                                    $adjustment->product?->model?->name

                                    ?? '-'

                                }}

                            </td>

                            {{-- ==========================================

                                DÉPÔT

                            ========================================== --}}

                            <td>

                                @if($depotName)

                                    <span

                                        class="

                                            badge

                                            bg-label-primary

                                        "

                                    >

                                        {{ $depotName }}

                                    </span>

                                @else

                                    <span class="text-muted">

                                        Non renseigné

                                    </span>

                                @endif

                            </td>

                            {{-- ==========================================

                                RAYON

                            ========================================== --}}

                            <td>

                                @if($rayonName)

                                    <span

                                        class="

                                            badge

                                            bg-label-info

                                        "

                                    >

                                        {{ $rayonName }}

                                    </span>

                                @else

                                    <span class="text-muted">

                                        -

                                    </span>

                                @endif

                            </td>

                            {{-- ==========================================

                                EMPLACEMENT

                            ========================================== --}}

                            <td>

                                @if($locationName)

                                    <span

                                        class="

                                            badge

                                            bg-label-secondary

                                        "

                                    >

                                        {{ $locationName }}

                                    </span>

                                @else

                                    <span class="text-muted">

                                        -

                                    </span>

                                @endif

                            </td>

                            {{-- ==========================================

                                QUANTITÉ AVANT

                            ========================================== --}}

                            <td class="text-center">

                                <span

                                    class="

                                        badge

                                        bg-label-secondary

                                    "

                                >

                                    {{

                                        number_format(

                                            $oldQty,

                                            2,

                                            ',',

                                            ' '

                                        )

                                    }}

                                    {{ $unit }}

                                </span>

                            </td>

                            {{-- ==========================================

                                QUANTITÉ APRÈS

                            ========================================== --}}

                            <td class="text-center">

                                <span

                                    class="

                                        badge

                                        bg-label-primary

                                    "

                                >

                                    {{

                                        number_format(

                                            $newQty,

                                            2,

                                            ',',

                                            ' '

                                        )

                                    }}

                                    {{ $unit }}

                                </span>

                            </td>

                            {{-- ==========================================

                                DIFFÉRENCE

                            ========================================== --}}

                            <td class="text-center">

                                @if($difference > 0)

                                    <span class="badge bg-success">

                                        +

                                        {{

                                            number_format(

                                                $difference,

                                                2,

                                                ',',

                                                ' '

                                            )

                                        }}

                                        {{ $unit }}

                                    </span>

                                @elseif($difference < 0)

                                    <span class="badge bg-danger">

                                        {{

                                            number_format(

                                                $difference,

                                                2,

                                                ',',

                                                ' '

                                            )

                                        }}

                                        {{ $unit }}

                                    </span>

                                @else

                                    <span class="badge bg-secondary">

                                        0,00 {{ $unit }}

                                    </span>

                                @endif

                            </td>

                            {{-- ==========================================

                                TYPE

                            ========================================== --}}

                            <td class="text-center">

                                <span

                                    class="

                                        badge

                                        {{ $typeClass }}

                                    "

                                >

                                    <i

                                        class="

                                            bx

                                            {{ $typeIcon }}

                                            me-1

                                        "

                                    ></i>

                                    {{ $typeLabel }}

                                </span>

                            </td>

                            {{-- ==========================================

                                RAISON

                            ========================================== --}}

                            <td>

                                <div

                                    class="inventory-reason"

                                    title="{{ $adjustment->reason ?? '-' }}"

                                >

                                    {{ $adjustment->reason ?? '-' }}

                                </div>

                            </td>

                            {{-- ==========================================

                                UTILISATEUR

                            ========================================== --}}

                            <td class="d-none d-xl-table-cell">

                                {{

                                    $adjustment->approver?->name

                                    ?? '-'

                                }}

                            </td>

                            {{-- ==========================================

                                DATE

                            ========================================== --}}

                            <td class="d-none d-xl-table-cell">

                                {{

                                    optional(

                                        $adjustment->created_at

                                    )->format(

                                        'd/m/Y H:i'

                                    )

                                }}

                            </td>

                            {{-- ==========================================

                                ACTION

                            ========================================== --}}

                            <td class="text-center col-action">
                                <div class="inventory-action-buttons">

                                    {{-- VOIR --}}
                                    <a
                                        href="{{ route('inventory-adjustments.show', $adjustment->id) }}"
                                        class="btn btn-info btn-sm"
                                        title="Voir le détail"
                                        aria-label="Voir le détail"
                                    >
                                        <i class="bx bx-show"></i>
                                    </a>

                                    {{-- MODIFIER : ADMIN UNIQUEMENT --}}
                                    @if($canEditAdjustment)
                                        <a
                                            href="{{ route('inventory-adjustments.edit', $adjustment->id) }}"
                                            class="btn btn-warning btn-sm"
                                            title="Modifier"
                                            aria-label="Modifier"
                                        >
                                            <i class="bx bx-edit"></i>
                                        </a>
                                    @endif

                                    {{-- SUPPRIMER : ADMIN UNIQUEMENT --}}
                                    @if($canDeleteAdjustment)
                                        <form
                                            action="{{ route('inventory-adjustments.destroy', $adjustment->id) }}"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Voulez-vous vraiment supprimer cet ajustement d\'inventaire ? Cette action est irréversible.');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-danger btn-sm"
                                                title="Supprimer"
                                                aria-label="Supprimer"
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

                                colspan="16"

                                class="

                                    text-center

                                    py-5

                                    text-muted

                                "

                            >

                                <i

                                    class="

                                        bx

                                        bx-package

                                        fs-1

                                        d-block

                                        mb-2

                                    "

                                ></i>

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

            method_exists(

                $adjustments,

                'links'

            )

            && $adjustments->hasPages()

        )

            <div

                class="

                    mt-4

                    d-flex

                    justify-content-center

                "

            >

                {{ $adjustments->links() }}

            </div>

        @endif

    </div>

</div>

@endsection
