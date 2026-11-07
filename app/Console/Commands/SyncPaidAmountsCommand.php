<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncPaidAmountsCommand extends Command
{
    protected $signature = 'payments:sync
        {--fix : Terapkan perbaikan (tanpa opsi ini hanya menampilkan)}
        {--id= : Batasi ke reservasi tertentu, pisahkan dengan koma}';

    protected $description = 'Samakan paid_amount reservasi dengan total transaksi pembayaran (Riwayat Pembayaran)';

    /**
     * Rekonsiliasi paid_amount vs ledger transaksi.
     *
     * Sumber kebenaran = SUM(transactions.amount) tanpa tipe additional/adjustment
     * (service charge & jejak ubah tanggal bukan pembayaran tagihan kamar).
     *
     * Kasus yang diperbaiki otomatis hanya "uang sudah masuk tapi paid_amount
     * tertinggal" (recorded > paid dan recorded <= total tagihan). Kasus lain
     * hanya dilaporkan karena butuh keputusan manual.
     */
    public function handle(): int
    {
        $fix = (bool) $this->option('fix');
        $ids = array_values(array_filter(array_map('intval', explode(',', (string) $this->option('id')))));

        $query = Reservation::query()->orderBy('id');
        if ($ids !== []) {
            $query->whereIn('id', $ids);
        }

        $fixable = [];
        $manual = [];

        foreach ($query->get() as $reservation) {
            $recorded = $reservation->recordedPaidAmount();
            $paid = (float) $reservation->paid_amount;
            $total = (float) $reservation->total_amount;

            if ($recorded <= 0 || abs($paid - $recorded) < 0.01) {
                continue;
            }

            if ($recorded > $total) {
                $manual[] = [$reservation, $recorded, $total];

                continue;
            }

            $fixable[] = [$reservation, $recorded, $total];
        }

        $this->info(($fix ? 'MODE FIX' : 'DRY RUN').' — siap diperbaiki: '.count($fixable).', perlu cek manual: '.count($manual));

        foreach ($fixable as [$reservation, $recorded, $total]) {
            $this->line(sprintf(
                '  FIX  #%d %-18s %-11s total=%s paid: %s -> %s',
                $reservation->id,
                $reservation->reservation_number,
                $reservation->status,
                $this->money($total),
                $this->money((float) $reservation->paid_amount),
                $this->money($recorded)
            ));
        }

        foreach ($manual as [$reservation, $recorded, $total]) {
            $this->warn(sprintf(
                '  SKIP #%d %-18s %-11s total=%s paid=%s transaksi=%s (transaksi > total tagihan)',
                $reservation->id,
                $reservation->reservation_number,
                $reservation->status,
                $this->money($total),
                $this->money((float) $reservation->paid_amount),
                $this->money($recorded)
            ));
        }

        if (! $fix) {
            if ($fixable !== []) {
                $this->line('Jalankan: php artisan payments:sync --fix');
            }

            return self::SUCCESS;
        }

        if ($fixable === []) {
            return self::SUCCESS;
        }

        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupFile = $backupDir.'/paid-sync-'.date('Ymd-His').'.json';
        $backup = array_map(fn ($row) => [
            'id' => $row[0]->id,
            'reservation_number' => $row[0]->reservation_number,
            'old_paid_amount' => (float) $row[0]->paid_amount,
            'new_paid_amount' => $row[1],
            'total_amount' => $row[2],
        ], $fixable);
        file_put_contents($backupFile, json_encode($backup, JSON_PRETTY_PRINT));

        $this->info('Backup: '.$backupFile);

        $updated = 0;

        DB::transaction(function () use ($fixable, &$updated) {
            foreach ($fixable as [$reservation, $recorded, $total]) {
                DB::table('reservations')->where('id', $reservation->id)->update([
                    'paid_amount' => $recorded,
                    'paid_date' => $recorded >= $total
                        ? ($reservation->paid_date ?: DB::table('transactions')
                            ->where('reservation_id', $reservation->id)
                            ->max('created_at'))
                        : null,
                ]);

                $updated++;
            }
        });

        $this->info("Selesai — {$updated} reservasi disamakan dengan Riwayat Pembayaran.");

        return self::SUCCESS;
    }

    private function money(float $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}
