<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * V6 — BT partiel et « BT du reste ».
 *
 * - bons_transfert.reste     : 0 = premier BT du BC, 1 = BT-…-R1, 2 = -R2…
 * - bons_transfert.parent_id : BT d'origine d'un BT du reste
 * - external_bon_commande_lignes.bon_transfert_id : BT qui a transféré la
 *   pièce (vide = pièce en attente)
 *
 * Les BC existants avaient un seul BT pour toutes leurs pièces : leurs lignes
 * sont rattachées à ce BT.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bons_transfert', function (Blueprint $table) {
            $table->unsignedSmallInteger('reste')->default(0)->after('sequence');
            $table->foreignId('parent_id')
                ->nullable()
                ->after('reste')
                ->constrained('bons_transfert')
                ->nullOnDelete();
        });

        // Un BT du reste garde l'année et la séquence de son BT d'origine.
        Schema::table('bons_transfert', function (Blueprint $table) {
            $table->dropUnique(['annee', 'sequence']);
            $table->unique(['annee', 'sequence', 'reste']);
        });

        Schema::table('external_bon_commande_lignes', function (Blueprint $table) {
            $table->foreignId('bon_transfert_id')
                ->nullable()
                ->after('depot_id')
                ->constrained('bons_transfert')
                ->nullOnDelete();
        });

        // Reprise : toutes les pièces d'un BC déjà transféré sont dans son BT.
        DB::table('external_bons_commande')
            ->whereNotNull('bon_transfert_id')
            ->orderBy('id')
            ->each(function ($bc) {
                DB::table('external_bon_commande_lignes')
                    ->where('external_bon_commande_id', $bc->id)
                    ->update(['bon_transfert_id' => $bc->bon_transfert_id]);
            });
    }

    public function down(): void
    {
        Schema::table('external_bon_commande_lignes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bon_transfert_id');
        });

        Schema::table('bons_transfert', function (Blueprint $table) {
            $table->dropUnique(['annee', 'sequence', 'reste']);
            $table->unique(['annee', 'sequence']);
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn('reste');
        });
    }
};
