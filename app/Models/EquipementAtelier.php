<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Consommation atelier (V6) : équipement du garage (pont élévateur,
 * compresseur d'air…). Copie de la liste tenue dans l'Atelier (récupérée par
 * AppAtelierApiService::listeEquipements). Un BC / BT de consommation = un
 * seul équipement, avec une ou plusieurs pièces.
 */
class EquipementAtelier extends Model
{
    protected $table = 'equipements_atelier';

    protected $fillable = ['atelier_id', 'nom', 'actif', 'synchronise_le'];

    protected $casts = [
        'actif'          => 'boolean',
        'synchronise_le' => 'datetime',
    ];

    /** BC de consommation (créés par l'Atelier ou BT directs du Magasin) */
    public function bonsCommande(): HasMany
    {
        return $this->hasMany(ExternalBonCommande::class, 'equipement_atelier_id');
    }
}
