<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        //'address',
        //'type',
        'credit_limit',
        'payment_terms',
        // V6 : le client est une assurance → ventes / proformas majorés
        'est_assurance',
        'taux_majoration',
    ];

    protected $casts = [
        'est_assurance'   => 'boolean',
        'taux_majoration' => 'decimal:2',
    ];

    /**
     * Taux de majoration des ventes / proformas faits à ce client : le taux
     * propre de l'assurance, sinon celui des paramètres (35 %) ; 0 si le
     * client n'est pas une assurance.
     */
    public function tauxMajoration(): float
    {
        if (! $this->est_assurance) {
            return 0.0;
        }

        return (float) ($this->taux_majoration ?? config('magasin.taux_majoration_assurance', 35));
    }

    /** Prix final d'une ligne : prix de base majoré, arrondi au franc (comme l'Atelier) */
    public static function prixMajore(float $prixBase, float $taux): float
    {
        return $taux > 0 ? round($prixBase * (1 + $taux / 100)) : round($prixBase, 2);
    }

    public function sales()
    {
        return $this->hasMany(
            Sale::class,
            'customer_id'
        );
    }

    /**
     * Véhicules appartenant au client.
     */
    public function vehicles()
    {
        return $this->hasMany(
            Vehicle::class,
            'customer_id'
        );
    }
}
