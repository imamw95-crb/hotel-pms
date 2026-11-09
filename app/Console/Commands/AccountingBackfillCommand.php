<?php

namespace App\Console\Commands;

use App\Models\Deposit;
use App\Models\Expense;
use App\Models\RestoTransaction;
use App\Models\ServiceCharge;
use App\Models\Transaction;
use App\Services\AccountingPostingService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Membuat jurnal akuntansi untuk transaksi lama yang belum punya jurnal.
 * Idempotent: transaksi yang sudah punya jurnal hanya diperbarui isinya.
 */
class AccountingBackfillCommand extends Command
{
    protected $signature = 'accounting:backfill
        {--from= : Tanggal awal (Y-m-d)}
        {--to= : Tanggal akhir (Y-m-d)}
        {--chunk=200 : Jumlah record per batch}';

    protected $description = 'Buat jurnal akuntansi untuk transaksi operasional yang belum tercatat';

    public function handle(AccountingPostingService $posting): int
    {
        $from = $this->option('from');
        $to = $this->option('to');
        $chunk = max(50, (int) $this->option('chunk'));

        $sources = [
            'Pembayaran reservasi' => [Transaction::query(), 'created_at'],
            'Pendapatan resto' => [RestoTransaction::query(), 'created_at'],
            'Other revenue' => [ServiceCharge::query(), 'charge_date'],
            'Pengeluaran' => [Expense::query(), 'expense_date'],
            'Deposit tamu' => [Deposit::query(), 'created_at'],
        ];

        $created = 0;
        $failed = 0;

        foreach ($sources as $label => [$query, $column]) {
            $count = 0;

            /** @var Builder $query */
            $query->when($from, fn (Builder $q) => $q->whereDate($column, '>=', Carbon::parse($from)->toDateString()))
                ->when($to, fn (Builder $q) => $q->whereDate($column, '<=', Carbon::parse($to)->toDateString()))
                ->orderBy('id')
                ->chunkById($chunk, function ($records) use ($posting, &$count, &$failed) {
                    foreach ($records as $record) {
                        try {
                            if ($posting->syncModel($record)) {
                                $count++;
                            }
                        } catch (\Throwable $e) {
                            $failed++;
                            $this->warn('  ! '.$record::class.'#'.$record->getKey().': '.$e->getMessage());
                        }
                    }
                });

            $created += $count;
            $this->info("{$label}: {$count} jurnal tercatat");
        }

        $this->info("Selesai — total {$created} jurnal, gagal {$failed}.");

        return self::SUCCESS;
    }
}
