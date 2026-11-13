<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * V6 — « supprimer » séparé de « modifier », module par module.
 * Reprise des réglages faits à la main pour chaque utilisateur
 * (table user_permissions) : un écart sur l'ancienne permission est recopié
 * sur la nouvelle permission de suppression, pour que personne ne gagne ni
 * ne perde un droit en silence. « supprimer_referentiels » est remplacé par
 * produits + catégories + fournisseurs.
 */
return new class extends Migration
{
    private array $correspondances = [
        'supprimer_referentiels' => ['supprimer_produits', 'supprimer_categories', 'supprimer_fournisseurs'],
        'modifier_clients'       => ['supprimer_clients'],
        'modifier_mouvements'    => ['supprimer_mouvements'],
        'modifier_ajustements'   => ['supprimer_ajustements'],
        'gerer_depots'           => ['supprimer_depots'],
        'gerer_transferts'       => ['supprimer_transferts'],
        'gerer_proformas'        => ['supprimer_proformas'],
        'gerer_utilisateurs'     => ['supprimer_utilisateurs'],
    ];

    public function up(): void
    {
        foreach ($this->correspondances as $ancienne => $nouvelles) {
            $lignes = DB::table('user_permissions')->where('permission_key', $ancienne)->get();

            foreach ($lignes as $ligne) {
                foreach ($nouvelles as $nouvelle) {
                    DB::table('user_permissions')->updateOrInsert(
                        ['user_id' => $ligne->user_id, 'permission_key' => $nouvelle],
                        ['granted' => $ligne->granted]
                    );
                }
            }
        }

        DB::table('user_permissions')->where('permission_key', 'supprimer_referentiels')->delete();
    }

    public function down(): void
    {
        DB::table('user_permissions')->whereIn('permission_key', [
            'supprimer_produits', 'supprimer_categories', 'supprimer_fournisseurs', 'supprimer_clients',
            'supprimer_mouvements', 'supprimer_ajustements', 'supprimer_depots', 'supprimer_transferts',
            'supprimer_proformas', 'supprimer_utilisateurs',
        ])->delete();
    }
};
