<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtendStayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-13 08:00:00'));

        PaymentMethod::firstOrCreate(['slug' => 'cash'], [
            'name' => 'Cash',
            'slug' => 'cash',
            'source_type' => 'tunai',
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_extend_multi_night_records_one_transaction_per_night_date(): void
    {
        $room = Room::factory()->create([
            'price_per_night' => 500000,
            'price_weekday' => 500000,
            'price_weekend' => 600000,
        ]);

        $reservation = Reservation::factory()->checkedIn()->create([
            'room_id' => $room->id,
            'check_in' => Carbon::parse('2026-11-12 14:00:00'),
            'check_out' => Carbon::parse('2026-11-13 12:00:00'),
            'total_amount' => 500000,
            'paid_amount' => 0,
            'payment_method' => 'cash',
        ]);

        // 13/11 (Jumat), 14/11 (Sabtu), 15/11 (Minggu)
        $this->actingAs(User::factory()->create(['role' => 'owner']))
            ->postJson(route('reservations.extend', $reservation), ['new_check_out' => '2026-11-16'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $transactions = Transaction::where('reservation_id', $reservation->id)->orderBy('created_at')->get();

        $this->assertCount(3, $transactions);
        $this->assertSame(
            ['2026-11-13', '2026-11-14', '2026-11-15'],
            $transactions->map(fn ($t) => $t->created_at->format('Y-m-d'))->all()
        );

        // Harga tiap baris mengikuti harga kamar pada tanggal malam tersebut
        $expectedTotal = 0;
        foreach ($transactions as $transaction) {
            $expected = (float) $room->getPriceForDate($transaction->created_at->format('Y-m-d'));
            $this->assertSame($expected, (float) $transaction->amount);
            $this->assertSame('extend', $transaction->type);
            $expectedTotal += $expected;
        }

        $this->assertSame(500000.0 + $expectedTotal, (float) $reservation->fresh()->total_amount);
    }

    public function test_extend_preview_returns_price_per_night(): void
    {
        $room = Room::factory()->create([
            'price_per_night' => 500000,
            'price_weekday' => 500000,
            'price_weekend' => 600000,
        ]);

        $reservation = Reservation::factory()->checkedIn()->create([
            'room_id' => $room->id,
            'check_in' => Carbon::parse('2026-11-12 14:00:00'),
            'check_out' => Carbon::parse('2026-11-13 12:00:00'),
            'total_amount' => 500000,
            'paid_amount' => 0,
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAs(User::factory()->create(['role' => 'owner']))
            ->getJson(route('reservations.extend-preview', $reservation).'?new_check_out=2026-11-16')
            ->assertOk()
            ->json();

        $this->assertTrue($response['success']);
        $this->assertSame(3, $response['nights_count']);
        $this->assertSame(
            ['2026-11-13', '2026-11-14', '2026-11-15'],
            array_column($response['nights'], 'date')
        );
        $this->assertSame(
            array_sum(array_column($response['nights'], 'price')),
            $response['total']
        );
    }
}
