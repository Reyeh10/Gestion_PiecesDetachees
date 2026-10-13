<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôle d'accès par permission, appliqué à toutes les pages web :
 * la permission requise par chaque route est définie dans
 * PermissionService::$routes. Utilisateur non connecté : on laisse passer,
 * le middleware `auth` de la route s'en charge.
 *
 * Bloque aussi les comptes désactivés (ce que faisait l'ancien middleware
 * `role`).
 */
class CheckRoutePermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! $user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')->with('error', 'Compte désactivé.');
        }

        $permission = PermissionService::forRoute($request->route()?->getName());

        if ($permission !== null && ! $user->hasPermission($permission)) {
            abort(403, "Vous n'avez pas accès à cette page.");
        }

        return $next($request);
    }
}
