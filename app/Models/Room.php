<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Room extends Model
{
    protected $fillable = [
        'room_number', 'room_type_id', 'room_type_name', 'price_per_night',
        'price_weekday', 'price_weekend',
        'max_occupancy', 'status', 'facilities'
    ];

    protected $casts = [
        'facilities' => 'array',
        'price_per_night' => 'decimal:2',
        'price_weekday' => 'decimal:2',
        'price_weekend' => 'decimal:2',
    ];

    /**
     * Determine if a given date is a weekend (Saturday or Sunday).
     */
    public static function isWeekend(Carbon|string $date): bool
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        return $carbon->isWeekend();
    }

    /**
     * Get the effective price per night for a given date.
     * Falls back to price_per_night if weekday/weekend prices are not set.
     */
    public function getPriceForDate(Carbon|string $date): float
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        if (self::isWeekend($carbon)) {
            return $this->price_weekend > 0 ? (float) $this->price_weekend : (float) $this->price_per_night;
        }

        return $this->price_weekday > 0 ? (float) $this->price_weekday : (float) $this->price_per_night;
    }

    /**
     * Calculate total price for a date range, applying weekend/weekday rates per night.
     */
    public function calculateTotalForRange(Carbon $checkIn, Carbon $checkOut): float
    {
        $total = 0;
        $current = $checkIn->copy()->startOfDay();
        $end = $checkOut->copy()->startOfDay();

        while ($current->lt($end)) {
            $total += $this->getPriceForDate($current);
            $current->addDay();
        }

        return $total;
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function housekeepingTasks(): HasMany
    {
        return $this->hasMany(HousekeepingTask::class);
    }

    public function isAvailable($checkIn, $checkOut, $excludeReservationId = null)
    {
        // Back-to-Back Booking: check-out jam 12:00 dan check-in jam 14:00
        // di hari yang sama TIDAK dianggap bentrok.
        // Overlap hanya terjadi jika:
        //   existing_check_in < new_check_out AND existing_check_out > new_check_in
        // (strict less-than / greater-than, bukan inclusive)
        $query = $this->reservations()
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where('check_in', '<', $checkOut)
                  ->where('check_out', '>', $checkIn);
            })
            ->whereIn('status', ['pending', 'checked_in']);

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return !$query->exists();
    }
}