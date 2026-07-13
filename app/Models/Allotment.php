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
        'channel',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'allotment' => 'integer',
        'booked' => 'integer',
    ];

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
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

        while ($current->lt($checkOut)) {
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
    public static function incrementBooked(
        int $roomTypeId,
        Carbon $checkIn,
        Carbon $checkOut,
        ?string $channel = null
    ): void {
        $current = $checkIn->copy()->startOfDay();

        while ($current->lt($checkOut)) {
            $allotment = static::where('room_type_id', $roomTypeId)
                ->where('date', $current->format('Y-m-d'))
                ->where(function ($q) use ($channel) {
                    if ($channel) {
                        $q->where('channel', $channel)
                            ->orWhereNull('channel');
                    }
                })
                ->orderBy('channel', 'desc')
                ->first();

            if ($allotment) {
                $allotment->increment('booked');
            }

            $current->addDay();
        }
    }

    /**
     * Decrement booked count untuk room type pada range tanggal.
     */
    public static function decrementBooked(
        int $roomTypeId,
        Carbon $checkIn,
        Carbon $checkOut,
        ?string $channel = null
    ): void {
        $current = $checkIn->copy()->startOfDay();

        while ($current->lt($checkOut)) {
            $allotment = static::where('room_type_id', $roomTypeId)
                ->where('date', $current->format('Y-m-d'))
                ->where(function ($q) use ($channel) {
                    if ($channel) {
                        $q->where('channel', $channel)
                            ->orWhereNull('channel');
                    }
                })
                ->orderBy('channel', 'desc')
                ->first();

            if ($allotment && $allotment->booked > 0) {
                $allotment->decrement('booked');
            }

            $current->addDay();
        }
    }
}
