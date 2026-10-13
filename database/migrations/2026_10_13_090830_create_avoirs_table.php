<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Avoirs (comme l'app Atelier) : une facture émise ne se supprime jamais.
 * Pour l'annuler, on émet un avoir qui la reprend en totalité (mêmes lignes,
 * mêmes montants). La facture passe en « annulée », les pièces reviennent
 * dans leur dépôt, et le montant déjà encaissé est indiqué « à rembourser ».
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avoirs', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->unsignedSmallInteger('annee');
            $table->unsignedInteger('sequence');
            $table->foreignId('sale_id')->unique()->constrained('sales')->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->text('motif');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 8, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tva', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('montant_deja_paye', 15, 2)->default(0);
            $table->decimal('montant_a_rembourser', 15, 2)->default(0);
            $table->date('rembourse_le')->nullable();
            $table->string('mode_remboursement', 30)->nullable();
            $table->foreignId('cree_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['annee', 'sequence']);
        });

        Schema::create('avoir_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('avoir_id')->constrained('avoirs')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('depot_id')->nullable()->constrained('depots')->nullOnDelete();
            $table->string('reference', 100)->nullable();
            $table->string('designation')->nullable();
            $table->decimal('quantite', 12, 2);
            $table->decimal('prix_unitaire', 15, 2);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avoir_lignes');
        Schema::dropIfExists('avoirs');
    }
};
