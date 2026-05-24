<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guest extends Model
{
    protected $fillable = [
        'guest_name', 'id_number', 'phone', 'email', 'address', 'notes'
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}