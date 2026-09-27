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
        Schema::create('supplier_orders', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | NUMÉRO DU BON DE COMMANDE
            |--------------------------------------------------------------------------
            |
            | Exemple :
            | BC-2026-000001
            |
            */
            $table->string('order_number', 50)->unique();

            /*
            |--------------------------------------------------------------------------
            | FOURNISSEUR
            |--------------------------------------------------------------------------
            */
            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | DÉPÔT DE DESTINATION
            |--------------------------------------------------------------------------
            |
            | Le BC indique où les marchandises doivent être livrées.
            | AUCUN stock n'est ajouté lors de la création du BC.
            |
            */
            $table->foreignId('depot_id')
                ->nullable()
                ->constrained('depots')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | UTILISATEUR AYANT PRÉPARÉ LE BC
            |--------------------------------------------------------------------------
            */
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | APPROBATION
            |--------------------------------------------------------------------------
            */
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | DATES
            |--------------------------------------------------------------------------
            */
            $table->date('order_date');

            $table->date('expected_delivery_date')
                ->nullable();

            $table->timestamp('approved_at')
                ->nullable();

            $table->timestamp('sent_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | STATUT DU BON DE COMMANDE
            |--------------------------------------------------------------------------
            |
            | draft
            | approved
            | sent
            | partial_received
            | received
            | cancelled
            |
            */
            $table->string('status', 30)
                ->default('draft');

            /*
            |--------------------------------------------------------------------------
            | DEVISE
            |--------------------------------------------------------------------------
            |
            | Exemple :
            | DJF
            | USD
            | EUR
            |
            */
            $table->string('currency', 10)
                ->default('DJF');

            /*
            |--------------------------------------------------------------------------
            | MONTANTS
            |--------------------------------------------------------------------------
            */
            $table->decimal('subtotal', 18, 2)
                ->default(0);

            $table->decimal('discount', 18, 2)
                ->default(0);

            $table->decimal('shipping_cost', 18, 2)
                ->default(0);

            $table->decimal('tax_amount', 18, 2)
                ->default(0);

            $table->decimal('total', 18, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | CONDITIONS
            |--------------------------------------------------------------------------
            */
            $table->string('payment_terms')
                ->nullable();

            $table->string('delivery_terms')
                ->nullable();

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
            $table->index('status');
            $table->index('order_date');
            $table->index(['supplier_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_orders');
    }
};
