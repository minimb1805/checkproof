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
        define('RECORDS_PER_PAGE', 5);
        define('APP_ADMIN_EMAIL', 'youremail@example.com');
    }
}
