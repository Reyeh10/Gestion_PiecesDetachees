<?php

namespace App\Services;

use Ripcord\Ripcord;
use Exception;

class OdooService
{
    public $uid;
    public $models;
    public $db;
    public $password;

    public function __construct()
    {
        $url = config('services.odoo.url');

        $this->db = config('services.odoo.db');
        $username = config('services.odoo.username');
        $this->password = config('services.odoo.password');

        $common = Ripcord::client(
            $url . '/xmlrpc/2/common'
        );

        $this->uid = $common->authenticate(
            $this->db,
            $username,
            $this->password,
            []
        );

        if (!$this->uid) {
            throw new Exception(
                'Impossible de se connecter à Odoo. Vérifiez les paramètres API.'
            );
        }

        $this->models = Ripcord::client(
            $url . '/xmlrpc/2/object'
        );
    }

    /**
     * Rechercher un partenaire par email
     */
    public function findPartnerByEmail($email)
    {
        return $this->models->execute_kw(
            $this->db,
            $this->uid,
            $this->password,
            'res.partner',
            'search_read',
            [
                [
                    ['email', '=', $email]
                ]
            ],
            [
                'fields' => [
                    'id',
                    'name',
                    'email',
                    'phone'
                ],
                'limit' => 1
            ]
        );
    }

    /**
     * Créer un partenaire
     */
    public function createPartner(
        $name,
        $email,
        $phone = null
    ) {
        return $this->models->execute_kw(
            $this->db,
            $this->uid,
            $this->password,
            'res.partner',
            'create',
            [[
                'name'  => $name,
                'email' => $email,
                'phone' => $phone,
            ]]
        );
    }

    /**
     * Créer ou récupérer un partenaire existant
     */
    public function createOrGetPartner(
        $name,
        $email,
        $phone = null
    ) {
        $partner = $this->findPartnerByEmail($email);

        if (!empty($partner)) {
            return $partner[0]['id'];
        }

        return $this->createPartner(
            $name,
            $email,
            $phone
        );
    }

    /**
     * Liste des partenaires
     */
    public function getPartners($limit = 10)
    {
        return $this->models->execute_kw(
            $this->db,
            $this->uid,
            $this->password,
            'res.partner',
            'search_read',
            [[]],
            [
                'fields' => [
                    'id',
                    'name',
                    'email',
                    'phone'
                ],
                'limit' => $limit
            ]
        );
    }
    /**
     * Invoice
     */
    
    public function createInvoice(
    $partnerId,
    $productId,
    $quantity,
    $price
) {

    return $this->models->execute_kw(
        $this->db,
        $this->uid,
        $this->password,
        'account.move',
        'create',
        [[
            'move_type' => 'out_invoice',

            'partner_id' => $partnerId,

            'invoice_line_ids' => [[0, 0, [

                'product_id' => $productId,

                'quantity' => $quantity,

                'price_unit' => $price,

            ]]]
        ]]
    );
}
}