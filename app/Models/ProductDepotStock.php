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
