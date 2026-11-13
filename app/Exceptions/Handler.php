<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Auth;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        /*
         * Erreur 419 « Page expirée » : le jeton du formulaire ne correspond
         * plus à la session (session fermée après 10 min sans utilisation,
         * déconnexion dans un autre onglet…). Au lieu de la page 419 :
         * - déconnexion demandée, ou plus connecté : page de connexion ;
         * - sinon : retour à la page, avec un message pour renvoyer.
         */
        // Laravel transforme l'erreur de jeton en erreur HTTP 419 avant d'arriver ici.
        $this->renderable(function (HttpException $e, $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Page expirée, rechargez la page.'], 419);
            }

            if ($request->routeIs('logout') || ! Auth::check()) {
                if (Auth::check()) {
                    Auth::logout();
                }

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('timeout', "Votre session a expiré après 10 minutes d'inactivité. Veuillez vous reconnecter.");
            }

            return redirect()->back()->withInput($request->except('_token', 'password', 'password_confirmation'))
                ->with('error', 'La page avait expiré : vérifiez puis renvoyez le formulaire.');
        });
    }
}
