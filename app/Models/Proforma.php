<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proforma extends Model
{
    protected $fillable = [
        'customer_id',
        'subtotal',
        'discount',
        'tva',
        'total',
        'proforma_number',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(ProformaItem::class);
    }
}
