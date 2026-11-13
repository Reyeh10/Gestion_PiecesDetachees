<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * « Mon profil » (menu de l'avatar, en haut à droite) : chacun modifie son
 * nom, son e-mail et son mot de passe. La suppression d'un compte reste
 * dans « Utilisateurs » (permissions).
 */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return redirect()->route('profile.edit')->with('success', 'Vos informations ont été enregistrées.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('motDePasse', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.confirmed'                => 'La confirmation ne correspond pas au nouveau mot de passe.',
            'password.min'                      => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
        ]);

        $request->user()->update([
            'password'             => $validated['password'],
            'must_change_password' => false,
        ]);

        return redirect()->route('profile.edit')->with('success', 'Votre mot de passe a été changé.');
    }
}
