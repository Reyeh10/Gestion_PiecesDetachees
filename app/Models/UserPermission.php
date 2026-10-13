<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Permission ajoutée (granted = true) ou retirée (granted = false) à la main
 * pour un utilisateur, par rapport aux permissions par défaut de son rôle.
 */
class UserPermission extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'permission_key', 'granted'];

    protected $casts = ['granted' => 'boolean'];
}
