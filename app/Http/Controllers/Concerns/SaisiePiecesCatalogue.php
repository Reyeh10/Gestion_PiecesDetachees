<?php

namespace App\Http\Controllers\Concerns;

use App\Models\BonTransfert;
use App\Models\Depot;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Saisie des pièces d'un BT interne (sortie bus, BT direct « + BT »), comme
 * un devis : pièce du catalogue, dépôt, quantité ; prix catalogue (sortie
 * bus) ou modifiable (BT direct). Va avec la vue partials/lignes-pieces-catalogue.
 */
trait SaisiePiecesCatalogue
{
    /** Règles de validation des lignes */
    protected function reglesLignes(bool $avecPrix = false): array
    {
        return [
            'lignes'              => ['required', 'array', 'min:1'],
            'lignes.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'lignes.*.depot_id'   => ['required', 'integer', 'exists:depots,id'],
            'lignes.*.quantite'   => ['required', 'numeric', 'gt:0'],
        ] + ($avecPrix ? ['lignes.*.prix' => ['required', 'numeric', 'min:0']] : []);
    }

    protected function messagesLignes(): array
    {
        return [
            'lignes.required'              => 'Ajoutez au moins une pièce.',
            'lignes.*.product_id.required' => 'Choisissez la pièce de chaque ligne.',
            'lignes.*.depot_id.required'   => 'Choisissez le dépôt de chaque ligne.',
            'lignes.*.quantite.gt'         => 'La quantité doit être supérieure à 0.',
            'lignes.*.prix.required'       => 'Indiquez le prix de chaque ligne.',
            'lignes.*.prix.min'            => 'Le prix ne peut pas être négatif.',
        ];
    }

    /** Pièces en stock (+ celles déjà dans le BT modifié), dépôts et lignes de départ */
    protected function donneesPieces(?BonTransfert $bt): array
    {
        $dejaDansBt = $bt ? $bt->lignes->pluck('product_id')->all() : [];

        $products = Product::query()
            ->with(['depotStocks' => fn ($q) => $q->where('quantity', '>', 0)->orderByDesc('quantity'), 'depotStocks.depot'])
            ->where(fn ($q) => $q->whereHas('depotStocks', fn ($s) => $s->where('quantity', '>', 0))->orWhereIn('id', $dejaDansBt))
            ->orderBy('designation')
            ->get(['id', 'reference', 'designation', 'sale_price', 'quantity']);

        return [
            'pieces' => $products->map(fn ($p) => [
                'id'          => $p->id,
                'text'        => $p->reference . ' — ' . $p->designation . ' — ' . number_format((float) $p->sale_price, 0, ',', ' ') . ' FDJ',
                'reference'   => $p->reference,
                'designation' => $p->designation,
                'prix'        => (float) $p->sale_price,
            ])->values(),
            'depotsParPiece' => $products->mapWithKeys(fn ($p) => [$p->id => $p->depotStocks->map(fn ($s) => [
                'depot_id' => $s->depot_id,
                'name'     => $s->depot->name ?? ('Dépôt #' . $s->depot_id),
                'quantity' => (float) $s->quantity,
            ])->values()]),
            'lignes' => old('lignes', $bt ? $bt->lignes->map(fn ($l) => [
                'product_id' => $l->product_id,
                'depot_id'   => $l->depot_id,
                'quantite'   => (float) $l->quantite,
                'prix'       => (float) $l->prix_unitaire,
            ])->all() : [['product_id' => null, 'depot_id' => null, 'quantite' => 1]]),
            'depots' => Depot::query()->orderBy('name')->get(['id', 'name']),
        ];
    }
}
