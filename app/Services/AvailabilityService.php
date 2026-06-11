<?php

namespace App\Services;

use App\Models\OutOfOrder;
use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * AvailabilityService — Centralized availability engine for Hotel PMS.
 *
 * Handles:
 * - Room availability checking (back-to-back aware)
 * - Occupancy calendar generation
 * - Room rack view (timeline per room)
 * - Forecasting / projections
 */
class AvailabilityService
{
    /**
     * Check if a room is available for a given date range.
     * Back-to-back booking allowed: check-out 12:00, check-in 14:00 same day = OK.
     */
    public function isRoomAvailable(int $roomId, Carbon $checkIn, Carbon $checkOut, ?int $excludeReservationId = null): bool
    {
        // Check Out of Order first
        $oooExists = OutOfOrder::where('room_id', $roomId)
            ->where('status', OutOfOrder::STATUS_ACTIVE)
            ->where('start_date', '<=', $checkOut->format('Y-m-d'))
            ->where(function ($q) use ($checkIn) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $checkIn->format('Y-m-d'));
            })
            ->exists();

        if ($oooExists) {
            return false;
        }

        $query = Reservation::where('room_id', $roomId)
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where('check_in', '<', $checkOut)
                    ->where('check_out', '>', $checkIn);
            })
            ->whereIn('status', ['pending', 'checked_in']);

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return ! $query->exists();
    }

    /**
     * Get available rooms for a given date range.
     */
    public function getAvailableRooms(Carbon $checkIn, Carbon $checkOut, ?string $roomType = null)
    {
        $bookedIds = Reservation::whereIn('status', ['pending', 'checked_in'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where('check_in', '<', $checkOut)
                    ->where('check_out', '>', $checkIn);
            })
            ->pluck('room_id')
            ->unique();

        // Exclude rooms with active Out of Order for the requested period
        $oooRoomIds = OutOfOrder::where('status', OutOfOrder::STATUS_ACTIVE)
            ->where('start_date', '<=', $checkOut->format('Y-m-d'))
            ->where(function ($q) use ($checkIn) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $checkIn->format('Y-m-d'));
            })
            ->pluck('room_id')
            ->unique();

        $query = Room::whereNotIn('id', $bookedIds)
            ->whereNotIn('id', $oooRoomIds)
            ->where('status', '!=', 'maintenance');

        if ($roomType) {
            $query->where('room_type_name', $roomType);
        }

        return $query->orderBy('room_number')->get();
    }

    /**
     * Limit available rooms to 25% per room type (floor, minimum 1).
     * Used by public API to show limited inventory for marketing purposes.
     * Internal services should use getAvailableRooms() directly for full list.
     *
     * @param  Collection  $rooms  Full available rooms collection
     * @return Collection Limited rooms (max 25% per type, min 1)
     */
    public function limitAvailablePerType($rooms)
    {
        return $rooms->groupBy('room_type_name')->flatMap(function ($typeRooms) {
            $totalAvailable = $typeRooms->count();
            $limit = max(1, (int) floor(0.25 * $totalAvailable));

            return $typeRooms->take($limit);
        })->values();
    }

    /**
     * Generate occupancy calendar data for a date range.
     * Returns per-room, per-day occupancy status.
     */
    public function getOccupancyCalendar(Carbon $startDate, Carbon $endDate): array
    {
        $rooms = Room::with(['roomType'])->orderBy('room_number')->get();
        $reservations = Reservation::with(['guest'])
            ->whereIn('status', ['pending', 'checked_in'])
            ->where('check_in', '<', $endDate->copy()->addDay())
            ->where('check_out', '>', $startDate)
            ->get()
            ->groupBy('room_id');

        $days = [];
        $period = $startDate->copy();
        while ($period->lte($endDate)) {
            $days[] = $period->copy();
            $period->addDay();
        }

        $calendar = [];
        foreach ($rooms as $room) {
            $roomReservations = $reservations->get($room->id, collect());
            $row = [
                'room' => $room,
                'days' => [],
            ];

            foreach ($days as $day) {
                $dayEnd = $day->copy()->endOfDay();
                $booking = $roomReservations->first(function ($r) use ($day, $dayEnd) {
                    $ci = $r->check_in;
                    $co = $r->check_out;

                    // Occupied if check_in <= day AND check_out > day (strict)
                    return $ci->lte($dayEnd) && $co->gt($day);
                });

                if ($booking) {
                    $isCheckin = $booking->check_in->format('Y-m-d') === $day->format('Y-m-d');
                    $isCheckout = $booking->check_out->format('Y-m-d') === $day->format('Y-m-d');
                    $row['days'][] = [
                        'status' => 'occupied',
                        'booking' => $booking,
                        'is_checkin' => $isCheckin,
                        'is_checkout' => $isCheckout,
                    ];
                } elseif ($room->status === 'maintenance') {
                    $row['days'][] = ['status' => 'maintenance', 'booking' => null];
                } elseif ($room->status === 'cleaning') {
                    $row['days'][] = ['status' => 'dirty', 'booking' => null];
                } else {
                    $row['days'][] = ['status' => 'available', 'booking' => null];
                }
            }

            $calendar[] = $row;
        }

        return [
            'calendar' => $calendar,
            'days' => $days,
            'rooms' => $rooms,
        ];
    }

    /**
     * Room rack view — timeline of bookings per room.
     * Optimized: single query with join, no N+1.
     */
    public function getRoomRack(Carbon $startDate, int $days = 14): array
    {
        $endDate = $startDate->copy()->addDays($days - 1);

        $rooms = Room::with(['roomType'])
            ->orderBy('room_number')
            ->get();

        $reservations = Reservation::with(['guest'])
            ->whereIn('status', ['pending', 'checked_in', 'checked_out'])
            ->where('check_in', '<', $endDate->copy()->addDay())
            ->where('check_out', '>', $startDate)
            ->get()
            ->groupBy('room_id');

        $period = [];
        for ($i = 0; $i < $days; $i++) {
            $period[] = $startDate->copy()->addDays($i);
        }

        $rack = [];
        foreach ($rooms as $room) {
            $roomReservations = $reservations->get($room->id, collect());
            $blocks = [];

            foreach ($roomReservations as $r) {
                $blockStart = max($r->check_in->copy(), $startDate->copy());
                $blockEnd = min($r->check_out->copy(), $endDate->copy()->addDay());
                $nights = $blockStart->diffInDays($blockEnd);
                if ($nights <= 0) {
                    continue;
                }

                $blocks[] = [
                    'id' => $r->id,
                    'guest_name' => $r->guest->guest_name ?? 'N/A',
                    'reservation_number' => $r->reservation_number,
                    'status' => $r->status,
                    'check_in' => $r->check_in,
                    'check_out' => $r->check_out,
                    'start_date' => $blockStart,
                    'end_date' => $blockEnd,
                    'nights' => $nights,
                ];
            }

            // Sort blocks by check_in descending so back-to-back check-in (14:00) appears after checkout (12:00)
            usort($blocks, function ($a, $b) {
                return $b['check_in']->timestamp - $a['check_in']->timestamp;
            });

            $rack[] = [
                'room' => $room,
                'blocks' => $blocks,
            ];
        }

        return [
            'rack' => $rack,
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days' => $days,
        ];
    }

    /**
     * Availability forecast — projection for next N days.
     */
    public function getForecast(int $days = 30): array
    {
        $start = Carbon::today();
        $end = $start->copy()->addDays($days - 1);

        $totalRooms = Room::where('status', '!=', 'maintenance')->count();
        if ($totalRooms === 0) {
            return [];
        }

        $reservations = Reservation::whereIn('status', ['pending', 'checked_in'])
            ->where('check_in', '<', $end->copy()->addDay())
            ->where('check_out', '>', $start)
            ->get(['check_in', 'check_out', 'room_id']);

        $forecast = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i);
            $occupied = $reservations->filter(function ($r) use ($day) {
                return $r->check_in->lte($day->copy()->endOfDay()) && $r->check_out->gt($day);
            })->unique('room_id')->count();

            $available = $totalRooms - $occupied;
            $forecast[] = [
                'date' => $day->format('Y-m-d'),
                'label' => $day->isoFormat('ddd, D MMM'),
                'total' => $totalRooms,
                'occupied' => $occupied,
                'available' => $available,
                'occupancy_pct' => round(($occupied / $totalRooms) * 100),
            ];
        }

        return $forecast;
    }
}
