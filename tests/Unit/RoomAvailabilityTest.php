<?php

namespace Tests\Unit;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class RoomAvailabilityTest extends TestCase
{
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->room = Room::factory()->create([
            'status' => 'available',
        ]);
    }

    public function test_room_available_when_no_reservations()
    {
        $checkIn = Carbon::tomorrow()->setHour(14);
        $checkOut = Carbon::tomorrow()->addDay()->setHour(12);

        $this->assertTrue($this->room->isAvailable($checkIn, $checkOut));
    }

    public function test_room_not_available_when_overlapping()
    {
        $checkIn = Carbon::tomorrow()->setHour(14);
        $checkOut = Carbon::tomorrow()->addDay()->setHour(12);

        // Existing reservation overlaps
        Reservation::factory()->create([
            'room_id' => $this->room->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'status' => 'checked_in',
        ]);

        // Same time = overlap
        $this->assertFalse($this->room->isAvailable($checkIn, $checkOut));
    }

    public function test_room_available_for_back_to_back_same_day()
    {
        $user = User::factory()->create(['role' => 'owner']);

        // Existing reservation: check-out today at 12:00
        $existingCheckIn = Carbon::yesterday()->setHour(14);
        $existingCheckOut = Carbon::today()->setHour(12);

        Reservation::factory()->create([
            'room_id' => $this->room->id,
            'check_in' => $existingCheckIn,
            'check_out' => $existingCheckOut,
            'status' => 'checked_in',
            'created_by' => $user->id,
        ]);

        // New reservation: check-in today at 14:00 (same day, 2 hours later)
        $newCheckIn = Carbon::today()->setHour(14);
        $newCheckOut = Carbon::tomorrow()->setHour(12);

        // Back-to-back: existing check_out (12:00) < new check_in (14:00) = no overlap
        $this->assertTrue($this->room->isAvailable($newCheckIn, $newCheckOut));
    }

    public function test_room_available_for_different_dates()
    {
        $checkIn = Carbon::tomorrow()->setHour(14);
        $checkOut = Carbon::tomorrow()->addDay()->setHour(12);

        Reservation::factory()->create([
            'room_id' => $this->room->id,
            'check_in' => Carbon::parse('+10 days')->setHour(14),
            'check_out' => Carbon::parse('+12 days')->setHour(12),
            'status' => 'checked_in',
        ]);

        // Different date range = available
        $this->assertTrue($this->room->isAvailable($checkIn, $checkOut));
    }

    public function test_pending_reservation_blocks_availability()
    {
        $checkIn = Carbon::tomorrow()->setHour(14);
        $checkOut = Carbon::tomorrow()->addDay()->setHour(12);

        Reservation::factory()->create([
            'room_id' => $this->room->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'status' => 'pending',
        ]);

        // Pending should also block
        $this->assertFalse($this->room->isAvailable($checkIn, $checkOut));
    }

    public function test_room_available_when_excluding_own_reservation()
    {
        $checkIn = Carbon::tomorrow()->setHour(14);
        $checkOut = Carbon::tomorrow()->addDay()->setHour(12);

        $reservation = Reservation::factory()->create([
            'room_id' => $this->room->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'status' => 'checked_in',
        ]);

        // Excluding own reservation = available (for update scenario)
        $this->assertTrue($this->room->isAvailable($checkIn, $checkOut, $reservation->id));
    }

    public function test_cancelled_reservation_does_not_block()
    {
        $checkIn = Carbon::tomorrow()->setHour(14);
        $checkOut = Carbon::tomorrow()->addDay()->setHour(12);

        Reservation::factory()->create([
            'room_id' => $this->room->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'status' => 'cancelled',
        ]);

        // Cancelled = does not block
        $this->assertTrue($this->room->isAvailable($checkIn, $checkOut));
    }
}
