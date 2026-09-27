<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'supplier_id',
        'depot_id',
        'created_by',
        'approved_by',
        'order_date',
        'expected_delivery_date',
        'approved_at',
        'sent_at',
        'status',
        'currency',

        'subtotal',

        'discount_rate',
        'discount',

        'shipping_cost',

        'tax_rate',
        'tax_amount',

        'total',

        'payment_terms',
        'delivery_terms',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'approved_at' => 'datetime',
        'sent_at' => 'datetime',

        'subtotal' => 'decimal:2',

        'discount_rate' => 'decimal:2',
        'discount' => 'decimal:2',

        'shipping_cost' => 'decimal:2',

        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',

        'total' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | STATUTS
    |--------------------------------------------------------------------------
    */

    public const STATUS_DRAFT = 'draft';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_SENT = 'sent';

    public const STATUS_PARTIAL_RECEIVED = 'partial_received';

    public const STATUS_RECEIVED = 'received';

    public const STATUS_CANCELLED = 'cancelled';


    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            SupplierOrderItem::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isPartiallyReceived(): bool
    {
        return $this->status === self::STATUS_PARTIAL_RECEIVED;
    }

    public function isReceived(): bool
    {
        return $this->status === self::STATUS_RECEIVED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }


    /*
    |--------------------------------------------------------------------------
    | NUMÉRO DU BON DE COMMANDE
    |--------------------------------------------------------------------------
    */

    public static function generateOrderNumber(): string
    {
        $year = now()->format('Y');

        $prefix = 'BC-' . $year . '-';

        $lastOrder = static::query()
            ->where(
                'order_number',
                'like',
                $prefix . '%'
            )
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastOrder) {

            $lastSequence = (int) substr(
                $lastOrder->order_number,
                strlen($prefix)
            );

            $nextNumber = $lastSequence + 1;
        }

        return $prefix
            . str_pad(
                (string) $nextNumber,
                6,
                '0',
                STR_PAD_LEFT
            );
    }


    /*
    |--------------------------------------------------------------------------
    | RECALCUL DES TOTAUX
    |--------------------------------------------------------------------------
    */

    public function recalculateTotals(): void
    {
        $subtotal = round(
            (float) $this->items()->sum('line_total'),
            2
        );

        $discountRate = max(
            0,
            min(100, (float) $this->discount_rate)
        );

        $shippingCost = round(
            max(0, (float) $this->shipping_cost),
            2
        );

        $taxRate = max(
            0,
            min(100, (float) $this->tax_rate)
        );

        /*
        |--------------------------------------------------------------------------
        | REMISE
        |--------------------------------------------------------------------------
        */

        $discountAmount = round(
            $subtotal * $discountRate / 100,
            2
        );

        /*
        |--------------------------------------------------------------------------
        | BASE APRÈS REMISE
        |--------------------------------------------------------------------------
        */

        $afterDiscount = max(
            0,
            $subtotal - $discountAmount
        );

        /*
        |--------------------------------------------------------------------------
        | BASE TAXABLE
        |--------------------------------------------------------------------------
        |
        | La taxe est calculée après remise et en incluant le transport.
        |
        */

        $taxableAmount =
            $afterDiscount + $shippingCost;

        /*
        |--------------------------------------------------------------------------
        | TAXE
        |--------------------------------------------------------------------------
        */

        $taxAmount = round(
            $taxableAmount * $taxRate / 100,
            2
        );

        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */

        $total = round(
            $taxableAmount + $taxAmount,
            2
        );

        $this->update([
            'subtotal' => $subtotal,

            'discount_rate' => $discountRate,
            'discount' => $discountAmount,

            'shipping_cost' => $shippingCost,

            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,

            'total' => max(0, $total),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | QUANTITÉS
    |--------------------------------------------------------------------------
    */

    public function getOrderedQuantityAttribute(): float
    {
        return (float) $this->items()
            ->sum('quantity_ordered');
    }

    public function getReceivedQuantityAttribute(): float
    {
        return (float) $this->items()
            ->sum('quantity_received');
    }

    public function getRemainingQuantityAttribute(): float
    {
        return max(
            0,
            $this->ordered_quantity
            - $this->received_quantity
        );
    }
}
