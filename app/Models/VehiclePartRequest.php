<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiclePartRequest extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Constantes des statuts
    |--------------------------------------------------------------------------
    */

    //public const STATUS_PENDING = 'pending';
    public const STATUS_SEARCHING = 'searching';
    public const STATUS_FOUND = 'found';
    public const STATUS_ORDERED = 'ordered';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_NOT_FOUND = 'not_found';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'vehicle_id',
        'product_id',
        'supplier_id',
        'created_by',

        'reference',
        'part_name',
        'description',
        'quantity',
        'unit',

        'status',

        'supplier_reference',
        'order_reference',
        'estimated_price',
        'purchase_price',

        'requested_at',
        'search_started_at',
        'found_at',
        'ordered_at',
        'received_at',
        'not_found_at',
        'cancelled_at',

        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'estimated_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',

        'requested_at' => 'datetime',
        'search_started_at' => 'datetime',
        'found_at' => 'datetime',
        'ordered_at' => 'datetime',
        'received_at' => 'datetime',
        'not_found_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function histories()
    {
        return $this->hasMany(VehiclePartRequestHistory::class)
            ->orderByDesc('changed_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Libellés des statuts
    |--------------------------------------------------------------------------
    */

   public static function statuses(): array
    {
        return [
            self::STATUS_SEARCHING => 'En recherche',
            self::STATUS_ORDERED => 'Commandée',
            self::STATUS_RECEIVED => 'Reçue',
            self::STATUS_NOT_FOUND => 'Non trouvée',
            self::STATUS_CANCELLED => 'Annulée',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    /*
    |--------------------------------------------------------------------------
    | Couleur Bootstrap du statut
    |--------------------------------------------------------------------------
    */

  public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SEARCHING => 'warning',
            self::STATUS_ORDERED => 'primary',
            self::STATUS_RECEIVED => 'success',
            self::STATUS_NOT_FOUND => 'danger',
            self::STATUS_CANCELLED => 'dark',
            default => 'secondary',
        };
    }
    /*
    |--------------------------------------------------------------------------
    | Vérification des transitions autorisées
    |--------------------------------------------------------------------------
    */

   public function availableNextStatuses(): array
    {
        return match ($this->status) {

            /*
            * Une nouvelle demande commence en recherche.
            * Elle peut ensuite être commandée ou déclarée non trouvée.
            */
            self::STATUS_SEARCHING => [
                self::STATUS_ORDERED,
                self::STATUS_NOT_FOUND,
                self::STATUS_CANCELLED,
            ],

            /*
            * Une pièce commandée peut ensuite être reçue.
            */
            self::STATUS_ORDERED => [
                self::STATUS_RECEIVED,
                self::STATUS_CANCELLED,
            ],

            /*
            * Une pièce non trouvée peut être remise en recherche.
            */
            self::STATUS_NOT_FOUND => [
                self::STATUS_SEARCHING,
                self::STATUS_CANCELLED,
            ],

            /*
            * Une pièce reçue ou annulée termine le processus.
            */
            self::STATUS_RECEIVED,
            self::STATUS_CANCELLED => [],

            default => [],
        };
    }

    public function canChangeTo(string $newStatus): bool
    {
        return in_array(
            $newStatus,
            $this->availableNextStatuses(),
            true
        );
    }
}
