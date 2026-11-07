<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaidAmountSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create(['id' => 1]);
    }

    /**
     * Kasus live: 2 pembayaran (445.000 + 192.000) tapi paid_amount hanya 387.000.
     */
    public function test_paid_amount_matches_payment_history(): void
    {
        $reservation = Reservation::factory()->create([
            'total_amount' => 637000,
            'paid_amount' => 387000,
        ]);

        $this->addTransaction($reservation, 'dp', 445000);
        $this->addTransaction($reservation, 'pelunasan', 192000);

        $this->assertSame(637000.0, $reservation->recordedPaidAmount());

        $reservation->syncPaidAmount();
        $reservation->save();

        $this->assertSame(637000.0, (float) $reservation->fresh()->paid_amount);
        $this->assertSame(0.0, (float) $reservation->fresh()->remaining_payment);
    }

    public function test_service_charge_and_adjustment_are_not_bill_payments(): void
    {
        $reservation = Reservation::factory()->create(['total_amount' => 637000, 'paid_amount' => 0]);

        $this->addTransaction($reservation, 'pelunasan', 637000);
        $this->addTransaction($reservation, 'additional', 250000);
        $this->addTransaction($reservation, 'adjustment', 0);

        $this->assertSame(637000.0, $reservation->recordedPaidAmount());
    }

    public function test_legacy_paid_amount_without_transactions_is_preserved(): void
    {
        $reservation = Reservation::factory()->create(['total_amount' => 600000, 'paid_amount' => 500000]);

        $this->assertSame(500000.0, $reservation->unrecordedPaidAmount());

        $legacyPaid = $reservation->unrecordedPaidAmount();
        $this->addTransaction($reservation, 'pelunasan', 100000);

        $reservation->syncPaidAmount($legacyPaid);
        $reservation->save();

        $this->assertSame(600000.0, (float) $reservation->fresh()->paid_amount);
    }

    public function test_command_fixes_drifted_paid_amount(): void
    {
        $drifted = Reservation::factory()->create(['total_amount' => 637000, 'paid_amount' => 387000]);
        $this->addTransaction($drifted, 'dp', 445000);
        $this->addTransaction($drifted, 'pelunasan', 192000);

        $overTotal = Reservation::factory()->create(['total_amount' => 100000, 'paid_amount' => 100000]);
        $this->addTransaction($overTotal, 'pelunasan', 150000);

        $this->artisan('payments:sync --fix')->assertSuccessful();

        $this->assertSame(637000.0, (float) $drifted->fresh()->paid_amount);
        $this->assertNotNull($drifted->fresh()->paid_date);

        // Transaksi melebihi total tagihan → dibiarkan untuk cek manual
        $this->assertSame(100000.0, (float) $overTotal->fresh()->paid_amount);
    }

    public function test_command_dry_run_does_not_change_data(): void
    {
        $reservation = Reservation::factory()->create(['total_amount' => 637000, 'paid_amount' => 387000]);
        $this->addTransaction($reservation, 'pelunasan', 637000);

        $this->artisan('payments:sync')->assertSuccessful();

        $this->assertSame(387000.0, (float) $reservation->fresh()->paid_amount);
    }

    public function test_add_payment_keeps_paid_amount_in_sync_with_history(): void
    {
        $this->setUpPaymentMethod();

        $reservation = Reservation::factory()->create(['total_amount' => 637000, 'paid_amount' => 0]);
        $this->addTransaction($reservation, 'dp', 445000);

        // paid_amount sengaja dibuat tertinggal dari ledger
        $reservation->update(['paid_amount' => 100000]);

        $this->actingAs($this->owner())
            ->post(route('reservations.add-payment', $reservation), [
                'payment_type' => 'pelunasan',
                'payment_method' => 'cash',
                'amount' => 192000,
                'payment_date' => now()->format('Y-m-d'),
            ]);

        $fresh = $reservation->fresh();
        $this->assertSame(637000.0, (float) $fresh->paid_amount);
        $this->assertSame(637000.0, $fresh->recordedPaidAmount());
    }

    public function test_add_payment_rejected_when_ledger_already_covers_the_bill(): void
    {
        $this->setUpPaymentMethod();

        // Kondisi live: 2 transaksi (637.000) tapi paid_amount hanya 387.000
        $reservation = Reservation::factory()->create(['total_amount' => 637000, 'paid_amount' => 387000]);
        $this->addTransaction($reservation, 'dp', 445000);
        $this->addTransaction($reservation, 'pelunasan', 192000);

        $response = $this->actingAs($this->owner())
            ->postJson(route('reservations.add-payment', $reservation), [
                'payment_type' => 'pelunasan',
                'payment_method' => 'cash',
                'amount' => 250000,
            ]);

        $response->assertOk();
        $response->assertJson(['success' => false]);

        // Tidak ada pembayaran baru yang tercatat
        $this->assertSame(2, $reservation->transactions()->count());
        $this->assertSame(637000.0, $reservation->fresh()->recordedPaidAmount());
    }

    private function setUpPaymentMethod(): void
    {
        \App\Models\PaymentMethod::firstOrCreate(['slug' => 'cash'], [
            'name' => 'Cash',
            'slug' => 'cash',
            'source_type' => 'tunai',
            'is_active' => true,
        ]);
    }

    private function owner(): User
    {
        return User::factory()->create(['role' => 'owner']);
    }

    private function addTransaction(Reservation $reservation, string $type, float $amount): Transaction
    {
        return Transaction::factory()->create([
            'reservation_id' => $reservation->id,
            'type' => $type,
            'amount' => $amount,
        ]);
    }
}
