<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = config('app.supported_locales');
        $fallbackLocale = config('app.fallback_locale');
        $locale = $request->header('lang');

        if (! in_array($locale, $supportedLocales)) {
            $locale = $fallbackLocale;
        }

        App::setLocale($locale);

        return $next($request);
    }
}
