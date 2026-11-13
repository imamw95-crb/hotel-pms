<?php

namespace Tests\Feature;

use App\Models\NightAuditLog;
use App\Models\PaymentMethod;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\User;
use App\Services\NightAuditLockService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NightAuditLockGuardTest extends TestCase
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

    public function test_business_date_follows_cutoff(): void
    {
        $this->assertSame('2026-11-07', NightAuditLockService::businessDateOf(Carbon::parse('2026-11-08 02:30:00')));
        $this->assertSame('2026-11-08', NightAuditLockService::businessDateOf(Carbon::parse('2026-11-08 06:00:00')));
    }

    public function test_transaction_business_date_follows_cutoff(): void
    {
        $reservation = Reservation::factory()->create();

        $beforeCutoff = Transaction::factory()->create([
            'reservation_id' => $reservation->id,
            'created_at' => Carbon::parse('2026-11-14 01:02:02'),
        ]);
        $afterCutoff = Transaction::factory()->create([
            'reservation_id' => $reservation->id,
            'created_at' => Carbon::parse('2026-11-14 08:15:00'),
        ]);

        // Transaksi 01:02 milik periode bisnis 13/11, bukan 14/11
        $this->assertSame('2026-11-13', $beforeCutoff->businessDate()->format('Y-m-d'));
        $this->assertSame('01:02:02', $beforeCutoff->businessDate()->format('H:i:s'));
        $this->assertSame('2026-11-14', $afterCutoff->businessDate()->format('Y-m-d'));
    }

    public function test_add_payment_in_locked_period_is_recorded_as_correction(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-09 10:00:00'));

        NightAuditLog::create([
            'audit_date' => '2026-11-07',
            'status' => 'locked',
            'locked_at' => Carbon::parse('2026-11-08 05:34:00'),
        ]);

        $reservation = Reservation::factory()->create(['total_amount' => 637000, 'paid_amount' => 0]);

        $this->actingAs(User::factory()->create(['role' => 'owner']))
            ->post(route('reservations.add-payment', $reservation), [
                'payment_type' => 'dp',
                'payment_method' => 'cash',
                'amount' => 200000,
                'payment_date' => '2026-11-07',
            ]);

        $txn = Transaction::where('reservation_id', $reservation->id)->firstOrFail();

        // Dicatat di periode berjalan, bukan di periode yang sudah locked
        $this->assertSame('2026-11-09', $txn->created_at->format('Y-m-d'));
        $this->assertStringContainsString('[Koreksi NA 2026-11-07]', (string) $txn->notes);
        $this->assertSame(200000.0, (float) $reservation->fresh()->paid_amount);
    }

    public function test_edit_payment_in_locked_period_creates_delta_correction(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-09 10:00:00'));

        NightAuditLog::create([
            'audit_date' => '2026-11-05',
            'status' => 'locked',
            'locked_at' => Carbon::parse('2026-11-06 05:00:00'),
        ]);

        $reservation = Reservation::factory()->create(['total_amount' => 637000, 'paid_amount' => 445000]);
        $original = Transaction::factory()->create([
            'reservation_id' => $reservation->id,
            'type' => 'dp',
            'amount' => 445000,
            'payment_method' => 'cash',
            'created_at' => Carbon::parse('2026-11-05 14:00:00'),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'owner']))
            ->put(route('transactions.edit-payment', $original->id), [
                'payment_type' => 'dp',
                'payment_method' => 'cash',
                'amount' => 500000,
                'payment_date' => '2026-11-05',
            ]);

        // Baris lama tidak berubah (laporan locked tetap sama)
        $this->assertSame(445000.0, (float) $original->fresh()->amount);

        $correction = Transaction::where('reservation_id', $reservation->id)
            ->where('type', 'koreksi')
            ->firstOrFail();

        $this->assertSame(55000.0, (float) $correction->amount);
        $this->assertSame(500000.0, (float) $reservation->fresh()->paid_amount);
    }

    public function test_edit_payment_date_change_in_locked_period_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-09 10:00:00'));

        NightAuditLog::create([
            'audit_date' => '2026-11-05',
            'status' => 'locked',
            'locked_at' => Carbon::parse('2026-11-06 05:00:00'),
        ]);

        $reservation = Reservation::factory()->create(['total_amount' => 637000, 'paid_amount' => 445000]);
        $original = Transaction::factory()->create([
            'reservation_id' => $reservation->id,
            'type' => 'dp',
            'amount' => 445000,
            'payment_method' => 'cash',
            'created_at' => Carbon::parse('2026-11-05 14:00:00'),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'owner']))
            ->put(route('transactions.edit-payment', $original->id), [
                'payment_type' => 'dp',
                'payment_method' => 'cash',
                'amount' => 445000,
                'payment_date' => '2026-11-06',
            ])
            ->assertSessionHas('error');

        // Tanggal transaksi tidak berpindah dan tidak ada koreksi yang tercatat
        $this->assertSame('2026-11-05 14:00:00', $original->fresh()->created_at->format('Y-m-d H:i:s'));
        $this->assertSame(0, Transaction::where('reservation_id', $reservation->id)->where('type', 'koreksi')->count());
    }

    public function test_edit_payment_cannot_move_date_into_locked_period(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-09 10:00:00'));

        NightAuditLog::create([
            'audit_date' => '2026-11-05',
            'status' => 'locked',
            'locked_at' => Carbon::parse('2026-11-06 05:00:00'),
        ]);

        $reservation = Reservation::factory()->create(['total_amount' => 637000, 'paid_amount' => 445000]);
        $original = Transaction::factory()->create([
            'reservation_id' => $reservation->id,
            'type' => 'dp',
            'amount' => 445000,
            'payment_method' => 'cash',
            'created_at' => Carbon::parse('2026-11-09 09:00:00'),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'owner']))
            ->put(route('transactions.edit-payment', $original->id), [
                'payment_type' => 'dp',
                'payment_method' => 'cash',
                'amount' => 445000,
                'payment_date' => '2026-11-05',
            ])
            ->assertSessionHas('error');

        $this->assertSame('2026-11-09 09:00:00', $original->fresh()->created_at->format('Y-m-d H:i:s'));
    }

    public function test_edit_payment_with_same_business_date_keeps_timestamp(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-14 02:00:00'));

        $reservation = Reservation::factory()->create(['total_amount' => 587000, 'paid_amount' => 587000]);
        $original = Transaction::factory()->create([
            'reservation_id' => $reservation->id,
            'type' => 'pelunasan',
            'amount' => 587000,
            'payment_method' => 'cash',
            'created_at' => Carbon::parse('2026-11-14 01:02:02'),
        ]);

        // Tanggal bisnis yang tampil di UI (13/11) disimpan ulang tanpa perubahan
        $this->actingAs(User::factory()->create(['role' => 'owner']))
            ->put(route('transactions.edit-payment', $original->id), [
                'payment_type' => 'pelunasan',
                'payment_method' => 'cash',
                'amount' => 587000,
                'payment_date' => '2026-11-13',
            ])
            ->assertSessionHas('success');

        $this->assertSame('2026-11-14 01:02:02', $original->fresh()->created_at->format('Y-m-d H:i:s'));
        $this->assertSame(587000.0, (float) $reservation->fresh()->paid_amount);
    }

    public function test_delete_payment_in_locked_period_creates_negative_reversal(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-09 10:00:00'));

        NightAuditLog::create([
            'audit_date' => '2026-11-05',
            'status' => 'locked',
            'locked_at' => Carbon::parse('2026-11-06 05:00:00'),
        ]);

        $reservation = Reservation::factory()->create(['total_amount' => 637000, 'paid_amount' => 445000]);
        $original = Transaction::factory()->create([
            'reservation_id' => $reservation->id,
            'type' => 'dp',
            'amount' => 445000,
            'payment_method' => 'cash',
            'created_at' => Carbon::parse('2026-11-05 14:00:00'),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'owner']))
            ->delete(route('transactions.delete-payment', $original->id));

        // Baris asli tetap ada
        $this->assertDatabaseHas('transactions', ['id' => $original->id]);

        $reversal = Transaction::where('reservation_id', $reservation->id)
            ->where('type', 'koreksi')
            ->firstOrFail();

        $this->assertSame(-445000.0, (float) $reversal->amount);
        $this->assertSame(0.0, (float) $reservation->fresh()->paid_amount);
    }

    public function test_repeated_delete_in_locked_period_does_not_duplicate_reversal(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-09 10:00:00'));

        NightAuditLog::create([
            'audit_date' => '2026-11-05',
            'status' => 'locked',
            'locked_at' => Carbon::parse('2026-11-06 05:00:00'),
        ]);

        $reservation = Reservation::factory()->create(['total_amount' => 637000, 'paid_amount' => 445000]);
        $original = Transaction::factory()->create([
            'reservation_id' => $reservation->id,
            'type' => 'dp',
            'amount' => 445000,
            'payment_method' => 'cash',
            'created_at' => Carbon::parse('2026-11-05 14:00:00'),
        ]);

        $user = User::factory()->create(['role' => 'owner']);

        // Baris asli tetap tampil setelah dibatalkan, jadi user bisa menekan hapus berkali-kali
        $this->actingAs($user)->delete(route('transactions.delete-payment', $original->id));
        $this->actingAs($user)->delete(route('transactions.delete-payment', $original->id));
        $this->actingAs($user)->delete(route('transactions.delete-payment', $original->id));

        $this->assertSame(1, Transaction::where('reservation_id', $reservation->id)
            ->where('type', 'koreksi')
            ->count());
        $this->assertSame(0.0, (float) $reservation->fresh()->paid_amount);
    }
}
