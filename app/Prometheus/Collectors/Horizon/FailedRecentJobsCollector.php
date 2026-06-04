<?php

namespace App\Prometheus\Collectors\Horizon;

use App\Prometheus\Collectors\Collector;
use App\Prometheus\Facades\Prometheus;
use Laravel\Horizon\Contracts\JobRepository;

class FailedRecentJobsCollector implements Collector
{
    public function register(): void
    {
        Prometheus::getOrRegisterGauge(
            config('prometheus.default_namespace'),
            'horizon_failed_recent_jobs',
            'The number of recently failed jobs',
        )->set(app(JobRepository::class)->countRecentlyFailed());
    }
}
