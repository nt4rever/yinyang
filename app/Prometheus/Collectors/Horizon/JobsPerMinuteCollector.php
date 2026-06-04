<?php

namespace App\Prometheus\Collectors\Horizon;

use App\Prometheus\Collectors\Collector;
use App\Prometheus\Facades\Prometheus;
use Laravel\Horizon\Contracts\MetricsRepository;

class JobsPerMinuteCollector implements Collector
{
    public function register(): void
    {
        Prometheus::getOrRegisterGauge(
            config('prometheus.default_namespace'),
            'horizon_jobs_per_minute',
            'The number of jobs per minute',
        )->set(app(MetricsRepository::class)->jobsProcessedPerMinute());
    }
}
