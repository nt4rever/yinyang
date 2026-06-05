<?php

namespace App\Prometheus\Collectors\Horizon;

use App\Prometheus\Collectors\Collector;
use App\Prometheus\Facades\Prometheus;
use Laravel\Horizon\Contracts\JobRepository;

class RecentJobsCollector implements Collector
{
    public function register(): void
    {
        Prometheus::getOrRegisterGauge(
            config('prometheus.default_namespace'),
            'horizon_recent_jobs',
            'The number of recent jobs',
        )->set(app(JobRepository::class)->countRecent());
    }
}
