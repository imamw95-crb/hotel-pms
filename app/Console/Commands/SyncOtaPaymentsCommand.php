<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Models\Transaction;
use App\Services\OtaPaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncOtaPaymentsCommand extends Command
{
    protected $signature = 'ota:sync-payments {--limit=50 : Maksimal reservasi diproses per run}';

    protected $description = 'Pastikan pembayaran OTA (paid_ota/partial_ota) sudah tercatat di Riwayat Pembayaran';

    /**
     * Pengaman (reconciliation) untuk kasus sync OTA yang tidak sempat/terlewat
     * membuat transaksi pembayaran — misalnya saat worker masih memakai kode lama.
     * Idempotent: hanya mencatat selisih yang belum ada.
     */
    public function handle(OtaPaymentService $otaPayments): int
    {
        $limit = (int) $this->option('limit');

        $candidates = Reservation::whereIn('ota_payment_status', ['paid_ota', 'partial_ota'])
            ->where('ota_paid_amount', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->orderBy('id')
            ->get();

        $fixed = 0;
        $skipped = 0;
        $checked = 0;

        foreach ($candidates as $reservation) {
            if ($fixed >= $limit) {
                break;
            }

            $checked++;

            $delta = round((float) $reservation->ota_paid_amount - $otaPayments->alreadyRecorded($reservation), 2);
            if ($delta <= 0) {
                continue;
            }

            // Jangan buat paid_amount melebihi total tagihan (butuh keputusan manual)
            if ((float) $reservation->total_amount > 0
                && (float) $reservation->ota_paid_amount > (float) $reservation->total_amount) {
                $skipped++;
                Log::warning('OTASyncPayments: dilewati, nominal OTA melebihi total tagihan', [
                    'reservation_id' => $reservation->id,
                    'ota_paid_amount' => (float) $reservation->ota_paid_amount,
                    'total_amount' => (float) $reservation->total_amount,
                ]);

                continue;
            }

            // check_in hanya dipakai sebagai tanggal bisnis untuk backfill data lama
            // (check_in sudah lewat). Kalau check_in masih di depan / tamu belum
            // check-in, pakai waktu sekarang supaya nominal yang baru diterima tidak
            // tercatat di periode night audit yang belum berjalan.
            $checkIn = $reservation->check_in;
            $businessAt = ($checkIn && $checkIn->lessThan(now())) ? $checkIn : now();

            $transaction = $otaPayments->record(
                $reservation,
                (float) $reservation->ota_paid_amount,
                $reservation->payment_method,
                $businessAt
            );

            if ($transaction) {
                $fixed++;
                Log::warning('OTASyncPayments: pembayaran OTA tercatat (reconciliation)', [
                    'reservation_id' => $reservation->id,
                    'reservation_number' => $reservation->reservation_number,
                    'amount' => (float) $transaction->amount,
                    'transaction_id' => $transaction->id,
                ]);
                $this->line("  [OK] #{$reservation->id} {$reservation->reservation_number} +".number_format((float) $transaction->amount, 0, ',', '.'));
            }
        }

        $this->info("Cek {$checked} reservasi OTA — dicatat: {$fixed}, dilewati: {$skipped}");

        return self::SUCCESS;
    }
}
