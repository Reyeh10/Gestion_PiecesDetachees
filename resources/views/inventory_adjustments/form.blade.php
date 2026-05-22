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

<div class="row">

    {{-- PRODUIT --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">
            Produit
        </label>

        <select name="product_id"
                class="form-control"
                required>

            <option value="">
                -- Sélectionner --
            </option>

            @foreach($products as $product)

                <option value="{{ $product->id }}"

                    {{ old('product_id',
                        $inventoryAdjustment->product_id ?? ''
                    ) == $product->id ? 'selected' : '' }}>

                    {{ $product->reference }}
                    -
                    {{ $product->designation }}

                </option>

            @endforeach

        </select>

    </div>

    {{-- NOUVELLE QUANTITE --}}
    <div class="col-md-3 mb-3">

        <label class="form-label">
            Nouvelle quantité
        </label>

        <input type="number"
               min="0"
               name="new_qty"
               class="form-control"
               value="{{ old('new_qty',
                    $inventoryAdjustment->new_qty ?? 0
               ) }}"
               required>

    </div>

    {{-- RAISON --}}
    <div class="col-md-12 mb-3">

        <label class="form-label">
            Raison de l'ajustement
        </label>

        <textarea name="reason"
                  rows="4"
                  class="form-control"
                  required>{{ old('reason',
                        $inventoryAdjustment->reason ?? ''
                  ) }}</textarea>

    </div>

</div>
