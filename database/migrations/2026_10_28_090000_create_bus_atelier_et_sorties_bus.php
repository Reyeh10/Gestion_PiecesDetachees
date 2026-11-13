<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * V6 — Bus leasing / bus du transport public : la sortie des pièces se fait
 * au Magasin.
 * - bus_atelier : copie de la liste des bus tenue dans l'Atelier ;
 * - external_bons_commande : un BC interne (source « magasin-bus ») porte
 *   chaque sortie bus, pour réutiliser le circuit BT (stock, PDF, numéro) ;
 * - bons_transfert.atelier_bc_numero : n° du BC créé côté Atelier à la
 *   réception du BT bus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bus_atelier', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('atelier_id')->nullable();
            $table->string('groupe', 12);
            $table->string('immatriculation', 30);
            $table->string('immatriculation_normalisee', 30)->unique();
            $table->string('marque', 100)->nullable();
            $table->string('modele', 100)->nullable();
            $table->string('vin', 100)->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamp('synchronise_le')->nullable();
            $table->timestamps();
        });

        Schema::table('external_bons_commande', function (Blueprint $table) {
            $table->foreignId('bus_atelier_id')->nullable()->after('source_system')->constrained('bus_atelier')->nullOnDelete();
            $table->string('groupe_bus', 12)->nullable()->after('bus_atelier_id');
            $table->index('source_system');
        });

        Schema::table('bons_transfert', function (Blueprint $table) {
            $table->string('atelier_bc_numero', 50)->nullable()->after('envoi_atelier_erreur');
        });
    }

    public function down(): void
    {
        Schema::table('bons_transfert', fn (Blueprint $t) => $t->dropColumn('atelier_bc_numero'));

        Schema::table('external_bons_commande', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bus_atelier_id');
            $table->dropColumn('groupe_bus');
            $table->dropIndex(['source_system']);
        });

        Schema::dropIfExists('bus_atelier');
    }
};
