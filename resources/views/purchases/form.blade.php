<div class="row">

    {{-- FOURNISSEUR --}}
    <div class="col-md-6 mb-4">

        <label
            for="supplier_id"
            class="form-label fw-bold"
        >
            Fournisseur
            <span class="text-danger">*</span>
        </label>

        <select
            name="supplier_id"
            id="supplier_id"
            class="form-select @error('supplier_id') is-invalid @enderror"
            required
        >

            <option value="">
                -- Sélectionner fournisseur --
            </option>

            @foreach($suppliers as $supplier)

                <option
                    value="{{ $supplier->id }}"
                    @selected(
                        old('supplier_id') == $supplier->id
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


    {{-- DÉPÔT DE RÉCEPTION --}}
    <div class="col-md-6 mb-4">

        <label
            for="depot_id"
            class="form-label fw-bold"
        >
            Dépôt de réception
            <span class="text-danger">*</span>
        </label>

        <select
            name="depot_id"
            id="depot_id"
            class="form-select @error('depot_id') is-invalid @enderror"
            required
        >

            <option value="">
                -- Sélectionner le dépôt --
            </option>

            @foreach($depots as $depot)

                <option
                    value="{{ $depot->id }}"
                    @selected(
                        old('depot_id') == $depot->id
                    )
                >
                    {{ $depot->code ? $depot->code . ' - ' : '' }}
                    {{ $depot->name }}
                </option>

            @endforeach

        </select>

        @error('depot_id')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

        <div class="form-text">
            Toutes les pièces de cet achat seront réceptionnées
            dans ce dépôt.
        </div>

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
                    Stock total
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
        class="btn btn-success"
    >
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
        class="btn btn-primary"
    >
        Enregistrer achat
    </button>

</div>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | ELEMENTS
        |--------------------------------------------------------------------------
        */

        const supplierSelect =
            document.getElementById(
                'supplier_id'
            );

        const depotSelect =
            document.getElementById(
                'depot_id'
            );

        const addButton =
            document.getElementById(
                'add-product-btn'
            );

        const tbody =
            document.getElementById(
                'purchase-items-body'
            );

        const totalElement =
            document.getElementById(
                'grand-total'
            );

        let supplierProducts = [];

        let rowIndex = 0;


        /*
        |--------------------------------------------------------------------------
        | CHARGER PRODUITS FOURNISSEUR
        |--------------------------------------------------------------------------
        */

        supplierSelect.addEventListener(
            'change',
            function () {

                const supplierId =
                    this.value;

                tbody.innerHTML = '';

                totalElement.innerHTML =
                    '0.00';

                supplierProducts = [];

                if (!supplierId) {
                    return;
                }

                fetch(
                    `/purchases/supplier-products/${supplierId}`
                )
                .then(response => {

                    if (!response.ok) {

                        throw new Error(
                            'Erreur HTTP '
                            +
                            response.status
                        );
                    }

                    return response.json();
                })
                .then(data => {

                    supplierProducts =
                        data;
                })
                .catch(error => {

                    console.error(
                        error
                    );

                    alert(
                        'Erreur lors du chargement '
                        +
                        'des produits du fournisseur.'
                    );
                });
            }
        );


        /*
        |--------------------------------------------------------------------------
        | AJOUTER LIGNE
        |--------------------------------------------------------------------------
        */

        addButton.addEventListener(
            'click',
            function () {

                const supplierId =
                    supplierSelect.value;

                const depotId =
                    depotSelect.value;

                if (!supplierId) {

                    alert(
                        'Veuillez sélectionner '
                        +
                        'un fournisseur.'
                    );

                    return;
                }

                if (!depotId) {

                    alert(
                        'Veuillez sélectionner '
                        +
                        'le dépôt de réception.'
                    );

                    return;
                }

                if (
                    supplierProducts.length === 0
                ) {

                    alert(
                        'Aucun produit disponible '
                        +
                        'pour ce fournisseur.'
                    );

                    return;
                }

                let options = '';

                supplierProducts.forEach(
                    product => {

                        options += `

                            <option
                                value="${product.id}"
                                data-price="${product.purchase_price}"
                                data-stock="${product.stock}"
                            >
                                ${product.reference ?? ''}
                                -
                                ${product.designation ?? ''}
                            </option>
                        `;
                    }
                );

                const row = `

                    <tr>

                        <td>

                            <select
                                name="items[${rowIndex}][product_id]"
                                class="form-select product-select"
                                required
                            >

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
                                value="0"
                                readonly
                            >

                        </td>


                        <td>

                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="items[${rowIndex}][price]"
                                class="form-control price-input"
                                required
                            >

                        </td>


                        <td>

                            <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                name="items[${rowIndex}][quantity]"
                                class="form-control quantity-input"
                                value="1"
                                required
                            >

                        </td>


                        <td>

                            <input
                                type="text"
                                class="form-control total-input"
                                value="0.00"
                                readonly
                            >

                        </td>


                        <td>

                            <button
                                type="button"
                                class="btn btn-danger remove-row"
                            >
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
            }
        );


        /*
        |--------------------------------------------------------------------------
        | PRODUIT CHANGE
        |--------------------------------------------------------------------------
        */

        tbody.addEventListener(
            'change',
            function (e) {

                if (
                    !e.target.classList.contains(
                        'product-select'
                    )
                ) {
                    return;
                }

                const row =
                    e.target.closest('tr');

                const option =
                    e.target.selectedOptions[0];

                const stock =
                    option?.dataset?.stock
                    ??
                    0;

                const price =
                    option?.dataset?.price
                    ??
                    0;

                row.querySelector(
                    '.stock-input'
                ).value =
                    stock;

                row.querySelector(
                    '.price-input'
                ).value =
                    price;

                calculateRow(
                    row
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | PRIX / QUANTITÉ
        |--------------------------------------------------------------------------
        */

        tbody.addEventListener(
            'input',
            function (e) {

                if (
                    e.target.classList.contains(
                        'price-input'
                    )
                    ||
                    e.target.classList.contains(
                        'quantity-input'
                    )
                ) {

                    calculateRow(
                        e.target.closest('tr')
                    );
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | SUPPRIMER LIGNE
        |--------------------------------------------------------------------------
        */

        tbody.addEventListener(
            'click',
            function (e) {

                if (
                    !e.target.classList.contains(
                        'remove-row'
                    )
                ) {
                    return;
                }

                e.target
                    .closest('tr')
                    .remove();

                calculateGrandTotal();
            }
        );


        /*
        |--------------------------------------------------------------------------
        | CALCUL LIGNE
        |--------------------------------------------------------------------------
        */

        function calculateRow(row)
        {
            const price =
                parseFloat(
                    row.querySelector(
                        '.price-input'
                    ).value
                )
                ||
                0;

            const quantity =
                parseFloat(
                    row.querySelector(
                        '.quantity-input'
                    ).value
                )
                ||
                0;

            const total =
                price * quantity;

            row.querySelector(
                '.total-input'
            ).value =
                total.toFixed(2);

            calculateGrandTotal();
        }


        /*
        |--------------------------------------------------------------------------
        | TOTAL GÉNÉRAL
        |--------------------------------------------------------------------------
        */

        function calculateGrandTotal()
        {
            let grandTotal = 0;

            document.querySelectorAll(
                '.total-input'
            )
            .forEach(input => {

                grandTotal +=
                    parseFloat(
                        input.value
                    )
                    ||
                    0;
            });

            totalElement.innerHTML =
                grandTotal.toFixed(2);
        }
    }
);

</script>
