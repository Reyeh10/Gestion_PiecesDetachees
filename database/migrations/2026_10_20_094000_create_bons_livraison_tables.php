<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * V6 — Bons de livraison (BL-AAAA-NNNN), facultatifs, générés après la
 * facture. Un BL peut ne livrer qu'une partie des pièces : plusieurs BL par
 * facture, jamais plus que facturé. Sans prix. Ne touche pas au stock (la
 * facture l'a déjà sorti).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bons_livraison', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->unsignedSmallInteger('annee');
            $table->unsignedInteger('sequence');
            $table->foreignId('sale_id')->constrained('sales')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('livre_le');
            $table->string('recu_par', 150)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('annule_le')->nullable();
            $table->timestamps();

            $table->unique(['annee', 'sequence']);
        });

        Schema::create('bon_livraison_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_livraison_id')->constrained('bons_livraison')->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained('sale_items')->restrictOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('reference', 150)->nullable();
            $table->string('designation', 255);
            $table->string('unite', 30)->nullable();
            $table->decimal('quantite', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bon_livraison_lignes');
        Schema::dropIfExists('bons_livraison');
    }
};
