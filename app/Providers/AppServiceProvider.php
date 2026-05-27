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

        // Apply stored locale (TH ↔ EN toggle).
        // Wrapped in try/catch so it doesn't break CLI/artisan where no session exists.
        try {
            $locale = request()->hasSession()
                ? (request()->session()->get('locale', config('app.locale', 'th')))
                : config('app.locale', 'th');
            app()->setLocale($locale);
        } catch (\Throwable) {
            // session not started yet — locale stays at app default
        }
    }
}
