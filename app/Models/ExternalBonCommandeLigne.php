<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalBonCommandeLigne extends Model
{
    protected $fillable = [
        'external_bon_commande_id', 'product_id', 'depot_id', 'bon_transfert_id', 'position',
        'reference', 'designation',
        'quantite_demandee', 'quantite_disponible', 'disponible',
        'prix_unitaire', 'note',
    ];

    protected $casts = [
        'quantite_demandee'   => 'decimal:2',
        'quantite_disponible' => 'decimal:2',
        'disponible'          => 'boolean',
        'prix_unitaire'       => 'decimal:2',
    ];

    public function externalBonCommande(): BelongsTo
    {
        return $this->belongsTo(ExternalBonCommande::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** BT qui a transféré la pièce ; vide = pièce en attente (V6). */
    public function bonTransfert(): BelongsTo
    {
        return $this->belongsTo(BonTransfert::class);
    }

    /** Demande de pièce spéciale créée par « Commander au fournisseur » (V6). */
    public function demandePiece(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(VehiclePartRequest::class)->latestOfMany();
    }

    /** Pièce prête à partir : identifiée, avec un dépôt, et disponible. */
    public function estPrete(): bool
    {
        return $this->disponible === true && $this->product_id !== null && $this->depot_id !== null;
    }

    /** Dépôt dans lequel la pièce sera prélevée pour la vente. */
    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    /**
     * La ligne a-t-elle été identifiée manuellement par le vendeur ?
     * (le garage n'a transmis aucune référence pour cette ligne).
     */
    public function estIdentificationManuelle(): bool
    {
        return is_null($this->reference);
    }

    /**
     * Référence à afficher : celle du garage si fournie, sinon celle de la
     * pièce choisie manuellement.
     */
    public function getReferenceAfficheeAttribute(): ?string
    {
        return $this->reference ?: $this->product?->reference;
    }
}
