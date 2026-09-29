<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     */
    public function up(): void
    {
        Schema::table('supplier_orders', function (Blueprint $table) {

            /*
            |----------------------------------------------------------------------
            | UTILISATEUR AYANT REJETÉ LE BON DE COMMANDE
            |----------------------------------------------------------------------
            |
            | Nous conservons l'utilisateur qui a effectué le rejet.
            |
            | Si cet utilisateur est supprimé plus tard, le BC reste conservé
            | et rejected_by devient simplement NULL.
            |
            */

            $table->foreignId('rejected_by')
                ->nullable()
                ->after('approved_by')
                ->constrained('users')
                ->nullOnDelete();


            /*
            |----------------------------------------------------------------------
            | DATE ET HEURE DU REJET
            |----------------------------------------------------------------------
            */

            $table->timestamp('rejected_at')
                ->nullable()
                ->after('approved_at');


            /*
            |----------------------------------------------------------------------
            | MOTIF DU REJET
            |----------------------------------------------------------------------
            |
            | Le motif permettra de conserver la raison administrative du rejet.
            |
            */

            $table->text('rejection_reason')
                ->nullable()
                ->after('rejected_at');
        });
    }


    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::table('supplier_orders', function (Blueprint $table) {

            /*
            |----------------------------------------------------------------------
            | SUPPRIMER D'ABORD LA CLÉ ÉTRANGÈRE
            |----------------------------------------------------------------------
            */

            $table->dropForeign([
                'rejected_by',
            ]);


            /*
            |----------------------------------------------------------------------
            | SUPPRIMER LES COLONNES DE REJET
            |----------------------------------------------------------------------
            */

            $table->dropColumn([
                'rejected_by',
                'rejected_at',
                'rejection_reason',
            ]);
        });
    }
};
