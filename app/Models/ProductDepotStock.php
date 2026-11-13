<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDepotStock extends Model
{
    protected $fillable = [

        'product_id',
        'depot_id',
        'rayon_id',
        'location_id',
        'quantity',

    ];

    /*
    |--------------------------------------------------------------------------
    | V6 : UN RAYON = UN SEUL DÉPÔT
    |--------------------------------------------------------------------------
    |
    | Une ligne de stock ne garde jamais le rayon d'un autre dépôt (ex. le rayon
    | du produit recopié lors d'un achat) : elle passe alors « Non défini ».
    | L'emplacement doit être dans le rayon de la ligne.
    |
    */

    protected static function booted(): void
    {
        static::saving(function (ProductDepotStock $stock) {
            if ($stock->rayon_id) {
                $depotDuRayon = Rayon::query()->whereKey($stock->rayon_id)->value('depot_id');

                if ($depotDuRayon !== null && (int) $depotDuRayon !== (int) $stock->depot_id) {
                    $stock->rayon_id = null;
                }
            }

            if ($stock->location_id) {
                $rayonDeLEmplacement = Location::query()->whereKey($stock->location_id)->value('rayon_id');

                if (! $stock->rayon_id || (int) $rayonDeLEmplacement !== (int) $stock->rayon_id) {
                    $stock->location_id = null;
                }
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | PRODUCT
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | DEPOT
    |--------------------------------------------------------------------------
    */

    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }

    /*
    |--------------------------------------------------------------------------
    | RAYON
    |--------------------------------------------------------------------------
    */

    public function rayon()
    {
        return $this->belongsTo(Rayon::class);
    }

    /*
    |--------------------------------------------------------------------------
    | EMPLACEMENT
    |--------------------------------------------------------------------------
    */

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
