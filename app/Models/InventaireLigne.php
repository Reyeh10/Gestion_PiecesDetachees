<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Pièce d'une fiche d'inventaire : attendue, comptée, corrections (V6). */
class InventaireLigne extends Model
{
    protected $fillable = [
        'inventaire_id', 'ordre', 'product_id', 'rayon_id', 'location_id',
        'reference', 'designation', 'qte_attendue', 'qte_comptee',
        'nouveau_rayon_id', 'nouvel_emplacement_id', 'reference_signalee',
        'remarque', 'hors_liste', 'inventory_adjustment_id',
    ];

    protected $casts = [
        'qte_attendue' => 'decimal:2',
        'qte_comptee'  => 'decimal:2',
        'hors_liste'   => 'boolean',
    ];

    public function inventaire(): BelongsTo
    {
        return $this->belongsTo(Inventaire::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function rayon(): BelongsTo
    {
        return $this->belongsTo(Rayon::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function nouveauRayon(): BelongsTo
    {
        return $this->belongsTo(Rayon::class, 'nouveau_rayon_id');
    }

    public function nouvelEmplacement(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'nouvel_emplacement_id');
    }

    /** Ajustement créé à la validation de la fiche (quantité et / ou place). */
    public function ajustement(): BelongsTo
    {
        return $this->belongsTo(InventoryAdjustment::class, 'inventory_adjustment_id');
    }

    public function estComptee(): bool
    {
        return $this->qte_comptee !== null;
    }

    /** Écart compté - attendu (null si pas encore comptée). */
    public function ecart(): ?float
    {
        return $this->qte_comptee === null ? null : round((float) $this->qte_comptee - (float) $this->qte_attendue, 2);
    }

    public function changeDePlace(): bool
    {
        return ($this->nouveau_rayon_id && $this->nouveau_rayon_id != $this->rayon_id)
            || ($this->nouvel_emplacement_id && $this->nouvel_emplacement_id != $this->location_id);
    }
}
