<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rayon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'depot_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // V6 : un rayon appartient à un seul dépôt
    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }

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

    /*
    |--------------------------------------------------------------------------
    | DÉPÔT (V6)
    |--------------------------------------------------------------------------
    */

    /** Rayons d'un dépôt. */
    public function scopeDuDepot(Builder $query, $depotId): Builder
    {
        return $query->where('depot_id', $depotId);
    }

    /** Ce rayon peut-il servir dans ce dépôt ? (un rayon sans dépôt n'est proposé nulle part) */
    public function estDuDepot($depotId): bool
    {
        return $this->depot_id !== null && (int) $this->depot_id === (int) $depotId;
    }

    /** « », « Non défini », « non defini »… : pas de rayon. */
    public static function estNomVide(?string $nom): bool
    {
        $nom = mb_strtolower(trim((string) $nom));

        return $nom === '' || in_array($nom, ['non défini', 'non defini', 'non définie', 'non definie', '-', '—'], true);
    }

    /**
     * Rayon d'un import pour un dépôt : le rayon de ce nom s'il est dans ce dépôt
     * (ou sans dépôt : il lui est alors donné), sinon il est créé dans ce dépôt.
     * Retourne [rayon|null, erreur|null] ; null sans erreur = pas de rayon.
     */
    public static function pourDepot(?string $nom, $depotId, bool $creer = true): array
    {
        if (self::estNomVide($nom)) {
            return [null, null];
        }

        $nom = trim($nom);
        $rayon = self::query()->with('depot')->where('name', $nom)->first();

        if (! $rayon) {
            return $creer ? [self::create(['name' => $nom, 'depot_id' => $depotId]), null] : [null, "rayon « {$nom} » inconnu"];
        }

        if ($rayon->depot_id === null) {
            $rayon->update(['depot_id' => $depotId]);

            return [$rayon, null];
        }

        if (! $rayon->estDuDepot($depotId)) {
            return [null, "le rayon « {$rayon->name} » est dans le dépôt " . ($rayon->depot?->name ?? '?')];
        }

        return [$rayon, null];
    }
}
