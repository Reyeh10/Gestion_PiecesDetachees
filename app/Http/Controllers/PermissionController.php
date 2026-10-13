<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\Request;

/**
 * Permissions personnalisées d'un utilisateur, comme dans l'app Atelier.
 *
 * Deux couches :
 *   1. permissions par défaut du rôle (PermissionService::$roleDefaults) ;
 *   2. surcharges individuelles (table user_permissions).
 *
 * Seuls les ÉCARTS par rapport au rôle sont enregistrés : si on recoche
 * exactement les permissions du rôle, l'utilisateur n'a plus aucune
 * surcharge et suit de nouveau son rôle.
 *
 * Accès : permission « gerer_utilisateurs » (route users.*). Un
 * non-administrateur ne peut ni modifier ses propres permissions, ni celles
 * d'un administrateur (qui a de toute façon tous les droits).
 */
class PermissionController extends Controller
{
    public function edit(User $user)
    {
        $this->autoriser($user);

        $user->load('userPermissions');

        return view('users.permissions', [
            'cible'    => $user,
            'groups'   => PermissionService::$groups,
            'defaults' => PermissionService::defaultsForRole($user->role),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->autoriser($user);

        $toutes   = array_keys(PermissionService::all());
        $defauts  = PermissionService::defaultsForRole($user->role);
        $cochees  = array_intersect((array) $request->input('permissions', []), $toutes);

        $user->userPermissions()->delete();

        foreach ($toutes as $cle) {
            $voulue    = in_array($cle, $cochees, true);
            $parDefaut = in_array($cle, $defauts, true);

            if ($voulue !== $parDefaut) {
                $user->userPermissions()->create([
                    'permission_key' => $cle,
                    'granted'        => $voulue,
                ]);
            }
        }

        return redirect()->route('users.index')
            ->with('success', "Permissions de {$user->name} mises à jour.");
    }

    /*
|--------------------------------------------------------------------------
| AUTORISATION — GESTION DES PERMISSIONS
|--------------------------------------------------------------------------
|
| Règles de sécurité :
|
| 1. Les permissions d'un administrateur ne peuvent pas être modifiées.
| 2. Un non-administrateur ne peut pas modifier ses propres permissions.
| 3. L'utilisateur connecté doit être correctement identifié.
|
*/

private function autoriser(User $cible): void
{
    /** @var \App\Models\User|null $utilisateur */
    $utilisateur = auth()->user();

    // Vérifier que l'utilisateur est authentifié.
    abort_unless(
        $utilisateur instanceof User,
        403,
        'Vous devez être connecté.'
    );

    // Un administrateur conserve toujours toutes ses permissions.
    abort_if(
        $cible->isAdmin(),
        403,
        'Un administrateur a toujours tous les droits : ses permissions ne se modifient pas.'
    );

    // Un non-administrateur ne peut pas modifier ses propres permissions.
    abort_if(
        ! $utilisateur->isAdmin()
            && $cible->id === $utilisateur->id,
        403,
        'Vous ne pouvez pas modifier vos propres permissions.'
    );
}
}
