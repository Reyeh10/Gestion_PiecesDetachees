<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [

        'name',
        'phone',
        'email',
        //'address',
        //'type',
        'credit_limit',
        'payment_terms',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
