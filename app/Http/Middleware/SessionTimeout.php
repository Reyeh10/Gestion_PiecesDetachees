<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Déconnexion après 10 minutes sans utilisation, comme l'app Atelier.
 *
 * La page compte aussi l'inactivité et prévient 60 s avant
 * (layoutMaster) ; ici c'est la sécurité côté serveur.
 */
class SessionTimeout
{
    public const TIMEOUT = 600; // 10 minutes en secondes

    /**
     * Requêtes automatiques de la page : elles ne comptent pas comme une
     * utilisation (sinon la session ne s'arrêterait jamais).
     */
    private const AUTOMATIQUES = [
        'notifications.nouvelles',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            // Reconnexion : le compteur repart de zéro.
            $request->session()->forget('last_activity');

            return $next($request);
        }

        $lastActivity = $request->session()->get('last_activity');

        if ($lastActivity !== null && (time() - $lastActivity) > self::TIMEOUT) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'Votre session a expiré après 10 minutes d\'inactivité. Veuillez vous reconnecter.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message, 'expire' => true], 401);
            }

            return redirect()->route('login')->with('timeout', $message);
        }

        if (! $request->routeIs(...self::AUTOMATIQUES)) {
            $request->session()->put('last_activity', time());
        }

        return $next($request);
    }
}
