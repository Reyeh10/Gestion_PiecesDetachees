<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'currency',
    ];

        /*
            |--------------------------------------------------------------------------
            | RELATIONS
            |--------------------------------------------------------------------------
            */
    public function products()
    {
        return $this->belongsToMany(
            Product::class
        )
        ->withPivot([
            'supplier_reference',
            'purchase_price',
            'delivery_delay',
            'is_primary',
            'active'
        ])
        ->withTimestamps();
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
