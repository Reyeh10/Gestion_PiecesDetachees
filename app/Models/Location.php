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

    /**
     * V6 : emplacement d'un import, cherché (ou créé) dans son rayon.
     * Pas de rayon ou nom vide / « Non défini » : pas d'emplacement.
     */
    public static function pourRayon(?string $nom, ?Rayon $rayon, bool $creer = true): ?self
    {
        if (! $rayon || Rayon::estNomVide($nom)) {
            return null;
        }

        $nom = trim($nom);
        $emplacement = self::query()->where('rayon_id', $rayon->id)->where('name', $nom)->first();

        return $emplacement ?: ($creer ? self::create(['name' => $nom, 'rayon_id' => $rayon->id]) : null);
    }
}
