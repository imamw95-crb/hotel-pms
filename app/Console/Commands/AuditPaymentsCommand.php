<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AuditPaymentsCommand extends Command
{
    protected $signature = 'payments:audit';

    protected $description = 'Audit harian pembayaran: selisih paid_amount, transaksi bertanggal masa depan, dan koreksi periode NA yang sudah di-lock';

    public function handle(): int
    {
        $lines = [];
        $issues = 0;

        // ─── 1. paid_amount vs ledger ─────────────────────────────
        // Sumber kebenaran = SUM(transaksi pembayaran); additional/adjustment
        // bukan pembayaran tagihan kamar.
        $sumSub = "(select coalesce(sum(t.amount), 0) from transactions t where t.reservation_id = r.id and t.type not in ('additional', 'adjustment'))";
        $rows = DB::select("select r.id, r.reservation_number, r.status, r.paid_amount, r.total_amount, {$sumSub} as recorded from reservations r");

        $fixable = [];
        $manual = [];
        foreach ($rows as $row) {
            $paid = (float) $row->paid_amount;
            $recorded = (float) $row->recorded;
            $total = (float) $row->total_amount;

            if (abs($paid - $recorded) < 0.01 || $recorded <= 0) {
                continue;
            }

            if ($recorded > $paid && $recorded <= $total && $row->status !== 'cancelled') {
                $fixable[] = $row;
            } else {
                $manual[] = $row;
            }
        }

        if ($fixable !== []) {
            $issues += count($fixable);
            $lines[] = 'paid_amount < ledger (bisa diperbaiki): '.count($fixable).' → jalankan: php artisan payments:sync --fix';
            foreach (array_slice($fixable, 0, 10) as $row) {
                $lines[] = sprintf(
                    '  #%d %s paid=%s ledger=%s',
                    $row->id,
                    $row->reservation_number,
                    $this->money((float) $row->paid_amount),
                    $this->money((float) $row->recorded)
                );
            }
        }

        if ($manual !== []) {
            $issues += count($manual);
            $lines[] = 'paid_amount vs ledger perlu cek manual: '.count($manual).' (paid > ledger / transaksi > total / cancelled)';
            foreach (array_slice($manual, 0, 10) as $row) {
                $lines[] = sprintf(
                    '  #%d %s %s paid=%s ledger=%s total=%s',
                    $row->id,
                    $row->reservation_number,
                    $row->status,
                    $this->money((float) $row->paid_amount),
                    $this->money((float) $row->recorded),
                    $this->money((float) $row->total_amount)
                );
            }
        }

        // ─── 2. transaksi bertanggal masa depan ───────────────────
        $future = Transaction::where('created_at', '>', now())->orderBy('created_at')->get();
        if ($future->isNotEmpty()) {
            $issues += $future->count();
            $lines[] = 'transaksi bertanggal masa depan: '.$future->count().' → jalankan: php artisan payments:future-fix --fix';
            foreach ($future as $txn) {
                $lines[] = sprintf(
                    '  %s res=%s %s %s',
                    $txn->transaction_number,
                    $txn->reservation_id,
                    $txn->created_at->format('d/m/Y H:i'),
                    $this->money((float) $txn->amount)
                );
            }
        }

        // ─── 3. koreksi untuk periode NA yang sudah di-lock ───────
        // Koreksi sengaja dicatat sebagai transaksi baru di periode berjalan
        // dengan penanda di notes, jadi laporan locked tidak berubah.
        $corrections = Transaction::where('notes', 'like', '[Koreksi NA %')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        if ($corrections > 0) {
            $lines[] = 'koreksi pembayaran periode locked (24 jam terakhir): '.$corrections.' transaksi';
        }

        // ─── 4. reservasi batal yang belum punya transaksi refund ─
        $cancelled = Reservation::where('status', 'cancelled')
            ->whereRaw("(select coalesce(sum(t.amount), 0) from transactions t where t.reservation_id = reservations.id and t.type not in ('additional', 'adjustment')) > 0")
            ->count();

        if ($cancelled > 0) {
            $lines[] = 'reservasi cancelled tanpa transaksi refund: '.$cancelled.' (informasi historis)';
        }

        // ─── Output ───────────────────────────────────────────────
        $header = '['.now()->format('Y-m-d H:i:s').'] payments:audit — '.($issues === 0 ? 'OK, tidak ada selisih' : $issues.' temuan');
        $this->info($header);
        foreach ($lines as $line) {
            $this->line($line);
        }

        File::ensureDirectoryExists(storage_path('logs'));
        File::append(storage_path('logs/payments-audit.log'), $header.PHP_EOL.($lines ? implode(PHP_EOL, $lines).PHP_EOL : '').PHP_EOL);

        return self::SUCCESS;
    }

    private function money(float $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}
