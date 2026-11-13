<?php

use App\Models\ExternalBonCommande;
use Illuminate\Database\Migrations\Migration;

/*
 * Rattrapage du compteur « Commandes garage » du menu (V6) : les BC qui ont
 * déjà un bon de transfert À JOUR sont traités ; ils ne doivent plus compter
 * comme « nouveaux ». Ne touche qu'à la colonne vu_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        ExternalBonCommande::query()
            ->whereNull('vu_at')
            ->whereNotNull('bon_transfert_id')
            ->with(['lignes', 'bonTransfert.lignes'])
            ->chunkById(100, function ($bcs) {
                foreach ($bcs as $bc) {
                    if ($bc->bonTransfert && $bc->bonTransfertAJour()) {
                        $bc->forceFill(['vu_at' => now()])->saveQuietly();
                    }
                }
            });
    }

    public function down(): void
    {
        // Rien à défaire : l'ancienne valeur (null) n'avait pas de sens.
    }
};
