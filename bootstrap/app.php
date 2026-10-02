<?php

use App\Http\Middleware\ApiKeyMiddleware;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'api.key' => ApiKeyMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        // ─── Scheduler Heartbeat ───────────────────────────────────
        // Monitoring: kalau timestamp ini berhenti update → cron mati.
        $schedule->command('scheduler:heartbeat')
            ->everyMinute();

        // ─── OTA Email Autopilot ───────────────────────────────────
        $schedule->command('hotel:read-emails')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/ota-autopilot.log'));

        // ─── Auto-Cancel Pending Web Bookings ──────────────────────
        $schedule->command('hotel:auto-cancel-pending')
            ->everyTenMinutes()
            ->withoutOverlapping(15)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/auto-cancel-pending.log'));

        // ─── OTS Proof Upgrader (blockchain Bitcoin) ───────────────
        // Konfirmasi proof OpenTimestamps ke blockchain Bitcoin.
        // WAJIB ada di sini: schedule() di App\Console\Kernel TIDAK dipakai
        // lagi sejak bootstrap/app.php pakai withSchedule() (Laravel 11+).
        $schedule->command('ots:upgrade --limit=100 --retry-failed')
            ->everyTenMinutes()
            ->withoutOverlapping(10)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/ots-upgrade.log'));
    })->create();
