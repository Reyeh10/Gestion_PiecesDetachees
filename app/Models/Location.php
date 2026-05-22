<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'rayon_id',
        'name',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // Un emplacement appartient à un rayon
    public function rayon()
    {
        return $this->belongsTo(Rayon::class, 'rayon_id');
    }

    // Un emplacement peut contenir plusieurs produits
    public function products()
    {
        return $this->hasMany(Product::class, 'location_id');
    }
}
