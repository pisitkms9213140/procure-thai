<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

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
        // is HTTP and generates http:// URLs → Mixed Content errors (broken
        // assets, and Livewire file-upload previews stuck on "Waiting for size").
        // Driven by APP_URL scheme so it works regardless of APP_ENV.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Allow InitializeTenancyByDomain to pass through on central domains
        // instead of throwing TenantCouldNotBeIdentifiedException.
        InitializeTenancyByDomain::$onFail = function ($exception, $request, $next) {
            if (in_array($request->getHost(), config('tenancy.central_domains', []))) {
                return $next($request);
            }
            throw $exception;
        };

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
