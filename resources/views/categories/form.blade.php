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

    {{-- FAMILLE --}}
    <div class="col-md-12 mb-3">

        <label class="form-label">
            Famille
        </label>

        <input type="text"
               name="name"
               class="form-control"
               value="{{ old('name', $category->name ?? '') }}"
               required>

    </div>

    {{-- SOUS FAMILLES --}}
    <div class="col-md-12">

        <label class="form-label mb-3">
            Sous-familles
        </label>

        <div id="subcategories-wrapper">

           @if(isset($category) && $category->subfamilies->count())

                @foreach($category->subfamilies as $sub)

                    <div class="input-group mb-2">

                        <input type="text"
                            name="subcategories[]"
                            class="form-control"
                            value="{{ $sub->name }}">

                        <button type="button"
                                class="btn btn-danger remove-sub">

                            X

                        </button>

                    </div>

                @endforeach
            @else

                <div class="input-group mb-2">

                    <input type="text"
                           name="subcategories[]"
                           class="form-control"
                           placeholder="Sous-famille">

                    <button type="button"
                            class="btn btn-danger remove-sub">

                        X

                    </button>

                </div>

            @endif

        </div>

        <button type="button"
                class="btn btn-success mt-2"
                id="add-subcategory">

            + Ajouter sous-famille

        </button>

    </div>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const wrapper = document.getElementById('subcategories-wrapper');

    const addBtn = document.getElementById('add-subcategory');

    addBtn.addEventListener('click', function () {

        const html = `
            <div class="input-group mb-2">

                <input type="text"
                       name="subcategories[]"
                       class="form-control"
                       placeholder="Sous-famille">

                <button type="button"
                        class="btn btn-danger remove-sub">

                    X

                </button>

            </div>
        `;

        wrapper.insertAdjacentHTML('beforeend', html);
    });

    document.addEventListener('click', function (e) {

        if (e.target.classList.contains('remove-sub')) {

            e.target.closest('.input-group').remove();
        }
    });

});

</script>
