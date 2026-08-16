@extends('layouts.layoutMaster')

@section('content')

<div class="page-wrapper">
    <div class="content">
        <div class="container-fluid">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>
                </div>
            @endif

            <div class="row g-4">

                {{-- Informations principales --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white py-4">
                            <div
                                class="d-flex
                                       justify-content-between
                                       align-items-center"
                            >
                                <div>
                                    <h3 class="mb-1">
                                        {{ $vehiclePartRequest->part_name }}
                                    </h3>

                                    <p class="text-muted mb-0">
                                        Suivi de la pièce
                                    </p>
                                </div>

                                <span
                                    class="badge bg-{{
                                        $vehiclePartRequest->status_badge
                                    }} fs-6"
                                >
                                    {{
                                        $vehiclePartRequest->status_label
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row g-4">

                               <div class="col-md-6">

                                    <div class="text-muted">
                                        Véhicule
                                    </div>

                                    @if($vehiclePartRequest->vehicle)

                                        <div class="fw-bold text-primary mt-1">
                                            {{ $vehiclePartRequest->vehicle->plate_number ?? '-' }}
                                        </div>

                                        @if($vehiclePartRequest->vehicle->customer)

                                            <div class="text-muted">
                                                Client :
                                                {{ $vehiclePartRequest->vehicle->customer->name }}
                                            </div>

                                        @endif

                                        @if(
                                            $vehiclePartRequest->vehicle->brand
                                            || $vehiclePartRequest->vehicle->model
                                        )

                                            <div class="text-muted">
                                                {{ $vehiclePartRequest->vehicle->brand ?? '' }}
                                                {{ $vehiclePartRequest->vehicle->model ?? '' }}
                                            </div>

                                        @endif

                                    @else

                                        <div class="text-danger">
                                            Véhicule introuvable
                                        </div>

                                    @endif

                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted">
                                        Référence de la pièce
                                    </div>

                                    <div class="fw-bold">
                                        {{
                                            $vehiclePartRequest->reference
                                            ?: 'Non renseignée'
                                        }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted">
                                        Quantité
                                    </div>

                                    <div class="fw-bold">
                                        {{
                                            number_format(
                                                $vehiclePartRequest->quantity,
                                                2,
                                                ',',
                                                ' '
                                            )
                                        }}

                                        {{ $vehiclePartRequest->unit }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted">
                                        Fournisseur
                                    </div>

                                    <div class="fw-bold">
                                        {{
                                            optional(
                                                $vehiclePartRequest->supplier
                                            )->name
                                            ?: 'Non renseigné'
                                        }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted">
                                        Référence fournisseur
                                    </div>

                                    <div class="fw-bold">
                                        {{
                                            $vehiclePartRequest
                                                ->supplier_reference
                                            ?: 'Non renseignée'
                                        }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted">
                                        Référence de commande
                                    </div>

                                    <div class="fw-bold">
                                        {{
                                            $vehiclePartRequest
                                                ->order_reference
                                            ?: 'Non renseignée'
                                        }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted">
                                        Prix estimé
                                    </div>

                                    <div class="fw-bold">
                                        @if(
                                            $vehiclePartRequest
                                                ->estimated_price !== null
                                        )
                                            {{
                                                number_format(
                                                    $vehiclePartRequest
                                                        ->estimated_price,
                                                    2,
                                                    ',',
                                                    ' '
                                                )
                                            }}
                                            FDJ
                                        @else
                                            Non renseigné
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted">
                                        Prix d’achat
                                    </div>

                                    <div class="fw-bold">
                                        @if(
                                            $vehiclePartRequest
                                                ->purchase_price !== null
                                        )
                                            {{
                                                number_format(
                                                    $vehiclePartRequest
                                                        ->purchase_price,
                                                    2,
                                                    ',',
                                                    ' '
                                                )
                                            }}
                                            FDJ
                                        @else
                                            Non renseigné
                                        @endif
                                    </div>
                                </div>

                                @if($vehiclePartRequest->description)
                                    <div class="col-12">
                                        <div class="text-muted">
                                            Description
                                        </div>

                                        <div>
                                            {{
                                                $vehiclePartRequest
                                                    ->description
                                            }}
                                        </div>
                                    </div>
                                @endif

                                @if($vehiclePartRequest->notes)
                                    <div class="col-12">
                                        <div class="text-muted">
                                            Notes
                                        </div>

                                        <div>
                                            {{ $vehiclePartRequest->notes }}
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <hr class="my-4">

                            <div class="d-flex gap-2">
                                <a
                                    href="{{
                                        route(
                                            'vehicle-part-requests.edit',
                                            $vehiclePartRequest
                                        )
                                    }}"
                                    class="btn btn-warning"
                                >
                                    Modifier
                                </a>

                                <a
                                    href="{{
                                        route(
                                            'vehicle-part-requests.index'
                                        )
                                    }}"
                                    class="btn btn-secondary"
                                >
                                    Retour
                                </a>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Changement de statut --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                Modifier le statut
                            </h5>
                        </div>

                        <div class="card-body">

                            @if($availableStatuses->isNotEmpty())
                               <form
                                method="POST"
                                action="{{ route(
                                    'vehicle-part-requests.change-status',
                                    $vehiclePartRequest
                                ) }}"
                                id="statusUpdateForm"
                                >
                                @csrf
                                @method('PATCH')

                                {{-- Nouveau statut --}}
                                <div class="mb-3">
                                    <label
                                        for="status"
                                        class="form-label"
                                    >
                                        Nouveau statut
                                    </label>

                                    <select
                                        name="status"
                                        id="status"
                                        class="form-select @error('status') is-invalid @enderror"
                                        required
                                    >
                                        <option value="">
                                            Sélectionner
                                        </option>

                                        @foreach($availableStatuses as $value => $label)

                                            <option
                                                value="{{ $value }}"
                                                @selected(old('status') === $value)
                                            >
                                                {{ $label }}
                                            </option>

                                        @endforeach
                                    </select>

                                    @error('status')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                {{-- Champs affichés uniquement pour le statut Commandée --}}
                                <div
                                    id="orderFields"
                                    class="d-none"
                                >

                                    {{-- Fournisseur --}}
                                    <div class="mb-3">
                                        <label
                                            for="supplier_id"
                                            class="form-label"
                                        >
                                            Fournisseur
                                            <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="supplier_id"
                                            id="supplier_id"
                                            class="form-select @error('supplier_id') is-invalid @enderror"
                                        >
                                            <option value="">
                                                Sélectionner un fournisseur
                                            </option>

                                            @foreach($suppliers as $supplier)

                                                <option
                                                    value="{{ $supplier->id }}"
                                                    @selected(
                                                        old(
                                                            'supplier_id',
                                                            $vehiclePartRequest->supplier_id
                                                        ) == $supplier->id
                                                    )
                                                >
                                                    {{ $supplier->name }}
                                                </option>

                                            @endforeach
                                        </select>

                                        @error('supplier_id')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- Référence de commande --}}
                                    <div class="mb-3">
                                        <label
                                            for="order_reference"
                                            class="form-label"
                                        >
                                            Référence de commande
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="order_reference"
                                            id="order_reference"
                                            value="{{ old(
                                                'order_reference',
                                                $vehiclePartRequest->order_reference
                                            ) }}"
                                            class="form-control @error('order_reference') is-invalid @enderror"
                                            placeholder="Exemple : CMD-2026-0001"
                                        >

                                        @error('order_reference')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- Prix d’achat --}}
                                    <div class="mb-3">
                                        <label
                                            for="purchase_price"
                                            class="form-label"
                                        >
                                            Prix d’achat
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="number"
                                            name="purchase_price"
                                            id="purchase_price"
                                            min="0"
                                            step="0.01"
                                            value="{{ old(
                                                'purchase_price',
                                                $vehiclePartRequest->purchase_price
                                            ) }}"
                                            class="form-control @error('purchase_price') is-invalid @enderror"
                                            placeholder="0.00"
                                        >

                                        @error('purchase_price')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                </div>

                                {{-- Commentaire --}}
                                <div class="mb-3">
                                    <label
                                        for="comment"
                                        class="form-label"
                                    >
                                        Commentaire
                                    </label>

                                    <textarea
                                        name="comment"
                                        id="comment"
                                        rows="3"
                                        class="form-control @error('comment') is-invalid @enderror"
                                        placeholder="Informations sur ce changement"
                                    >{{ old('comment') }}</textarea>

                                    @error('comment')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <button
                                    type="submit"
                                    class="btn btn-primary w-100"
                                >
                                    <i class="bx bx-save me-1"></i>
                                    Mettre à jour le statut
                                </button>
                            </form>
                            @else
                                <div class="alert alert-info mb-0">
                                    Cette demande est terminée.
                                </div>
                            @endif

                        </div>
                    </div>

                    {{-- Dates --}}
                    <div class="card border-0 shadow-sm mt-4">

                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                Dates de suivi
                            </h5>
                        </div>

                        <div class="card-body">
                            <table class="table table-sm mb-0">
                              <tr>
                                    <th>Demande</th>
                                    <td class="text-end">
                                        {{
                                            optional($vehiclePartRequest->requested_at)
                                                ->format('d/m/Y H:i')
                                            ?: '-'
                                        }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Commandée</th>
                                    <td class="text-end">
                                        {{
                                            optional($vehiclePartRequest->ordered_at)
                                                ->format('d/m/Y H:i')
                                            ?: '-'
                                        }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Reçue</th>
                                    <td class="text-end">
                                        {{
                                            optional($vehiclePartRequest->received_at)
                                                ->format('d/m/Y H:i')
                                            ?: '-'
                                        }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Non trouvée</th>
                                    <td class="text-end">
                                        {{
                                            optional($vehiclePartRequest->not_found_at)
                                                ->format('d/m/Y H:i')
                                            ?: '-'
                                        }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Historique --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                Historique des changements
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Ancien statut</th>
                                            <th>Nouveau statut</th>
                                            <th>Utilisateur</th>
                                            <th>Commentaire</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse(
                                            $vehiclePartRequest->histories
                                            as $history
                                        )
                                            <tr>
                                                <td>
                                                    {{
                                                        optional(
                                                            $history->changed_at
                                                        )->format('d/m/Y H:i')
                                                        ?: '-'
                                                    }}
                                                </td>

                                                <td>
                                                    {{
                                                        $history
                                                            ->old_status_label
                                                    }}
                                                </td>

                                                <td>
                                                    <strong>
                                                        {{
                                                            $history
                                                                ->new_status_label
                                                        }}
                                                    </strong>
                                                </td>

                                                <td>
                                                    {{
                                                        optional(
                                                            $history->user
                                                        )->name
                                                        ?: 'Système'
                                                    }}
                                                </td>

                                                <td>
                                                    {{
                                                        $history->comment
                                                        ?: '-'
                                                    }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td
                                                    colspan="5"
                                                    class="text-center"
                                                >
                                                    Aucun historique.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusSelect = document.getElementById('status');
    const orderFields = document.getElementById('orderFields');
    const supplierSelect = document.getElementById('supplier_id');
    const orderReferenceInput =
        document.getElementById('order_reference');
    const purchasePriceInput =
        document.getElementById('purchase_price');

    if (!statusSelect || !orderFields) {
        return;
    }

    function toggleOrderFields() {
        const isOrdered = statusSelect.value === 'ordered';

        if (isOrdered) {
            orderFields.classList.remove('d-none');

            /*
             * Les champs deviennent obligatoires uniquement
             * lorsque le statut Commandée est sélectionné.
             */
            if (supplierSelect) {
                supplierSelect.required = true;
            }

            if (orderReferenceInput) {
                orderReferenceInput.required = true;
            }

            if (purchasePriceInput) {
                purchasePriceInput.required = true;
            }
        } else {
            orderFields.classList.add('d-none');

            /*
             * Pour les autres statuts, ils ne doivent pas
             * empêcher l’envoi du formulaire.
             */
            if (supplierSelect) {
                supplierSelect.required = false;
            }

            if (orderReferenceInput) {
                orderReferenceInput.required = false;
            }

            if (purchasePriceInput) {
                purchasePriceInput.required = false;
            }
        }
    }

    statusSelect.addEventListener(
        'change',
        toggleOrderFields
    );

    /*
     * Appliquer également la logique au chargement de la page.
     * C’est utile après une erreur de validation Laravel.
     */
    toggleOrderFields();
});
</script>
@endpush
