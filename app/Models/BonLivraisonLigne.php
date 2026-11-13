<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Pièce livrée par un bon de livraison (V6). Sans prix. */
class BonLivraisonLigne extends Model
{
    protected $table = 'bon_livraison_lignes';

    protected $fillable = [
        'bon_livraison_id', 'sale_item_id', 'product_id',
        'reference', 'designation', 'unite', 'quantite',
    ];

    protected $casts = [
        'quantite' => 'decimal:2',
    ];

    public function bonLivraison(): BelongsTo
    {
        return $this->belongsTo(BonLivraison::class);
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
