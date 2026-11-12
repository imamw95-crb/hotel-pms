<?php

namespace Tests\Feature;

use App\Http\Controllers\NightAuditController;
use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NightAuditRoomRevenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_lists_room_revenue_for_the_reported_night_only(): void
    {
        $room = Room::factory()->create([
            'price_per_night' => 500000,
            'price_weekday' => 500000,
            'price_weekend' => 600000,
        ]);

        // Malam 11, 12, 13 November (semua weekday)
        $stay = Reservation::factory()->checkedIn()->create([
            'room_id' => $room->id,
            'check_in' => Carbon::parse('2026-11-11 14:00:00'),
            'check_out' => Carbon::parse('2026-11-14 12:00:00'),
            'total_amount' => 1500000,
            'paid_amount' => 0,
        ]);

        // Reservasi yang baru menginap malam 13/11 (check-in 13/11 14:00)
        Reservation::factory()->checkedIn()->create([
            'room_id' => Room::factory(),
            'check_in' => Carbon::parse('2026-11-13 14:00:00'),
            'check_out' => Carbon::parse('2026-11-14 12:00:00'),
            'total_amount' => 500000,
            'paid_amount' => 0,
        ]);

        $data = app(NightAuditController::class)->buildSnapshotData('2026-11-12');

        $rows = collect($data['roomNightlyRevenue']);
        $this->assertCount(1, $rows);
        $this->assertSame($stay->room->room_number, $rows->first()['room_number']);
        $this->assertSame(500000.0, (float) $rows->first()['amount']);
        $this->assertSame(500000.0, (float) $data['roomNightlyRevenueTotal']);
    }
}
