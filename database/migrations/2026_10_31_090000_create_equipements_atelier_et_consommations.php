<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * V6 — Consommation atelier : pièces prises par le garage pour un équipement
 * (pont élévateur, compresseur d'air…) et non pour un véhicule.
 * - equipements_atelier : copie de la liste tenue dans l'Atelier ;
 * - external_bons_commande.equipement : équipement d'un BC « consommation »
 *   (créé par l'Atelier, ou BC interne « magasin-conso » d'un BT direct) ;
 * - external_bons_commande.demande_par : qui a demandé les pièces (BT direct).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipements_atelier', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('atelier_id')->nullable()->unique();
            $table->string('nom', 150);
            $table->boolean('actif')->default(true);
            $table->timestamp('synchronise_le')->nullable();
            $table->timestamps();
        });

        Schema::table('external_bons_commande', function (Blueprint $table) {
            $table->foreignId('equipement_atelier_id')->nullable()->after('groupe_bus')->constrained('equipements_atelier')->nullOnDelete();
            $table->string('equipement', 150)->nullable()->after('equipement_atelier_id');
            $table->string('demande_par', 150)->nullable()->after('equipement');
        });
    }

    public function down(): void
    {
        Schema::table('external_bons_commande', function (Blueprint $table) {
            $table->dropConstrainedForeignId('equipement_atelier_id');
            $table->dropColumn(['equipement', 'demande_par']);
        });

        Schema::dropIfExists('equipements_atelier');
    }
};
