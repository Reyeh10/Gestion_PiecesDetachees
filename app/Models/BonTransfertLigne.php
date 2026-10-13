<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonTransfertLigne extends Model
{
    protected $table = 'bon_transfert_lignes';

    protected $fillable = [
        'bon_transfert_id',
        'position',
        'designation_garage',
        'product_id',
        'depot_id',
        'quantite',
        'prix_unitaire',
        'total',
    ];

    protected $casts = [
        'quantite'      => 'decimal:2',
        'prix_unitaire' => 'decimal:2',
        'total'         => 'decimal:2',
    ];

    public function bonTransfert(): BelongsTo
    {
        return $this->belongsTo(BonTransfert::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    /**
     * Stock de la pièce dans le dépôt de prélèvement : c'est lui qui porte le
     * rayon et l'emplacement propres à ce dépôt. Charger
     * `lignes.product.depotStocks.rayon` / `.location` pour éviter une requête
     * par ligne.
     */
    public function stockDepot(): ?ProductDepotStock
    {
        return $this->product?->depotStocks->firstWhere('depot_id', $this->depot_id);
    }

    public function getRayonNomAttribute(): ?string
    {
        return $this->stockDepot()?->rayon?->name;
    }

    public function getEmplacementNomAttribute(): ?string
    {
        return $this->stockDepot()?->location?->name;
    }
}
