<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiclePartRequestHistory extends Model
{
    use HasFactory;


    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'vehicle_part_request_id',

        'old_status',
        'new_status',

        'old_received_quantity',
        'new_received_quantity',

        'comment',

        'changed_by',
        'changed_at',
    ];


    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'old_received_quantity' =>
            'decimal:2',

        'new_received_quantity' =>
            'decimal:2',

        'changed_at' =>
            'datetime',
    ];


    /*
    |--------------------------------------------------------------------------
    | RELATION DEMANDE
    |--------------------------------------------------------------------------
    */

    public function request()
    {
        return $this->belongsTo(
            VehiclePartRequest::class,
            'vehicle_part_request_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UTILISATEUR
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'changed_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ANCIEN STATUT
    |--------------------------------------------------------------------------
    */

    public function getOldStatusLabelAttribute(): string
    {
        if (!$this->old_status) {

            return 'Création';
        }

        return
            VehiclePartRequest::statuses()[
                $this->old_status
            ]
            ??
            $this->old_status;
    }


    /*
    |--------------------------------------------------------------------------
    | NOUVEAU STATUT
    |--------------------------------------------------------------------------
    */

    public function getNewStatusLabelAttribute(): string
    {
        return
            VehiclePartRequest::statuses()[
                $this->new_status
            ]
            ??
            $this->new_status;
    }
}