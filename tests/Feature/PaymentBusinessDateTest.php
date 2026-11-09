<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentDateService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentBusinessDateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create(['id' => 1]);
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

    /**
     * Kasus live: input jam 02:30 dengan tanggal kemarin → harus masuk periode
     * bisnis tanggal yang dipilih, bukan periode sebelumnya (yang sudah locked).
     */
    public function test_past_date_before_cutoff_moves_into_that_business_date(): void
    {
        $timestamp = PaymentDateService::forBusinessDate(
            Carbon::parse('2026-11-08'),
            Carbon::parse('2026-11-09 02:30:23')
        );

        $this->assertSame('2026-11-09 02:30:23', $timestamp->toDateTimeString());
    }

    public function test_past_date_after_cutoff_stays_on_selected_date(): void
    {
        $timestamp = PaymentDateService::forBusinessDate(
            Carbon::parse('2026-11-08'),
            Carbon::parse('2026-11-09 14:10:00')
        );

        $this->assertSame('2026-11-08 14:10:00', $timestamp->toDateTimeString());
    }

    public function test_today_selection_uses_the_actual_time(): void
    {
        $timestamp = PaymentDateService::forBusinessDate(
            Carbon::parse('2026-11-09'),
            Carbon::parse('2026-11-09 02:30:23')
        );

        $this->assertSame('2026-11-09 02:30:23', $timestamp->toDateTimeString());
    }

    public function test_add_payment_early_morning_lands_in_current_business_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-09 02:30:23'));

        $reservation = Reservation::factory()->create(['total_amount' => 950000, 'paid_amount' => 0]);

        $this->actingAs(User::factory()->create(['role' => 'owner']))
            ->post(route('reservations.add-payment', $reservation), [
                'payment_type' => 'pelunasan',
                'payment_method' => 'cash',
                'amount' => 950000,
                'payment_date' => '2026-11-09',
            ]);

        $txn = Transaction::where('reservation_id', $reservation->id)->firstOrFail();

        // 11-09 02:30 masih periode bisnis 11-08 (cutoff 06:00)
        $this->assertSame('2026-11-09 02:30:23', $txn->created_at->toDateTimeString());
        $this->assertTrue($txn->created_at->gte(Carbon::parse('2026-11-08 06:00:00')));
        $this->assertTrue($txn->created_at->lt(Carbon::parse('2026-11-09 06:00:00')));
    }
}
