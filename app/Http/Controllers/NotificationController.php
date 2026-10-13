<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
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

    /*
    |--------------------------------------------------------------------------
    | NOUVELLES NOTIFICATIONS (interrogée toutes les 20 s par la page)
    |--------------------------------------------------------------------------
    |
    | Comme l'app Atelier : renvoie les notifications arrivées depuis
    | « depuis » (horodatage en millisecondes), le nombre de non lues et
    | l'horodatage de la plus récente. La page joue alors le son, affiche
    | une fenêtre en bas à droite et met la cloche à jour.
    |
    */
    public function nouvelles(Request $request): JsonResponse
    {
        $user = $request->user();
        $depuis = (int) $request->query('depuis', 0);

        $nouvelles = $user->notifications()
            ->when($depuis > 0, fn ($q) => $q->where('created_at', '>', Carbon::createFromTimestampMs($depuis)))
            ->latest()
            ->limit(20)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (DatabaseNotification $n) => [
                'id'    => $n->id,
                'ms'    => $n->created_at->getTimestampMs(),
                'titre' => $n->data['title'] ?? 'Notification',
                'corps' => $n->data['message'] ?? '',
                'url'   => route('notifications.open', $n->id),
            ]);

        $derniere = $user->notifications()->latest()->value('created_at');

        return response()->json([
            'nouvelles' => $nouvelles,
            'non_lues'  => $user->unreadNotifications()->count(),
            'dernier'   => $derniere ? Carbon::parse($derniere)->getTimestampMs() : 0,
        ]);
    }
}
