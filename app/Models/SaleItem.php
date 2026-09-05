<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'sale_id',
        'product_id',
        'vehicle_id',
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
        'sale_id' => 'integer',
        'product_id' => 'integer',
        'vehicle_id' => 'integer',
        'depot_id' => 'integer',
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION : VENTE
    |--------------------------------------------------------------------------
    */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(
            Sale::class,
            'sale_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATION : PRODUIT
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
    | RELATION : VÉHICULE
    |--------------------------------------------------------------------------
    */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(
            Vehicle::class,
            'vehicle_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATION : DÉPÔT
    |--------------------------------------------------------------------------
    |
    | Dépôt dans lequel le stock a été prélevé au moment de la vente.
    | Cette information permet de remettre le stock dans le bon dépôt
    | lors d'une annulation ou suppression de vente.
    |
    */
    public function depot(): BelongsTo
    {
        return $this->belongsTo(
            Depot::class,
            'depot_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL DE LA LIGNE
    |--------------------------------------------------------------------------
    */
    public function getLineTotalAttribute(): float
    {
        return round(
            (float) $this->quantity
            *
            (float) $this->price,
            2
        );
    }
}
