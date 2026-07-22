<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\InvoiceTimestamp;
use App\Repositories\InvoiceTimestampRepository;
use App\Services\OpenTimestampService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Upgrade semua proof OTS yang masih pending ke blockchain Bitcoin.
 *
 * Cara pakai:
 *   php artisan ots:upgrade                # Upgrade semua pending
 *   php artisan ots:upgrade --limit=10     # Upgrade 10 pending saja
 *   php artisan ots:upgrade --retry-failed # Coba ulang yang gagal
 *   php artisan ots:upgrade --dry-run      # Lihat daftar tanpa mengeksekusi
 *
 * Jadwalkan di scheduler (Kernel.php):
 *   $schedule->command('ots:upgrade --limit=20')
 *       ->hourly()
 *       ->withoutOverlapping(60)
 *       ->appendOutputTo(storage_path('logs/ots-upgrade.log'));
 */
class OtsUpgradeCommand extends Command
{
    protected $signature = 'ots:upgrade
        {--limit=50 : Jumlah maksimal proof yang di-upgrade per eksekusi}
        {--retry-failed : Sertakan juga yang gagal untuk dicoba ulang}
        {--dry-run : Hitung jumlah tanpa mengeksekusi upgrade}';

    protected $description = 'Upgrade OpenTimestamps proof ke blockchain Bitcoin';

    public function __construct(
        private readonly OpenTimestampService $otsService,
        private readonly InvoiceTimestampRepository $repository,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $retryFailed = (bool) $this->option('retry-failed');
        $dryRun = (bool) $this->option('dry-run');

        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║     OTS Proof Upgrader                  ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();

        // Ambil pending timestamps
        $pending = $this->repository->getPendingTimestamps($limit);
        $total = $pending->count();

        $failed = collect();
        if ($retryFailed) {
            $failed = $this->repository->getFailedTimestamps(10);
            $total += $failed->count();
        }

        if ($total === 0) {
            $this->info('✅ Tidak ada timestamp yang perlu di-upgrade.');

            return self::SUCCESS;
        }

        $this->line("Ditemukan {$total} timestamp untuk diproses:");
        $this->line("  - Pending   : {$pending->count()}");
        if ($retryFailed) {
            $this->line("  - Failed    : {$failed->count()}");
        }
        $this->newLine();

        if ($dryRun) {
            $this->table(
                ['ID', 'Invoice Type', 'Invoice ID', 'Revision', 'SHA-256', 'Created'],
                $pending->merge($failed)->map(fn (InvoiceTimestamp $t) => [
                    $t->id,
                    $t->invoice_type,
                    $t->invoice_id,
                    $t->revision,
                    substr($t->sha256, 0, 16) . '...',
                    $t->created_at?->format('d/m/Y H:i') ?? '-',
                ])->toArray()
            );

            $this->newLine();
            $this->info('═══════════════════════════════════════');
            $this->info('  ✅ DRY RUN — Tidak ada perubahan data');
            $this->info('═══════════════════════════════════════');

            return self::SUCCESS;
        }

        // Proses upgrade
        $success = 0;
        $failedCount = 0;

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        foreach ($pending as $timestamp) {
            try {
                $result = $this->otsService->upgradeProof($timestamp);

                if ($result['success']) {
                    $success++;
                    Log::info('OTS: Upgrade success via cron', [
                        'id' => $timestamp->id,
                        'sha256' => $timestamp->sha256,
                    ]);
                } else {
                    $failedCount++;
                    $this->markAsFailed($timestamp, $result['message']);
                    Log::warning('OTS: Upgrade failed via cron', [
                        'id' => $timestamp->id,
                        'message' => $result['message'],
                    ]);
                }
            } catch (\Exception $e) {
                $failedCount++;
                $this->markAsFailed($timestamp, $e->getMessage());
                Log::error('OTS: Upgrade exception via cron', [
                    'id' => $timestamp->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $progressBar->advance();
        }

        // Proses failed retry
        foreach ($failed as $timestamp) {
            try {
                $result = $this->otsService->upgradeProof($timestamp);

                if ($result['success']) {
                    $success++;
                } else {
                    $failedCount++;
                }
            } catch (\Exception $e) {
                $failedCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║  SUMMARY                                ║');
        $this->info('╠══════════════════════════════════════════╣');
        $this->info("║  Total diproses : {$total}");
        $this->info("║  Success        : {$success}");
        $this->info("║  Failed         : {$failedCount}");
        $this->info('╚══════════════════════════════════════════╝');

        if ($failedCount > 0) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Tandai timestamp sebagai failed.
     */
    private function markAsFailed(InvoiceTimestamp $timestamp, string $reason): void
    {
        $this->repository->updateStatus(
            $timestamp,
            InvoiceTimestamp::STATUS_FAILED,
        );

        Log::error('OTS: Timestamp marked as failed', [
            'id' => $timestamp->id,
            'sha256' => $timestamp->sha256,
            'reason' => $reason,
        ]);
    }
}
