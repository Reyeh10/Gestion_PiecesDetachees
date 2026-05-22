<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subfamily extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_id',
        'name',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // Une sous-famille appartient à une famille
    public function family()
    {
        return $this->belongsTo(FamilyModel::class, 'family_id');
    }

    // Une sous-famille peut être utilisée par plusieurs produits
    public function products()
    {
        return $this->hasMany(Product::class, 'subfamily_id');
    }
}
