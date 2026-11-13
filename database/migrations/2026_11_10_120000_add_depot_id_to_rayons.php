<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * V6 : un rayon appartient à un seul dépôt (ses emplacements suivent leur rayon).
 * Le dépôt de chaque rayon existant est rempli ensuite par la commande
 * `php artisan rayons:affecter-depots` (aperçu, puis --appliquer).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('rayons', 'depot_id')) {
            return;
        }

        Schema::table('rayons', function (Blueprint $table) {
            $table->foreignId('depot_id')->nullable()->after('name')
                ->constrained('depots')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('rayons', 'depot_id')) {
            Schema::table('rayons', function (Blueprint $table) {
                $table->dropConstrainedForeignId('depot_id');
            });
        }
    }
};
