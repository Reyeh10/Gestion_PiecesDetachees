<div class="row">

    {{-- FOURNISSEUR --}}
    <div class="col-12 mb-4">

        <label class="form-label fw-bold">
            Fournisseur
        </label>

        <select
            name="supplier_id"
            id="supplier_id"
            class="form-control"
            required>

            <option value="">
                -- Sélectionner fournisseur --
            </option>

            @foreach($suppliers as $supplier)

                <option value="{{ $supplier->id }}">

                    {{ $supplier->name }}

                </option>

            @endforeach

        </select>

    </div>

</div>

{{-- TABLE PRODUITS --}}
<div class="table-responsive">

    <table class="table table-bordered align-middle">

        <thead class="table-light">

            <tr>

                <th width="35%">
                    Produit
                </th>

                <th width="10%">
                    Stock
                </th>

                <th width="15%">
                    Prix
                </th>

                <th width="15%">
                    Quantité
                </th>

                <th width="15%">
                    Total
                </th>

                <th width="10%">
                    Action
                </th>

            </tr>

        </thead>

        <tbody id="purchase-items-body">

        </tbody>

    </table>

</div>

<div class="mb-3">

    <button
        type="button"
        id="add-product-btn"
        class="btn btn-success">

        + Ajouter produit

    </button>

</div>

<div class="text-end mb-4">

    <h3>

        Total :
        <span id="grand-total">
            0.00
        </span>
        $

    </h3>

</div>

<div class="text-end">

    <button
        type="submit"
        class="btn btn-primary">

        Enregistrer achat

    </button>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ELEMENTS
    |--------------------------------------------------------------------------
    */

    const supplierSelect =
        document.getElementById('supplier_id');

    const addButton =
        document.getElementById('add-product-btn');

    const tbody =
        document.getElementById('purchase-items-body');

    const totalElement =
        document.getElementById('grand-total');

    let supplierProducts = [];

    let rowIndex = 0;

    /*
    |--------------------------------------------------------------------------
    | LOAD PRODUITS FOURNISSEUR
    |--------------------------------------------------------------------------
    */

    supplierSelect.addEventListener('change', function () {

        const supplierId = this.value;

        tbody.innerHTML = '';

        totalElement.innerHTML = '0.00';

        if (!supplierId) {

            supplierProducts = [];

            return;
        }

        fetch(
            `/purchases/supplier-products/${supplierId}`
        )
        .then(response => response.json())
        .then(data => {

            supplierProducts = data;

            console.log(data);

        })
        .catch(error => {

            console.error(error);

            alert(
                'Erreur chargement produits fournisseur'
            );

        });

    });

    /*
    |--------------------------------------------------------------------------
    | AJOUTER LIGNE
    |--------------------------------------------------------------------------
    */

    addButton.addEventListener('click', function () {

        const supplierId =
            supplierSelect.value;

        if (!supplierId) {

            alert(
                'Veuillez sélectionner un fournisseur.'
            );

            return;
        }

        if (supplierProducts.length === 0) {

            alert(
                'Aucun produit pour ce fournisseur.'
            );

            return;
        }

        let options = '';

        supplierProducts.forEach(product => {

            options += `

                <option
                    value="${product.id}"
                    data-price="${product.purchase_price}"
                    data-stock="${product.stock}">

                    ${product.reference}
                    -
                    ${product.designation}

                </option>

            `;
        });

        const row = `

            <tr>

                <td>

                    <select
                        name="items[${rowIndex}][product_id]"
                        class="form-control product-select"
                        required>

                        <option value="">
                            -- Produit --
                        </option>

                        ${options}

                    </select>

                </td>

                <td>

                    <input
                        type="text"
                        class="form-control stock-input"
                        readonly>

                </td>

                <td>

                    <input
                        type="number"
                        step="0.01"
                        name="items[${rowIndex}][price]"
                        class="form-control price-input"
                        required>

                </td>

                <td>

                    <input
                        type="number"
                        min="1"
                        name="items[${rowIndex}][quantity]"
                        class="form-control quantity-input"
                        value="1"
                        required>

                </td>

                <td>

                    <input
                        type="text"
                        class="form-control total-input"
                        readonly>

                </td>

                <td>

                    <button
                        type="button"
                        class="btn btn-danger remove-row">

                        X

                    </button>

                </td>

            </tr>

        `;

        tbody.insertAdjacentHTML(
            'beforeend',
            row
        );

        rowIndex++;

    });

    /*
    |--------------------------------------------------------------------------
    | EVENTS TABLE
    |--------------------------------------------------------------------------
    */

    tbody.addEventListener('change', function (e) {

        /*
        |--------------------------------------------------------------------------
        | PRODUIT CHANGE
        |--------------------------------------------------------------------------
        */

        if (
            e.target.classList.contains(
                'product-select'
            )
        ) {

            const row =
                e.target.closest('tr');

            const option =
                e.target.selectedOptions[0];

            const stock =
                option.dataset.stock || 0;

            const price =
                option.dataset.price || 0;

            row.querySelector(
                '.stock-input'
            ).value = stock;

            row.querySelector(
                '.price-input'
            ).value = price;

            calculateRow(row);
        }

    });

    /*
    |--------------------------------------------------------------------------
    | INPUT EVENTS
    |--------------------------------------------------------------------------
    */

    tbody.addEventListener('input', function (e) {

        if (

            e.target.classList.contains(
                'price-input'
            )

            ||

            e.target.classList.contains(
                'quantity-input'
            )

        ) {

            const row =
                e.target.closest('tr');

            calculateRow(row);
        }

    });

    /*
    |--------------------------------------------------------------------------
    | REMOVE ROW
    |--------------------------------------------------------------------------
    */

    tbody.addEventListener('click', function (e) {

        if (
            e.target.classList.contains(
                'remove-row'
            )
        ) {

            e.target
                .closest('tr')
                .remove();

            calculateGrandTotal();
        }

    });

    /*
    |--------------------------------------------------------------------------
    | CALCUL LIGNE
    |--------------------------------------------------------------------------
    */

    function calculateRow(row)
    {
        const price = parseFloat(
            row.querySelector('.price-input').value
        ) || 0;

        const quantity = parseFloat(
            row.querySelector('.quantity-input').value
        ) || 0;

        const total =
            price * quantity;

        row.querySelector(
            '.total-input'
        ).value = total.toFixed(2);

        calculateGrandTotal();
    }

    /*
    |--------------------------------------------------------------------------
    | CALCUL TOTAL
    |--------------------------------------------------------------------------
    */

    function calculateGrandTotal()
    {
        let grandTotal = 0;

        document.querySelectorAll(
            '.total-input'
        ).forEach(input => {

            grandTotal +=
                parseFloat(input.value) || 0;
        });

        totalElement.innerHTML =
            grandTotal.toFixed(2);
    }

});

</script>
