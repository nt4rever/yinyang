<?php

namespace App\Prometheus\Collectors\Horizon;

use App\Prometheus\Collectors\Collector;
use App\Prometheus\Facades\Prometheus;
use Laravel\Horizon\Contracts\WorkloadRepository;

class CurrentWorkloadCollector implements Collector
{
    public function register(): void
    {
        foreach ($this->workloads() as $workload) {
            Prometheus::getOrRegisterGauge(
                config('prometheus.default_namespace'),
                'horizon_current_workload',
                'Current workload of all queues',
                ['queue'],
            )->set($workload['length'], [$workload['name']]);
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
