<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'price',
        'total',

        'previous_quantity',
        'previous_purchase_price',
        'previous_cost_price',
        'previous_sale_price',
        'previous_coef_purchase',
        'previous_coef_sale',

        'new_weighted_purchase_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'total' => 'decimal:2',

        'previous_quantity' => 'decimal:2',
        'previous_purchase_price' => 'decimal:4',
        'previous_cost_price' => 'decimal:4',
        'previous_sale_price' => 'decimal:2',

        'previous_coef_purchase' => 'decimal:2',
        'previous_coef_sale' => 'decimal:2',

        'new_weighted_purchase_price' => 'decimal:4',
    ];

    /*
    |--------------------------------------------------------------------------
    | ACHAT
    |--------------------------------------------------------------------------
    */

    public function purchase()
    {
        return $this->belongsTo(
            Purchase::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PRODUIT
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo(
            Product::class
        );
    }
}
