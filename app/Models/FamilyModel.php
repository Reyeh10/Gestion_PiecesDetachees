<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyModel extends Model
{
    use HasFactory;

    protected $table = 'families';

    protected $fillable = [
        'name',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // Une famille possède plusieurs sous-familles
    public function subfamilies()
    {
        return $this->hasMany(Subfamily::class, 'family_id');
    }

    // Une famille peut être utilisée par plusieurs produits
    public function products()
    {
        return $this->hasMany(Product::class, 'family_id');
    }
}
