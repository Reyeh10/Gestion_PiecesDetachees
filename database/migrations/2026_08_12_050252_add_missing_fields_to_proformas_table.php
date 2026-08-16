<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajouter les colonnes manquantes dans proformas.
     */
    public function up(): void
    {
        Schema::table('proformas', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | VÉHICULE
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('proformas', 'vehicle_id')) {
                $table->unsignedBigInteger('vehicle_id')
                    ->nullable()
                    ->after('customer_id');
            }


            /*
            |--------------------------------------------------------------------------
            | UTILISATEUR AYANT CRÉÉ LE PROFORMA
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('proformas', 'created_by')) {
                $table->unsignedBigInteger('created_by')
                    ->nullable()
                    ->after('vehicle_id');
            }


            /*
            |--------------------------------------------------------------------------
            | MODE DE PAIEMENT
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('proformas', 'payment_type')) {
                $table->string('payment_type', 50)
                    ->nullable()
                    ->after('created_by');
            }


            /*
            |--------------------------------------------------------------------------
            | MONTANT DE LA REMISE
            |--------------------------------------------------------------------------
            |
            | discount = pourcentage
            | discount_amount = montant réellement déduit
            |
            */

            if (!Schema::hasColumn('proformas', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)
                    ->default(0)
                    ->after('discount');
            }


            /*
            |--------------------------------------------------------------------------
            | STATUT
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('proformas', 'status')) {
                $table->string('status', 50)
                    ->default('Validé')
                    ->after('total');
            }


            /*
            |--------------------------------------------------------------------------
            | VENTE ASSOCIÉE APRÈS CONVERSION
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('proformas', 'sale_id')) {
                $table->unsignedBigInteger('sale_id')
                    ->nullable()
                    ->after('status');
            }


            /*
            |--------------------------------------------------------------------------
            | CONVERSION EN VENTE
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('proformas', 'converted_at')) {
                $table->timestamp('converted_at')
                    ->nullable()
                    ->after('sale_id');
            }

            if (!Schema::hasColumn('proformas', 'converted_by')) {
                $table->unsignedBigInteger('converted_by')
                    ->nullable()
                    ->after('converted_at');
            }


            /*
            |--------------------------------------------------------------------------
            | ANNULATION
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('proformas', 'cancelled_at')) {
                $table->timestamp('cancelled_at')
                    ->nullable()
                    ->after('converted_by');
            }

            if (!Schema::hasColumn('proformas', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')
                    ->nullable()
                    ->after('cancelled_at');
            }
        });


        /*
        |--------------------------------------------------------------------------
        | TABLE PROFORMA_ITEMS
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('proforma_items')) {

            Schema::create('proforma_items', function (Blueprint $table) {

                $table->id();

                $table->unsignedBigInteger('proforma_id');

                $table->unsignedBigInteger('product_id');

                $table->decimal('quantity', 15, 2)
                    ->default(1);

                $table->decimal('price', 15, 2)
                    ->default(0);

                $table->decimal('total', 15, 2)
                    ->default(0);

                $table->timestamps();

                $table->index('proforma_id');
                $table->index('product_id');
            });

        } else {

            Schema::table('proforma_items', function (Blueprint $table) {

                if (!Schema::hasColumn('proforma_items', 'total')) {
                    $table->decimal('total', 15, 2)
                        ->default(0);
                }
            });
        }
    }


    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {

            $columns = [
                'vehicle_id',
                'created_by',
                'payment_type',
                'discount_amount',
                'status',
                'sale_id',
                'converted_at',
                'converted_by',
                'cancelled_at',
                'cancelled_by',
            ];

            foreach ($columns as $column) {

                if (Schema::hasColumn('proformas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};