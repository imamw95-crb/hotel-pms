<?php

namespace App\Providers;

use App\Models\NightAuditLog;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        // Only force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Share night audit status with all views
        View::composer('*', function ($view) {
            $nightAudit = NightAuditLog::latest()->first();
            $view->with('nightAuditClosed', $nightAudit && $nightAudit->status === 'completed' && $nightAudit->audit_date === now()->subDay()->toDateString());
        });
    }
}
