<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        // Fix for older MySQL / MariaDB versions with small index length limits
        // Ensures default VARCHAR(255) indexed columns (like unique email) don't exceed key length.
        Schema::defaultStringLength(191);

    }
}
