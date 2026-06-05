<?php

namespace App\Http\Controllers;

use App\Http\Middleware\MetricsAuth;
use App\Prometheus\Facades\Prometheus;
use App\Prometheus\PrometheusMetricsRegistrar;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Prometheus\RenderTextFormat;

#[Middleware(MetricsAuth::class)]
class MetricsController extends Controller
{
    public function __construct(
        private PrometheusMetricsRegistrar $metricsRegistrar,
    ) {}

    public function __invoke(Request $request)
    {
        $this->metricsRegistrar->registerHorizonMetrics();
        $this->metricsRegistrar->registerQueueCollectors(['default']);
        $result = (new RenderTextFormat)->render(Prometheus::getMetricFamilySamples());

        return response(
            $result,
            headers: ['Content-Type' => RenderTextFormat::MIME_TYPE]
        );
    }
}
