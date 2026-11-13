<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * V6 — Vente / proforma à une assurance : le véhicule n'est pas obligatoire et
 * peut être celui d'un autre client. L'immatriculation est saisie librement
 * (facultative) ; si elle correspond à un véhicule connu, vehicle_id y est
 * aussi rattaché.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['sales', 'proformas'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('immatriculation', 30)->nullable()->after('vehicle_id');
            });
        }
    }

    public function down(): void
    {
        foreach (['sales', 'proformas'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn('immatriculation'));
        }
    }
};
