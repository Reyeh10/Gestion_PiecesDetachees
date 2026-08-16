<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepotTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'source_depot_id',
        'destination_depot_id',
        'quantity',
        'note',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];


    /**
     * ============================================================
     * PRODUIT
     * ============================================================
     */
    public function product()
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }


    /**
     * ============================================================
     * DÉPÔT SOURCE
     * ============================================================
     */
    public function sourceDepot()
    {
        return $this->belongsTo(
            Depot::class,
            'source_depot_id'
        );
    }


    /**
     * ============================================================
     * DÉPÔT DESTINATION
     * ============================================================
     */
    public function destinationDepot()
    {
        return $this->belongsTo(
            Depot::class,
            'destination_depot_id'
        );
    }


    /**
     * ============================================================
     * UTILISATEUR
     * ============================================================
     */
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
}