<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proforma extends Model
{
    use HasFactory;

    protected $table = 'proformas';

    public const STATUS_VALIDATED = 'Validé';
    public const STATUS_CONVERTED = 'Converti';
    public const STATUS_CANCELLED = 'Annulé';

    protected $fillable = [
        'proforma_number',
        'customer_id',
        'vehicle_id',
        'created_by',
        'payment_type',

        'subtotal',
        'discount',
        'discount_amount',
        'tva',
        'total',

        'status',

        'sale_id',
        'converted_at',
        'converted_by',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tva' => 'decimal:2',
        'total' => 'decimal:2',

        'converted_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $attributes = [
        'discount' => 0,
        'discount_amount' => 0,
        'tva' => 0,
        'total' => 0,
        'status' => self::STATUS_VALIDATED,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProformaItem::class, 'proforma_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function convertedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function getProformaNumberAttribute($value): string
    {
        if (!empty($value)) {
            return (string) $value;
        }

        return 'PROFORMA-' . str_pad(
            (string) $this->getKey(),
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    public function getCalculatedSubtotalAttribute(): float
    {
        return round((float) ($this->subtotal ?? 0), 2);
    }

    public function getDiscountRateAttribute(): float
    {
        return round(
            max(0, min(100, (float) ($this->discount ?? 0))),
            2
        );
    }

    public function getCalculatedDiscountAmountAttribute(): float
    {
        if (
            $this->discount_amount !== null
            && (float) $this->discount_amount > 0
        ) {
            return round((float) $this->discount_amount, 2);
        }

        return round(
            $this->calculated_subtotal * $this->discount_rate / 100,
            2
        );
    }

    public function getTaxableAmountAttribute(): float
    {
        return max(
            0,
            round(
                $this->calculated_subtotal
                - $this->calculated_discount_amount,
                2
            )
        );
    }

    public function getCalculatedTvaAttribute(): float
    {
        if ($this->tva !== null && (float) $this->tva > 0) {
            return round((float) $this->tva, 2);
        }

        return round($this->taxable_amount * 0.10, 2);
    }

    public function getCalculatedTotalAttribute(): float
    {
        if ($this->total !== null && (float) $this->total > 0) {
            return round((float) $this->total, 2);
        }

        return round(
            $this->taxable_amount + $this->calculated_tva,
            2
        );
    }

    public function getTotalAmountAttribute(): float
    {
        return $this->calculated_total;
    }

    public function getIsConvertedAttribute(): bool
    {
        return
            $this->status === self::STATUS_CONVERTED
            && !empty($this->sale_id);
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}
