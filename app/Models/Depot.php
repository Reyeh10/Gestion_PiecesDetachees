<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Depot extends Model
{
    protected $fillable = [

        'name',
        'code',
        'address',
        'is_active',

    ];

    /*
    |--------------------------------------------------------------------------
    | STOCKS
    |--------------------------------------------------------------------------
    */

    public function stocks()
    {
        return $this->hasMany(
            ProductDepotStock::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSFERTS ENVOYES
    |--------------------------------------------------------------------------
    */

    public function outgoingTransfers()
    {
        return $this->hasMany(
            DepotTransfer::class,
            'source_depot_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSFERTS RECUS
    |--------------------------------------------------------------------------
    */

    public function incomingTransfers()
    {
        return $this->hasMany(
            DepotTransfer::class,
            'destination_depot_id'
        );
    }
}
