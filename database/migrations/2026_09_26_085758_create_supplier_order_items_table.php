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
        Schema::create('supplier_order_items', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | BON DE COMMANDE
            |--------------------------------------------------------------------------
            */
            $table->foreignId('supplier_order_id')
                ->constrained('supplier_orders')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | DEMANDE DE PIÈCE VÉHICULE
            |--------------------------------------------------------------------------
            |
            | Permet de retrouver exactement quelle demande a généré
            | cette ligne du bon de commande.
            |
            */
            $table->foreignId('vehicle_part_request_id')
                ->nullable()
                ->constrained('vehicle_part_requests')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | PRODUIT
            |--------------------------------------------------------------------------
            |
            | Nullable car une pièce recherchée peut ne pas encore exister
            | dans le catalogue au moment de la création du BC.
            |
            */
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | SNAPSHOT DE LA PIÈCE
            |--------------------------------------------------------------------------
            |
            | On conserve référence et désignation sur le BC.
            | Une modification future du produit ne modifiera donc pas
            | l'ancien document commercial.
            |
            */
            $table->string('reference')
                ->nullable();

            $table->string('description');

            $table->string('unit', 50)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | QUANTITÉS
            |--------------------------------------------------------------------------
            */
            $table->decimal('quantity_ordered', 15, 2);

            $table->decimal('quantity_received', 15, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | PRIX
            |--------------------------------------------------------------------------
            |
            | unit_price = prix convenu avec le fournisseur pour CE BC.
            |
            */
            $table->decimal('unit_price', 18, 4)
                ->default(0);

            $table->decimal('line_total', 18, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | NOTES
            |--------------------------------------------------------------------------
            */
            $table->text('notes')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEX
            |--------------------------------------------------------------------------
            */
            $table->index('vehicle_part_request_id');
            $table->index('product_id');

            /*
            |--------------------------------------------------------------------------
            | PROTECTION CONTRE LE DOUBLON
            |--------------------------------------------------------------------------
            |
            | Une même demande de pièce ne doit apparaître qu'une fois
            | dans un même BC.
            |
            */
            $table->unique(
                [
                    'supplier_order_id',
                    'vehicle_part_request_id',
                ],
                'supplier_order_vehicle_part_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_order_items');
    }
};
