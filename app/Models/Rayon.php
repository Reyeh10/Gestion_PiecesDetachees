<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rayon extends Model
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

    // Un rayon possède plusieurs emplacements
    public function locations()
    {
        return $this->hasMany(Location::class, 'rayon_id');
    }

    // Un rayon peut contenir plusieurs produits
    public function products()
    {
        return $this->hasMany(Product::class, 'rayon_id');
    }
}
