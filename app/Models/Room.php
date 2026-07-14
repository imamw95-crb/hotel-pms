<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number', 'room_type_id', 'room_type_name', 'price_per_night',
        'price_weekday', 'price_weekend',
        'max_occupancy', 'status', 'facilities',
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
     * Priority: Promo price > Weekend price > Weekday price > Default price_per_night.
     */
    public function getPriceForDate(Carbon|string $date): float
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $dateStr = $carbon->format('Y-m-d');

        // Check promo price from room type date prices
        if ($this->relationLoaded('roomType') && $this->roomType) {
            $promo = $this->roomType->datePrices->first(function ($dp) use ($dateStr) {
                return $dp->date->format('Y-m-d') === $dateStr;
            });
            if ($promo && $promo->price > 0) {
                return (float) $promo->price;
            }
        }

        // Fallback: weekend/weekday pricing
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

    public function outOfOrders(): HasMany
    {
        return $this->hasMany(OutOfOrder::class);
    }

    /**
     * Check if room has active Out of Order for a given date range.
     */
    public function isOutOfOrder($checkIn, $checkOut): bool
    {
        $checkInDate = $checkIn instanceof Carbon ? $checkIn->format('Y-m-d') : $checkIn;
        $checkOutDate = $checkOut instanceof Carbon ? $checkOut->format('Y-m-d') : $checkOut;

        return OutOfOrder::where('room_id', $this->id)
            ->where('status', OutOfOrder::STATUS_ACTIVE)
            ->where('start_date', '<=', $checkOutDate)
            ->where(function ($q) use ($checkInDate) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $checkInDate);
            })
            ->exists();
    }

    public function isAvailable($checkIn, $checkOut, $excludeReservationId = null)
    {
        // Back-to-Back Booking: check-out 12:00, check-in 14:00 same day is allowed.
        // Overlap occurs only when existing_check_in < new_check_out AND existing_check_out > new_check_in
        // (strict, not inclusive).

        // Eager‑load reservations and out‑of‑order to avoid N+1 queries when called repeatedly.
        $this->loadMissing(['reservations' => function ($q) use ($checkIn, $checkOut, $excludeReservationId) {
            $q->where(function ($sub) use ($checkIn, $checkOut) {
                $sub->where('check_in', '<', $checkOut)
                    ->where('check_out', '>', $checkIn);
            })
            ->whereIn('status', ['pending', 'checked_in']);
            if ($excludeReservationId) {
                $q->where('id', '!=', $excludeReservationId);
            }
        }]);

        // Also check if room is Out of Order
        if ($this->isOutOfOrder($checkIn, $checkOut)) {
            return false;
        }

        return $this->reservations->isEmpty();
    }
}
