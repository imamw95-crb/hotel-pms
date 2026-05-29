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
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ─── OTA Email Autopilot ──────────────────────────────────
        // Check for new OTA emails every 2 minutes
        // and auto-sync to reservations
        $schedule->command('hotel:read-emails --limit=5')
            ->everyTwoMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/ota-autopilot.log'));

        // ─── Alternative: every minute (more responsive) ──────────
        // Uncomment below and comment above for 1-minute interval
        // $schedule->command('hotel:read-emails --limit=5')
        //     ->everyMinute()
        //     ->withoutOverlapping()
        //     ->runInBackground()
        //     ->appendOutputTo(storage_path('logs/ota-autopilot.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
