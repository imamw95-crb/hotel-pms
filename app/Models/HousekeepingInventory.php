<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousekeepingInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'quantity',
        'min_quantity',
        'unit',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_quantity' => 'integer',
    ];

    /**
     * Scope: items with stock below minimum threshold.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'min_quantity');
    }

    /**
     * Accessor: apakah stok menipis.
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity <= $this->min_quantity;
    }

    /**
     * Accessor: label status stok.
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->quantity <= 0) {
            return 'habis';
        }
        if ($this->quantity <= $this->min_quantity) {
            return 'menipis';
        }

        return 'tersedia';
    }

    /**
     * Accessor: color class untuk status stok.
     */
    public function getStockStatusColorAttribute(): string
    {
        return match ($this->stock_status) {
            'habis' => 'bg-red-100 text-red-800 border-red-300',
            'menipis' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            default => 'bg-green-100 text-green-800 border-green-300',
        };
    }
}
