<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ) {

        if (!auth()->check()) {

            return redirect('/');
        }

        /*
        |--------------------------------------------------------------------------
        | UTILISATEUR INACTIF
        |--------------------------------------------------------------------------
        */

        if (!auth()->user()->is_active) {

            auth()->logout();

            return redirect('/')
                ->with(
                    'error',
                    'Compte désactivé.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | ROLE CHECK
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                auth()->user()->role,
                $roles
            )
        ) {

            abort(403);
        }

        return $next($request);
    }
}
