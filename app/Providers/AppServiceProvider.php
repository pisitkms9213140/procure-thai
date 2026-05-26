<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Force HTTPS when behind Cloudflare (Flexible SSL mode).
        // Cloudflare → Origin is plain HTTP, so Laravel thinks the request
        // is HTTP and generates http:// URLs → Mixed Content errors.
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
