<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

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

    protected $casts = [
        'customer_id' => 'integer',
        'year' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class,
            'customer_id',
            'id'
        );
    }

    /**
     * Anciennes lignes de vente.
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(
            SaleItem::class,
            'vehicle_id',
            'id'
        );
    }

    /**
     * Nouvelles ventes liées directement au véhicule.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(
            Sale::class,
            'vehicle_id',
            'id'
        );
    }

    public function getDisplayNameAttribute(): string
    {
        $details = array_filter([
            $this->plate_number,
            $this->brand,
            $this->model,
        ]);

        return implode(' - ', $details);
    }

    public function setPlateNumberAttribute(mixed $value): void
    {
        $this->attributes['plate_number'] =
            strtoupper(
                preg_replace(
                    '/[^A-Z0-9]/',
                    '',
                    trim((string) $value)
                ) ?? ''
            );
    }

    public function setVinAttribute(mixed $value): void
    {
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

    public function setBrandAttribute(mixed $value): void
    {
        $this->attributes['brand'] =
            $value !== null &&
            trim((string) $value) !== ''
                ? trim((string) $value)
                : null;
    }

    public function setModelAttribute(mixed $value): void
    {
        $this->attributes['model'] =
            $value !== null &&
            trim((string) $value) !== ''
                ? trim((string) $value)
                : null;
    }

    public function setColorAttribute(mixed $value): void
    {
        $this->attributes['color'] =
            $value !== null &&
            trim((string) $value) !== ''
                ? trim((string) $value)
                : null;
    }
}
