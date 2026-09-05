<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | PROFORMAS : MODE DE PAIEMENT FACULTATIF
        |--------------------------------------------------------------------------
        |
        | Le mode de paiement n'est plus demandé lors de la création
        | du proforma. Il sera choisi uniquement au moment du paiement
        | de la facture après conversion en vente.
        |
        */
        if (
            Schema::hasTable('proformas')
            &&
            Schema::hasColumn(
                'proformas',
                'payment_type'
            )
        ) {
            Schema::table(
                'proformas',
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
            Schema::hasTable('proformas')
            &&
            Schema::hasColumn(
                'proformas',
                'payment_type'
            )
        ) {
            /*
            |--------------------------------------------------------------------------
            | ÉVITER LES NULL AVANT DE REMETTRE NOT NULL
            |--------------------------------------------------------------------------
            */
            DB::table('proformas')
                ->whereNull('payment_type')
                ->update([
                    'payment_type' => 'Cash',
                ]);

            Schema::table(
                'proformas',
                function (Blueprint $table) {
                    $table
                        ->string('payment_type')
                        ->nullable(false)
                        ->change();
                }
            );
        }
    }
};
