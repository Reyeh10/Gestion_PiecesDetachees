<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chaque ligne d'un bon de commande reçu du garage doit mémoriser le dépôt
 * dans lequel la pièce sera prélevée.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('external_bon_commande_lignes', 'depot_id')) {
            Schema::table(
                'external_bon_commande_lignes',
                function (Blueprint $table) {
                    $table
                        ->foreignId('depot_id')
                        ->nullable()
                        ->after('product_id')
                        ->constrained('depots')
                        ->nullOnDelete();
                }
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('external_bon_commande_lignes', 'depot_id')) {
            Schema::table(
                'external_bon_commande_lignes',
                function (Blueprint $table) {
                    $table->dropConstrainedForeignId('depot_id');
                }
            );
        }
    }
};
