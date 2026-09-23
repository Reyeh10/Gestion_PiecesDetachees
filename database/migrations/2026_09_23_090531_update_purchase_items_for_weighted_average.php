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
        | QUANTITÉ EN DÉCIMAL
        |--------------------------------------------------------------------------
        */

        Schema::table('purchase_items', function (Blueprint $table) {

            $table
                ->decimal('quantity', 15, 2)
                ->change();
        });

        /*
        |--------------------------------------------------------------------------
        | ÉTAT DU PRODUIT AVANT L'ACHAT
        |--------------------------------------------------------------------------
        |
        | Ces valeurs permettent de conserver une trace exacte de la
        | valorisation avant réception du nouvel achat.
        |
        */

        Schema::table('purchase_items', function (Blueprint $table) {

            $table
                ->decimal('previous_quantity', 15, 2)
                ->nullable()
                ->after('total');

            $table
                ->decimal('previous_purchase_price', 15, 4)
                ->nullable()
                ->after('previous_quantity');

            $table
                ->decimal('previous_cost_price', 15, 4)
                ->nullable()
                ->after('previous_purchase_price');

            $table
                ->decimal('previous_sale_price', 15, 2)
                ->nullable()
                ->after('previous_cost_price');

            $table
                ->decimal('previous_coef_purchase', 8, 2)
                ->nullable()
                ->after('previous_sale_price');

            $table
                ->decimal('previous_coef_sale', 8, 2)
                ->nullable()
                ->after('previous_coef_purchase');

            $table
                ->decimal('new_weighted_purchase_price', 15, 4)
                ->nullable()
                ->after('previous_coef_sale');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {

            $table->dropColumn([
                'previous_quantity',
                'previous_purchase_price',
                'previous_cost_price',
                'previous_sale_price',
                'previous_coef_purchase',
                'previous_coef_sale',
                'new_weighted_purchase_price',
            ]);
        });

        Schema::table('purchase_items', function (Blueprint $table) {

            $table
                ->integer('quantity')
                ->change();
        });
    }
};
