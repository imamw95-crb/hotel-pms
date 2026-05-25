<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

    /**
     * Relasi dengan permissions
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /**
     * Relasi dengan users
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Check apakah role memiliki permission
     */
    public function hasPermission($permissionSlug)
    {
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    /**
     * Check apakah role memiliki semua permissions
     */
    public function hasAllPermissions($permissionSlugs)
    {
        $slugs = is_array($permissionSlugs) ? $permissionSlugs : [$permissionSlugs];
        foreach ($slugs as $slug) {
            if (!$this->hasPermission($slug)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check apakah role memiliki salah satu permission
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
