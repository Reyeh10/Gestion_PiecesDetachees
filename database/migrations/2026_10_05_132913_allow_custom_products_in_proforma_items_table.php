<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permet d'ajouter dans un proforma :
 *
 * 1. Un produit existant dans le catalogue :
 *    - product_id renseigné
 *    - depot_id renseigné
 *    - prix provenant normalement du produit
 *
 * 2. Un produit hors catalogue :
 *    - product_id = NULL
 *    - depot_id = NULL
 *    - référence saisie manuellement
 *    - désignation saisie manuellement
 *    - description facultative
 *    - prix saisi manuellement
 *
 * IMPORTANT :
 * Un produit hors catalogue ajouté à un proforma n'est PAS automatiquement
 * créé dans la table products et n'entre PAS automatiquement en stock.
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
        | Actuellement product_id est obligatoire.
        |
        | Pour permettre une ligne hors catalogue, nous devons autoriser :
        |
        |     product_id = NULL
        |
        | La clé étrangère existante vers products est conservée.
        |
        */

        Schema::table('proforma_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')
                ->nullable()
                ->change();
        });


        /*
        |--------------------------------------------------------------------------
        | INFORMATIONS DU PRODUIT HORS CATALOGUE
        |--------------------------------------------------------------------------
        |
        | Ces colonnes sont utilisées lorsqu'aucun produit du catalogue
        | n'est sélectionné.
        |
        | Pour les anciens proformas et les produits existants, elles restent
        | simplement à NULL.
        |
        */

        Schema::table('proforma_items', function (Blueprint $table) {

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

        Schema::table('proforma_items', function (Blueprint $table) {

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

        Schema::table('proforma_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')
                ->nullable(false)
                ->change();
        });
    }
};
