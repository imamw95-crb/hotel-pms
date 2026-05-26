<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['name', 'slug', 'is_active'];

    public function getDisplayNameAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->name));
    }
}
