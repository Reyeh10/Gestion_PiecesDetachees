<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    |
    | Une ligne de vente peut maintenant représenter :
    |
    | 1. Un produit existant dans le catalogue :
    |    - product_id renseigné
    |    - depot_id renseigné
    |
    | 2. Un produit hors catalogue :
    |    - product_id = NULL
    |    - depot_id = NULL
    |    - référence / désignation / description saisies manuellement
    |
    */

    protected $fillable = [
        'sale_id',
        'product_id',
        'vehicle_id',
        'depot_id',

        /*
        |--------------------------------------------------------------------------
        | PRODUIT HORS CATALOGUE
        |--------------------------------------------------------------------------
        |
        | Ces champs sont utilisés lorsque product_id est NULL.
        |
        | Le produit hors catalogue n'est pas automatiquement créé
        | dans la table products et ne génère aucun mouvement de stock.
        |
        */

        'reference_libre',
        'designation_libre',
        'description_libre',

        'quantity',
        'price',
        'total',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'sale_id' => 'integer',
        'product_id' => 'integer',
        'vehicle_id' => 'integer',
        'depot_id' => 'integer',
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION : VENTE
    |--------------------------------------------------------------------------
    */

    public function sale(): BelongsTo
    {
        return $this->belongsTo(
            Sale::class,
            'sale_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATION : PRODUIT
    |--------------------------------------------------------------------------
    |
    | Pour un produit hors catalogue, product_id est NULL.
    | Dans ce cas, cette relation retourne naturellement NULL.
    |
    */

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATION : VÉHICULE
    |--------------------------------------------------------------------------
    */

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(
            Vehicle::class,
            'vehicle_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATION : DÉPÔT
    |--------------------------------------------------------------------------
    |
    | Dépôt dans lequel le stock a été prélevé au moment de la vente.
    |
    | Pour un produit du catalogue, cette information permet notamment
    | de remettre le stock dans le bon dépôt lors d'une annulation
    | ou suppression de vente.
    |
    | Pour un produit hors catalogue, depot_id reste NULL puisqu'aucun
    | stock n'est prélevé.
    |
    */

    public function depot(): BelongsTo
    {
        return $this->belongsTo(
            Depot::class,
            'depot_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL DE LA LIGNE
    |--------------------------------------------------------------------------
    */

    public function getLineTotalAttribute(): float
    {
        return round(
            (float) $this->quantity
            *
            (float) $this->price,
            2
        );
    }
}
