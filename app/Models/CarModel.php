<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    use HasFactory;

    protected $table = 'models';

    protected $fillable = [
        'brand_id',
        'name',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // Un modèle appartient à une marque
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    // Un modèle peut être utilisé par plusieurs produits
    public function products()
    {
        return $this->hasMany(Product::class, 'model_id');
    }
}
