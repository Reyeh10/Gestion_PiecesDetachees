<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProformaItem extends Model
{
    protected $fillable = [
        'proforma_id',
        'product_id',
        'quantity',
        'price',
    ];

    public function proforma()
    {
        return $this->belongsTo(Proforma::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
