<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permet d'ajouter dans une vente des produits hors catalogue.
 *
 * Une ligne de vente peut désormais représenter :
 *
 * 1. Un produit existant dans le catalogue :
 *    - product_id renseigné
 *    - depot_id renseigné
 *    - stock contrôlé et diminué lors de la vente
 *
 * 2. Un produit hors catalogue provenant notamment d'un proforma :
 *    - product_id = NULL
 *    - depot_id = NULL
 *    - référence saisie manuellement
 *    - désignation saisie manuellement
 *    - description facultative
 *    - aucun mouvement de stock
 *
 * IMPORTANT :
 * Un produit hors catalogue n'est PAS automatiquement créé
 * dans la table products et ne modifie PAS le stock.
 */
return new class extends Migration
{
    /**
     * Exécuter la migration.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | PRODUCT_ID DEVIENT NULLABLE
        |--------------------------------------------------------------------------
        |
        | Jusqu'à présent, chaque ligne de vente devait obligatoirement
        | correspondre à un produit existant dans la table products.
        |
        | Les produits hors catalogue n'ayant pas de product_id,
        | cette colonne doit maintenant accepter NULL.
        |
        | La clé étrangère vers products est conservée.
        |
        */

        Schema::table('sale_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')
                ->nullable()
                ->change();
        });


        /*
        |--------------------------------------------------------------------------
        | INFORMATIONS DU PRODUIT HORS CATALOGUE
        |--------------------------------------------------------------------------
        |
        | Ces informations sont enregistrées directement dans sale_items.
        |
        | Elles restent NULL pour les produits provenant normalement
        | du catalogue.
        |
        */

        Schema::table('sale_items', function (Blueprint $table) {
            $table->string('reference_libre', 150)
                ->nullable()
                ->after('depot_id');

            $table->string('designation_libre', 255)
                ->nullable()
                ->after('reference_libre');

            $table->text('description_libre')
                ->nullable()
                ->after('designation_libre');
        });
    }


    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | SUPPRESSION DES CHAMPS HORS CATALOGUE
        |--------------------------------------------------------------------------
        */

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn([
                'reference_libre',
                'designation_libre',
                'description_libre',
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | PRODUCT_ID REDEVIENT OBLIGATOIRE
        |--------------------------------------------------------------------------
        |
        | ATTENTION :
        | Ce rollback suppose qu'aucune ligne hors catalogue avec
        | product_id = NULL n'existe encore.
        |
        */

        Schema::table('sale_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')
                ->nullable(false)
                ->change();
        });
    }
};
