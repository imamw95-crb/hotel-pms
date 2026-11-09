<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGuardTest extends TestCase
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

    public function test_same_submit_token_is_recorded_once(): void
    {
        $reservation = Reservation::factory()->create(['total_amount' => 637000, 'paid_amount' => 0]);
        $owner = User::factory()->create(['role' => 'owner']);

        $payload = [
            'payment_type' => 'dp',
            'payment_method' => 'cash',
            'amount' => 300000,
            'payment_date' => now()->format('Y-m-d'),
            'submit_token' => 'token-double-submit',
        ];

        $this->actingAs($owner)->post(route('reservations.add-payment', $reservation), $payload);
        $this->actingAs($owner)->post(route('reservations.add-payment', $reservation), $payload);

        $this->assertSame(1, Transaction::where('reservation_id', $reservation->id)->count());
        $this->assertSame(300000.0, (float) $reservation->fresh()->paid_amount);
    }

    public function test_cancel_with_refund_records_negative_transaction(): void
    {
        $reservation = Reservation::factory()->create(['total_amount' => 637000, 'paid_amount' => 445000]);
        Transaction::factory()->create([
            'reservation_id' => $reservation->id,
            'type' => 'dp',
            'amount' => 445000,
            'payment_method' => 'cash',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'owner']))
            ->post(route('reservations.cancel', $reservation), ['confirm_refund' => 1]);

        $refund = Transaction::where('reservation_id', $reservation->id)
            ->where('type', 'refund')
            ->firstOrFail();

        $this->assertSame(-445000.0, (float) $refund->amount);
        $this->assertSame(0.0, (float) $reservation->fresh()->paid_amount);
        $this->assertSame('cancelled', $reservation->fresh()->status);
    }

    public function test_future_fix_command_moves_future_dated_transactions(): void
    {
        $reservation = Reservation::factory()->create(['total_amount' => 637000, 'paid_amount' => 0]);

        $future = Transaction::factory()->create([
            'reservation_id' => $reservation->id,
            'type' => 'pelunasan',
            'amount' => 637000,
            'created_at' => now()->addDays(10),
        ]);

        $this->artisan('payments:future-fix --fix')->assertSuccessful();

        $this->assertTrue($future->fresh()->created_at->lessThanOrEqualTo(now()));
    }

    public function test_audit_command_runs_and_reports_drift(): void
    {
        $reservation = Reservation::factory()->create(['total_amount' => 637000, 'paid_amount' => 1000]);
        Transaction::factory()->create([
            'reservation_id' => $reservation->id,
            'type' => 'pelunasan',
            'amount' => 637000,
        ]);

        $cancelled = Reservation::factory()->cancelled()->create(['total_amount' => 500000, 'paid_amount' => 0]);
        Transaction::factory()->create([
            'reservation_id' => $cancelled->id,
            'type' => 'dp',
            'amount' => 200000,
        ]);

        $this->artisan('payments:audit')
            ->expectsOutputToContain('paid_amount != ledger')
            ->assertSuccessful();
    }
}
