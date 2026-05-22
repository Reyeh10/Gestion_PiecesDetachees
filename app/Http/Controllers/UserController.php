<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTE UTILISATEURS
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $users = User::latest()->get();

        return view(
            'users.index',
            compact('users')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FORM CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('users.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE USER
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([

            'name' => 'required|string|max:255',

            'email' => 'required|email|unique:users,email',

            'password' => 'required|min:6|confirmed',

            'role' => 'required',

            'is_active' => 'required',

        ]);

        User::create([

            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(
                $request->password
            ),

            'role' => $request->role,
            'is_active' => $request->is_active,
            'must_change_password' => true,

        ]);

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Utilisateur créé avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW USER
    |--------------------------------------------------------------------------
    */

    public function show(string $id)
    {
        $user = User::findOrFail($id);

        return view(
            'users.show',
            compact('user')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT USER
    |--------------------------------------------------------------------------
    */

    public function edit(string $id)
    {
        $user = User::findOrFail($id);

        return view(
            'users.edit',
            compact('user')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE USER
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        string $id
    ) {

        $user = User::findOrFail($id);

        $request->validate([

            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required',
            'is_active' => 'required',

        ]);

        $data = [

            'name' => $request->name,

            'email' => $request->email,

            'role' => $request->role,

            'is_active' => $request->is_active,

        ];

        /*
        |--------------------------------------------------------------------------
        | PASSWORD OPTIONNEL
        |--------------------------------------------------------------------------
        */

        if ($request->filled('password')) {

            $request->validate([
                'password' => 'min:6|confirmed'
            ]);

            $data['password'] = Hash::make(
                $request->password
            );
        }

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Utilisateur modifié avec succès.'
            );
    }

    /*
|--------------------------------------------------------------------------
| FORM CHANGE PASSWORD
|--------------------------------------------------------------------------
*/

public function changePasswordForm()
{
    return view('users.change-password');
}

/*
|--------------------------------------------------------------------------
| CHANGE PASSWORD
|--------------------------------------------------------------------------
*/

public function changePassword(Request $request)
{
    $request->validate([

        'password' => 'required|min:6|confirmed'

    ]);

   /** @var \App\Models\User $user */
        $user = auth()->user();

    $user->password =
        Hash::make($request->password);

    $user->must_change_password = false;

    $user->save();

    return redirect()
        ->route('dashboard')
        ->with(
            'success',
            'Mot de passe modifié avec succès.'
        );
}
    /*
    |--------------------------------------------------------------------------
    | DELETE USER
    |--------------------------------------------------------------------------
    */

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | EMPÊCHER SUPPRESSION DE SON PROPRE COMPTE
        |--------------------------------------------------------------------------
        */

        if ($user->id == auth()->id()) {

            return back()->with(
                'error',
                'Impossible de supprimer votre propre compte.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | EMPÊCHER SUPPRESSION DU DERNIER ADMIN
        |--------------------------------------------------------------------------
        */

        if (
            $user->role === 'admin'
            &&
            User::where('role', 'admin')->count() <= 1
        ) {

            return back()->with(
                'error',
                'Impossible de supprimer le dernier administrateur.'
            );
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Utilisateur supprimé avec succès.'
            );
    }
}
