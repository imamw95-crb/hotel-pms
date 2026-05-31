<?php

namespace Tests\Unit;

use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Tests\TestCase;

class RoomPricingTest extends TestCase
{
    public function test_price_per_night_returns_default_when_no_weekday_weekend_set()
    {
        $room = Room::factory()->create([
            'price_per_night' => 500000,
            'price_weekday' => 0,
            'price_weekend' => 0,
        ]);

        $weekday = Carbon::parse('2026-06-01'); // Monday
        $weekend = Carbon::parse('2026-06-06'); // Saturday

        $this->assertEquals(500000, $room->getPriceForDate($weekday));
        $this->assertEquals(500000, $room->getPriceForDate($weekend));
    }

    public function test_get_price_for_date_weekday()
    {
        $room = Room::factory()->create([
            'price_per_night' => 500000,
            'price_weekday' => 450000,
            'price_weekend' => 600000,
        ]);

        $monday = Carbon::parse('2026-06-01'); // Monday
        $this->assertEquals(450000, $room->getPriceForDate($monday));
    }

    public function test_get_price_for_date_weekend()
    {
        $room = Room::factory()->create([
            'price_per_night' => 500000,
            'price_weekday' => 450000,
            'price_weekend' => 600000,
        ]);

        $saturday = Carbon::parse('2026-06-06'); // Saturday
        $sunday = Carbon::parse('2026-06-07');   // Sunday

        $this->assertEquals(600000, $room->getPriceForDate($saturday));
        $this->assertEquals(600000, $room->getPriceForDate($sunday));
    }

    public function test_calculate_total_for_range_mixed_weekday_weekend()
    {
        $room = Room::factory()->create([
            'price_per_night' => 500000,
            'price_weekday' => 450000,
            'price_weekend' => 600000,
        ]);

        // Thursday(weekday) -> Friday(weekday) -> Saturday(weekend)
        $checkIn = Carbon::parse('2026-06-04')->setHour(14); // Thursday
        $checkOut = Carbon::parse('2026-06-06')->setHour(12); // Saturday (check-out, not charged)

        $total = $room->calculateTotalForRange($checkIn, $checkOut);

        // Thursday night (weekday): 450000
        // Friday night (weekday): 450000
        $this->assertEquals(900000, $total);
    }
}
