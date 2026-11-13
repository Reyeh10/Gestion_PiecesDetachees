<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Fiche d'inventaire (INV-AAAA-NNN) — V6 : liste imprimée d'un ou plusieurs
 * rayons d'un dépôt, comptée sur place, saisie puis validée en ajustements.
 */
class Inventaire extends Model
{
    public const EN_COURS = 'en_cours';
    public const VALIDE = 'valide';
    public const ANNULE = 'annule';

    protected $fillable = [
        'numero', 'annee', 'sequence', 'depot_id', 'perimetre', 'statut',
        'compteur', 'notes', 'cree_par', 'valide_par', 'valide_le',
    ];

    protected $casts = [
        'valide_le' => 'datetime',
    ];

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(InventaireLigne::class)->orderBy('ordre')->orderBy('id');
    }

    public function creePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function validePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function estEnCours(): bool
    {
        return $this->statut === self::EN_COURS;
    }

    public function getStatutLabel(): string
    {
        return match ($this->statut) {
            self::VALIDE => 'Validé',
            self::ANNULE => 'Annulé',
            default      => 'En cours',
        };
    }

    /** INV-AAAA-NNN, numérotation propre, remise à 001 chaque année. */
    public static function prochainNumero(): array
    {
        $annee = (int) now()->format('Y');
        $sequence = (int) static::query()->where('annee', $annee)->lockForUpdate()->max('sequence') + 1;

        return [$annee, $sequence, sprintf('INV-%d-%03d', $annee, $sequence)];
    }
}
