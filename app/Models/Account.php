<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    public const TYPES = [
        'asset' => 'Aset',
        'liability' => 'Liabilitas',
        'equity' => 'Ekuitas',
        'revenue' => 'Pendapatan',
        'expense' => 'Beban',
    ];

    public const BALANCE_TYPES = ['asset', 'expense'];

    protected $fillable = [
        'code', 'name', 'type', 'group_label', 'normal_balance',
        'is_cash', 'is_active', 'description', 'sort_order',
    ];

    protected $casts = [
        'is_cash' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCash($query)
    {
        return $query->where('is_cash', true);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getIsRevenueAttribute(): bool
    {
        return $this->type === 'revenue';
    }

    public function getIsExpenseAttribute(): bool
    {
        return $this->type === 'expense';
    }

    public static function byCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }
}
