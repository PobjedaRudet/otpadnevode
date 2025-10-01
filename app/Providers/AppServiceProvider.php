<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;

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

        // Blade component alias for auth layout
        Blade::component('layouts.auth', 'auth-layout');

    }
}
