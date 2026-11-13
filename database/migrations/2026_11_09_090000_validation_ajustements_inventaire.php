<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * V6 — Ajustements d'inventaire validés avant de toucher le stock.
 *
 * - statut : en_attente → valide | refuse | annule ;
 * - motif choisi dans une liste (+ « reason » = remarque libre) ;
 * - ecart enregistré à la saisie, appliqué au stock du dépôt à la validation ;
 * - valeur de l'écart au CUMP (purchase_price) ;
 * - created_by = qui saisit, approved_by = qui valide ;
 * - corrige_id : contre-ajustement (plus de modification / suppression).
 *
 * Les ajustements existants sont repris « validés » (ils ont déjà touché le stock).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->string('statut', 20)->default('en_attente')->after('id')->index();
            $table->string('source', 20)->nullable()->after('statut');
            $table->string('motif', 30)->nullable()->after('new_qty');
            $table->decimal('ecart', 15, 2)->nullable()->after('motif');
            $table->decimal('cump', 15, 4)->nullable()->after('ecart');
            $table->decimal('valeur_ecart', 15, 2)->nullable()->after('cump');
            $table->foreignId('created_by')->nullable()->after('reason')->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable()->after('approved_by');
            $table->foreignId('refuse_par')->nullable()->after('validated_at')->constrained('users')->nullOnDelete();
            $table->timestamp('refuse_le')->nullable()->after('refuse_par');
            $table->string('motif_refus', 500)->nullable()->after('refuse_le');
            $table->foreignId('corrige_id')->nullable()->after('motif_refus')->constrained('inventory_adjustments')->nullOnDelete();
        });

        DB::table('inventory_adjustments')->update([
            'statut'       => 'valide',
            'ecart'        => DB::raw('new_qty - old_qty'),
            'created_by'   => DB::raw('approved_by'),
            'validated_at' => DB::raw('created_at'),
            'source'       => DB::raw("CASE WHEN reason LIKE 'Inventaire INV-%' THEN 'inventaire' ELSE 'manuel' END"),
        ]);

        // les ajustements faits par import Excel se reconnaissent à leur mouvement
        DB::statement("UPDATE inventory_adjustments a JOIN stock_movements m ON m.reference = CONCAT('ADJ-', a.id)
                       SET a.source = 'import' WHERE m.source LIKE 'Ajustement inventaire Excel%'");
    }

    public function down(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('corrige_id');
            $table->dropConstrainedForeignId('refuse_par');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['statut', 'source', 'motif', 'ecart', 'cump', 'valeur_ecart', 'validated_at', 'refuse_le', 'motif_refus']);
        });
    }
};
