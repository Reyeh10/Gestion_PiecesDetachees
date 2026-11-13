<?php

namespace App\Console\Commands;

use App\Models\Rayon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * V6 : un rayon appartient à un seul dépôt.
 *
 * 1. donne son dépôt à chaque rayon d'après son nom :
 *    - Guelleh-Batal : rayons A à Q + Grillage ;
 *    - Cité Hillac   : rayons T à W + Chambre + Conteneur ;
 *    - Cité Nassib   : rayons CN… ;
 * 2. une ligne de stock rangée dans un rayon d'un autre dépôt passe « Non défini » ;
 * 3. le faux rayon « Non défini » (et les emplacements « Non défini ») sont
 *    supprimés : les pièces concernées sont simplement sans rayon / sans
 *    emplacement (affiché « Non défini »). Aucune quantité ne change.
 *
 * Aperçu par défaut (rien n'est enregistré) ; --appliquer pour enregistrer.
 */
class AffecterRayonsDepots extends Command
{
    protected $signature = 'rayons:affecter-depots {--appliquer : enregistrer (sinon simple aperçu)}';

    protected $description = 'V6 : donne son dépôt à chaque rayon et retire le faux rayon « Non défini »';

    public function handle(): int
    {
        $depots = $this->depots();

        if (! $depots) {
            return self::FAILURE;
        }

        $appliquer = (bool) $this->option('appliquer');
        $this->info($appliquer ? 'Application…' : 'APERÇU (rien n\'est enregistré ; --appliquer pour enregistrer)');

        try {
            DB::transaction(function () use ($depots, $appliquer) {
                $this->etapeDepots($depots);
                $this->etapeLignesMalPlacees();
                $this->etapeNonDefini();
                $this->bilan($depots);

                if (! $appliquer) {
                    throw new RuntimeException('apercu');
                }
            });
        } catch (RuntimeException $e) {
            if ($e->getMessage() !== 'apercu') {
                throw $e;
            }
        }

        $this->info($appliquer ? 'Terminé.' : 'Aperçu terminé : rien n\'a été enregistré.');

        return self::SUCCESS;
    }

    /** [clé => id du dépôt] trouvés par leur nom. */
    private function depots(): ?array
    {
        $trouver = fn (array $mots) => DB::table('depots')->where(function ($q) use ($mots) {
            foreach ($mots as $mot) {
                $q->orWhere('name', 'like', "%{$mot}%");
            }
        })->pluck('id', 'name');

        $depots = [];

        foreach (['guelleh' => ['guelleh'], 'hillac' => ['hillac'], 'nassib' => ['nassib', 'nasib']] as $cle => $mots) {
            $liste = $trouver($mots);

            if ($liste->count() !== 1) {
                $this->error("Dépôt « {$cle} » : " . $liste->count() . ' dépôt(s) trouvé(s) au lieu d\'un seul. Rien n\'est fait.');

                return null;
            }

            $depots[$cle] = ['id' => (int) $liste->first(), 'nom' => $liste->keys()->first()];
        }

        return $depots;
    }

    /** Clé du dépôt d'un rayon d'après son nom (null : non reconnu, ou « Non défini »). */
    public static function depotDuNom(string $nom): ?string
    {
        $n = mb_strtoupper(trim($nom));

        if (Rayon::estNomVide($nom)) {
            return null;
        }

        if (preg_match('/^CN\s*\d/', $n) || in_array($n, ['NASIB', 'NASSIB'], true)) {
            return 'nassib';
        }

        if (in_array($n, ['CHAMBRE', 'CONTENEUR', 'HILLAC'], true)) {
            return 'hillac';
        }

        if (in_array($n, ['GRILLAGE', 'GB'], true) || str_starts_with($n, 'GUELLE')) {
            return 'guelleh';
        }

        $lettre = mb_substr($n, 0, 1);

        if ($lettre >= 'A' && $lettre <= 'Q') {
            return 'guelleh';
        }

        if ($lettre >= 'T' && $lettre <= 'W') {
            return 'hillac';
        }

        return null;
    }

    private function etapeDepots(array $depots): void
    {
        $this->line('');
        $this->info('1. Dépôt de chaque rayon (d\'après son nom)');

        $parDepot = array_fill_keys(array_keys($depots), []);
        $inconnus = [];

        foreach (DB::table('rayons')->whereNull('depot_id')->orderBy('name')->get() as $rayon) {
            if (Rayon::estNomVide($rayon->name)) {
                continue; // traité à l'étape 3
            }

            $cle = self::depotDuNom($rayon->name);

            if ($cle === null) {
                $inconnus[] = $rayon->name;
                continue;
            }

            DB::table('rayons')->where('id', $rayon->id)->update(['depot_id' => $depots[$cle]['id'], 'updated_at' => now()]);
            $parDepot[$cle][] = $rayon->name;
        }

        foreach ($parDepot as $cle => $noms) {
            $this->line(sprintf('   %-15s %3d rayon(s) : %s', $depots[$cle]['nom'], count($noms), implode(', ', $noms) ?: '—'));
        }

        $this->line('   Non reconnus (à ranger à la main) : ' . (count($inconnus) ? implode(', ', $inconnus) : 'aucun'));
    }

    private function etapeLignesMalPlacees(): void
    {
        $this->line('');
        $this->info('2. Pièces rangées dans un rayon d\'un autre dépôt → « Non défini »');

        $lignes = DB::table('product_depot_stocks as s')
            ->join('rayons as r', 'r.id', '=', 's.rayon_id')
            ->join('products as p', 'p.id', '=', 's.product_id')
            ->join('depots as d', 'd.id', '=', 's.depot_id')
            ->whereNotNull('r.depot_id')
            ->whereColumn('r.depot_id', '<>', 's.depot_id')
            ->get(['s.id', 'p.reference', 'd.name as depot', 'r.name as rayon', 's.quantity']);

        foreach ($lignes as $l) {
            $this->line(sprintf('   %-20s %-15s rayon %-10s qté %s', $l->reference, $l->depot, $l->rayon, rtrim(rtrim((string) $l->quantity, '0'), '.')));
        }

        DB::table('product_depot_stocks')->whereIn('id', $lignes->pluck('id'))
            ->update(['rayon_id' => null, 'location_id' => null, 'updated_at' => now()]);

        $this->line("   {$lignes->count()} ligne(s), dont " . $lignes->where('quantity', '>', 0)->count() . ' avec du stock (quantités inchangées).');
    }

    private function etapeNonDefini(): void
    {
        $this->line('');
        $this->info('3. Faux rayon « Non défini » → pièces sans rayon / sans emplacement');

        $rayonIds = DB::table('rayons')->get(['id', 'name'])->filter(fn ($r) => Rayon::estNomVide($r->name))->pluck('id');
        $emplacementIds = DB::table('locations')->get(['id', 'name', 'rayon_id'])
            ->filter(fn ($l) => Rayon::estNomVide($l->name) || $rayonIds->contains($l->rayon_id))
            ->pluck('id');

        $tables = [
            'product_depot_stocks'  => [['rayon_id'], ['location_id']],
            'products'              => [['rayon_id'], ['location_id']],
            'inventory_adjustments' => [['rayon_id', 'nouveau_rayon_id'], ['location_id', 'nouvel_emplacement_id']],
            'inventaire_lignes'     => [['rayon_id', 'nouveau_rayon_id'], ['location_id', 'nouvel_emplacement_id']],
        ];

        foreach ($tables as $table => [$colsRayon, $colsEmpl]) {
            $total = 0;

            foreach ($colsEmpl as $col) {
                $total += DB::table($table)->whereIn($col, $emplacementIds)->update([$col => null]);
            }

            foreach ($colsRayon as $col) {
                $total += DB::table($table)->whereIn($col, $rayonIds)->update([$col => null]);
            }

            $this->line(sprintf('   %-22s %4d valeur(s) remise(s) à vide', $table, $total));
        }

        DB::table('locations')->whereIn('id', $emplacementIds)->delete();
        DB::table('rayons')->whereIn('id', $rayonIds)->delete();

        $this->line("   Supprimés : {$rayonIds->count()} rayon(s) « Non défini », {$emplacementIds->count()} emplacement(s) (« Non défini » ou rangés dedans).");
    }

    private function bilan(array $depots): void
    {
        $this->line('');
        $this->info('Bilan');

        foreach ($depots as $d) {
            $rayons = DB::table('rayons')->where('depot_id', $d['id'])->count();
            $sans = DB::table('product_depot_stocks')->where('depot_id', $d['id'])->whereNull('rayon_id')->count();
            $this->line(sprintf('   %-15s %3d rayon(s), %4d pièce(s) « Non défini »', $d['nom'], $rayons, $sans));
        }

        $this->line('   Rayons sans dépôt : ' . DB::table('rayons')->whereNull('depot_id')->count());
        $this->line('   Pièces dans un rayon d\'un autre dépôt : ' . DB::table('product_depot_stocks as s')
            ->join('rayons as r', 'r.id', '=', 's.rayon_id')->whereColumn('r.depot_id', '<>', 's.depot_id')->count());
    }
}
