<?php

namespace App\Http\Middleware;

use App\Prometheus\Prom;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CountRequestMetric
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is(config('prometheus.ignored_routes'))) {
            return $response;
        }

        $this->registerRequestMetric($request, $response);

        return $response;
    }

    private function registerRequestMetric(Request $request, Response $response): void
    {
        Prom::getOrRegisterCounter(
            config('prometheus.default_namespace'),
            'request_count',
            'Total number of HTTP requests',
            ['method', 'route', 'status'],
        )->inc([
            $request->method(),
            $request->route()?->uri() ?? 'unknown',
            (string) $response->getStatusCode(),
        ]);
    }
}
