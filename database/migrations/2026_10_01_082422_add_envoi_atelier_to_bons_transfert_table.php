<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Envoi du bon de transfert à l'app Atelier (garage) :
 * - suivi : date du dernier envoi réussi, ou message de la dernière erreur ;
 * - sur chaque ligne : position de la ligne dans le BC (l'« index » attendu
 *   par l'Atelier) et désignation du garage.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bons_transfert', function (Blueprint $table) {
            $table->timestamp('envoye_atelier_at')->nullable()->after('total');
            $table->text('envoi_atelier_erreur')->nullable()->after('envoye_atelier_at');
        });

        Schema::table('bon_transfert_lignes', function (Blueprint $table) {
            $table->unsignedInteger('position')->nullable()->after('bon_transfert_id');
            $table->string('designation_garage')->nullable()->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('bon_transfert_lignes', function (Blueprint $table) {
            $table->dropColumn(['position', 'designation_garage']);
        });

        Schema::table('bons_transfert', function (Blueprint $table) {
            $table->dropColumn(['envoye_atelier_at', 'envoi_atelier_erreur']);
        });
    }
};
