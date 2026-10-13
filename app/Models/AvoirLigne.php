<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Ligne d'un avoir : copie conforme d'une ligne de la facture annulée. */
class AvoirLigne extends Model
{
    protected $table = 'avoir_lignes';

    protected $fillable = [
        'avoir_id', 'product_id', 'depot_id', 'reference', 'designation',
        'quantite', 'prix_unitaire', 'total',
    ];

    protected $casts = [
        'quantite'      => 'decimal:2',
        'prix_unitaire' => 'decimal:2',
        'total'         => 'decimal:2',
    ];

    public function avoir(): BelongsTo
    {
        return $this->belongsTo(Avoir::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }
}
