<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | OUVRIR UNE NOTIFICATION
    |--------------------------------------------------------------------------
    |
    | - Vérifie que la notification appartient bien à l'utilisateur connecté.
    | - Marque la notification comme lue.
    | - Redirige vers l'URL enregistrée dans la notification.
    |
    */

    public function open(
        Request $request,
        string $notification
    ): RedirectResponse {

        $user = $request->user();

        $databaseNotification = DatabaseNotification::query()
            ->where('id', $notification)
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey())
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | MARQUER COMME LUE
        |--------------------------------------------------------------------------
        */

        if (is_null($databaseNotification->read_at)) {
            $databaseNotification->markAsRead();
        }

        /*
        |--------------------------------------------------------------------------
        | RÉCUPÉRER L'URL
        |--------------------------------------------------------------------------
        */

        $url = $databaseNotification->data['url'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | REDIRECTION
        |--------------------------------------------------------------------------
        */

        if (!empty($url)) {
            return redirect()->to($url);
        }

        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                'Notification marquée comme lue.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | MARQUER TOUTES LES NOTIFICATIONS COMME LUES
    |--------------------------------------------------------------------------
    */

    public function markAllAsRead(
        Request $request
    ): RedirectResponse {

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | UNIQUEMENT LES NOTIFICATIONS NON LUES DE L'UTILISATEUR
        |--------------------------------------------------------------------------
        */

        $user->unreadNotifications->markAsRead();

        return back()->with(
            'success',
            'Toutes les notifications ont été marquées comme lues.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SUPPRIMER LES NOTIFICATIONS DÉJÀ LUES
    |--------------------------------------------------------------------------
    |
    | Les notifications non lues sont conservées.
    |
    */

    public function clearRead(
        Request $request
    ): RedirectResponse {

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | SUPPRESSION UNIQUEMENT DES NOTIFICATIONS LUES
        |--------------------------------------------------------------------------
        */

        $user->readNotifications()->delete();

        return back()->with(
            'success',
            'Les notifications lues ont été supprimées.'
        );
    }
}
