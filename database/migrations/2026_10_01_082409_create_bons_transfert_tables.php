<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bons de transfert (BT) : remplacent la vente/facture pour les bons de
 * commande reçus du garage. Les pièces sortent du stock du magasin et sont
 * transférées au garage (sans TVA, sans paiement).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bons_transfert', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->unsignedSmallInteger('annee');
            $table->unsignedInteger('sequence');
            $table->foreignId('external_bon_commande_id')
                ->constrained('external_bons_commande')
                ->restrictOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['annee', 'sequence']);
        });

        Schema::create('bon_transfert_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_transfert_id')
                ->constrained('bons_transfert')
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();
            $table->foreignId('depot_id')
                ->constrained('depots')
                ->restrictOnDelete();
            $table->decimal('quantite', 12, 2);
            $table->decimal('prix_unitaire', 15, 2);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });

        Schema::table('external_bons_commande', function (Blueprint $table) {
            $table->foreignId('bon_transfert_id')
                ->nullable()
                ->after('vente_id')
                ->constrained('bons_transfert')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('external_bons_commande', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bon_transfert_id');
        });

        Schema::dropIfExists('bon_transfert_lignes');
        Schema::dropIfExists('bons_transfert');
    }
};
