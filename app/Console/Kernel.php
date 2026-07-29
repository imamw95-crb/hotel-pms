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
        Commands\OtsUpgradeCommand::class,
        Commands\SchedulerHeartbeatCommand::class,
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
        $schedule->command('scheduler:heartbeat')
            ->everyMinute();

        // ─── OTA Email Autopilot ──────────────────────────────
        $schedule->command('hotel:read-emails --limit=5')
            ->everyFiveMinutes()
            ->withoutOverlapping(30)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/ota-autopilot.log'));

        // ─── Auto-Cancel Pending Web Bookings ────────────────
        $schedule->command('hotel:auto-cancel-pending')
            ->everyTenMinutes()
            ->withoutOverlapping(15)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/auto-cancel-pending.log'));

        // ─── OTS Proof Upgrader ──────────────────────────────
        // Upgrade proof OTS yang masih pending ke blockchain Bitcoin
        $schedule->command('ots:upgrade --limit=20')
            ->hourly()
            ->withoutOverlapping(60)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/ots-upgrade.log'));
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
