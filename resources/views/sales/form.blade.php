@extends('layouts.layoutMaster')

@section('content')

<form action="{{ route('sales.store') }}" method="POST" id="saleForm">
    @csrf

    {{-- ALERTES --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">

        <div class="card-header border-0 pb-0">
            <h3 class="mb-0 fw-bold">
                Nouvelle vente
            </h3>
        </div>

        <div class="card-body">

            {{-- CLIENT, VÉHICULE ET PAIEMENT --}}
            <div class="row align-items-end">

                {{-- CLIENT --}}
                <div class="col-lg-4 col-md-6 mb-3">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label for="customer_id" class="form-label fw-semibold mb-0">
                            Client
                        </label>

                        <button
                            type="button"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#customerModal"
                        >
                            <i class="bx bx-plus"></i>
                            Nouveau client
                        </button>
                    </div>

                    <select
                        name="customer_id"
                        id="customer_id"
                        class="form-control select2"
                        required
                    >
                        <option value="">
                            Sélectionner un client
                        </option>

                        @foreach($customers as $customer)
                            <option
                                value="{{ $customer->id }}"
                                {{ old('customer_id') == $customer->id ? 'selected' : '' }}
                            >
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('customer_id')
                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- VÉHICULE --}}
                <div class="col-lg-4 col-md-6 mb-3">

                    <label for="vehicle_id" class="form-label fw-semibold">
                        Numéro d’immatriculation
                    </label>

                    <select
                        name="vehicle_id"
                        id="vehicle_id"
                        class="form-control vehicle-select"
                        required
                    >
                        <option value="">
                            Sélectionner une immatriculation
                        </option>

                        @foreach($vehicles as $vehicle)
                            <option
                                value="{{ $vehicle->id }}"
                                data-customer-id="{{ $vehicle->customer_id }}"
                                {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}
                            >
                                {{ $vehicle->plate_number }}

                                @if(!empty($vehicle->brand) || !empty($vehicle->model))
                                    - {{ $vehicle->brand }} {{ $vehicle->model }}
                                @endif

                                @if($vehicle->customer)
                                    - Client : {{ $vehicle->customer->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>

                    @error('vehicle_id')
                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>
                    @enderror

                    <!--small class="text-muted">
                        Le véhicule sélectionné sera associé à toute la vente.
                    </small-->
                </div>

                {{-- PAIEMENT --}}
                <div class="col-lg-4 col-md-6 mb-3">

                    <label for="payment_type" class="form-label fw-semibold">
                        Paiement
                    </label>

                    <select
                        name="payment_type"
                        id="payment_type"
                        class="form-control"
                        required
                    >
                        <option
                            value="cash"
                            {{ old('payment_type', 'cash') === 'cash' ? 'selected' : '' }}
                        >
                            Cash
                        </option>

                        <option
                            value="bon_commande"
                            {{ old('payment_type') === 'bon_commande' ? 'selected' : '' }}
                        >
                            Bon de commande
                        </option>
                    </select>

                    @error('payment_type')
                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

            </div>

            <hr class="my-4">

            {{-- PRODUITS --}}
            <div class="table-responsive">

                <table
                    class="table table-bordered align-middle mb-0"
                    id="itemsTable"
                >
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 360px;">
                                Référence / Produit
                            </th>

                            <th
                                style="min-width: 120px;"
                                class="text-center"
                            >
                                Stock
                            </th>

                            <th style="min-width: 260px; width: 260px;">
                                Prix unitaire
                            </th>

                            <th style="min-width: 120px;">
                                Quantité
                            </th>

                            <th style="min-width: 150px;">
                                Total
                            </th>

                            <th
                                style="min-width: 90px;"
                                class="text-center"
                            >
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody></tbody>
                </table>

            </div>

            <button
                type="button"
                class="btn btn-success mt-3"
                id="addProductButton"
            >
                <i class="bx bx-plus"></i>
                Ajouter produit
            </button>

            <hr class="my-4">

            {{-- TOTAUX --}}
            <div class="row justify-content-end">

                <div class="col-lg-5 col-md-6">

                    <div class="d-flex justify-content-between mb-3">
                        <strong>Sous-total :</strong>

                        <span>
                            <span id="subTotal">0.00</span>
                            FDJ
                        </span>
                    </div>

                    <div class="mb-3">
                        <label for="discount" class="form-label fw-semibold">
                            Remise (%)
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            name="discount"
                            id="discount"
                            value="{{ old('discount', 0) }}"
                            class="form-control"
                        >

                        @error('discount')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <strong>Montant de la remise :</strong>

                        <span class="text-danger">
                            -
                            <span id="discountAmount">0.00</span>
                            FDJ
                        </span>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <strong>TVA (10 %) :</strong>

                        <span>
                            <span id="tvaAmount">0.00</span>
                            FDJ
                        </span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <h4 class="mb-0 fw-bold">
                            Total :
                        </h4>

                        <h3 class="text-primary fw-bold mb-0">
                            <span id="grandTotal">0.00</span>
                            FDJ
                        </h3>
                    </div>

                </div>

            </div>

            <input
                type="hidden"
                name="final_total"
                id="final_total_input"
                value="0"
            >

        </div>

        <div class="card-footer bg-white text-end border-0">

            <button
                type="submit"
                class="btn btn-primary px-4"
            >
                <i class="bx bx-check-circle"></i>
                Valider la vente
            </button>

        </div>

    </div>
</form>

{{-- MODAL NOUVEAU CLIENT --}}
<div
    class="modal fade"
    id="customerModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Nouveau client
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fermer"
                ></button>
            </div>

            <div class="modal-body">

                <div
                    id="customerModalError"
                    class="alert alert-danger d-none"
                ></div>

                <div class="mb-3">
                    <label for="customer_code" class="form-label">
                        Code *
                    </label>

                    <input
                        type="text"
                        id="customer_code"
                        class="form-control"
                    >
                </div>

                <div class="mb-3">
                    <label for="customer_name" class="form-label">
                        Nom *
                    </label>

                    <input
                        type="text"
                        id="customer_name"
                        class="form-control"
                    >
                </div>

                <div class="mb-3">
                    <label for="customer_phone" class="form-label">
                        Téléphone
                    </label>

                    <input
                        type="text"
                        id="customer_phone"
                        class="form-control"
                    >
                </div>

                <div class="mb-3">
                    <label for="customer_email" class="form-label">
                        Email
                    </label>

                    <input
                        type="email"
                        id="customer_email"
                        class="form-control"
                    >
                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Annuler
                </button>

                <button
                    type="button"
                    id="saveCustomerBtn"
                    class="btn btn-primary"
                >
                    Enregistrer
                </button>

            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let rowIndex = 0;

    const itemsTableBody = document.querySelector('#itemsTable tbody');
    const addProductButton = document.getElementById('addProductButton');
    const discountInput = document.getElementById('discount');
    const customerSelect = document.getElementById('customer_id');
    const vehicleSelect = document.getElementById('vehicle_id');
    const saleForm = document.getElementById('saleForm');

    /*
    |--------------------------------------------------------------------------
    | SELECT2
    |--------------------------------------------------------------------------
    */

    $('#customer_id').select2({
        width: '100%',
        placeholder: 'Sélectionner un client',
        allowClear: true
    });

    $('#vehicle_id').select2({
        width: '100%',
        placeholder: 'Rechercher une immatriculation',
        allowClear: true
    });

    /*
    |--------------------------------------------------------------------------
    | AJOUTER UNE LIGNE PRODUIT
    |--------------------------------------------------------------------------
    */

    function addRow(oldItem = null) {

        const currentIndex = rowIndex;

        const row = document.createElement('tr');

        row.innerHTML = `
            <td>
                <select
                    name="items[${currentIndex}][product_id]"
                    class="form-control product-select"
                    required
                >
                    <option value="">
                        Choisir un produit
                    </option>

                    @foreach($products as $product)
                        <option
                            value="{{ $product->id }}"
                            data-price="{{ $product->sale_price }}"
                            data-stock="{{ $product->quantity }}"
                            data-unit="{{ $product->unit_label ?? 'Pièce' }}"
                        >
                            {{ $product->reference }}
                            |
                            {{ $product->designation }}
                            |
                            {{ $product->brand->name ?? '' }}
                            |
                            {{ $product->model->name ?? '' }}
                        </option>
                    @endforeach
                </select>
            </td>

            <td class="text-center fw-bold">
                <span id="stock_${currentIndex}">0</span>
                <br>
                <small id="stock_unit_${currentIndex}">
                    Pièce
                </small>
            </td>

            <td style="width: 280px; min-width: 280px;">

                <div
                    class="d-flex align-items-center justify-content-between gap-2"
                    style="min-width: 250px;"
                >
                    {{-- Valeur numérique envoyée au serveur --}}
                    <input
                        type="hidden"
                        name="items[${currentIndex}][price]"
                        id="price_${currentIndex}"
                        value="0"
                    >

                    {{-- Prix visible --}}
                    <div
                        class="form-control text-end fw-bold bg-white"
                        id="price_display_${currentIndex}"
                        style="
                            min-width: 145px;
                            width: 145px;
                            font-size: 16px;
                            white-space: nowrap;
                            overflow: visible;
                        "
                    >
                        0
                    </div>

                    <span
                        class="fw-semibold text-nowrap"
                        style="min-width: 90px;"
                    >
                        FDJ /
                        <span id="price_unit_${currentIndex}">
                            Pièce
                        </span>
                    </span>
                </div>

            </td>

            <td>
                <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="items[${currentIndex}][quantity]"
                    id="qty_${currentIndex}"
                    class="form-control"
                    value="1"
                    required
                >
            </td>

            <td class="fw-bold">
                <span id="total_${currentIndex}">
                    0.00
                </span>
                FDJ
            </td>

            <td class="text-center">
                <button
                    type="button"
                    class="btn btn-danger btn-sm remove-row"
                    title="Supprimer cette ligne"
                >
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        `;

        itemsTableBody.appendChild(row);

        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector(`#qty_${currentIndex}`);
        const removeButton = row.querySelector('.remove-row');

        $(productSelect).select2({
            width: '100%',
            placeholder: 'Rechercher par référence, désignation, marque ou modèle',
            allowClear: true
        });

        $(productSelect).on('change', function () {
            updatePriceAndStock(this, currentIndex);
        });

        quantityInput.addEventListener('input', function () {
            calculateRow(currentIndex);
        });

        removeButton.addEventListener('click', function () {
            $(productSelect).select2('destroy');
            row.remove();

            if (itemsTableBody.querySelectorAll('tr').length === 0) {
                addRow();
            }

            calculateGrandTotal();
        });

        if (oldItem && oldItem.product_id) {
            $(productSelect)
                .val(String(oldItem.product_id))
                .trigger('change');

            quantityInput.value = oldItem.quantity ?? 1;
            calculateRow(currentIndex);
        }

        rowIndex++;
    }

    /*
    |--------------------------------------------------------------------------
    | PRIX ET STOCK
    |--------------------------------------------------------------------------
    */

    function updatePriceAndStock(select, index) {

        const selectedOption = select.options[select.selectedIndex];

        const price = parseFloat(
            selectedOption?.dataset?.price || 0
        );

        const stock = parseFloat(
            selectedOption?.dataset?.stock || 0
        );

        const unit =
            selectedOption?.dataset?.unit || 'Pièce';

        document.getElementById(`price_${index}`).value =
            price.toFixed(2);

        document.getElementById(`price_display_${index}`).textContent =
            new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }).format(price);
        document.getElementById(`stock_${index}`).textContent =
            stock;

        document.getElementById(`stock_unit_${index}`).textContent =
            unit;

        document.getElementById(`price_unit_${index}`).textContent =
            unit;

        calculateRow(index);
    }

    /*
    |--------------------------------------------------------------------------
    | CALCUL D’UNE LIGNE
    |--------------------------------------------------------------------------
    */

    function calculateRow(index) {

        const priceInput = document.getElementById(`price_${index}`);
        const quantityInput = document.getElementById(`qty_${index}`);
        const stockElement = document.getElementById(`stock_${index}`);
        const unitElement = document.getElementById(`stock_unit_${index}`);
        const totalElement = document.getElementById(`total_${index}`);

        if (
            !priceInput ||
            !quantityInput ||
            !stockElement ||
            !totalElement
        ) {
            return;
        }

        const price = parseFloat(priceInput.value) || 0;

        let quantity = parseFloat(quantityInput.value) || 0;
        const stock = parseFloat(stockElement.textContent) || 0;
        const unit = unitElement?.textContent || 'Pièce';

        if (quantity > stock && stock >= 0) {

            Swal.fire({
                icon: 'warning',
                title: 'Stock insuffisant',
                text: `Stock disponible : ${stock} ${unit}`,
                confirmButtonColor: '#696cff'
            });

            quantity = stock;
            quantityInput.value = stock;
        }

        const lineTotal = price * quantity;

        totalElement.textContent =
            lineTotal.toFixed(2);

        calculateGrandTotal();
    }

    /*
    |--------------------------------------------------------------------------
    | CALCUL TOTAL
    |--------------------------------------------------------------------------
    */

    function calculateGrandTotal() {

        let subtotal = 0;

        document
            .querySelectorAll('[id^="total_"]')
            .forEach(function (element) {
                subtotal +=
                    parseFloat(element.textContent) || 0;
            });

        let discountPercent =
            parseFloat(discountInput.value) || 0;

        if (discountPercent < 0) {
            discountPercent = 0;
            discountInput.value = 0;
        }

        if (discountPercent > 100) {
            discountPercent = 100;
            discountInput.value = 100;
        }

        const discountAmount =
            subtotal * discountPercent / 100;

        const taxable =
            Math.max(0, subtotal - discountAmount);

        const tva =
            taxable * 0.10;

        const total =
            taxable + tva;

        document.getElementById('subTotal').textContent =
            subtotal.toFixed(2);

        document.getElementById('discountAmount').textContent =
            discountAmount.toFixed(2);

        document.getElementById('tvaAmount').textContent =
            tva.toFixed(2);

        document.getElementById('grandTotal').textContent =
            total.toFixed(2);

        document.getElementById('final_total_input').value =
            total.toFixed(2);
    }

    /*
    |--------------------------------------------------------------------------
    | COHÉRENCE CLIENT / VÉHICULE
    |--------------------------------------------------------------------------
    */

    $('#vehicle_id').on('change', function () {

        const option =
            this.options[this.selectedIndex];

        const vehicleCustomerId =
            option?.dataset?.customerId || '';

        /*
         * Si le véhicule possède déjà un client et qu'aucun client
         * n'est encore sélectionné, sélectionner automatiquement ce client.
         */
        if (vehicleCustomerId && !customerSelect.value) {
            $('#customer_id')
                .val(vehicleCustomerId)
                .trigger('change');
        }
    });

    /*
    |--------------------------------------------------------------------------
    | SOUMISSION
    |--------------------------------------------------------------------------
    */

    saleForm.addEventListener('submit', function (event) {

        if (!customerSelect.value) {
            event.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: 'Client obligatoire',
                text: 'Veuillez sélectionner le client.',
                confirmButtonColor: '#696cff'
            });

            return;
        }

        if (!vehicleSelect.value) {
            event.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: 'Véhicule obligatoire',
                text: 'Veuillez sélectionner le numéro d’immatriculation.',
                confirmButtonColor: '#696cff'
            });

            return;
        }

        const productRows =
            itemsTableBody.querySelectorAll('tr');

        if (productRows.length === 0) {
            event.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: 'Produit obligatoire',
                text: 'Veuillez ajouter au moins un produit.',
                confirmButtonColor: '#696cff'
            });
        }
    });

    /*
    |--------------------------------------------------------------------------
    | NOUVEAU CLIENT AJAX
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('saveCustomerBtn')
        .addEventListener('click', function () {

            const code =
                document.getElementById('customer_code').value.trim();

            const name =
                document.getElementById('customer_name').value.trim();

            const phone =
                document.getElementById('customer_phone').value.trim();

            const email =
                document.getElementById('customer_email').value.trim();

            const errorBox =
                document.getElementById('customerModalError');

            errorBox.classList.add('d-none');
            errorBox.innerHTML = '';

            if (!code || !name) {
                errorBox.innerHTML =
                    'Le code et le nom du client sont obligatoires.';

                errorBox.classList.remove('d-none');
                return;
            }

            fetch("{{ route('customers.store') }}", {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },

                body: JSON.stringify({
                    code: code,
                    name: name,
                    phone: phone,
                    email: email
                })
            })
            .then(async function (response) {

                const data = await response.json();

                if (!response.ok) {
                    throw data;
                }

                return data;
            })
            .then(function (data) {

                if (!data.success || !data.customer) {
                    throw new Error(
                        'La réponse du serveur est invalide.'
                    );
                }

                const newOption = new Option(
                    data.customer.name,
                    data.customer.id,
                    true,
                    true
                );

                customerSelect.add(newOption);

                $('#customer_id')
                    .val(String(data.customer.id))
                    .trigger('change');

                document.getElementById('customer_code').value = '';
                document.getElementById('customer_name').value = '';
                document.getElementById('customer_phone').value = '';
                document.getElementById('customer_email').value = '';

                const modalElement =
                    document.getElementById('customerModal');

                const modal =
                    bootstrap.Modal.getInstance(modalElement);

                if (modal) {
                    modal.hide();
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: 'Client créé avec succès.',
                    confirmButtonColor: '#696cff'
                });
            })
            .catch(function (error) {

                let message =
                    'Une erreur est survenue pendant la création du client.';

                if (error?.errors) {
                    message = Object.values(error.errors)
                        .flat()
                        .join('<br>');
                } else if (error?.message) {
                    message = error.message;
                }

                errorBox.innerHTML = message;
                errorBox.classList.remove('d-none');
            });
        });

    /*
    |--------------------------------------------------------------------------
    | INITIALISATION
    |--------------------------------------------------------------------------
    */

    addProductButton.addEventListener('click', function () {
        addRow();
    });

    discountInput.addEventListener('input', calculateGrandTotal);

    @if(is_array(old('items')) && count(old('items')) > 0)
        const oldItems = @json(old('items'));

        oldItems.forEach(function (item) {
            addRow(item);
        });
    @else
        addRow();
    @endif

    calculateGrandTotal();
});
</script>

@endsection
