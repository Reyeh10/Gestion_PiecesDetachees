<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | PRÉREQUIS
        |--------------------------------------------------------------------------
        |
        | Cette migration suppose que la colonne products.initial_quantity
        | existe déjà.
        |
        | Elle corrige les produits existants sans modifier products.quantity.
        |
        */

        if (!Schema::hasColumn('products', 'initial_quantity')) {
            throw new RuntimeException(
                'La colonne products.initial_quantity n’existe pas. ' .
                'Exécutez d’abord la migration qui ajoute cette colonne.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | RECALCUL DES QUANTITÉS INITIALES EXISTANTES
        |--------------------------------------------------------------------------
        |
        | Formule :
        |
        | initial_quantity =
        |     quantité disponible actuelle
        |   + quantité vendue non annulée
        |   - somme nette des ajustements inventaire
        |
        | Exemple :
        |
        | disponible actuel : 155
        | vendu             :   0
        | ajustement net    :  +5
        |
        | initial_quantity  : 150
        |
        | L'ajustement ne change donc pas la quantité initiale.
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

            SET p.initial_quantity =
                ROUND(
                    p.quantity
                    + COALESCE(sold.sold_qty, 0)
                    - COALESCE(adj.adjustment_qty, 0),
                    2
                )
        ");
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | PAS DE ROLLBACK DE DONNÉES
        |--------------------------------------------------------------------------
        |
        | On ne remet pas initial_quantity à zéro afin de ne pas détruire
        | les valeurs historiques calculées.
        |
        */
    }
};