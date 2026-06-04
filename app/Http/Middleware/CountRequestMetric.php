<?php

namespace App\Http\Middleware;

use App\Prometheus\Facades\Prometheus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CountRequestMetric
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = hrtime(true);
        $response = $next($request);
        $durationInSeconds = (hrtime(true) - $startTime) / 1e9;

        if ($request->is(config('prometheus.ignored_routes'))) {
            return $response;
        }

        $this->registerRequestMetric($request, $response, $durationInSeconds);

        return $response;
    }

    private function registerRequestMetric(Request $request, Response $response, float $durationInSeconds): void
    {
        $labels = [
            $request->method(),
            $request->route()?->uri() ?? 'unknown',
            (string) $response->getStatusCode(),
        ];

        try {
            Prometheus::getOrRegisterCounter(
                config('prometheus.default_namespace'),
                'request_count',
                'Total number of HTTP requests',
                ['method', 'route', 'status'],
            )->inc($labels);

            Prometheus::getOrRegisterHistogram(
                config('prometheus.default_namespace'),
                'request_duration_seconds',
                'HTTP request duration in seconds',
                ['method', 'route', 'status'],
            )->observe($durationInSeconds, $labels);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
