<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
Use App\Models\Permission;

class Role extends Model
{
    protected $fillable = ['name'];

    // 🔹 utilisateurs liés
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // 🔹 permissions du role
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }
}
