<?php

namespace App\Providers;

use App\Models\NightAuditLog;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Output\ConsoleOutput;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS when accessed via HTTPS (works regardless of APP_ENV)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            URL::forceScheme('https');
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }

        // Share night audit status only with authenticated views (skip login/logout)
        View::composer(['layouts.app', 'housekeeping.*', 'dashboard.*', 'reservations.*', 'reports.*'], function ($view) {
            $nightAudit = NightAuditLog::latest()->first(['audit_date', 'status']);
            $view->with('nightAuditClosed', $nightAudit && $nightAudit->status === 'completed' && $nightAudit->audit_date === now()->subDay()->toDateString());
            $view->with('nightAuditPending', ! $nightAudit || $nightAudit->audit_date->toDateString() !== now()->toDateString() || $nightAudit->status !== 'locked');
        });

        // Block dangerous migrate commands in production
        $this->blockDangerousMigrateCommands();
    }

    /**
     * Block migrate:fresh, migrate:reset, migrate:refresh in production
     */
    private function blockDangerousMigrateCommands(): void
    {
        if (! app()->isProduction()) {
            return;
        }

        $dangerousCommands = ['migrate:fresh', 'migrate:reset', 'migrate:refresh'];

        Event::listen(function (CommandStarting $event) use ($dangerousCommands) {
            $command = $event->command;

            if (in_array($command, $dangerousCommands)) {
                $output = new ConsoleOutput;
                $output->writeln('<error>❌ BLOCKED: '.strtoupper($command).' cannot be executed in PRODUCTION!</error>');
                $output->writeln('<comment>This command would DELETE ALL DATA.</comment>');
                $output->writeln('<info>To reset database, contact system administrator with backup verification.</info>');

                exit(1);
            }
        });
    }
}
