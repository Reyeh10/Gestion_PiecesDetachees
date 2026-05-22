<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDepotStock extends Model
{
    protected $fillable = [

        'product_id',
        'depot_id',
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
}
