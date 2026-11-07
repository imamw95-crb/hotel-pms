<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\Room;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BookingSyncService;
use App\Services\OtaPaymentService;
use Carbon\Carbon;
use Tests\TestCase;

class OtaAutoPaymentDateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // BookingSync memakai user id 1 sebagai "system user" untuk reservasi OTA.
        User::factory()->create(['id' => 1]);

        PaymentMethod::firstOrCreate(['slug' => 'ota_payment'], [
            'name' => 'OTA Payment',
            'slug' => 'ota_payment',
            'source_type' => 'ota',
            'is_active' => true,
        ]);
    }

    public function test_business_date_uses_check_in_when_already_past(): void
    {
        $this->assertSame(
            '2026-10-01 14:00:00',
            OtaPaymentService::businessDate(
                Carbon::parse('2026-10-01 14:00:00'),
                Carbon::parse('2026-10-05 09:00:00')
            )->toDateTimeString()
        );
    }

    public function test_business_date_falls_back_to_now_for_future_check_in(): void
    {
        $now = Carbon::parse('2026-10-05 09:00:00');

        $this->assertSame(
            '2026-10-05 09:00:00',
            OtaPaymentService::businessDate(Carbon::parse('2026-10-20 14:00:00'), $now)->toDateTimeString()
        );
    }

    public function test_ota_auto_payment_is_dated_at_check_in(): void
    {
        $room = Room::factory()->create();
        $checkIn = Carbon::today()->subDays(3);

        $result = app(BookingSyncService::class)->sync([
            'reservation_id' => 'OTA-PAST-1',
            'guest_name' => 'Budi Santoso',
            'checkin_date' => $checkIn->format('Y-m-d'),
            'checkout_date' => $checkIn->copy()->addDay()->format('Y-m-d'),
            'total_price' => 600000,
            'payment_method' => 'tiket.com',
            'status' => 'confirmed',
            'ota_source' => 'tiket.com',
        ], $room->id);

        $this->assertTrue($result['success'], $result['error'] ?? '');

        $reservation = $result['reservation'];
        $txn = Transaction::where('reservation_id', $reservation->id)->firstOrFail();

        $this->assertSame($checkIn->format('Y-m-d'), $txn->created_at->format('Y-m-d'));
        $this->assertSame($checkIn->format('Y-m-d'), $reservation->fresh()->paid_date->format('Y-m-d'));
        $this->assertEquals(600000, (float) $reservation->fresh()->paid_amount);
    }

    public function test_ota_auto_payment_for_future_check_in_is_dated_today(): void
    {
        $room = Room::factory()->create();
        $checkIn = Carbon::today()->addDays(10);

        $result = app(BookingSyncService::class)->sync([
            'reservation_id' => 'OTA-FUTURE-1',
            'guest_name' => 'Siti Rahma',
            'checkin_date' => $checkIn->format('Y-m-d'),
            'checkout_date' => $checkIn->copy()->addDay()->format('Y-m-d'),
            'total_price' => 500000,
            'payment_method' => 'traveloka.com',
            'status' => 'confirmed',
            'ota_source' => 'traveloka.com',
        ], $room->id);

        $this->assertTrue($result['success'], $result['error'] ?? '');

        $reservation = $result['reservation'];
        $txn = Transaction::where('reservation_id', $reservation->id)->firstOrFail();

        $this->assertSame(now()->format('Y-m-d'), $txn->created_at->format('Y-m-d'));
    }
}
