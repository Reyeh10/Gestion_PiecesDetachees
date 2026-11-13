<?php

namespace App\Services\Concerns;

use App\Models\ExternalBonCommande;
use App\Models\Product;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * BC internes du Magasin (sorties bus, BT directs) : lignes saisies à
 * l'écran, toutes disponibles. Prix : celui saisi (BT direct), sinon le prix
 * de vente du catalogue.
 */
trait LignesBcInterne
{
    /** Fusionne les doublons (même pièce, même dépôt ; le 1er prix saisi est gardé) et retire les lignes vides. */
    protected function regrouper(array $lignes): array
    {
        return collect($lignes)
            ->filter(fn ($l) => ! empty($l['product_id']) && ! empty($l['depot_id']) && (float) ($l['quantite'] ?? 0) > 0)
            ->groupBy(fn ($l) => $l['product_id'].':'.$l['depot_id'])
            ->map(fn (Collection $g) => [
                'product_id' => (int) $g->first()['product_id'],
                'depot_id'   => (int) $g->first()['depot_id'],
                'quantite'   => round($g->sum(fn ($l) => (float) $l['quantite']), 2),
                'prix'       => isset($g->first()['prix']) && $g->first()['prix'] !== '' ? round((float) $g->first()['prix'], 2) : null,
            ])
            ->values()->all();
    }

    protected function ecrireLignes(ExternalBonCommande $bc, array $lignes, ?int $btId = null): void
    {
        $produits = Product::query()->whereIn('id', array_column($lignes, 'product_id'))->get()->keyBy('id');

        foreach ($lignes as $i => $l) {
            $p = $produits->get($l['product_id']) ?? throw new RuntimeException('Pièce introuvable dans le catalogue.');

            $bc->lignes()->create([
                'position'            => $i + 1,
                'product_id'          => $p->id,
                'depot_id'            => $l['depot_id'],
                'bon_transfert_id'    => $btId,
                'reference'           => $p->reference,
                'designation'         => $p->designation,
                'quantite_demandee'   => $l['quantite'],
                'quantite_disponible' => $l['quantite'],
                'disponible'          => true,
                'prix_unitaire'       => $l['prix'] ?? round((float) $p->sale_price, 2),
            ]);
        }
    }

    /** PREFIXE-AAAA-NNNN (numéro interne, distinct des BC du garage) */
    protected function prochainNumeroBc(string $prefixe): string
    {
        $prefixe .= '-'.now()->format('Y').'-';

        $dernier = ExternalBonCommande::query()
            ->where('numero', 'like', $prefixe.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('numero');

        $suivant = $dernier ? ((int) substr($dernier, strlen($prefixe))) + 1 : 1;

        return sprintf('%s%04d', $prefixe, $suivant);
    }
}
