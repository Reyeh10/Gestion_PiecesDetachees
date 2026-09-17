<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_depot_stocks', function (Blueprint $table) {
            if (!Schema::hasColumn('product_depot_stocks', 'rayon_id')) {
                $table->foreignId('rayon_id')
                    ->nullable()
                    ->after('depot_id')
                    ->constrained('rayons')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('product_depot_stocks', 'location_id')) {
                $table->foreignId('location_id')
                    ->nullable()
                    ->after('rayon_id')
                    ->constrained('locations')
                    ->nullOnDelete();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | BACKFILL
        |--------------------------------------------------------------------------
        |
        | Pour les lignes de stock déjà existantes, on copie le rayon/emplacement
        | actuel du produit comme point de départ (à corriger ensuite dépôt par
        | dépôt si besoin), plutôt que de tout laisser vide.
        |
        */

        DB::statement('
            UPDATE product_depot_stocks pds
            INNER JOIN products p ON p.id = pds.product_id
            SET
                pds.rayon_id = p.rayon_id,
                pds.location_id = p.location_id
            WHERE pds.rayon_id IS NULL
            AND pds.location_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('product_depot_stocks', function (Blueprint $table) {
            if (Schema::hasColumn('product_depot_stocks', 'location_id')) {
                $table->dropConstrainedForeignId('location_id');
            }

            if (Schema::hasColumn('product_depot_stocks', 'rayon_id')) {
                $table->dropConstrainedForeignId('rayon_id');
            }
        });
    }
};
