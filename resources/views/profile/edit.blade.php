@extends('layouts.layoutMaster')

@section('title', 'Mon profil')

@section('content')

<style>
    .profil-grille {
        display: grid;
        grid-template-columns: 320px 1fr 1fr;
        gap: 20px;
        align-items: start;
    }

    .profil-carte {
        padding: 22px;
        background: var(--stcd-surface, #fff);
        border: 1px solid var(--stcd-border, #E6EAED);
        border-radius: 10px;
    }

    .profil-carte h5 {
        margin: 0 0 4px;
        color: var(--stcd-text, #212B36);
        font-size: 16px;
        font-weight: 800;
    }

    .profil-carte .profil-aide {
        margin: 0 0 18px;
        color: var(--stcd-muted, #8a9198);
        font-size: 13px;
    }

    .profil-identite { text-align: center; }

    .profil-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 84px;
        height: 84px;
        margin: 4px auto 14px;
        color: #fff;
        background: linear-gradient(135deg, var(--stcd-primary, #FE9F43), #ff7a45);
        border-radius: 50%;
        font-size: 28px;
        font-weight: 800;
    }

    .profil-nom { color: var(--stcd-text, #212B36); font-size: 18px; font-weight: 800; }
    .profil-mail { color: var(--stcd-muted, #8a9198); font-size: 13.5px; word-break: break-all; }

    .profil-role {
        display: inline-block;
        margin-top: 10px;
        padding: 4px 12px;
        color: var(--stcd-primary, #FE9F43);
        background: rgba(254, 159, 67, .12);
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 800;
    }

    .profil-infos {
        margin: 18px 0 0;
        padding: 14px 0 0;
        border-top: 1px solid var(--stcd-border, #E6EAED);
        font-size: 13px;
        text-align: left;
    }

    .profil-infos div { display: flex; justify-content: space-between; padding: 4px 0; }
    .profil-infos span { color: var(--stcd-muted, #8a9198); }

    .profil-champ { margin-bottom: 14px; }
    .profil-champ label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 700; }
    .profil-erreur { margin-top: 4px; color: #dc2626; font-size: 12.5px; }

    @media (max-width: 1199.98px) {
        .profil-grille { grid-template-columns: 1fr 1fr; }
        .profil-identite { grid-column: 1 / -1; }
    }

    @media (max-width: 767.98px) {
        .profil-grille { grid-template-columns: 1fr; }
    }
</style>

@php
    $initiales = collect(preg_split('/\s+/', trim((string) $user->name)))
        ->filter()->take(2)
        ->map(fn ($mot) => mb_strtoupper(mb_substr($mot, 0, 1)))
        ->implode('') ?: 'U';
@endphp

<h4 class="mb-1">Mon profil</h4>
<p class="text-muted mb-4">Vos informations personnelles et votre mot de passe.</p>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="profil-grille">

    {{-- Identité --}}
    <div class="profil-carte profil-identite">
        <div class="profil-avatar">{{ $initiales }}</div>
        <div class="profil-nom">{{ $user->name }}</div>
        <div class="profil-mail">{{ $user->email }}</div>
        <span class="profil-role">{{ $user->getRoleLabel() }}</span>

        <div class="profil-infos">
            <div><span>Compte</span><strong>{{ $user->is_active ? 'Actif' : 'Désactivé' }}</strong></div>
            <div><span>Créé le</span><strong>{{ optional($user->created_at)->format('d/m/Y') }}</strong></div>
        </div>
    </div>

    {{-- Informations --}}
    <form method="POST" action="{{ route('profile.update') }}" class="profil-carte">
        @csrf
        @method('PUT')

        <h5>Mes informations</h5>
        <p class="profil-aide">Nom affiché dans l'application et e-mail de connexion.</p>

        <div class="profil-champ">
            <label for="name">Nom</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required maxlength="255">
            @error('name') <div class="profil-erreur">{{ $message }}</div> @enderror
        </div>

        <div class="profil-champ">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required maxlength="255">
            @error('email') <div class="profil-erreur">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>

    {{-- Mot de passe --}}
    <form method="POST" action="{{ route('profile.password') }}" class="profil-carte">
        @csrf
        @method('PUT')

        <h5>Mot de passe</h5>
        <p class="profil-aide">Au moins 8 caractères. Le mot de passe actuel est demandé.</p>

        <div class="profil-champ">
            <label for="current_password">Mot de passe actuel</label>
            <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password" required>
            @error('current_password', 'motDePasse') <div class="profil-erreur">{{ $message }}</div> @enderror
        </div>

        <div class="profil-champ">
            <label for="password">Nouveau mot de passe</label>
            <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" required>
            @error('password', 'motDePasse') <div class="profil-erreur">{{ $message }}</div> @enderror
        </div>

        <div class="profil-champ">
            <label for="password_confirmation">Confirmer le nouveau mot de passe</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" required>
        </div>

        <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
    </form>

</div>

@endsection
