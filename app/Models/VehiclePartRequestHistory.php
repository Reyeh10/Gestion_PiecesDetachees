<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiclePartRequestHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_part_request_id',
        'old_status',
        'new_status',
        'comment',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(
            VehiclePartRequest::class,
            'vehicle_part_request_id'
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getOldStatusLabelAttribute(): string
    {
        if (!$this->old_status) {
            return 'Création';
        }

        return VehiclePartRequest::statuses()[$this->old_status]
            ?? $this->old_status;
    }

    public function getNewStatusLabelAttribute(): string
    {
        return VehiclePartRequest::statuses()[$this->new_status]
            ?? $this->new_status;
    }
}
