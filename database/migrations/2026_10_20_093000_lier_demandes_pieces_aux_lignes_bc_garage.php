<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * V6 — « Commander au fournisseur » depuis un BC du garage : la demande de
 * pièce spéciale retient la ligne du BC garage d'où elle vient. À la
 * réception, cette ligne redevient disponible (BT du reste possible).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_part_requests', function (Blueprint $table) {
            $table->foreignId('external_bon_commande_ligne_id')
                ->nullable()
                ->after('supplier_id')
                ->constrained('external_bon_commande_lignes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_part_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('external_bon_commande_ligne_id');
        });
    }
};
