<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | COLONNE TOTAL SUR sale_items
        |--------------------------------------------------------------------------
        |
        | Votre modèle SaleItem possède déjà le champ "total" et les nouvelles
        | ventes / conversions de proforma l'enregistrent.
        |
        */
        if (
            Schema::hasTable('sale_items')
            &&
            !Schema::hasColumn(
                'sale_items',
                'total'
            )
        ) {
            Schema::table(
                'sale_items',
                function (Blueprint $table) {
                    $table
                        ->decimal(
                            'total',
                            15,
                            2
                        )
                        ->nullable()
                        ->after('price');
                }
            );
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('sale_items')
            &&
            Schema::hasColumn(
                'sale_items',
                'total'
            )
        ) {
            Schema::table(
                'sale_items',
                function (Blueprint $table) {
                    $table->dropColumn(
                        'total'
                    );
                }
            );
        }
    }
};
