<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $table->decimal(
                'initial_quantity',
                15,
                2
            )
            ->default(0)
            ->after('quantity');
        });

        /*
        |--------------------------------------------------------------------------
        | INITIALISATION DES DONNÉES EXISTANTES
        |--------------------------------------------------------------------------
        |
        | Pour les produits déjà présents :
        |
        | quantité initiale = quantité disponible actuelle
        |                    + quantité déjà vendue
        |
        | ATTENTION :
        | cette partie devra être adaptée si vos ventes sont stockées
        | différemment.
        |
        */
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('initial_quantity');
        });
    }
};