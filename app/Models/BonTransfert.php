<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Bon de transfert (BT-AAAA-NNNN) : pièces sorties du stock du magasin et
 * transférées au garage pour un bon de commande (BC). Sans TVA ni paiement.
 */
class BonTransfert extends Model
{
    protected $table = 'bons_transfert';

    protected $fillable = [
        'numero',
        'annee',
        'sequence',
        'external_bon_commande_id',
        'user_id',
        'total',
        'envoye_atelier_at',
        'envoi_atelier_erreur',
    ];

    protected $casts = [
        'annee'    => 'integer',
        'sequence' => 'integer',
        'total'    => 'decimal:2',
        'envoye_atelier_at' => 'datetime',
    ];

    public function lignes(): HasMany
    {
        return $this->hasMany(BonTransfertLigne::class);
    }

    public function bonCommande(): BelongsTo
    {
        return $this->belongsTo(ExternalBonCommande::class, 'external_bon_commande_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
