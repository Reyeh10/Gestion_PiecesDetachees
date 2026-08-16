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

                                <span class="badge bg-{{ $vehiclePartRequest->status_badge }} fs-6">
                                    {{ $vehiclePartRequest->status_label }}
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


                              {{-- Quantité commandée --}}
                                <div class="col-md-4">
                                    <div class="text-muted">
                                        Quantité commandée
                                    </div>

                                    <div class="fw-bold">
                                        {{ number_format(
                                            (float) $vehiclePartRequest->quantity,
                                            2,
                                            ',',
                                            ' '
                                        ) }}
                                        {{ $vehiclePartRequest->unit }}
                                    </div>
                                </div>


                                {{-- Quantité reçue --}}
                                <div class="col-md-4">
                                    <div class="text-muted">
                                        Quantité reçue
                                    </div>

                                    <div class="fw-bold text-success">
                                        {{ number_format(
                                            (float) ($vehiclePartRequest->received_quantity ?? 0),
                                            2,
                                            ',',
                                            ' '
                                        ) }}
                                        {{ $vehiclePartRequest->unit }}
                                    </div>
                                </div>


                                {{-- Reste à recevoir --}}
                                <div class="col-md-4">
                                    <div class="text-muted">
                                        Reste à recevoir
                                    </div>

                                    <div class="fw-bold text-warning">
                                        {{ number_format(
                                            (float) $vehiclePartRequest->remaining_quantity,
                                            2,
                                            ',',
                                            ' '
                                        ) }}
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

                            @php
                                $manualStatuses = $availableStatuses->except([
                                    \App\Models\VehiclePartRequest::STATUS_PARTIAL_RECEIVED,
                                    \App\Models\VehiclePartRequest::STATUS_RECEIVED,
                                ]);
                            @endphp

                            @if($manualStatuses->isNotEmpty())
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

                                        @foreach($manualStatuses as $value => $label)

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
                                            <span class="text-danger"></span>
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
                                            <span class="text-danger"></span>
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
                                            <span class="text-danger"></span>
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
                                    Aucun changement manuel de statut n'est disponible.
                                    @if(
                                        in_array(
                                            $vehiclePartRequest->status,
                                            [
                                                \App\Models\VehiclePartRequest::STATUS_ORDERED,
                                                \App\Models\VehiclePartRequest::STATUS_PARTIAL_RECEIVED,
                                            ],
                                            true
                                        )
                                    )
                                        Utilisez le formulaire de réception ci-dessous.
                                    @endif
                                </div>
                            @endif

                        </div>
                    </div>

                    {{-- Réception de la commande --}}
                    @if(
                        in_array(
                            $vehiclePartRequest->status,
                            [
                                \App\Models\VehiclePartRequest::STATUS_ORDERED,
                                \App\Models\VehiclePartRequest::STATUS_PARTIAL_RECEIVED,
                            ],
                            true
                        )
                    )
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-1">Réception de la commande</h5>
                                <small class="text-muted">
                                    Saisissez uniquement la quantité reçue dans cette nouvelle livraison.
                                </small>
                            </div>

                            <div class="card-body">
                                <div class="alert alert-light border mb-4">
                                    <div class="row g-3 text-center">
                                        <div class="col-4">
                                            <div class="text-muted small">Commandée</div>
                                            <div class="fw-bold">
                                                {{ number_format((float) $vehiclePartRequest->quantity, 2, ',', ' ') }}
                                                {{ $vehiclePartRequest->unit }}
                                            </div>
                                        </div>

                                        <div class="col-4">
                                            <div class="text-muted small">Déjà reçue</div>
                                            <div class="fw-bold text-success">
                                                {{ number_format((float) ($vehiclePartRequest->received_quantity ?? 0), 2, ',', ' ') }}
                                                {{ $vehiclePartRequest->unit }}
                                            </div>
                                        </div>

                                        <div class="col-4">
                                            <div class="text-muted small">Restante</div>
                                            <div class="fw-bold text-warning">
                                                {{ number_format((float) $vehiclePartRequest->remaining_quantity, 2, ',', ' ') }}
                                                {{ $vehiclePartRequest->unit }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'vehicle-part-requests.update-received-quantity',
                                        $vehiclePartRequest
                                    ) }}"
                                    id="receivedQuantityForm"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <div class="mb-3">
                                        <label for="received_now" class="form-label">
                                            Quantité reçue maintenant
                                            <span class="text-danger"></span>
                                        </label>

                                        <input
                                            type="number"
                                            name="received_now"
                                            id="received_now"
                                            class="form-control @error('received_now') is-invalid @enderror"
                                            step="0.01"
                                            min="0"
                                            max="{{ (float) $vehiclePartRequest->remaining_quantity }}"
                                            value="{{ old('received_now') }}"
                                            data-ordered-quantity="{{ (float) $vehiclePartRequest->quantity }}"
                                            data-current-received-quantity="{{ (float) ($vehiclePartRequest->received_quantity ?? 0) }}"
                                            data-current-remaining-quantity="{{ (float) $vehiclePartRequest->remaining_quantity }}"
                                            required
                                        >

                                        @error('received_now')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                        <div class="form-text">
                                            Saisissez uniquement la quantité reçue dans cette livraison.
                                            Exemple : si 20 pièces sont déjà reçues et que 8 autres arrivent,
                                            saisissez 8.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            Reste à recevoir après cette saisie
                                        </label>

                                        <input
                                            type="text"
                                            id="remaining_quantity_preview"
                                            class="form-control"
                                            value="{{ number_format(
                                                (float) $vehiclePartRequest->remaining_quantity,
                                                2,
                                                ',',
                                                ' '
                                            ) }} {{ $vehiclePartRequest->unit }}"
                                            readonly
                                        >
                                    </div>

                                    <div class="mb-3">
                                        <label for="reception_comment" class="form-label">
                                            Commentaire
                                        </label>

                                        <textarea
                                            name="comment"
                                            id="reception_comment"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Ex : 5 pièces reçues sur les 10 commandées."
                                        >{{ old('comment') }}</textarea>
                                    </div>

                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bx bx-package me-1"></i>
                                        Enregistrer la réception
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

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
                                            <th>Ancienne qté reçue</th>
                                            <th>Nouvelle qté reçue</th>
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
                                                    @if($history->old_received_quantity !== null)
                                                        {{ number_format((float) $history->old_received_quantity, 2, ',', ' ') }}
                                                        {{ $vehiclePartRequest->unit }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>

                                                <td>
                                                    @if($history->new_received_quantity !== null)
                                                        {{ number_format((float) $history->new_received_quantity, 2, ',', ' ') }}
                                                        {{ $vehiclePartRequest->unit }}
                                                    @else
                                                        -
                                                    @endif
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
                                                    colspan="7"
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

    /*
    |--------------------------------------------------------------------------
    | CHANGEMENT DE STATUT
    |--------------------------------------------------------------------------
    */

    const statusSelect = document.getElementById('status');
    const orderFields = document.getElementById('orderFields');
    const supplierSelect = document.getElementById('supplier_id');
    const orderReferenceInput = document.getElementById('order_reference');
    const purchasePriceInput = document.getElementById('purchase_price');

    function toggleOrderFields() {

        if (!statusSelect || !orderFields) {
            return;
        }

        const isOrdered = statusSelect.value === 'ordered';

        if (isOrdered) {

            orderFields.classList.remove('d-none');

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

    if (statusSelect && orderFields) {
        statusSelect.addEventListener('change', toggleOrderFields);
        toggleOrderFields();
    }


    /*
    |--------------------------------------------------------------------------
    | RÉCEPTION DE LA COMMANDE
    |--------------------------------------------------------------------------
    |
    | Exemple :
    |
    | commandée       = 30
    | déjà reçue      = 20
    | reçue maintenant = 8
    |
    | total reçu après saisie = 28
    | reste après saisie      = 2
    |
    */

    const receivedNowInput = document.getElementById('received_now');
    const remainingQuantityPreview =
        document.getElementById('remaining_quantity_preview');

    function updateRemainingQuantityPreview() {

        if (!receivedNowInput || !remainingQuantityPreview) {
            return;
        }

        const orderedQuantity = parseFloat(
            receivedNowInput.dataset.orderedQuantity || '0'
        );

        const currentReceivedQuantity = parseFloat(
            receivedNowInput.dataset.currentReceivedQuantity || '0'
        );

        const remainingBeforeReception = Math.max(
            0,
            orderedQuantity - currentReceivedQuantity
        );

        const rawValue = receivedNowInput.value.trim();

        let receivedNow = 0;

        if (rawValue !== '') {
            receivedNow = parseFloat(rawValue);

            if (Number.isNaN(receivedNow) || receivedNow < 0) {
                receivedNow = 0;
            }
        }

        const remainingAfterReception = Math.max(
            0,
            remainingBeforeReception - receivedNow
        );

        const unit = @json($vehiclePartRequest->unit ?? '');

        remainingQuantityPreview.value =
            remainingAfterReception.toLocaleString(
                'fr-FR',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            ) + (unit ? ' ' + unit : '');
    }

    if (receivedNowInput) {

        receivedNowInput.addEventListener(
            'input',
            updateRemainingQuantityPreview
        );

        updateRemainingQuantityPreview();
    }

});
</script>
@endpush
