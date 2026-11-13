<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Bon de livraison (BL-AAAA-NNNN) — V6. Facultatif, généré après la facture
 * pour tout ou partie des pièces facturées. Sans prix, sans effet sur le
 * stock. Annulé si la facture est annulée par un avoir.
 */
class BonLivraison extends Model
{
    protected $table = 'bons_livraison';

    protected $fillable = [
        'numero', 'annee', 'sequence', 'sale_id', 'user_id',
        'livre_le', 'recu_par', 'notes', 'annule_le',
    ];

    protected $casts = [
        'annee'     => 'integer',
        'sequence'  => 'integer',
        'livre_le'  => 'date',
        'annule_le' => 'datetime',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(BonLivraisonLigne::class);
    }

    /** Annulé à la main, ou facture annulée par un avoir. */
    public function estAnnule(): bool
    {
        return $this->annule_le !== null || $this->sale?->status === 'cancelled';
    }

    /** BL-AAAA-NNNN, numérotation propre aux BL, remise à 0001 chaque année. */
    public static function prochainNumero(): array
    {
        $annee = (int) now()->format('Y');
        $sequence = (int) static::query()->where('annee', $annee)->lockForUpdate()->max('sequence') + 1;

        return [$annee, $sequence, sprintf('BL-%d-%04d', $annee, $sequence)];
    }
}
