<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\Reservation;
use App\Models\Transaction;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;

class OtaPaymentService
{
    /**
     * Catat nominal yang sudah dibayar OTA sebagai transaksi pembayaran hotel.
     *
     * OTA (Tiket.com / Traveloka / dll) menagih tamu lebih dulu, jadi nominal
     * OTA harus:
     *   1. muncul di "Riwayat Pembayaran" (tabel transactions)
     *   2. ikut menambah paid_amount reservasi
     *
     * Idempotent — hanya membuat transaksi sebesar SELISIH yang belum tercatat,
     * jadi aman dipanggil berulang kali (sync email, update nomor OTA, dsb).
     *
     * @param  CarbonInterface|null  $at  Tanggal bisnis transaksi (default: sekarang).
     *                                    Dipakai saat backfill data lama supaya pendapatan
     *                                    masuk ke periode yang benar, bukan menumpuk hari ini.
     * @return Transaction|null Transaksi baru, atau null kalau tidak ada yang perlu dicatat
     */
    public function record(Reservation $reservation, float $otaPaidAmount, ?string $paymentMethod = null, ?CarbonInterface $at = null): ?Transaction
    {
        if ($otaPaidAmount <= 0) {
            return null;
        }

        $delta = round($otaPaidAmount - $this->recordedAmount($reservation), 2);

        if ($delta <= 0) {
            return null;
        }

        $method = $this->resolvePaymentMethod($paymentMethod, $reservation->ota_source);
        $total = (float) $reservation->total_amount;
        $paidAt = $at ?? now();

        // NB: created_at/updated_at TIDAK ada di $fillable, jadi harus di-set langsung
        // (mass assignment akan diabaikan dan Eloquent memakai waktu sekarang).
        $transaction = new Transaction([
            'transaction_number' => 'TRX-'.strtoupper(uniqid()),
            'reservation_id' => $reservation->id,
            'type' => $otaPaidAmount >= $total && $total > 0 ? 'pelunasan' : 'dp',
            'amount' => $delta,
            'payment_method' => $method,
            'source_type' => 'ota',
            'notes' => 'Pembayaran OTA '.$method.' — '.str_replace('_', ' ', $reservation->ota_payment_status ?? 'paid ota').' (auto dari sync OTA)',
            'created_by' => auth()->id() ?? 1,
        ]);
        $transaction->created_at = $paidAt;
        $transaction->updated_at = $paidAt;
        $transaction->save();

        $reservation->paid_amount = (float) $reservation->paid_amount + $delta;

        if ($total > 0 && (float) $reservation->paid_amount >= $total && ! $reservation->paid_date) {
            $reservation->paid_date = $paidAt;
        }

        $reservation->save();

        Log::info('OtaPayment: OTA payment recorded', [
            'reservation_id' => $reservation->id,
            'reservation_number' => $reservation->reservation_number,
            'amount' => $delta,
            'payment_method' => $method,
            'business_date' => $paidAt->toDateTimeString(),
            'paid_amount' => (float) $reservation->paid_amount,
        ]);

        return $transaction;
    }

    /**
     * Total nominal OTA yang sudah tercatat sebagai transaksi.
     */
    public function recordedAmount(Reservation $reservation): float
    {
        return (float) Transaction::where('reservation_id', $reservation->id)
            ->where('source_type', 'ota')
            ->whereIn('type', ['dp', 'pelunasan', 'ota_payment'])
            ->sum('amount');
    }

    /**
     * Tentukan slug PaymentMethod yang dipakai untuk pembayaran OTA.
     */
    private function resolvePaymentMethod(?string $paymentMethod, ?string $otaSource): string
    {
        $candidates = array_filter([
            $paymentMethod,
            $otaSource,
            $otaSource ? 'ota_'.str_replace(['.', '-'], '_', $otaSource) : null,
            'ota_payment',
        ]);

        foreach ($candidates as $slug) {
            if (PaymentMethod::where('slug', $slug)->exists()) {
                return $slug;
            }
        }

        return 'ota_payment';
    }
}
