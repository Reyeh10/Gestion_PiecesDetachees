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

    {{-- REFERENCE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Référence</label>

        <input type="text"
               name="reference"
               class="form-control"
               value="{{ old('reference', $product->reference ?? '') }}"
               required>
    </div>

    {{-- DESIGNATION --}}
    <div class="col-md-8 mb-3">
        <label class="form-label">Désignation</label>

        <input type="text"
               name="designation"
               class="form-control"
               value="{{ old('designation', $product->designation ?? '') }}"
               required>
    </div>

    {{-- TYPE UNITE --}}
    <div class="col-md-3 mb-3">

        <label class="form-label">
            Type unité
        </label>

        <select name="unit_type"
                id="unit_type"
                class="form-control select2">

            <option value="piece"
                {{ old('unit_type', $product->unit_type ?? 'piece') == 'piece' ? 'selected' : '' }}>
                Pièce
            </option>

            <option value="litre"
                {{ old('unit_type', $product->unit_type ?? '') == 'litre' ? 'selected' : '' }}>
                Litre
            </option>

        </select>

    </div>

    {{-- LIBELLE UNITE --}}
    <div class="col-md-3 mb-3">

        <label class="form-label">
            Libellé unité
        </label>

        <input type="text"
            name="unit_label"
            id="unit_label"
            class="form-control"
            value="{{ old('unit_label', $product->unit_label ?? 'Pièce') }}">

    </div>

    {{-- MARQUE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Marque</label>

        <select name="brand_id"
                id="brand_id"
                class="form-control select2">

            <option value="">Sélectionner</option>

            @foreach($brands as $brand)
                <option value="{{ $brand->id }}"
                    {{ old('brand_id', $product->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                    {{ $brand->name }}
                </option>
            @endforeach

        </select>
    </div>

    {{-- MODELE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Modèle</label>

        <select name="model_id"
                id="model_id"
                class="form-control select2">

            <option value="">Sélectionner</option>

            @foreach($models as $model)
                <option value="{{ $model->id }}"
                        data-brand="{{ $model->brand_id }}"
                    {{ old('model_id', $product->model_id ?? '') == $model->id ? 'selected' : '' }}>
                    {{ $model->name }}
                </option>
            @endforeach

        </select>
    </div>

    {{-- FAMILLE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Famille</label>

        <select name="family_id"
                id="family_id"
                class="form-control select2">

            <option value="">Sélectionner</option>

            @foreach($families as $family)
                <option value="{{ $family->id }}"
                    {{ old('family_id', $product->family_id ?? '') == $family->id ? 'selected' : '' }}>
                    {{ $family->name }}
                </option>
            @endforeach

        </select>
    </div>

    {{-- SOUS FAMILLE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Sous-famille</label>

        <select name="subfamily_id"
                id="subfamily_id"
                class="form-control select2">

            <option value="">Sélectionner</option>

            @foreach($subfamilies as $sub)
                <option value="{{ $sub->id }}"
                        data-family="{{ $sub->family_id }}"
                    {{ old('subfamily_id', $product->subfamily_id ?? '') == $sub->id ? 'selected' : '' }}>
                    {{ $sub->name }}
                </option>
            @endforeach

        </select>
    </div>

    {{-- RAYON --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Rayon</label>

        <select name="rayon_id"
                id="rayon_id"
                class="form-control select2">

            <option value="">Sélectionner</option>

            @foreach($rayons as $rayon)
                <option value="{{ $rayon->id }}"
                    {{ old('rayon_id', $product->rayon_id ?? '') == $rayon->id ? 'selected' : '' }}>
                    {{ $rayon->name }}
                </option>
            @endforeach

        </select>
    </div>

    {{-- LOCATION --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Emplacement</label>

        <select name="location_id"
                id="location_id"
                class="form-control select2">

            <option value="">Sélectionner</option>

            @foreach($locations as $location)
                <option value="{{ $location->id }}"
                        data-rayon="{{ $location->rayon_id }}"
                    {{ old('location_id', $product->location_id ?? '') == $location->id ? 'selected' : '' }}>
                    {{ $location->name }}
                </option>
            @endforeach

        </select>
    </div>

    {{-- STOCK --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Quantité</label>
        <input type="number"
        step="0.01"
        name="quantity"
        class="form-control"
        value="{{ old('quantity', $product->quantity ?? 0) }}"
        required>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Seuil minimum</label>

        <input type="number"
        step="0.01"
        name="min_stock"
        class="form-control"
        value="{{ old('min_stock', $product->min_stock ?? 0) }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Seuil maximum</label>
        <input type="number"
        step="0.01"
        name="max_stock"
        class="form-control"
        value="{{ old('max_stock', $product->max_stock ?? 0) }}">
    </div>

    {{-- PRIX --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Prix achat</label>

        <input type="number"
               step="0.01"
               name="purchase_price"
               class="form-control"
               value="{{ old('purchase_price', $product->purchase_price ?? 0) }}"
               required>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Coef achat</label>

        <input type="number"
               step="0.01"
               name="coef_purchase"
               class="form-control"
               value="{{ old('coef_purchase', $product->coef_purchase ?? 1) }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Prix revient</label>

        <input type="number"
               step="0.01"
               name="cost_price"
               class="form-control"
               value="{{ old('cost_price', $product->cost_price ?? 0) }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Coef vente</label>

        <input type="number"
               step="0.01"
               name="coef_sale"
               class="form-control"
               value="{{ old('coef_sale', $product->coef_sale ?? 1) }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Prix vente</label>

        <input type="number"
               step="0.01"
               name="sale_price"
               class="form-control"
               value="{{ old('sale_price', $product->sale_price ?? 0) }}"
               required>
    </div>

    {{-- STATUS --}}
    <div class="col-md-4 mb-3">

        <label class="form-label">Status</label>

       <select name="status"
                class="form-control select2">

            <option value="">
                Tous les status
            </option>

            <option value="disponible"
                {{ old('status', $product->status ?? '') == 'disponible' ? 'selected' : '' }}>
                Disponible
            </option>

            <option value="vendu"
                {{ old('status', $product->status ?? '') == 'vendu' ? 'selected' : '' }}>
                Vendu
            </option>

        </select>

    </div>

</div>
