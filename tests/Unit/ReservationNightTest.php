<?php

namespace Tests\Unit;

use App\Models\Reservation;
use Carbon\Carbon;
use Tests\TestCase;

class ReservationNightTest extends TestCase
{
    public function test_nights_one_night()
    {
        $reservation = new Reservation([
            'check_in' => Carbon::parse('2026-06-01 14:00:00'),
            'check_out' => Carbon::parse('2026-06-02 12:00:00'),
        ]);

        // 1 night: CI 01/06 14:00 -> CO 02/06 12:00
        $this->assertEquals(1, $reservation->nights);
    }

    public function test_nights_two_nights()
    {
        $reservation = new Reservation([
            'check_in' => Carbon::parse('2026-06-01 14:00:00'),
            'check_out' => Carbon::parse('2026-06-03 12:00:00'),
        ]);

        // 2 nights
        $this->assertEquals(2, $reservation->nights);
    }

    public function test_nights_minimum_one()
    {
        $reservation = new Reservation([
            'check_in' => Carbon::parse('2026-06-01 14:00:00'),
            'check_out' => Carbon::parse('2026-06-01 16:00:00'), // Same day (should be min 1)
        ]);

        // diffInDays = 0, but min 1
        $this->assertEquals(1, $reservation->nights);
    }

    public function test_nights_returns_zero_when_dates_null()
    {
        $reservation = new Reservation([
            'check_in' => null,
            'check_out' => null,
        ]);

        $this->assertEquals(0, $reservation->nights);
    }
}
