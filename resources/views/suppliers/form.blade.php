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

    {{-- CODE --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">
            Code fournisseur
        </label>

        <input type="text"
            name="code"
            class="form-control"
            placeholder="Ex: FR001"
            value="{{ old('code', $supplier->code ?? '') }}"
            required>

    </div>

    {{-- NOM --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">
            Nom fournisseur
        </label>

        <input type="text"
               name="name"
               class="form-control"
               value="{{ old('name', $supplier->name ?? '') }}"
               required>
    </div>


    {{-- TÉLÉPHONE --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">
            Téléphone
        </label>

        <input type="text"
               name="phone"
               class="form-control"
               value="{{ old('phone', $supplier->phone ?? '') }}">

    </div>


    {{-- EMAIL --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">
            Email
        </label>

        <input type="email"
               name="email"
               class="form-control"
               value="{{ old('email', $supplier->email ?? '') }}">

    </div>


    {{-- DEVISE --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">
            Devise
        </label>

        <select name="currency"
                class="form-control">

            <option value="XAF"
                {{ old('currency', $supplier->currency ?? '') == 'XAF' ? 'selected' : '' }}>
                XAF
            </option>

            <option value="USD"
                {{ old('currency', $supplier->currency ?? '') == 'USD' ? 'selected' : '' }}>
                USD
            </option>

            <option value="FDJ"
                {{ old('currency', $supplier->currency ?? '') == 'FDJ' ? 'selected' : '' }}>
                FDJ
            </option>

            <option value="EUR"
                {{ old('currency', $supplier->currency ?? '') == 'EUR' ? 'selected' : '' }}>
                EUR
            </option>

        </select>

    </div>


    {{-- ADRESSE --}}
    <div class="col-md-12 mb-3">

        <label class="form-label">
            Adresse
        </label>

        <textarea name="address"
                  class="form-control"
                  rows="4">{{ old('address', $supplier->address ?? '') }}</textarea>

    </div>

</div>
