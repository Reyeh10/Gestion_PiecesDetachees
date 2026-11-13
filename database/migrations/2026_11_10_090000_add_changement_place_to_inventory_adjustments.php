<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * V6 — Un ajustement peut aussi changer le rayon / l'emplacement de la pièce
 * dans le dépôt (fiche d'inventaire, import, correction). Comme la quantité,
 * le changement n'est appliqué qu'à la validation du chef magasinier / admin.
 * rayon_id / location_id = la place AVANT ; nouveau_* = la place demandée.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_adjustments', 'nouveau_rayon_id')) {
                $table->foreignId('nouveau_rayon_id')->nullable()->after('location_id')->constrained('rayons')->nullOnDelete();
            }
            if (! Schema::hasColumn('inventory_adjustments', 'nouvel_emplacement_id')) {
                $table->foreignId('nouvel_emplacement_id')->nullable()->after('nouveau_rayon_id')->constrained('locations')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_adjustments', 'nouvel_emplacement_id')) {
                $table->dropConstrainedForeignId('nouvel_emplacement_id');
            }
            if (Schema::hasColumn('inventory_adjustments', 'nouveau_rayon_id')) {
                $table->dropConstrainedForeignId('nouveau_rayon_id');
            }
        });
    }
};
