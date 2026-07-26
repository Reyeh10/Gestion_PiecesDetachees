<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Supprimer l’unicité de vehicle_id tout en conservant
     * la relation avec la table vehicles.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Supprimer temporairement la clé étrangère
        |--------------------------------------------------------------------------
        |
        | Laravel crée généralement cette contrainte sous le nom :
        | sales_vehicle_id_foreign
        |
        */

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign('sales_vehicle_id_foreign');
        });

        /*
        |--------------------------------------------------------------------------
        | 2. Supprimer l’index UNIQUE
        |--------------------------------------------------------------------------
        */

        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique('sales_vehicle_id_unique');
        });

        /*
        |--------------------------------------------------------------------------
        | 3. Ajouter un index normal
        |--------------------------------------------------------------------------
        |
        | Un même véhicule pourra maintenant être utilisé dans plusieurs ventes.
        |
        */

        Schema::table('sales', function (Blueprint $table) {
            $table->index('vehicle_id', 'sales_vehicle_id_index');
        });

        /*
        |--------------------------------------------------------------------------
        | 4. Recréer la clé étrangère
        |--------------------------------------------------------------------------
        */

        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('vehicle_id', 'sales_vehicle_id_foreign')
                ->references('id')
                ->on('vehicles')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Annuler la correction.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Supprimer la clé étrangère
        |--------------------------------------------------------------------------
        */

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign('sales_vehicle_id_foreign');
        });

        /*
        |--------------------------------------------------------------------------
        | 2. Supprimer l’index normal
        |--------------------------------------------------------------------------
        */

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_vehicle_id_index');
        });

        /*
        |--------------------------------------------------------------------------
        | 3. Recréer l’index UNIQUE
        |--------------------------------------------------------------------------
        |
        | Attention : cette opération échouera si plusieurs ventes utilisent
        | déjà le même véhicule.
        |
        */

        Schema::table('sales', function (Blueprint $table) {
            $table->unique('vehicle_id', 'sales_vehicle_id_unique');
        });

        /*
        |--------------------------------------------------------------------------
        | 4. Recréer la clé étrangère
        |--------------------------------------------------------------------------
        */

        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('vehicle_id', 'sales_vehicle_id_foreign')
                ->references('id')
                ->on('vehicles')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }
};
