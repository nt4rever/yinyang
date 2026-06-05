<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MetricsAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('prometheus.metrics_token');

        if ($token === null) {
            return $next($request);
        }

        if ($request->bearerToken() === $token) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }
}
