<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {

            $table->foreignId('vehicle_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('vehicles')
                ->restrictOnDelete();

            /*
             * Une contrainte unique empêchera qu’un véhicule
             * soit vendu dans deux ventes différentes.
             */
            $table->unique(
                'vehicle_id',
                'sales_vehicle_id_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {

            $table->dropUnique(
                'sales_vehicle_id_unique'
            );

            $table->dropForeign([
                'vehicle_id'
            ]);

            $table->dropColumn(
                'vehicle_id'
            );
        });
    }
};
