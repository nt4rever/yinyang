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

        try {
            $response = $next($request);
            $durationInSeconds = (hrtime(true) - $startTime) / 1e9;

            if (! $request->is(config('prometheus.ignored_routes'))) {
                $this->registerRequestMetric($request, $response, $durationInSeconds);
            }

            return $response;
        } catch (Throwable $e) {
            $durationInSeconds = (hrtime(true) - $startTime) / 1e9;

            if (! $request->is(config('prometheus.ignored_routes'))) {
                $this->registerFailedRequestMetric($request, $durationInSeconds);
            }

            throw $e;
        }
    }

    private function registerRequestMetric(Request $request, Response $response, float $durationInSeconds): void
    {
        $this->recordMetrics($request, (string) $response->getStatusCode(), $durationInSeconds);
    }

    private function registerFailedRequestMetric(Request $request, float $durationInSeconds): void
    {
        $this->recordMetrics($request, '500', $durationInSeconds);
    }

    private function recordMetrics(Request $request, string $statusCode, float $durationInSeconds): void
    {
        $labels = [
            $request->method(),
            $request->route()?->uri() ?? 'unknown',
            $statusCode,
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
