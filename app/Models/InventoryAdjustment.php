<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Ajustement d'inventaire d'UN produit dans UN dépôt.
 *
 * V6 : saisi « en attente » (écart enregistré), il ne touche le stock du dépôt
 * qu'à la validation du chef magasinier / de l'admin. Il ne se modifie ni ne se
 * supprime : une erreur se corrige par un contre-ajustement (corrige_id).
 * Voir App\Services\AjustementStockService.
 */
class InventoryAdjustment extends Model
{
    public const EN_ATTENTE = 'en_attente';
    public const VALIDE = 'valide';
    public const REFUSE = 'refuse';
    public const ANNULE = 'annule';

    public const STATUTS = [
        self::EN_ATTENTE => 'À valider',
        self::VALIDE     => 'Validé',
        self::REFUSE     => 'Refusé',
        self::ANNULE     => 'Annulé',
    ];

    public const MOTIFS = [
        'inventaire'         => 'Inventaire',
        'casse'              => 'Casse',
        'perte'              => 'Perte',
        'vol'                => 'Vol',
        'erreur_saisie'      => 'Erreur de saisie',
        'retour_fournisseur' => 'Retour fournisseur',
        'autre'              => 'Autre',
    ];

    public const SOURCES = [
        'manuel'     => 'Saisie',
        'import'     => 'Import Excel',
        'inventaire' => 'Fiche d\'inventaire',
        'correction' => 'Correction',
    ];

    protected $fillable = [
        'statut',
        'source',
        'product_id',
        'depot_id',
        'rayon_id',
        'location_id',
        'nouveau_rayon_id',
        'nouvel_emplacement_id',
        'old_qty',
        'new_qty',
        'motif',
        'ecart',
        'cump',
        'valeur_ecart',
        'reason',
        'created_by',
        'approved_by',
        'validated_at',
        'refuse_par',
        'refuse_le',
        'motif_refus',
        'corrige_id',
    ];

    protected $casts = [
        'old_qty'      => 'decimal:2',
        'new_qty'      => 'decimal:2',
        'ecart'        => 'decimal:2',
        'cump'         => 'decimal:4',
        'valeur_ecart' => 'decimal:2',
        'validated_at' => 'datetime',
        'refuse_le'    => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function rayon(): BelongsTo
    {
        return $this->belongsTo(Rayon::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /** Nouvelle place demandée (appliquée à la validation). */
    public function nouveauRayon(): BelongsTo
    {
        return $this->belongsTo(Rayon::class, 'nouveau_rayon_id');
    }

    public function nouvelEmplacement(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'nouvel_emplacement_id');
    }

    /** L'ajustement change-t-il la place de la pièce dans le dépôt ? */
    public function changeDePlace(): bool
    {
        return ($this->nouveau_rayon_id && (int) $this->nouveau_rayon_id !== (int) $this->rayon_id)
            || ($this->nouvel_emplacement_id && (int) $this->nouvel_emplacement_id !== (int) $this->location_id);
    }

    /** Qui a validé (avant V6 : qui a saisi, c'était la même personne). */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function refuseur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refuse_par');
    }

    /** L'ajustement que celui-ci corrige. */
    public function corrige(): BelongsTo
    {
        return $this->belongsTo(self::class, 'corrige_id');
    }

    /** Les contre-ajustements faits sur celui-ci. */
    public function corrections(): HasMany
    {
        return $this->hasMany(self::class, 'corrige_id');
    }

    public function estEnAttente(): bool
    {
        return $this->statut === self::EN_ATTENTE;
    }

    public function estValide(): bool
    {
        return $this->statut === self::VALIDE;
    }

    public function libelleStatut(): string
    {
        return self::STATUTS[$this->statut] ?? (string) $this->statut;
    }

    public function couleurStatut(): string
    {
        return match ($this->statut) {
            self::EN_ATTENTE => 'warning',
            self::VALIDE     => 'success',
            self::REFUSE     => 'danger',
            default          => 'secondary',
        };
    }

    public function libelleMotif(): string
    {
        return self::MOTIFS[$this->motif] ?? '—';
    }

    public function libelleSource(): string
    {
        return self::SOURCES[$this->source] ?? '—';
    }

    /** Écart enregistré (anciens ajustements : nouvelle − ancienne quantité). */
    public function ecartQuantite(): float
    {
        return $this->ecart !== null
            ? (float) $this->ecart
            : round((float) $this->new_qty - (float) $this->old_qty, 2);
    }
}
