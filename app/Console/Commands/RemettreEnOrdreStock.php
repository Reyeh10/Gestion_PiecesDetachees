<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * V6 — Remise en ordre du stock après les anciens ajustements (l'import Excel
 * et la modification d'un ajustement vidaient les autres dépôts).
 *
 *   php artisan stock:remettre-en-ordre              (aperçu, rien n'est enregistré)
 *   php artisan stock:remettre-en-ordre --appliquer  (enregistre)
 *
 * 1. Stock total du produit ≠ somme de ses dépôts → total = somme des dépôts
 *    (le stock par dépôt est celui que les ventes, BT et inventaires utilisent).
 * 2. Stock de dépôt sans rayon / emplacement → repris de la fiche produit.
 * 3. Rayon du dépôt différent de celui de la fiche produit : signalé seulement.
 *
 * Toutes les pièces concernées sont écrites dans un fichier CSV
 * (storage/app/remise-en-ordre-stock-…csv) pour les recompter avec une fiche
 * d'inventaire.
 */
class RemettreEnOrdreStock extends Command
{
    protected $signature = 'stock:remettre-en-ordre {--appliquer : enregistrer (sinon simple aperçu)}';

    protected $description = 'V6 : aligne le stock total des produits sur leurs dépôts et complète rayons / emplacements des dépôts';

    public function handle(): int
    {
        $appliquer = (bool) $this->option('appliquer');
        $this->info($appliquer ? 'Remise en ordre du stock — ENREGISTREMENT' : 'Remise en ordre du stock — APERÇU (rien n\'est enregistré ; --appliquer pour enregistrer)');

        $totaux = DB::select("
            SELECT p.id, p.reference, p.designation, p.quantity AS total, COALESCE(d.somme, 0) AS somme, COALESCE(d.nb, 0) AS nb_depots
            FROM products p
            LEFT JOIN (SELECT product_id, SUM(quantity) somme, COUNT(*) nb FROM product_depot_stocks GROUP BY product_id) d ON d.product_id = p.id
            WHERE ABS(p.quantity - COALESCE(d.somme, 0)) > 0.001
            ORDER BY p.reference
        ");

        $sansRayon = DB::select("
            SELECT s.id, p.reference, dp.name AS depot, p.rayon_id, p.location_id
            FROM product_depot_stocks s
            JOIN products p ON p.id = s.product_id
            JOIN depots dp ON dp.id = s.depot_id
            WHERE s.rayon_id IS NULL AND s.location_id IS NULL AND p.rayon_id IS NOT NULL
        ");

        $differents = DB::select("
            SELECT p.reference, dp.name AS depot, rs.name AS rayon_depot, rp.name AS rayon_produit
            FROM product_depot_stocks s
            JOIN products p ON p.id = s.product_id
            JOIN depots dp ON dp.id = s.depot_id
            LEFT JOIN rayons rs ON rs.id = s.rayon_id
            LEFT JOIN rayons rp ON rp.id = p.rayon_id
            WHERE s.rayon_id IS NOT NULL AND p.rayon_id IS NOT NULL AND s.rayon_id <> p.rayon_id
            ORDER BY p.reference
        ");

        $ecartTotal = array_sum(array_map(fn ($r) => (float) $r->somme - (float) $r->total, $totaux));

        if ($appliquer) {
            DB::transaction(function () use ($totaux, $sansRayon) {
                foreach ($totaux as $r) {
                    DB::table('products')->where('id', $r->id)->update(['quantity' => round((float) $r->somme, 2), 'updated_at' => now()]);
                }

                foreach ($sansRayon as $r) {
                    DB::table('product_depot_stocks')->where('id', $r->id)->update(['rayon_id' => $r->rayon_id, 'location_id' => $r->location_id, 'updated_at' => now()]);
                }
            });
        }

        $fichier = 'remise-en-ordre-stock-' . now()->format('Ymd-His') . '.csv';
        $lignes = ["Type;Référence;Désignation / dépôt;Stock total fiche produit;Somme des dépôts;Remarque"];

        foreach ($totaux as $r) {
            $lignes[] = implode(';', ['Total ≠ dépôts', $r->reference, str_replace(';', ',', (string) $r->designation), $r->total, $r->somme, "{$r->nb_depots} dépôt(s) — à recompter"]);
        }

        foreach ($differents as $r) {
            $lignes[] = implode(';', ['Rayon différent', $r->reference, $r->depot, '', '', "dépôt : {$r->rayon_depot} / fiche produit : {$r->rayon_produit}"]);
        }

        Storage::disk('local')->put($fichier, "\u{FEFF}" . implode("\n", $lignes));

        $this->table(['Étape', 'Nombre'], [
            ['Produits dont le stock total ≠ somme des dépôts → total = somme des dépôts', count($totaux)],
            ['   écart total (somme des dépôts − ancien total)', number_format($ecartTotal, 2, ',', ' ')],
            ['Stocks de dépôt sans rayon → rayon / emplacement de la fiche produit', count($sansRayon)],
            ['Rayon du dépôt ≠ rayon de la fiche produit (signalé, non modifié)', count($differents)],
        ]);

        $this->line('Liste des pièces à vérifier : storage/app/' . $fichier);
        $this->line($appliquer ? '<info>Enregistré.</info>' : '<comment>Aperçu seulement : relancez avec --appliquer pour enregistrer.</comment>');

        return self::SUCCESS;
    }
}
