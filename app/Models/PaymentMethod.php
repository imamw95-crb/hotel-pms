<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['name', 'slug', 'source_type', 'is_active'];

    public function scopeBySourceType($query, $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }

    public function getDisplayNameAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->name));
    }
}
