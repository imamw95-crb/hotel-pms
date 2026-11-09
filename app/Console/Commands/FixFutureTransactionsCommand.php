<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixFutureTransactionsCommand extends Command
{
    protected $signature = 'payments:future-fix {--fix : Terapkan perbaikan (default hanya menampilkan)}';

    protected $description = 'Geser transaksi bertanggal masa depan ke waktu diterima (sekarang) agar masuk night audit periode berjalan';

    public function handle(): int
    {
        $fix = (bool) $this->option('fix');

        $rows = Transaction::where('created_at', '>', now())
            ->orderBy('created_at')
            ->get(['id', 'transaction_number', 'reservation_id', 'created_at', 'amount', 'type', 'payment_method']);

        $this->info(($fix ? 'MODE FIX' : 'DRY RUN').' — transaksi bertanggal masa depan: '.$rows->count());

        foreach ($rows as $txn) {
            $this->line(sprintf(
                '  #%d %s res=%s %s %s %s',
                $txn->id,
                $txn->transaction_number,
                $txn->reservation_id,
                $txn->created_at->format('d/m/Y H:i'),
                $this->money((float) $txn->amount),
                $txn->type
            ));
        }

        if (! $fix || $rows->isEmpty()) {
            if (! $fix && $rows->isNotEmpty()) {
                $this->line('Jalankan: php artisan payments:future-fix --fix');
            }

            return self::SUCCESS;
        }

        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupFile = $backupDir.'/future-txn-'.date('Ymd-His').'.json';
        file_put_contents($backupFile, json_encode($rows, JSON_PRETTY_PRINT));
        $this->info('Backup: '.$backupFile);

        $now = now();
        DB::transaction(function () use ($rows, $now) {
            foreach ($rows as $txn) {
                DB::table('transactions')->where('id', $txn->id)->update([
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });

        $this->info("Selesai — {$rows->count()} transaksi digeser ke ".$now->format('d/m/Y H:i'));

        return self::SUCCESS;
    }

    private function money(float $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}
