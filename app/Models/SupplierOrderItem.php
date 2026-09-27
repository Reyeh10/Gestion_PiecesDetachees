<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_order_id',
        'vehicle_part_request_id',
        'product_id',
        'reference',
        'description',
        'unit',
        'quantity_ordered',
        'quantity_received',
        'unit_price',
        'line_total',
        'notes',
    ];

    protected $casts = [
        'quantity_ordered' => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'unit_price' => 'decimal:4',
        'line_total' => 'decimal:2',
    ];


    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function supplierOrder(): BelongsTo
    {
        return $this->belongsTo(
            SupplierOrder::class
        );
    }

    public function vehiclePartRequest(): BelongsTo
    {
        return $this->belongsTo(
            VehiclePartRequest::class
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | QUANTITÉS
    |--------------------------------------------------------------------------
    */

    public function getRemainingQuantityAttribute(): float
    {
        return max(
            0,
            (float) $this->quantity_ordered
            - (float) $this->quantity_received
        );
    }

    public function isFullyReceived(): bool
    {
        return $this->remaining_quantity <= 0;
    }

    public function isPartiallyReceived(): bool
    {
        return
            (float) $this->quantity_received > 0
            &&
            !$this->isFullyReceived();
    }


    /*
    |--------------------------------------------------------------------------
    | CALCUL DU TOTAL DE LA LIGNE
    |--------------------------------------------------------------------------
    */

    public function calculateLineTotal(): float
    {
        return round(
            (float) $this->quantity_ordered
            * (float) $this->unit_price,
            2
        );
    }
}
