<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply inside tenant context
        if (!tenancy()->initialized) {
            return $next($request);
        }

        if (tenant('onboarding_completed')) {
            return $next($request);
        }

        $path = $request->path();

        // Exempt paths that must remain accessible during onboarding
        $exempt = [
            'onboarding',  // the wizard itself
            'logout',
            'livewire',
            'filament',    // Filament assets/API
        ];

        foreach ($exempt as $keyword) {
            if (str_contains($path, $keyword)) {
                return $next($request);
            }
        }

        return redirect('/app/onboarding');
    }
}
