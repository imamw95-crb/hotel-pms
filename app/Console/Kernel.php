<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     */
    protected $commands = [
        Commands\ReadHotelEmailsCommand::class,
        Commands\TestReadOneEmailCommand::class,
        Commands\TestOtaEmailCommand::class,
        Commands\AiReservationCommand::class,
        Commands\AutoCancelPendingBookingCommand::class,
        Commands\BlockMigrateFreshCommand::class,
        Commands\BlockMigrateResetCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ─── Scheduler Heartbeat ───────────────────────────────────
        // Writes a timestamp every minute so monitoring can detect
        // if the Laravel scheduler (cron) is actually running.
        // If this stops updating → cron job is dead.
        $schedule->call(function () {
            $heartbeatFile = storage_path('logs/scheduler-heartbeat.log');
            $dir = dirname($heartbeatFile);
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            file_put_contents(
                $heartbeatFile,
                now()->format('Y-m-d H:i:s').PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
            // Keep only last ~100 lines to prevent unbounded growth
            $lines = file($heartbeatFile);
            if (count($lines) > 100) {
                file_put_contents($heartbeatFile, implode('', array_slice($lines, -100)));
            }
        })->everyMinute();

        // ─── OTA Email Autopilot ──────────────────────────────────
        // Check for new OTA emails every 5 minutes
        // and auto-sync to reservations
        $schedule->command('hotel:read-emails --limit=5')
            ->everyFiveMinutes()
            ->withoutOverlapping(30) // lock expires after 30 minutes as fallback
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/ota-autopilot.log'));

        // ─── Auto-Cancel Pending Web Bookings ─────────────────────
        // Cancel website bookings that haven't confirmed payment
        // within the configured time window (default: 3 hours)
        $schedule->command('hotel:auto-cancel-pending')
            ->everyTenMinutes()
            ->withoutOverlapping(15)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/auto-cancel-pending.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
