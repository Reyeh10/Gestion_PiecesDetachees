<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    protected $table = 'sales';

    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        // CLIENT ET VÉHICULE
        'customer_id',
        'vehicle_id',
        'user_id',

        // INFORMATIONS DE LA VENTE
        'payment_type',
        'invoice_number',
        'document_type',

        // MONTANTS
        'subtotal',
        'discount',
        'discount_amount',
        'tva',
        'total',

        // STATUT
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'customer_id' => 'integer',
        'vehicle_id' => 'integer',

        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tva' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER
    |--------------------------------------------------------------------------
    */

    public function customer()
    {
        return $this->belongsTo(
            Customer::class,
            'customer_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VEHICLE
    |--------------------------------------------------------------------------
    */

    public function vehicle()
    {
        return $this->belongsTo(
            Vehicle::class,
            'vehicle_id',
            'id'
        );
    }

     /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ITEMS
    |--------------------------------------------------------------------------
    |
    | Cette relation peut être conservée temporairement si vos anciennes
    | factures utilisent encore la table sale_items.
    |
    | Pour les nouvelles ventes de véhicules, vehicle_id sera enregistré
    | directement dans la table sales.
    |
    */

    public function items()
    {
        return $this->hasMany(
            SaleItem::class,
            'sale_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PAYMENTS
    |--------------------------------------------------------------------------
    */

    public function payments()
    {
        return $this->hasMany(
            Payment::class,
            'sale_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL PAID
    |--------------------------------------------------------------------------
    */

    public function getPaidAmountAttribute(): float
    {
        if ($this->relationLoaded('payments')) {
            return (float) $this->payments->sum('amount');
        }

        return (float) $this->payments()->sum('amount');
    }

    /*
    |--------------------------------------------------------------------------
    | REMAINING AMOUNT
    |--------------------------------------------------------------------------
    */

    public function getRemainingAmountAttribute(): float
    {
        return max(
            0,
            round(
                (float) $this->total - (float) $this->paid_amount,
                2
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FULLY PAID
    |--------------------------------------------------------------------------
    */

    public function getIsPaidAttribute(): bool
    {
        return (float) $this->remaining_amount <= 0;
    }

    /*
    |--------------------------------------------------------------------------
    | PARTIALLY PAID
    |--------------------------------------------------------------------------
    */

    public function getIsPartiallyPaidAttribute(): bool
    {
        return (float) $this->paid_amount > 0
            && (float) $this->remaining_amount > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | CANCELLED
    |--------------------------------------------------------------------------
    */

    public function getIsCancelledAttribute(): bool
    {
        return in_array(
            strtolower((string) $this->status),
            [
                'cancelled',
                'annule',
                'annulé',
            ],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SOLD
    |--------------------------------------------------------------------------
    */

    public function getIsSoldAttribute(): bool
    {
        return in_array(
            strtolower((string) $this->status),
            [
                'vendu',
                'sold',
                'paid',
                'paye',
                'payé',
            ],
            true
        );
    }
}
