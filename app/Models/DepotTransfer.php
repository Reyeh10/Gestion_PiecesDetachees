<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepotTransfer extends Model
{
    protected $fillable = [

        'product_id',
        'source_depot_id',
        'destination_depot_id',
        'quantity',
        'note',
        'user_id',

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
    | SOURCE
    |--------------------------------------------------------------------------
    */

    public function sourceDepot()
    {
        return $this->belongsTo(
            Depot::class,
            'source_depot_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DESTINATION
    |--------------------------------------------------------------------------
    */

    public function destinationDepot()
    {
        return $this->belongsTo(
            Depot::class,
            'destination_depot_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | USER
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
