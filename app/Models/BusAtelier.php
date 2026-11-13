<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Bus leasing / bus du transport public (V6) : copie de la liste tenue dans
 * l'Atelier (récupérée par AppAtelierApiService::listeBus). Les pièces de ces
 * bus sortent du Magasin par un « BT bus », envoyé à l'Atelier pour son récap.
 */
class BusAtelier extends Model
{
    protected $table = 'bus_atelier';

    public const GROUPES = [
        'bus'       => 'Bus leasing',
        'transport' => 'Bus transport public',
    ];

    protected $fillable = [
        'atelier_id', 'groupe', 'immatriculation', 'immatriculation_normalisee',
        'marque', 'modele', 'vin', 'actif', 'synchronise_le',
    ];

    protected $casts = [
        'actif'          => 'boolean',
        'synchronise_le' => 'datetime',
    ];

    /** Sorties de pièces (BC internes) de ce bus */
    public function sorties(): HasMany
    {
        return $this->hasMany(ExternalBonCommande::class, 'bus_atelier_id');
    }

    public function getGroupeLabelAttribute(): string
    {
        return self::GROUPES[$this->groupe] ?? $this->groupe;
    }

    public function getVehiculeAttribute(): string
    {
        return trim($this->marque . ' ' . $this->modele);
    }

    /** « 45 D 81 », « 45-D-81 » → « 45D81 » (même règle que l'Atelier) */
    public static function normaliser(?string $immatriculation): string
    {
        return strtoupper(preg_replace('/[\s\-\.]+/', '', (string) $immatriculation));
    }

    public static function parImmatriculation(?string $immatriculation): ?self
    {
        $cle = self::normaliser($immatriculation);

        return $cle === '' ? null : self::where('immatriculation_normalisee', $cle)->first();
    }
}
