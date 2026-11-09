<?php

namespace App\Console\Commands;

use App\Models\NightAuditLog;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Services\PaymentDateService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AuditPaymentsCommand extends Command
{
    protected $signature = 'payments:audit';

    protected $description = 'Audit harian pembayaran: selisih paid_amount, transaksi bertanggal masa depan, dan perubahan setelah NA di-lock';

    public function handle(): int
    {
        $lines = [];
        $issues = 0;

        // ─── 1. paid_amount vs ledger ─────────────────────────────
        $sumSub = "(select coalesce(sum(t.amount), 0) from transactions t where t.reservation_id = r.id and t.type not in ('additional', 'adjustment'))";
        $rows = DB::select("select r.id, r.reservation_number, r.paid_amount, r.total_amount, {$sumSub} as recorded from reservations r");

        $drift = [];
        foreach ($rows as $row) {
            if (abs((float) $row->paid_amount - (float) $row->recorded) < 0.01) {
                continue;
            }
            $drift[] = $row;
        }

        if ($drift !== []) {
            $issues += count($drift);
            $lines[] = 'paid_amount != ledger: '.count($drift).' reservasi (jalankan payments:sync)';
            foreach ($drift as $row) {
                $lines[] = sprintf(
                    '  #%d %s paid=%s ledger=%s',
                    $row->id,
                    $row->reservation_number,
                    number_format((float) $row->paid_amount, 0, ',', '.'),
                    number_format((float) $row->recorded, 0, ',', '.')
                );
            }
        }

        // ─── 2. transaksi bertanggal masa depan ───────────────────
        $future = Transaction::where('created_at', '>', now())->orderBy('created_at')->get();
        if ($future->isNotEmpty()) {
            $issues += $future->count();
            $lines[] = 'transaksi bertanggal masa depan: '.$future->count().' (jalankan payments:future-fix)';
            foreach ($future as $txn) {
                $lines[] = sprintf(
                    '  %s res=%s %s %s',
                    $txn->transaction_number,
                    $txn->reservation_id,
                    $txn->created_at->format('d/m/Y H:i'),
                    number_format((float) $txn->amount, 0, ',', '.')
                );
            }
        }

        // ─── 3. perubahan setelah NA periode tersebut di-lock ─────
        $cutoff = PaymentDateService::cutoffTime();
        foreach (NightAuditLog::where('status', 'locked')->whereNotNull('locked_at')->get() as $log) {
            $date = Carbon::parse($log->audit_date)->format('Y-m-d');
            $start = $date.' '.$cutoff;
            $end = Carbon::parse($date)->addDay()->format('Y-m-d').' '.$cutoff;

            $changed = Transaction::where('created_at', '>=', $start)
                ->where('created_at', '<', $end)
                ->where('updated_at', '>', $log->locked_at)
                ->count();

            if ($changed > 0) {
                $issues += $changed;
                $lines[] = sprintf(
                    'NA %s sudah di-lock %s, tapi ada %d transaksi periode itu yang berubah sesudahnya',
                    $date,
                    Carbon::parse($log->locked_at)->format('d/m/Y H:i'),
                    $changed
                );
            }
        }

        // ─── 4. reservasi batal yang masih punya uang masuk ───────
        $cancelled = Reservation::where('status', 'cancelled')
            ->whereRaw("(select coalesce(sum(t.amount), 0) from transactions t where t.reservation_id = reservations.id and t.type not in ('additional', 'adjustment')) > 0")
            ->get(['id', 'reservation_number']);

        if ($cancelled->isNotEmpty()) {
            $issues += $cancelled->count();
            $lines[] = 'reservasi cancelled tapi belum tercatat refund: '.$cancelled->count();
            foreach ($cancelled as $row) {
                $lines[] = sprintf('  #%d %s', $row->id, $row->reservation_number);
            }
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
}
