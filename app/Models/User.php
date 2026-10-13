<?php

namespace App\Models;

use App\Services\PermissionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [

        'name',
        'email',
        'password',
        'role',
        'must_change_password',
        'is_active',

    ];

    protected $hidden = [

        'password',
        'remember_token',

    ];

    protected $casts = [

        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',

    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE HELPERS
    |--------------------------------------------------------------------------
    */

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isChefMagasinier()
    {
        return $this->role === 'chef_magasinier';
    }

    public function isMagasinier()
    {
        return $this->role === 'magasinier';
    }

    public function isVendeur()
    {
        return $this->role === 'vendeur';
    }

    public function isCaissier()
    {
        return $this->role === 'caissier';
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESS CHECK
    |--------------------------------------------------------------------------
    */

    public function hasRole($roles)
    {
        if (is_array($roles)) {

            return in_array($this->role, $roles);
        }

        return $this->role === $roles;
    }

    /*
    |--------------------------------------------------------------------------
    | PERMISSIONS (comme l'app Atelier)
    |--------------------------------------------------------------------------
    |
    | 1. L'administrateur a toujours tous les droits.
    | 2. Sinon, une permission ajoutée / retirée à la main pour cet
    |    utilisateur (écran « Permissions ») l'emporte.
    | 3. Sinon, on applique les permissions par défaut du rôle
    |    (App\Services\PermissionService::$roleDefaults).
    |
    */

    public function userPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $surcharge = $this->userPermissions->firstWhere('permission_key', $permission);

        if ($surcharge !== null) {
            return (bool) $surcharge->granted;
        }

        return in_array($permission, PermissionService::defaultsForRole($this->role), true);
    }

    public function getRoleLabel(): string
    {
        return match ($this->role) {
            'admin'           => 'Administrateur',
            'chef_magasinier' => 'Chef magasinier',
            'magasinier'      => 'Magasinier',
            'vendeur'         => 'Vendeur',
            'caissier'        => 'Caissier',
            default           => 'Utilisateur',
        };
    }
}
