@extends('layouts.layoutMaster')

@section('content')

<form action="{{ route('sales.store') }}" method="POST">

@csrf

{{-- ALERTS --}}
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
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow-sm border-0">

    {{-- HEADER --}}
    <div class="card-header border-0 pb-0">
        <h3 class="mb-0 fw-bold">
            Nouvelle vente
        </h3>
    </div>

    {{-- BODY --}}
    <div class="card-body">

        <div class="row align-items-end">

            {{-- CLIENT --}}
            <div class="col-md-6 mb-3">

                <div class="d-flex justify-content-between align-items-center mb-2">

                    <label class="form-label fw-semibold mb-0">
                        Client
                    </label>

                    <button type="button"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#customerModal">

                        <i class="bx bx-plus"></i>
                        Nouveau client
                    </button>

                </div>

                <select name="customer_id"
                        class="form-control select2">

                    <option value="">
                        Vente comptoir
                    </option>

                    @foreach($customers as $c)

                        <option value="{{ $c->id }}"
                            {{ old('customer_id') == $c->id ? 'selected' : '' }}>

                            {{ $c->name }}

                        </option>

                    @endforeach

                </select>

            </div>

            {{-- PAIEMENT --}}
            <div class="col-md-3 mb-3">

                <label class="form-label fw-semibold">
                    Paiement
                </label>

                <select name="payment_type"
                        class="form-control"
                        required>

                    <option value="cash">
                        Cash
                    </option>

                    <option value="bon_commande">
                        Bon de commande
                    </option>

                </select>

            </div>

        </div>

        {{-- TABLE --}}
        <div class="table-responsive mt-4">

            <table class="table table-bordered align-middle"
                   id="itemsTable">

                <thead class="table-light">

                    <tr>

                        <th width="55%">
                            Référence / Produit
                        </th>

                        <th width="10%"
                            class="text-center">

                            Stock
                        </th>

                        <th width="15%">
                            Prix unitaire
                        </th>

                        <th width="10%">
                            Qté
                        </th>

                        <th width="15%">
                            Total
                        </th>

                        <th width="10%"
                            class="text-center">

                            Action
                        </th>

                    </tr>

                </thead>

                <tbody></tbody>

            </table>

        </div>

        {{-- BTN AJOUT --}}
        <button type="button"
                class="btn btn-success mt-2"
                onclick="addRow()">

            <i class="bx bx-plus"></i>
            Ajouter produit

        </button>

        <hr class="my-4">

        {{-- TOTALS --}}
        <div class="row justify-content-end">

            <div class="col-md-4">

                <div class="d-flex justify-content-between mb-2">

                    <strong>
                        Sous-total :
                    </strong>

                    <span id="subTotal">
                        0.00
                    </span>

                </div>

                {{-- REMISE --}}
                <div class="mb-3">

                    <label class="form-label">
                        Remise
                    </label>

                    <input type="number"
                           step="0.01"
                          min="0"
                           name="discount"
                           id="discount"
                           value="0"
                           class="form-control"
                           oninput="calculateGrandTotal()">

                </div>

                {{-- TVA --}}
                <div class="d-flex justify-content-between mb-2">

                    <strong>
                        TVA (10%)
                    </strong>

                    <span id="tvaAmount">
                        0.00
                    </span>

                </div>

                {{-- TOTAL --}}
                <div class="d-flex justify-content-between align-items-center mt-4">

                    <h4 class="mb-0 fw-bold">
                        Total :
                    </h4>

                    <h2 class="text-primary fw-bold mb-0">

                        <span id="grandTotal">
                            0.00
                        </span>

                    </h2>

                </div>

            </div>

        </div>

        <input type="hidden"
               name="final_total"
               id="final_total_input">

    </div>

    {{-- FOOTER --}}
    <div class="card-footer bg-white text-end border-0">

        <button type="submit"
                class="btn btn-primary px-4">

            <i class="bx bx-check-circle"></i>
            Valider vente

        </button>

    </div>

</div>

</form>

{{-- ================= MODAL CLIENT ================= --}}
<div class="modal fade"
     id="customerModal"
     tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Nouveau client
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div id="customerModalError"
                     class="alert alert-danger d-none">
                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Code *
                    </label>

                    <input type="text"
                        id="customer_code"
                        class="form-control">

                </div>
                <div class="mb-3">
                    <label class="form-label">
                        Nom *
                    </label>

                    <input type="text"
                           id="customer_name"
                           class="form-control">
                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Téléphone
                    </label>

                    <input type="text"
                           id="customer_phone"
                           class="form-control">

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Email
                    </label>

                    <input type="email"
                           id="customer_email"
                           class="form-control">

                </div>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                    Annuler

                </button>

                <button type="button"
                        id="saveCustomerBtn"
                        class="btn btn-primary">

                    Enregistrer

                </button>

            </div>

        </div>

    </div>

</div>

{{-- ================= SCRIPT ================= --}}
<script>

let rowIndex = 0;

/*
|--------------------------------------------------------------------------
| ADD ROW
|--------------------------------------------------------------------------
*/

function addRow()
{
    const table =
        document.querySelector('#itemsTable tbody');

    const row = `

        <tr>

            {{-- PRODUIT --}}
            <td>

                <select name="items[${rowIndex}][product_id]"
                    class="form-control product-select product-large-select"
                        required
                        onchange="updatePriceAndStock(this, ${rowIndex})">

                    <option value="">
                        Choisir produit
                    </option>

                    @foreach($products as $product)

                <option value="{{ $product->id }}"
                    data-price="{{ $product->sale_price }}"
                    data-stock="{{ $product->quantity }}"
                    data-unit="{{ $product->unit_label }}">

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

            {{-- STOCK --}}
            <td class="text-center fw-bold">

               <span id="stock_${rowIndex}">
                    0
                </span>

                <small id="stock_unit_${rowIndex}">
                    Pièce
                </small>

            </td>

            {{-- PRIX --}}
            <td>

                <div class="input-group">

                    <input type="number"
                        step="0.01"
                        name="items[${rowIndex}][price]"
                        id="price_${rowIndex}"
                        class="form-control"
                        readonly>

                    <span class="input-group-text">

                        <span id="price_unit_${rowIndex}">
                            Pièce
                        </span>

                    </span>

                </div>

            </td>

            {{-- QUANTITE --}}
            <td>

                <input type="number"
                    step="0.01"
                    min="0.01"
                    name="items[${rowIndex}][quantity]"
                    id="qty_${rowIndex}"
                    class="form-control"
                    oninput="calculateRow(${rowIndex})"
                    required>

            </td>

            {{-- TOTAL --}}
            <td class="fw-bold">

                <span id="total_${rowIndex}">
                    0.00
                </span>

            </td>

            {{-- ACTION --}}
            <td class="text-center">

                <button type="button"
                        class="btn btn-danger btn-sm"
                        onclick="removeRow(this)">

                    X

                </button>

            </td>

        </tr>

    `;

    table.insertAdjacentHTML('beforeend', row);

            const newSelect =
            table.querySelector('tr:last-child .product-select');

        $(newSelect).select2({

            width: '100%',

            placeholder:
                'Rechercher par référence, désignation, marque ou modèle',

            allowClear: true

        });

    rowIndex++;
}

/*
|--------------------------------------------------------------------------
| UPDATE PRICE
|--------------------------------------------------------------------------
*/

function updatePriceAndStock(select, index)
{
    const option =
        select.options[select.selectedIndex];

    const price =
        parseFloat(option.dataset.price || 0);

    const stock =
        parseFloat(option.dataset.stock || 0);
        const unit =
    option.dataset.unit || 'Pièce';

    document.getElementById(
        `price_${index}`
    ).value = parseFloat(price).toFixed(2);

    document.getElementById(
        `stock_${index}`
    ).innerText = stock;

    document.getElementById(
    `stock_unit_${index}`
    ).innerText = unit;

    document.getElementById(
        `price_unit_${index}`
    ).innerText = unit;

    calculateRow(index);
}

/*
|--------------------------------------------------------------------------
| CALCUL LIGNE
|--------------------------------------------------------------------------
*/

function calculateRow(index)
{
    const price = parseFloat(
        document.getElementById(
            `price_${index}`
        ).value
    ) || 0;

    const qty = parseFloat(
        document.getElementById(
            `qty_${index}`
        ).value
    ) || 0;

    const stock = parseFloat(
        document.getElementById(
            `stock_${index}`
        ).innerText
    ) || 0;

    const unit =
    document.getElementById(
        `stock_unit_${index}`
    ).innerText;
    /*
    |--------------------------------------------------------------------------
    | VERIFICATION STOCK
    |--------------------------------------------------------------------------
    */

    if (qty > stock) {

        Swal.fire({

            icon: 'warning',

            title: 'Stock insuffisant',

            text:
              'Stock disponible : ' + stock + ' ' + unit,

            confirmButtonColor: '#696cff'
        });

        document.getElementById(
            `qty_${index}`
        ).value = stock;
    }

    const finalQty = parseFloat(
        document.getElementById(
            `qty_${index}`
        ).value
    ) || 0;

    const total = price * finalQty;

    document.getElementById(
        `total_${index}`
    ).innerText = total.toFixed(2);

    calculateGrandTotal();
}

/*
|--------------------------------------------------------------------------
| GRAND TOTAL
|--------------------------------------------------------------------------
*/

function calculateGrandTotal()
{
    let subtotal = 0;

    document.querySelectorAll('[id^="total_"]')
        .forEach(el => {

            subtotal +=
                parseFloat(el.innerText) || 0;

        });

    const discount = parseFloat(
        document.getElementById('discount').value
    ) || 0;

    const discountAmount =
    (subtotal * discount) / 100;

    const taxable =
        subtotal - discountAmount;

    const tva = taxable * 0.10;

    let total = taxable + tva;

    if (total < 0) {

        total = 0;
    }

    document.getElementById(
        'subTotal'
    ).innerText = subtotal.toFixed(2);

    document.getElementById(
        'tvaAmount'
    ).innerText = tva.toFixed(2);

    document.getElementById(
    'grandTotal'
    ).innerText = Math.round(total);

    document.getElementById(
        'final_total_input'
    ).value = Math.round(total);
}

/*
|--------------------------------------------------------------------------
| REMOVE ROW
|--------------------------------------------------------------------------
*/

function removeRow(button)
{
    button.closest('tr').remove();

    calculateGrandTotal();
}

/*
|--------------------------------------------------------------------------
| DEFAULT ROW
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', function () {

    addRow();

});

/*
|--------------------------------------------------------------------------
| CREATE CUSTOMER AJAX
|--------------------------------------------------------------------------
*/

document.getElementById(
    'saveCustomerBtn'
).addEventListener('click', function () {

    const code =
    document.getElementById(
        'customer_code'
    ).value.trim();

    document.getElementById(
            'customer_code'
        ).value = '';

    const name =
        document.getElementById(
            'customer_name'
        ).value.trim();

    const phone =
        document.getElementById(
            'customer_phone'
        ).value.trim();

    const email =
        document.getElementById(
            'customer_email'
        ).value.trim();

    const errorBox =
        document.getElementById(
            'customerModalError'
        );

    errorBox.classList.add('d-none');

    errorBox.innerHTML = '';

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

   if (!code || !name) {

        errorBox.innerHTML =
            'Le code et le nom du client sont obligatoires.';

        errorBox.classList.remove('d-none');

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX
    |--------------------------------------------------------------------------
    */

    fetch("{{ route('customers.store') }}", {

        method: "POST",

        headers: {

            "Content-Type": "application/json",

            "X-CSRF-TOKEN":
                "{{ csrf_token() }}",

            "Accept":
                "application/json"
        },

       body: JSON.stringify({

            code: code,

            name: name,

            phone: phone,

            email: email
        })

    })

    .then(async response => {

        const data = await response.json();

        if (!response.ok) {

            throw data;
        }

        return data;
    })

    .then(data => {

        if (data.success) {

            const select =
                document.querySelector(
                    'select[name="customer_id"]'
                );

            const option = new Option(

                data.customer.name,

                data.customer.id,

                true,

                true
            );

            select.add(option);

            /*
            |--------------------------------------------------------------------------
            | RESET
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                'customer_name'
            ).value = '';

            document.getElementById(
                'customer_phone'
            ).value = '';

            document.getElementById(
                'customer_email'
            ).value = '';

            /*
            |--------------------------------------------------------------------------
            | CLOSE MODAL
            |--------------------------------------------------------------------------
            */

            const modal =
                bootstrap.Modal.getInstance(

                    document.getElementById(
                        'customerModal'
                    )
                );

            modal.hide();

            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            Swal.fire({

                icon: 'success',

                title: 'Succès',

                text:
                    'Client créé avec succès.',

                confirmButtonColor:
                    '#696cff'
            });
        }

    })

        .catch(error => {

        let message = JSON.stringify(error);

        console.log(error);

        if (error.errors) {

            message =
                Object.values(error.errors)
                    .flat()
                    .join('<br>');
        }

        errorBox.innerHTML = message;

        errorBox.classList.remove('d-none');
    });

});

</script>

@endsection
