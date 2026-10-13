<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Avoir (facture d'avoir), comme dans l'app Atelier : une facture émise
 * n'est jamais supprimée. Pour l'annuler, on émet un avoir qui la reprend en
 * totalité (mêmes lignes, mêmes montants). Les montants sont stockés en
 * positif et affichés précédés d'un « - ».
 *
 * Numéro : AV-AAAA-NNNN, séquence propre aux avoirs, qui repart à 0001
 * chaque année.
 */
class Avoir extends Model
{
    protected $fillable = [
        'numero', 'annee', 'sequence', 'sale_id', 'customer_id', 'motif',
        'subtotal', 'discount', 'discount_amount', 'tva', 'total',
        'montant_deja_paye', 'montant_a_rembourser', 'rembourse_le', 'mode_remboursement',
        'cree_par',
    ];

    protected $casts = [
        'annee'                => 'integer',
        'sequence'             => 'integer',
        'subtotal'             => 'decimal:2',
        'discount'             => 'decimal:2',
        'discount_amount'      => 'decimal:2',
        'tva'                  => 'decimal:2',
        'total'                => 'decimal:2',
        'montant_deja_paye'    => 'decimal:2',
        'montant_a_rembourser' => 'decimal:2',
        'rembourse_le'         => 'date',
    ];

    public const MODES_REMBOURSEMENT = [
        'especes'  => 'Espèces',
        'cheque'   => 'Chèque',
        'waafi'    => 'Waafi',
        'virement' => 'Virement',
        'deduit'   => 'Déduit d\'une nouvelle facture',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(AvoirLigne::class);
    }

    /** Un montant reste à rendre au client tant que le remboursement n'est pas enregistré. */
    public function resteARembourser(): bool
    {
        return (float) $this->montant_a_rembourser > 0 && ! $this->rembourse_le;
    }

    public function getModeRemboursementLabel(): ?string
    {
        return $this->mode_remboursement
            ? (self::MODES_REMBOURSEMENT[$this->mode_remboursement] ?? $this->mode_remboursement)
            : null;
    }

    /**
     * Prochain numéro AV-AAAA-NNNN. À appeler dans une transaction : la
     * ligne du dernier avoir de l'année est verrouillée.
     *
     * @return array{0:int,1:int,2:string} [année, séquence, numéro]
     */
    public static function prochainNumero(): array
    {
        $annee = (int) now()->format('Y');

        $dernier = self::query()
            ->where('annee', $annee)
            ->orderByDesc('sequence')
            ->lockForUpdate()
            ->first();

        $sequence = $dernier ? $dernier->sequence + 1 : 1;

        return [$annee, $sequence, sprintf('AV-%d-%04d', $annee, $sequence)];
    }
}
