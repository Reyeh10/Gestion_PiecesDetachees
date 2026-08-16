<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'initial_quantity')) {
            throw new RuntimeException(
                'La colonne products.initial_quantity n’existe pas.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CORRIGER LES PRODUITS DONT INITIAL_QUANTITY EST RESTÉE À 0
        |--------------------------------------------------------------------------
        |
        | Formule :
        |
        | initial_quantity =
        |   quantity actuelle
        | + ventes réelles
        | - ajustements inventaire nets
        |
        | Exemple :
        | disponible actuelle = 10
        | ajustements : 5->4 (-1), 4->8 (+4), 8->10 (+2)
        | net ajustement = +5
        | initiale = 10 - 5 = 5
        |
        */

        DB::statement("
            UPDATE products AS p

            LEFT JOIN (
                SELECT
                    si.product_id,
                    SUM(si.quantity) AS sold_qty
                FROM sale_items AS si
                INNER JOIN sales AS s
                    ON s.id = si.sale_id
                WHERE
                    s.status IS NULL
                    OR LOWER(s.status) NOT IN (
                        'cancelled',
                        'annulé',
                        'annule'
                    )
                GROUP BY si.product_id
            ) AS sold
                ON sold.product_id = p.id

            LEFT JOIN (
                SELECT
                    ia.product_id,
                    SUM(ia.new_qty - ia.old_qty) AS adjustment_qty
                FROM inventory_adjustments AS ia
                GROUP BY ia.product_id
            ) AS adj
                ON adj.product_id = p.id

            SET p.initial_quantity = ROUND(
                p.quantity
                + COALESCE(sold.sold_qty, 0)
                - COALESCE(adj.adjustment_qty, 0),
                2
            )

            WHERE
                p.initial_quantity = 0
                AND (
                    p.quantity <> 0
                    OR COALESCE(sold.sold_qty, 0) <> 0
                    OR COALESCE(adj.adjustment_qty, 0) <> 0
                )
        ");
    }

    public function down(): void
    {
        // Ne pas remettre les quantités initiales à zéro.
    }
};
