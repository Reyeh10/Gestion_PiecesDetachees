<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sale_items', 'depot_id')) {
            Schema::table(
                'sale_items',
                function (Blueprint $table) {
                    $table
                        ->foreignId('depot_id')
                        ->nullable()
                        ->after('product_id')
                        ->constrained('depots')
                        ->restrictOnDelete();
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | payment_type nullable
        |--------------------------------------------------------------------------
        |
        | Le mode de paiement sera choisi au moment du paiement,
        | pas lors de la création de la vente.
        |
        */
        if (
            Schema::hasColumn(
                'sales',
                'payment_type'
            )
        ) {
            Schema::table(
                'sales',
                function (Blueprint $table) {
                    $table
                        ->string('payment_type')
                        ->nullable()
                        ->change();
                }
            );
        }
    }

    public function down(): void
    {
        if (
            Schema::hasColumn(
                'sales',
                'payment_type'
            )
        ) {
            DB::table('sales')
                ->whereNull(
                    'payment_type'
                )
                ->update([
                    'payment_type' =>
                        'Cash',
                ]);

            Schema::table(
                'sales',
                function (Blueprint $table) {
                    $table
                        ->string('payment_type')
                        ->nullable(false)
                        ->change();
                }
            );
        }

        if (
            Schema::hasColumn(
                'sale_items',
                'depot_id'
            )
        ) {
            Schema::table(
                'sale_items',
                function (Blueprint $table) {
                    $table
                        ->dropConstrainedForeignId(
                            'depot_id'
                        );
                }
            );
        }
    }
};
