<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | INFORMATIONS PRODUIT
        |--------------------------------------------------------------------------
        */

        'reference',
        'designation',

        /*
        |--------------------------------------------------------------------------
        | TYPE UNITÉ
        |--------------------------------------------------------------------------
        */

        'unit_type',
        'unit_label',

        /*
        |--------------------------------------------------------------------------
        | RELATIONS
        |--------------------------------------------------------------------------
        */

        'brand_id',
        'model_id',

        'family_id',
        'subfamily_id',

        'rayon_id',
        'location_id',

        /*
        |--------------------------------------------------------------------------
        | STOCK
        |--------------------------------------------------------------------------
        */

        'quantity',
        'received_quantity',

        // Quantité initialement entrée dans le stock
        'initial_quantity',

        'min_stock',
        'max_stock',

        /*
        |--------------------------------------------------------------------------
        | PRIX
        |--------------------------------------------------------------------------
        */

        'purchase_price',
        'coef_purchase',
        'cost_price',
        'coef_sale',
        'sale_price',

        /*
        |--------------------------------------------------------------------------
        | STATUT
        |--------------------------------------------------------------------------
        */

        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        /*
        |--------------------------------------------------------------------------
        | STOCK
        |--------------------------------------------------------------------------
        */

        'quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'initial_quantity' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'max_stock' => 'decimal:2',

        /*
        |--------------------------------------------------------------------------
        | PRIX
        |--------------------------------------------------------------------------
        */

        'purchase_price' => 'decimal:2',

        'coef_purchase' => 'decimal:2',

        'cost_price' => 'decimal:2',

        'coef_sale' => 'decimal:2',

        'sale_price' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Produit -> Marque
    |--------------------------------------------------------------------------
    */

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Produit -> Modèle
    |--------------------------------------------------------------------------
    */

    public function model()
    {
        return $this->belongsTo(
            CarModel::class,
            'model_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Produit -> Famille
    |--------------------------------------------------------------------------
    */

    public function family()
    {
        return $this->belongsTo(
            FamilyModel::class,
            'family_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Produit -> Sous-famille
    |--------------------------------------------------------------------------
    */

    public function subfamily()
    {
        return $this->belongsTo(
            Subfamily::class,
            'subfamily_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Produit -> Rayon
    |--------------------------------------------------------------------------
    */

    public function rayon()
    {
        return $this->belongsTo(
            Rayon::class,
            'rayon_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Produit -> Emplacement
    |--------------------------------------------------------------------------
    */

    public function location()
    {
        return $this->belongsTo(
            Location::class,
            'location_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Produit -> Catégorie
    |--------------------------------------------------------------------------
    */

    public function category()
    {
        return $this->belongsTo(
            Category::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Produit -> Mouvements de stock
    |--------------------------------------------------------------------------
    */

    public function stockMovements()
    {
        return $this->hasMany(
            StockMovement::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Produit -> Lignes d'achat
    |--------------------------------------------------------------------------
    */

    public function purchaseItems()
    {
        return $this->hasMany(
            PurchaseItem::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Produit -> Lignes de vente
    |--------------------------------------------------------------------------
    */

    public function saleItems()
    {
        return $this->hasMany(
            SaleItem::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Produit -> Ajustements inventaire
    |--------------------------------------------------------------------------
    */

    public function inventoryAdjustments()
    {
        return $this->hasMany(
            InventoryAdjustment::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Produit -> Fournisseurs
    |--------------------------------------------------------------------------
    */

    public function suppliers()
    {
        return $this->belongsToMany(
            Supplier::class
        )
        ->withPivot([
            'supplier_reference',
            'purchase_price',
            'delivery_delay',
            'is_primary',
            'active',
        ])
        ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Produit -> Stocks par dépôt
    |--------------------------------------------------------------------------
    */

    public function depotStocks()
    {
        return $this->hasMany(
            ProductDepotStock::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Produit -> Dépôts
    |--------------------------------------------------------------------------
    */

    public function depots()
    {
        return $this->belongsToMany(
            Depot::class,
            'product_depot_stocks'
        )
        ->withPivot('quantity')
        ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | STOCK TOTAL TOUS DÉPÔTS
    |--------------------------------------------------------------------------
    */

    public function getTotalStockAttribute()
    {
        return $this
            ->depotStocks()
            ->sum('quantity');
    }

    /*
    |--------------------------------------------------------------------------
    | Produit -> Demandes de pièces véhicule
    |--------------------------------------------------------------------------
    */

    public function vehiclePartRequests()
    {
        return $this->hasMany(
            VehiclePartRequest::class
        );
    }

     /*
    |--------------------------------------------------------------------------
    | Produit -> quantité initial
    |--------------------------------------------------------------------------
    */

    public function getUnavailableQuantityAttribute(): float
    {
        $initial = (float) ($this->initial_quantity ?? 0);
        $received = (float) ($this->received_quantity ?? 0);

        return max(0, $initial - $received);
    }

    public function getAvailabilityStatusAttribute(): string
    {
        $initial = (float) ($this->initial_quantity ?? 0);
        $received = (float) ($this->received_quantity ?? 0);

        if ($initial <= 0) {
            return 'non_defini';
        }

        if ($received <= 0) {
            return 'a_commander';
        }

        if ($received < $initial) {
            return 'partiellement_recu';
        }

        return 'recu';
    }
}
