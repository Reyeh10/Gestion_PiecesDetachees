<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Une demande de pièce peut être destinée :
     * - soit à un véhicule ;
     * - soit à un dépôt.
     *
     * Règle métier :
     *
     * Commande véhicule :
     * vehicle_id = ID du véhicule
     * depot_id   = NULL
     *
     * Commande dépôt :
     * vehicle_id = NULL
     * depot_id   = ID du dépôt
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | RENDRE LE VÉHICULE FACULTATIF
        |--------------------------------------------------------------------------
        |
        | vehicle_id était historiquement obligatoire.
        | Il devient nullable afin de permettre une demande destinée
        | uniquement à un dépôt.
        |
        */
        Schema::table('vehicle_part_requests', function (Blueprint $table) {
            $table->foreignId('vehicle_id')
                ->nullable()
                ->change();
        });

        /*
        |--------------------------------------------------------------------------
        | AJOUTER LE DÉPÔT
        |--------------------------------------------------------------------------
        */
        Schema::table('vehicle_part_requests', function (Blueprint $table) {
            $table->foreignId('depot_id')
                ->nullable()
                ->after('vehicle_id')
                ->constrained('depots')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | SUPPRIMER LE DÉPÔT
        |--------------------------------------------------------------------------
        */
        Schema::table('vehicle_part_requests', function (Blueprint $table) {
            $table->dropForeign(['depot_id']);
            $table->dropColumn('depot_id');
        });

        /*
        |--------------------------------------------------------------------------
        | REMETTRE vehicle_id OBLIGATOIRE
        |--------------------------------------------------------------------------
        |
        | Attention :
        | ce rollback suppose qu'aucune ligne ne possède vehicle_id = NULL.
        |
        */
        Schema::table('vehicle_part_requests', function (Blueprint $table) {
            $table->foreignId('vehicle_id')
                ->nullable(false)
                ->change();
        });
    }
};
