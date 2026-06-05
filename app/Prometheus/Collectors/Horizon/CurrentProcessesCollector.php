<?php

namespace App\Prometheus\Collectors\Horizon;

use App\Prometheus\Collectors\Collector;
use App\Prometheus\Facades\Prometheus;
use Laravel\Horizon\Contracts\WorkloadRepository;

class CurrentProcessesCollector implements Collector
{
    public function register(): void
    {
        foreach ($this->workloads() as $workload) {
            Prometheus::getOrRegisterGauge(
                config('prometheus.default_namespace'),
                'horizon_current_processes',
                'Current processes of all queues',
                ['queue'],
            )->set($workload['processes'], [$workload['name']]);
        }
    }

    /**
     * @return array<int, array{name: string, length: int, wait: int, processes: int, split_queues: null|array<int, array{name: string, wait: int, length: int}>}>
     */
    private function workloads(): array
    {
        return collect(app(WorkloadRepository::class)->get())
            ->sortBy('name')
            ->values()
            ->all();
    }
}
