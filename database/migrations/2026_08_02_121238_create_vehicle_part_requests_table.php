<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Création de la table.
     */
    public function up(): void
    {
        Schema::create('vehicle_part_requests', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relations
            |--------------------------------------------------------------------------
            */

            $table->foreignId('vehicle_id')
                ->constrained('vehicles')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * Le produit peut être vide parce que la pièce recherchée
             * n'existe pas encore forcément dans le catalogue.
             */
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            /*
             * Le fournisseur est facultatif au début de la recherche.
             */
            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained('suppliers')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            /*
             * Utilisateur ayant créé la demande.
             */
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Informations sur la pièce
            |--------------------------------------------------------------------------
            */

            $table->string('reference')->nullable();

            $table->string('part_name');

            $table->text('description')->nullable();

            $table->decimal('quantity', 15, 2)->default(1);

            $table->string('unit')->default('Piece');

            /*
            |--------------------------------------------------------------------------
            | Statut
            |--------------------------------------------------------------------------
            |
            | pending       = À rechercher
            | searching     = En cours de recherche
            | found         = Trouvée
            | ordered       = Commandée
            | received      = Reçue
            | not_found     = Non trouvée
            | cancelled     = Annulée
            |
            */

            $table->string('status')->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Informations de commande
            |--------------------------------------------------------------------------
            */

            $table->string('supplier_reference')->nullable();

            $table->string('order_reference')->nullable();

            $table->decimal('estimated_price', 15, 2)->nullable();

            $table->decimal('purchase_price', 15, 2)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Dates de suivi
            |--------------------------------------------------------------------------
            */

            $table->timestamp('requested_at')->nullable();

            $table->timestamp('search_started_at')->nullable();

            $table->timestamp('found_at')->nullable();

            $table->timestamp('ordered_at')->nullable();

            $table->timestamp('received_at')->nullable();

            $table->timestamp('not_found_at')->nullable();

            $table->timestamp('cancelled_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Commentaires
            |--------------------------------------------------------------------------
            */

            $table->text('notes')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Index pour accélérer les recherches
            |--------------------------------------------------------------------------
            */

            $table->index('status');
            $table->index('requested_at');
            $table->index(['vehicle_id', 'status']);
        });
    }

    /**
     * Suppression de la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_part_requests');
    }
};
