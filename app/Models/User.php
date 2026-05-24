<?php

namespace App\Models;

// use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;  // Hapus HasApiTokens dulu

    protected $fillable = [
        'name', 'email', 'password', 'role',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

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
}