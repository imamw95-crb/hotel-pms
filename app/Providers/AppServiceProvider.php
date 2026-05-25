<?php

namespace App\Providers;

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
        // Ensure helper functions are always loaded
        if (file_exists($helper = app_path('Helpers/PermissionHelper.php'))) {
            require_once $helper;
        }
    }
}
