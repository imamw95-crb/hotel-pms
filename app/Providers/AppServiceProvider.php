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
        URL::forceScheme('https');

        // Ensure helper functions are always loaded
        if (file_exists( = app_path('Helpers/PermissionHelper.php'))) {
            require_once ;
        }

        // Share night audit status with all views
        View::composer('*', function () {
             = NightAuditLog::latest()->first();
            ->with('nightAuditClosed',  && ->status === 'completed' && ->audit_date === now()->subDay()->toDateString());
        });
    }
}
