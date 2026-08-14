<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingGroupMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Daftar master harga per tipe kamar untuk grup ini.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(BookingGroupMasterPrice::class);
    }

    /**
     * Pengguna yang membuat master grup.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Ambil harga master untuk tipe kamar tertentu (atau null jika tidak di-set).
     */
    public function priceForRoomType(int $roomTypeId): ?BookingGroupMasterPrice
    {
        return $this->prices->first(fn ($price) => $price->room_type_id === $roomTypeId);
    }

    /**
     * Generate kode grup otomatis (GRP-0001, GRP-0002, ...) berdasarkan kode terakhir.
     */
    public static function generateCode(): string
    {
        $prefix = 'GRP-';
        $numbers = self::where('code', 'like', $prefix.'%')
            ->pluck('code')
            ->map(fn ($code) => (int) substr($code, strlen($prefix)))
            ->filter(fn ($n) => $n > 0);

        $next = ($numbers->max() ?? 0) + 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
