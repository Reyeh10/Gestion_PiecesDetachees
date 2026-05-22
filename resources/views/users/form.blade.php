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

    {{-- NOM --}}
    <div class="col-md-6 mb-3">

        <label>Nom</label>

        <input type="text"
               name="name"
               class="form-control"
               value="{{ old('name', $user->name ?? '') }}"
               required>

    </div>

    {{-- EMAIL --}}
    <div class="col-md-6 mb-3">

        <label>Email</label>

        <input type="email"
               name="email"
               class="form-control"
               value="{{ old('email', $user->email ?? '') }}"
               required>

    </div>

    {{-- PASSWORD --}}
    <div class="col-md-6 mb-3">

        <label>Mot de passe</label>

        <input type="password"
               name="password"
               class="form-control">

    </div>

    {{-- CONFIRM PASSWORD --}}
    <div class="col-md-6 mb-3">

        <label>Confirmation mot de passe</label>

        <input type="password"
               name="password_confirmation"
               class="form-control">

    </div>

    {{-- ROLE --}}
    <div class="col-md-6 mb-3">

        <label>Rôle</label>

        <select name="role"
                class="form-control"
                required>

            <option value="admin"
                {{ old('role', $user->role ?? '') == 'admin' ? 'selected' : '' }}>
                Administrateur
            </option>

            <option value="chef_magasinier"
                {{ old('role', $user->role ?? '') == 'chef_magasinier' ? 'selected' : '' }}>
                Chef magasinier
            </option>

            <option value="magasinier"
                {{ old('role', $user->role ?? '') == 'magasinier' ? 'selected' : '' }}>
                Magasinier
            </option>

            <option value="vendeur"
                {{ old('role', $user->role ?? '') == 'vendeur' ? 'selected' : '' }}>
                Vendeur
            </option>

            <option value="caissier"
                {{ old('role', $user->role ?? '') == 'caissier' ? 'selected' : '' }}>
                Caissier
            </option>

        </select>

    </div>

    {{-- ACTIVE --}}
    <div class="col-md-6 mb-3">

        <label>Compte actif</label>

        <select name="is_active"
                class="form-control">

            <option value="1"
                {{ old('is_active', $user->is_active ?? 1) == 1 ? 'selected' : '' }}>
                Oui
            </option>

            <option value="0"
                {{ old('is_active', $user->is_active ?? 1) == 0 ? 'selected' : '' }}>
                Non
            </option>

        </select>

    </div>

</div>

<div class="mt-3">

    <button type="submit"
            class="btn btn-primary">

        Enregistrer

    </button>

    <a href="{{ route('users.index') }}"
       class="btn btn-secondary">

        Retour

    </a>

</div>
