<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [

        'reference',

        'supplier_id',

        'depot_id',

        'user_id',

        'subtotal',

        'vat',

        'total',

        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | FOURNISSEUR
    |--------------------------------------------------------------------------
    */

    public function supplier()
    {
        return $this->belongsTo(
            Supplier::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DÉPÔT DE RÉCEPTION
    |--------------------------------------------------------------------------
    */

    public function depot()
    {
        return $this->belongsTo(
            Depot::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ITEMS
    |--------------------------------------------------------------------------
    */

    public function items()
    {
        return $this->hasMany(
            PurchaseItem::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | USER
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }
}
