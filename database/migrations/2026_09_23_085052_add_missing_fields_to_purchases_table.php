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
            | RÉFÉRENCE ACHAT
            |--------------------------------------------------------------------------
            |
            | Nullable pour rester compatible avec les anciens achats.
            |
            */

            $table
                ->string('reference')
                ->nullable()
                ->after('id');

            /*
            |--------------------------------------------------------------------------
            | UTILISATEUR AYANT CRÉÉ L'ACHAT
            |--------------------------------------------------------------------------
            |
            | Nullable pour les anciens achats qui n'ont pas cette information.
            |
            */

            $table
                ->foreignId('user_id')
                ->nullable()
                ->after('depot_id')
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | SOUS-TOTAL
            |--------------------------------------------------------------------------
            */

            $table
                ->decimal(
                    'subtotal',
                    15,
                    2
                )
                ->default(0)
                ->after('user_id');

            /*
            |--------------------------------------------------------------------------
            | TVA
            |--------------------------------------------------------------------------
            */

            $table
                ->decimal(
                    'vat',
                    15,
                    2
                )
                ->default(0)
                ->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {

            $table->dropForeign([
                'user_id',
            ]);

            $table->dropColumn([
                'reference',
                'user_id',
                'subtotal',
                'vat',
            ]);
        });
    }
};
