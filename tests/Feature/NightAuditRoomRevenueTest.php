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
        $this->assertSame(500000.0, (float) $data['totalRevenueAccrual']);
    }

    /**
     * Booking yang dibayar hari ini untuk check-in beberapa hari lagi tidak boleh
     * menambah pendapatan malam ini (basis akrual), walau kasnya masuk hari ini.
     */
    public function test_accrual_revenue_excludes_bookings_with_future_check_in(): void
    {
        $occupied = Room::factory()->create([
            'price_per_night' => 500000,
            'price_weekday' => 500000,
            'price_weekend' => 500000,
        ]);

        Reservation::factory()->checkedIn()->create([
            'room_id' => $occupied->id,
            'check_in' => Carbon::parse('2026-11-13 14:00:00'),
            'check_out' => Carbon::parse('2026-11-14 12:00:00'),
            'total_amount' => 500000,
            'paid_amount' => 500000,
        ]);

        $future = Reservation::factory()->create([
            'room_id' => Room::factory(),
            'status' => 'pending',
            'check_in' => Carbon::parse('2026-11-15 14:00:00'),
            'check_out' => Carbon::parse('2026-11-16 12:00:00'),
            'total_amount' => 500000,
            'paid_amount' => 500000,
        ]);

        $txn = $future->transactions()->create([
            'transaction_number' => 'TRX-FUTURE-1',
            'type' => 'pelunasan',
            'amount' => 500000,
            'payment_method' => 'transfer_bca',
            'source_type' => 'transfer',
        ]);
        $txn->forceFill(['created_at' => Carbon::parse('2026-11-13 10:00:00')])->saveQuietly();

        $data = app(NightAuditController::class)->buildSnapshotData('2026-11-13');

        // Akrual: hanya kamar yang menginap malam 13/11
        $this->assertSame(500000.0, (float) $data['totalRevenueAccrual']);
        // Kas: pembayaran booking CI 15/11 tetap terhitung sebagai uang masuk hari ini
        $this->assertSame(500000.0, (float) $data['revenueToday']);
    }

    /**
     * Nominal extend ikut terhitung di pendapatan per malam (lewat total_amount
     * yang sudah ditambah saat extend), dan tidak dobel dengan transaksi extend.
     */
    public function test_accrual_revenue_includes_extended_nights_without_double_counting(): void
    {
        $room = Room::factory()->create([
            'price_per_night' => 500000,
            'price_weekday' => 500000,
            'price_weekend' => 500000,
        ]);

        // 2 malam: 13/11 dan 14/11 (malam kedua berasal dari extend)
        $reservation = Reservation::factory()->checkedIn()->create([
            'room_id' => $room->id,
            'check_in' => Carbon::parse('2026-11-13 14:00:00'),
            'check_out' => Carbon::parse('2026-11-15 12:00:00'),
            'total_amount' => 1000000,
            'paid_amount' => 1000000,
        ]);

        $extend = $reservation->transactions()->create([
            'transaction_number' => 'TRX-EXTEND-1',
            'type' => 'extend',
            'amount' => 500000,
            'payment_method' => 'cash',
            'source_type' => 'tunai',
        ]);
        $extend->forceFill(['created_at' => Carbon::parse('2026-11-14 14:00:00')])->saveQuietly();

        $secondNight = app(NightAuditController::class)->buildSnapshotData('2026-11-14');

        $this->assertSame(500000.0, (float) $secondNight['roomNightlyRevenueTotal']);
        $this->assertSame(500000.0, (float) $secondNight['totalRevenueAccrual']);
    }
}
