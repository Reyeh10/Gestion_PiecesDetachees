@if($errors->any())

    <div class="alert alert-danger border-0 shadow-sm">

        <strong>
            Erreurs détectées :
        </strong>

        <ul class="mb-0 mt-2">

            @foreach($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif

<div class="row">

     {{-- CODE --}}
    <div class="col-md-6 mb-4">

        <label class="form-label fw-semibold">

            Code

        </label>

        <input type="text"
               name="code"
               class="form-control form-control-lg"
               placeholder="Ex: DP001"

               value="{{ old(
                    'code',
                    isset($depot) ? $depot->code : ''
               ) }}">

    </div>

    {{-- NOM --}}
    <div class="col-md-6 mb-4">

        <label class="form-label fw-semibold">

            Nom du dépôt

        </label>

        <input type="text"
               name="name"
               class="form-control form-control-lg"
               placeholder="Ex: Dépôt principal"

               value="{{ old(
                    'name',
                    isset($depot) ? $depot->name : ''
               ) }}"

               required>

    </div>



    {{-- ADRESSE --}}
    <div class="col-md-12 mb-4">

        <label class="form-label fw-semibold">

            Adresse

        </label>

        <input type="text"
               name="address"
               class="form-control form-control-lg"
               placeholder="Adresse du dépôt"

               value="{{ old(
                    'address',
                    isset($depot) ? $depot->address : ''
               ) }}">

    </div>

    {{-- ACTIF --}}
    <div class="col-md-12 mb-4">

        <div class="form-check form-switch">

            <input type="checkbox"
                   name="is_active"
                   value="1"
                   class="form-check-input"
                   id="is_active"

                   {{ old(
                        'is_active',
                        isset($depot)
                            ? $depot->is_active
                            : true
                   ) ? 'checked' : '' }}>

            <label class="form-check-label fw-semibold"
                   for="is_active">

                Dépôt actif

            </label>

        </div>

    </div>

</div>

{{-- BUTTONS --}}
<div class="d-flex justify-content-end gap-2 mt-4">

    <a href="{{ route('depots.index') }}"
       class="btn btn-outline-secondary px-4">

        <i class="bx bx-arrow-back"></i>

        Annuler

    </a>

    <button type="submit"
            class="btn btn-primary px-4">

        <i class="bx bx-save"></i>

        Enregistrer

    </button>

</div>
