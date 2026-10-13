<?php

namespace App\Notifications;

use App\Models\ExternalBonCommande;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Bon de commande reçu du garage (app Atelier) : nouveau, ou modifié après
 * coup (devis modifié). Notification interne (cloche + son dans la page).
 */
class CommandeGarageNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected ExternalBonCommande $bc,
        protected bool $nouveau
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $vehicule = trim(($this->bc->vehicule_marque ?? '') . ' ' . ($this->bc->vehicule_modele ?? ''));
        $plaque = $this->bc->vehicule_immatriculation;
        $nbPieces = $this->bc->lignes()->count();

        $detail = trim($vehicule . ($plaque ? ' (' . $plaque . ')' : ''));
        $detail = ($detail !== '' ? $detail . ' — ' : '') . $nbPieces . ' pièce(s)';

        if ($this->nouveau) {
            $titre = 'Nouvelle commande du garage';
            $message = $this->bc->numero . ' : ' . $detail . '.';
        } else {
            $titre = 'Commande garage modifiée';
            $message = $this->bc->numero . ' a été modifié par le garage'
                . ($this->bc->bon_transfert_id ? ' : le bon de transfert est à mettre à jour.' : '.')
                . ' ' . $detail . '.';
        }

        return [
            'type'         => 'commande_garage',
            'title'        => $titre,
            'message'      => $message,
            'order_number' => $this->bc->numero,
            'url'          => route('fournisseur-commandes.show', $this->bc),
        ];
    }
}
