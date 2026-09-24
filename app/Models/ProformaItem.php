<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProformaItem extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    protected $table = 'proforma_items';

    /*
    |--------------------------------------------------------------------------
    | CHAMPS AUTORISÉS
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'proforma_id',
        'product_id',
        'depot_id',
        'quantity',
        'price',
        'total',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'proforma_id' => 'integer',
        'product_id' => 'integer',
        'depot_id' => 'integer',
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | PROFORMA
    |--------------------------------------------------------------------------
    */

    public function proforma(): BelongsTo
    {
        return $this->belongsTo(
            Proforma::class,
            'proforma_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PRODUIT
    |--------------------------------------------------------------------------
    */

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DÉPÔT
    |--------------------------------------------------------------------------
    */

    public function depot(): BelongsTo
    {
        return $this->belongsTo(
            Depot::class,
            'depot_id'
        );
    }
}
