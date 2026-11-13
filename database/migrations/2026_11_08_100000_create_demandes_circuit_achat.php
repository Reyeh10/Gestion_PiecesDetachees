<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * V7 — Circuit pièce spéciale → bon de commande → réception.
 *
 * - demandes : un dossier qui regroupe les pièces demandées ensemble (une
 *   saisie, un import Excel ou une demande de l'Atelier). Il a un ou plusieurs
 *   bons de commande (un par fournisseur, plus les compléments).
 * - supplier_orders : validation (signature) du chef magasinier avant
 *   l'approbation de l'admin, et « Solder » (le reste ne sera pas livré).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->string('source', 20)->default('saisie'); // saisie | import | atelier
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('depot_id')->nullable()->constrained('depots')->nullOnDelete();
            $table->unsignedBigInteger('external_bon_commande_id')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('vehicle_part_requests', function (Blueprint $table) {
            $table->foreignId('demande_id')->nullable()->after('id')->constrained('demandes')->nullOnDelete();
        });

        Schema::table('supplier_orders', function (Blueprint $table) {
            $table->foreignId('demande_id')->nullable()->after('id')->constrained('demandes')->nullOnDelete();
            $table->unsignedBigInteger('validated_by')->nullable()->after('created_by');
            $table->timestamp('validated_at')->nullable()->after('approved_at');
            $table->unsignedBigInteger('closed_by')->nullable()->after('sent_at');
            $table->timestamp('closed_at')->nullable()->after('closed_by');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('demande_id');
            $table->dropColumn(['validated_by', 'validated_at', 'closed_by', 'closed_at']);
        });

        Schema::table('vehicle_part_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('demande_id');
        });

        Schema::dropIfExists('demandes');
    }
};
