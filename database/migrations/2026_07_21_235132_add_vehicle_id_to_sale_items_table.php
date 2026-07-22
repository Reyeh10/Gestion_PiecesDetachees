<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajouter vehicle_id dans sale_items.
     */
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('vehicle_id')
                ->nullable()
                ->after('product_id')
                ->constrained('vehicles')
                ->nullOnDelete();

            $table->index('vehicle_id');
        });
    }

    /**
     * Supprimer vehicle_id.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropIndex(['vehicle_id']);
            $table->dropColumn('vehicle_id');
        });
    }
};
