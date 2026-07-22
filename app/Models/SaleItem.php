<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'vehicle_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'sale_id' => 'integer',
        'product_id' => 'integer',
        'vehicle_id' => 'integer',
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    /**
     * Vente correspondante.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(
            Sale::class,
            'sale_id'
        );
    }

    /**
     * Produit vendu.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }

    /**
     * Véhicule concerné par la pièce.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(
            Vehicle::class,
            'vehicle_id'
        );
    }

    /**
     * Total de la ligne.
     */
    public function getLineTotalAttribute(): float
    {
        return
            (float) $this->quantity *
            (float) $this->price;
    }
}
