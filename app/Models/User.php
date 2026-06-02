<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'role',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relasi ke permissions (direct permissions)
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permission');
    }

    // ========== TAMBAHKAN METHOD INI ==========
    public function isOwner()
    {
        return $this->role === 'owner';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isFrontOffice()
    {
        return $this->role === 'frontoffice';
    }

    public function isHousekeeping()
    {
        return $this->role === 'housekeeping';
    }

    public function isUserManager()
    {
        return $this->role === 'user_manager';
    }

    /**
     * Check apakah user memiliki permission berdasarkan role
     */
    public function hasPermission($permissionSlug)
    {
        // Owner selalu punya semua permission
        if ($this->isOwner()) {
            return true;
        }

        // Check permission berdasarkan role
        $rolePermissions = Permission::whereIn('slug', [$permissionSlug])
            ->get()
            ->pluck('id')
            ->toArray();

        if (empty($rolePermissions)) {
            return false;
        }

        // Check di user_permission table (direct permission)
        $userHasPermission = $this->permissions()
            ->whereIn('permission_id', $rolePermissions)
            ->exists();

        if ($userHasPermission) {
            return true;
        }

        // Check di role_permission table (via role string column)
        $roleHasPermission = \DB::table('role_permission')
            ->whereIn('permission_id', $rolePermissions)
            ->where('role', $this->role)
            ->exists();

        return $roleHasPermission;
    }

    /**
     * Check apakah user memiliki semua permissions
     */
    public function hasAllPermissions($permissionSlugs)
    {
        $slugs = is_array($permissionSlugs) ? $permissionSlugs : [$permissionSlugs];
        foreach ($slugs as $slug) {
            if (! $this->hasPermission($slug)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check apakah user memiliki salah satu permission
     */
    public function hasAnyPermission($permissionSlugs)
    {
        $slugs = is_array($permissionSlugs) ? $permissionSlugs : [$permissionSlugs];
        foreach ($slugs as $slug) {
            if ($this->hasPermission($slug)) {
                return true;
            }
        }

        return false;
    }
}
