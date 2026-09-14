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

        <input
            type="text"
            class="form-control bg-light"
            value="{{ $nextCode ?? ($supplier->code ?? '') }}"
            readonly
        >

        @if(isset($nextCode))
            <div class="form-text">
                Code généré automatiquement par le système.
            </div>
        @endif

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



    {{-- ========================================================= --}}
    {{-- DEVISE --}}
    {{-- ========================================================= --}}
    <div class="mb-3">
        <label for="currency" class="form-label">
            Devise
        </label>

        <select
            name="currency"
            id="currency"
            class="form-select @error('currency') is-invalid @enderror"
        >
            <option value="FDJ"
                {{ old('currency', $supplier->currency ?? 'FDJ') === 'FDJ' ? 'selected' : '' }}>
                FDJ - Franc Djiboutien
            </option>

            <option value="USD"
                {{ old('currency', $supplier->currency ?? 'FDJ') === 'USD' ? 'selected' : '' }}>
                USD - Dollar américain ($)
            </option>

            <option value="EUR"
                {{ old('currency', $supplier->currency ?? 'FDJ') === 'EUR' ? 'selected' : '' }}>
                EUR - Euro (€)
            </option>

            <option value="AED"
                {{ old('currency', $supplier->currency ?? 'FDJ') === 'AED' ? 'selected' : '' }}>
                AED - Dirham des Émirats arabes unis (د.إ)
            </option>

            <option value="CNY"
                {{ old('currency', $supplier->currency ?? 'FDJ') === 'CNY' ? 'selected' : '' }}>
                CNY - Yuan chinois (¥)
            </option>
        </select>

        @error('currency')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
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
