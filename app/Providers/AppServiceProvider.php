<?php

namespace App\Providers;

use App\Models\NightAuditLog;
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
        // Ensure helper functions are always loaded
        if (file_exists($helper = app_path('Helpers/PermissionHelper.php'))) {
            require_once $helper;
        }

        // Share Night Audit v2 status with the main layout
        View::composer('layouts.app', function ($view) {
            $today = now()->format('Y-m-d');
            $locked = NightAuditLog::where('audit_date', $today)
                ->where('status', 'locked')
                ->exists();

            $view->with('nightAuditPending', !$locked);
        });
    }
}
