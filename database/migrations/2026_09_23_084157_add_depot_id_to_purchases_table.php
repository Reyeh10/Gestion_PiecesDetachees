<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | DÉPÔT DE RÉCEPTION
            |--------------------------------------------------------------------------
            |
            | Nullable volontairement :
            |
            | les anciens achats existants n'ont actuellement aucun dépôt associé.
            | Nous ne devons donc pas inventer leur dépôt lors de la migration.
            |
            */

            $table
                ->foreignId('depot_id')
                ->nullable()
                ->after('supplier_id')
                ->constrained('depots')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {

            $table->dropForeign([
                'depot_id',
            ]);

            $table->dropColumn(
                'depot_id'
            );
        });
    }
};
