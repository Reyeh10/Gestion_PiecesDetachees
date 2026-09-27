<?php

namespace App\Notifications;

use App\Models\SupplierOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewSupplierOrderNotification extends Notification
{
    use Queueable;

    /**
     * Bon de commande concerné.
     */
    protected SupplierOrder $supplierOrder;

    /**
     * Constructeur.
     */
    public function __construct(
        SupplierOrder $supplierOrder
    ) {
        $this->supplierOrder = $supplierOrder;
    }

    /**
     * Canaux utilisés.
     *
     * Pour le moment :
     * - notification interne en base de données uniquement
     *
     * Aucun e-mail n'est envoyé.
     */
    public function via(object $notifiable): array
    {
        return [
            'database',
        ];
    }

    /**
     * Données enregistrées dans la table notifications.
     */
    public function toDatabase(object $notifiable): array
    {
        $supplierOrder = $this->supplierOrder->loadMissing([
            'supplier',
            'creator',
        ]);

        return [
            'supplier_order_id' => $supplierOrder->id,

            'order_number' => $supplierOrder->order_number,

            'title' => 'Nouveau bon de commande',

            'message' =>
                'Le bon de commande '
                . $supplierOrder->order_number
                . ' attend votre approbation.',

            'supplier_name' =>
                $supplierOrder->supplier?->name
                ?? 'Fournisseur non renseigné',

            'total' =>
                (float) $supplierOrder->total,

            'currency' =>
                $supplierOrder->currency
                ?: 'DJF',

            'created_by' =>
                $supplierOrder->creator?->name
                ?? 'Utilisateur',

            'status' =>
                $supplierOrder->status,

            'url' =>
                route(
                    'supplier-orders.show',
                    $supplierOrder
                ),
        ];
    }

    /**
     * Représentation tableau.
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
