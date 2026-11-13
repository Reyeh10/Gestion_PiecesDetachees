<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * V6 — Fiches d'inventaire (INV-AAAA-NNN) : liste imprimée d'un ou plusieurs
 * rayons d'un dépôt, comptée sur place, saisie (écran ou Excel) puis validée
 * en ajustements d'inventaire.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventaires', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->unsignedSmallInteger('annee');
            $table->unsignedInteger('sequence');
            $table->foreignId('depot_id')->constrained('depots')->restrictOnDelete();
            $table->string('perimetre', 255)->nullable();      // rayons / emplacement choisis, en clair
            $table->string('statut', 20)->default('en_cours'); // en_cours, valide, annule
            $table->string('compteur', 150)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('cree_par')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('valide_le')->nullable();
            $table->timestamps();

            $table->unique(['annee', 'sequence']);
        });

        Schema::create('inventaire_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaire_id')->constrained('inventaires')->cascadeOnDelete();
            $table->unsignedInteger('ordre')->default(0);
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('rayon_id')->nullable()->constrained('rayons')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('reference', 150)->nullable();
            $table->string('designation', 255)->nullable();
            $table->decimal('qte_attendue', 12, 2)->default(0);
            $table->decimal('qte_comptee', 12, 2)->nullable();
            $table->foreignId('nouveau_rayon_id')->nullable()->constrained('rayons')->nullOnDelete();
            $table->foreignId('nouvel_emplacement_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('reference_signalee', 150)->nullable();
            $table->string('remarque', 500)->nullable();
            $table->boolean('hors_liste')->default(false);
            $table->foreignId('inventory_adjustment_id')->nullable()->constrained('inventory_adjustments')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaire_lignes');
        Schema::dropIfExists('inventaires');
    }
};
