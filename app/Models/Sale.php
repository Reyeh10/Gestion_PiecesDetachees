<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'customer_id',
        'payment_type',
        'invoice_number',
         'document_type',

        // TOTALS
        'subtotal',
        'discount',
        'tva',
        'total',

        // STATUS
        'status',

        // Discount
        'discount_amount',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'subtotal' => 'float',
        'discount' => 'float',
        'tva' => 'float',
        'total' => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER
    |--------------------------------------------------------------------------
    */

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ITEMS
    |--------------------------------------------------------------------------
    */

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | PAYMENTS
    |--------------------------------------------------------------------------
    */

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL PAID
    |--------------------------------------------------------------------------
    */

    public function getPaidAmountAttribute()
    {
        return $this->payments->sum('amount');
    }

    /*
    |--------------------------------------------------------------------------
    | REMAINING AMOUNT
    |--------------------------------------------------------------------------
    */

    public function getRemainingAmountAttribute()
    {
        return $this->total - $this->paid_amount;
    }

    /*
    |--------------------------------------------------------------------------
    | FULLY PAID ?
    |--------------------------------------------------------------------------
    */

    public function getIsPaidAttribute()
    {
        return $this->remaining_amount <= 0;
    }
}
