<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_adjustments', 'depot_id')) {
                $table->foreignId('depot_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('depots')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('inventory_adjustments', 'rayon_id')) {
                $table->foreignId('rayon_id')
                    ->nullable()
                    ->after('depot_id')
                    ->constrained('rayons')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('inventory_adjustments', 'location_id')) {
                $table->foreignId('location_id')
                    ->nullable()
                    ->after('rayon_id')
                    ->constrained('locations')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_adjustments', 'location_id')) {
                $table->dropConstrainedForeignId('location_id');
            }

            if (Schema::hasColumn('inventory_adjustments', 'rayon_id')) {
                $table->dropConstrainedForeignId('rayon_id');
            }

            if (Schema::hasColumn('inventory_adjustments', 'depot_id')) {
                $table->dropConstrainedForeignId('depot_id');
            }
        });
    }
};
