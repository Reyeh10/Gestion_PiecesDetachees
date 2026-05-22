<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // Une marque possède plusieurs modèles
    public function models()
    {
        return $this->hasMany(CarModel::class, 'brand_id');
    }

    // Une marque peut être utilisée par plusieurs produits
    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id');
    }
}
