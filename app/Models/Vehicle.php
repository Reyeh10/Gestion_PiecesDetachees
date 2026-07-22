<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * Champs autorisés pour l'enregistrement.
     */
    protected $fillable = [
        'customer_id',
        'plate_number',
        'vin',
        'brand',
        'model',
        'year',
        'color',
        'notes',
    ];

    /**
     * Conversion automatique des types.
     */
    protected $casts = [
        'customer_id' => 'integer',
        'year' => 'integer',
    ];

    /**
     * Client propriétaire du véhicule.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class,
            'customer_id'
        );
    }

    /**
     * Lignes de vente liées au véhicule.
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(
            SaleItem::class,
            'vehicle_id'
        );
    }

    /**
     * Nom complet utilisé dans les listes.
     */
    public function getDisplayNameAttribute(): string
    {
        $details = array_filter([
            $this->plate_number,
            $this->brand,
            $this->model,
        ]);

        return implode(
            ' - ',
            $details
        );
    }

    /**
     * Normaliser automatiquement l'immatriculation.
     */
    public function setPlateNumberAttribute(
        mixed $value
    ): void {
        $this->attributes['plate_number'] =
            strtoupper(
                preg_replace(
                    '/[^A-Z0-9]/',
                    '',
                    trim((string) $value)
                ) ?? ''
            );
    }

    /**
     * Normaliser automatiquement le VIN.
     */
    public function setVinAttribute(
        mixed $value
    ): void {
        if (
            $value === null ||
            trim((string) $value) === ''
        ) {
            $this->attributes['vin'] = null;

            return;
        }

        $this->attributes['vin'] =
            strtoupper(
                preg_replace(
                    '/\s+/',
                    '',
                    trim((string) $value)
                ) ?? ''
            );
    }

    /**
     * Normaliser la marque.
     */
    public function setBrandAttribute(
        mixed $value
    ): void {
        $this->attributes['brand'] =
            $value !== null &&
            trim((string) $value) !== ''
                ? trim((string) $value)
                : null;
    }

    /**
     * Normaliser le modèle.
     */
    public function setModelAttribute(
        mixed $value
    ): void {
        $this->attributes['model'] =
            $value !== null &&
            trim((string) $value) !== ''
                ? trim((string) $value)
                : null;
    }

    /**
     * Normaliser la couleur.
     */
    public function setColorAttribute(
        mixed $value
    ): void {
        $this->attributes['color'] =
            $value !== null &&
            trim((string) $value) !== ''
                ? trim((string) $value)
                : null;
    }
}
