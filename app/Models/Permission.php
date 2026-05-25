<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Role;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'group'];

    /**
     * Relasi dengan roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    /**
     * Relasi dengan users (melalui roles)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permission');
    }
}
