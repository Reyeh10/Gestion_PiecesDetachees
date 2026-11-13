<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * V6 — « + BT » direct du Magasin pour tout usage interne : consommation
 * atelier, vente intersociété, équipe commerciale, autre.
 * - motif : raison du BT direct (consommation_atelier, intersociete…) ;
 * - beneficiaire : à qui vont les pièces (texte libre ; l'équipement pour
 *   la consommation atelier).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('external_bons_commande', function (Blueprint $table) {
            $table->string('motif', 30)->nullable()->after('source_system');
            $table->string('beneficiaire', 150)->nullable()->after('demande_par');
        });

        DB::table('external_bons_commande')->where('source_system', 'magasin-conso')->update(['motif' => 'consommation_atelier']);
    }

    public function down(): void
    {
        Schema::table('external_bons_commande', function (Blueprint $table) {
            $table->dropColumn(['motif', 'beneficiaire']);
        });
    }
};
