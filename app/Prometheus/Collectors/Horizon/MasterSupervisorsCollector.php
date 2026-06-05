<?php

namespace App\Prometheus\Collectors\Horizon;

use App\Prometheus\Collectors\Collector;
use App\Prometheus\Facades\Prometheus;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;

class MasterSupervisorsCollector implements Collector
{
    public function register(): void
    {
        Prometheus::getOrRegisterGauge(
            config('prometheus.default_namespace'),
            'horizon_master_supervisors',
            'The number of master supervisors',
        )->set(count(app(MasterSupervisorRepository::class)->all()));
    }
}
