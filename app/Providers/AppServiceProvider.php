<?php

namespace App\Providers;

use App\Models\NightAuditLog;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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

        // Share night audit status with all views
        View::composer('*', function ($view) {
            $nightAudit = NightAuditLog::latest()->first();
            $view->with('nightAuditClosed', $nightAudit && $nightAudit->status === 'completed' && $nightAudit->audit_date === now()->subDay()->toDateString());
            $view->with('nightAuditPending', ! $nightAudit || $nightAudit->audit_date->toDateString() !== now()->toDateString() || $nightAudit->status !== 'locked');
        });
    }
}
