<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiclePartRequest extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | STATUTS
    |--------------------------------------------------------------------------
    */

    public const STATUS_SEARCHING = 'searching';

    public const STATUS_FOUND = 'found';

    public const STATUS_ORDERED = 'ordered';

    /*
    |--------------------------------------------------------------------------
    | RÉCEPTION PARTIELLE
    |--------------------------------------------------------------------------
    */

    public const STATUS_PARTIAL_RECEIVED = 'partial_received';

    /*
    |--------------------------------------------------------------------------
    | RÉCEPTION COMPLÈTE
    |--------------------------------------------------------------------------
    */

    public const STATUS_RECEIVED = 'received';

    public const STATUS_NOT_FOUND = 'not_found';

    public const STATUS_CANCELLED = 'cancelled';


    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'vehicle_id',
        'product_id',
        'supplier_id',
        'created_by',

        'reference',
        'part_name',
        'description',

        /*
        |--------------------------------------------------------------------------
        | QUANTITÉS
        |--------------------------------------------------------------------------
        |
        | quantity
        | = quantité demandée / commandée
        |
        | received_quantity
        | = quantité réellement reçue
        |
        */

        'quantity',
        'received_quantity',

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


    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'quantity' =>
            'decimal:2',

        'received_quantity' =>
            'decimal:2',

        'estimated_price' =>
            'decimal:2',

        'purchase_price' =>
            'decimal:2',

        'requested_at' =>
            'datetime',

        'search_started_at' =>
            'datetime',

        'found_at' =>
            'datetime',

        'ordered_at' =>
            'datetime',

        'received_at' =>
            'datetime',

        'not_found_at' =>
            'datetime',

        'cancelled_at' =>
            'datetime',
    ];


    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function vehicle()
    {
        return $this->belongsTo(
            Vehicle::class
        );
    }


    public function product()
    {
        return $this->belongsTo(
            Product::class
        );
    }


    public function supplier()
    {
        return $this->belongsTo(
            Supplier::class
        );
    }


    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    public function histories()
    {
        return $this->hasMany(
            VehiclePartRequestHistory::class
        )
        ->orderByDesc('changed_at');
    }


    /*
    |--------------------------------------------------------------------------
    | STATUTS DISPONIBLES
    |--------------------------------------------------------------------------
    */

    public static function statuses(): array
    {
        return [

            self::STATUS_SEARCHING =>
                'En recherche',

            self::STATUS_ORDERED =>
                'Commandée',

            self::STATUS_PARTIAL_RECEIVED =>
                'Réception partielle',

            self::STATUS_RECEIVED =>
                'Reçue',

            self::STATUS_NOT_FOUND =>
                'Non trouvée',

            self::STATUS_CANCELLED =>
                'Annulée',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | LIBELLÉ STATUT
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status]
            ?? $this->status;
    }


    /*
    |--------------------------------------------------------------------------
    | BADGE BOOTSTRAP
    |--------------------------------------------------------------------------
    */

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {

            self::STATUS_SEARCHING =>
                'warning',

            self::STATUS_ORDERED =>
                'primary',

            self::STATUS_PARTIAL_RECEIVED =>
                'info',

            self::STATUS_RECEIVED =>
                'success',

            self::STATUS_NOT_FOUND =>
                'danger',

            self::STATUS_CANCELLED =>
                'dark',

            default =>
                'secondary',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | QUANTITÉ RESTANTE
    |--------------------------------------------------------------------------
    */

    public function getRemainingQuantityAttribute(): float
    {
        $ordered =
            (float) $this->quantity;

        $received =
            (float) $this->received_quantity;

        return max(
            0,
            $ordered - $received
        );
    }


    /*
    |--------------------------------------------------------------------------
    | POURCENTAGE REÇU
    |--------------------------------------------------------------------------
    */

    public function getReceivedPercentageAttribute(): float
    {
        $ordered =
            (float) $this->quantity;

        if ($ordered <= 0) {
            return 0;
        }

        return min(
            100,
            (
                (float) $this->received_quantity
                /
                $ordered
            ) * 100
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RÉCEPTION PARTIELLE ?
    |--------------------------------------------------------------------------
    */

    public function getIsPartialReceivedAttribute(): bool
    {
        $received =
            (float) $this->received_quantity;

        $ordered =
            (float) $this->quantity;

        return
            $received > 0
            &&
            $received < $ordered;
    }


    /*
    |--------------------------------------------------------------------------
    | RÉCEPTION COMPLÈTE ?
    |--------------------------------------------------------------------------
    */

    public function getIsFullyReceivedAttribute(): bool
    {
        return
            (float) $this->quantity > 0
            &&
            (float) $this->received_quantity
            >=
            (float) $this->quantity;
    }


    /*
    |--------------------------------------------------------------------------
    | TRANSITIONS AUTORISÉES
    |--------------------------------------------------------------------------
    */

    public function availableNextStatuses(): array
    {
        return match ($this->status) {

            self::STATUS_SEARCHING => [

                self::STATUS_ORDERED,

                self::STATUS_NOT_FOUND,

                self::STATUS_CANCELLED,
            ],

            /*
            |--------------------------------------------------------------------------
            | COMMANDÉE
            |--------------------------------------------------------------------------
            */

            self::STATUS_ORDERED => [

                self::STATUS_PARTIAL_RECEIVED,

                self::STATUS_RECEIVED,

                self::STATUS_CANCELLED,
            ],

            /*
            |--------------------------------------------------------------------------
            | RÉCEPTION PARTIELLE
            |--------------------------------------------------------------------------
            |
            | Une nouvelle livraison peut encore arriver.
            |
            */

            self::STATUS_PARTIAL_RECEIVED => [

                self::STATUS_PARTIAL_RECEIVED,

                self::STATUS_RECEIVED,

                self::STATUS_CANCELLED,
            ],

            self::STATUS_NOT_FOUND => [

                self::STATUS_SEARCHING,

                self::STATUS_CANCELLED,
            ],

            /*
            |--------------------------------------------------------------------------
            | PROCESSUS TERMINÉ
            |--------------------------------------------------------------------------
            */

            self::STATUS_RECEIVED,
            self::STATUS_CANCELLED => [],

            default => [],
        };
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATION TRANSITION
    |--------------------------------------------------------------------------
    */

    public function canChangeTo(
        string $newStatus
    ): bool {

        return in_array(
            $newStatus,
            $this->availableNextStatuses(),
            true
        );
    }
}