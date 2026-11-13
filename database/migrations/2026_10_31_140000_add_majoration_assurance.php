<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * V6 — Majoration assurance (comme l'Atelier) : une vente directe ou un
 * proforma fait à une assurance a tous ses prix augmentés du taux de
 * l'assurance, sans ligne ni mention visible — seuls les prix unitaires montent.
 *
 *  - customers.est_assurance / taux_majoration : le client est une assurance,
 *    avec son taux propre (vide = config magasin.taux_majoration_assurance, 35 %)
 *  - sales / proformas.taux_majoration : taux appliqué (0 = pas de majoration)
 *  - sale_items / proforma_items.prix_base : prix avant majoration (catalogue
 *    ou saisi) ; price reste le prix final repris partout
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $t) {
            $t->boolean('est_assurance')->default(false)->after('email');
            $t->decimal('taux_majoration', 5, 2)->nullable()->after('est_assurance');
        });

        foreach (['sales', 'proformas'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->decimal('taux_majoration', 5, 2)->default(0)->after('subtotal');
            });
        }

        foreach (['sale_items', 'proforma_items'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->decimal('prix_base', 12, 2)->nullable()->after('price');
            });
            // lignes existantes : jamais majorées
            DB::table($table)->update(['prix_base' => DB::raw('price')]);
        }
    }

    public function down(): void
    {
        foreach (['sale_items', 'proforma_items'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn('prix_base'));
        }
        foreach (['sales', 'proformas'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn('taux_majoration'));
        }
        Schema::table('customers', fn (Blueprint $t) => $t->dropColumn(['est_assurance', 'taux_majoration']));
    }
};
