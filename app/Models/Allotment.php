<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Allotment extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id',
        'date',
        'allotment',
        'booked',
        'price',
        'channel',
    ];

    // Channel constants
    public const CHANNEL_API = 'api';
    public const CHANNEL_WEBSITE = 'website';

    protected $casts = [
        'date' => 'date:Y-m-d',
        'allotment' => 'integer',
        'booked' => 'integer',
        'price' => 'decimal:2',
    ];

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Dapatkan harga efektif: harga allotment jika di-set,
     * otherwise harga master dari room type (harga minimum kamar).
     */
    public function getEffectivePrice(): float
    {
        if ($this->price !== null && $this->price > 0) {
            return (float) $this->price;
        }

        // Fallback ke harga master dari kamar di tipe ini
        $minPrice = $this->roomType?->rooms()
            ->where('price_per_night', '>', 0)
            ->min('price_per_night');

        return (float) ($minPrice ?? 0);
    }

    /**
     * Cek apakah allotment masih tersedia untuk room type pada tanggal tertentu.
     */
    public static function isAvailable(int $roomTypeId, Carbon $date, ?string $channel = null): bool
    {
        $query = static::where('room_type_id', $roomTypeId)
            ->where('date', $date->format('Y-m-d'));

        if ($channel) {
            $query->where(function ($q) use ($channel) {
                $q->where('channel', $channel)
                    ->orWhereNull('channel');
            });
        }

        $allotment = $query->orderBy('channel', 'desc')->first();

        if (! $allotment) {
            // No allotment set = unlimited
            return true;
        }

        return $allotment->booked < $allotment->allotment;
    }

    /**
     * Cek allotment untuk range tanggal.
     * Return array tanggal yang tidak tersedia.
     */
    public static function checkAvailabilityInRange(
        int $roomTypeId,
        Carbon $checkIn,
        Carbon $checkOut,
        ?string $channel = null
    ): array {
        $unavailable = [];
        $current = $checkIn->copy()->startOfDay();
        $checkoutDay = $checkOut->copy()->startOfDay();

        while ($current->lt($checkoutDay)) {
            if (! static::isAvailable($roomTypeId, $current, $channel)) {
                $unavailable[] = $current->format('Y-m-d');
            }
            $current->addDay();
        }

        return $unavailable;
    }

    /**
     * Increment booked count untuk room type pada range tanggal.
     */
    /**
     * Increment booked count for a room type over a date range.
     * Uses a bulk update to reduce queries.
     */
    public static function incrementBooked(
        int $roomTypeId,
        Carbon $checkIn,
        Carbon $checkOut,
        ?string $channel = null
    ): void {
        $dates = [];
        $current = $checkIn->copy()->startOfDay();
        $checkoutDay = $checkOut->copy()->startOfDay();
        while ($current->lt($checkoutDay)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        if (empty($dates)) {
            return;
        }

        $query = static::where('room_type_id', $roomTypeId)
            ->whereIn('date', $dates);
        if ($channel) {
            $query->where(function ($q) use ($channel) {
                $q->where('channel', $channel)
                    ->orWhereNull('channel');
            });
        }
        // Increment booked for each matched row
        $query->increment('booked');
    }

    /**
     * Decrement booked count untuk room type pada range tanggal.
     */
    /**
     * Decrement booked count for a room type over a date range.
     * Uses a bulk update.
     */
    public static function decrementBooked(
        int $roomTypeId,
        Carbon $checkIn,
        Carbon $checkOut,
        ?string $channel = null
    ): void {
        $dates = [];
        $current = $checkIn->copy()->startOfDay();
        $checkoutDay = $checkOut->copy()->startOfDay();
        while ($current->lt($checkoutDay)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        if (empty($dates)) {
            return;
        }

        $query = static::where('room_type_id', $roomTypeId)
            ->whereIn('date', $dates);
        if ($channel) {
            $query->where(function ($q) use ($channel) {
                $q->where('channel', $channel)
                    ->orWhereNull('channel');
            });
        }
        // Decrement but ensure booked does not go below 0
        $query->where('booked', '>', 0)->decrement('booked');
    }
}
