<?php

namespace App\Prometheus\Collectors\Horizon;

use App\Prometheus\Collectors\Collector;
use App\Prometheus\Facades\Prometheus;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;

class HorizonStatusCollector implements Collector
{
    protected const INACTIVE = -1;

    protected const PAUSED = 0;

    protected const RUNNING = 1;

    public function register(): void
    {
        Prometheus::getOrRegisterGauge(
            config('prometheus.default_namespace'),
            'horizon_status',
            'The status of Horizon, -1 = inactive, 0 = paused, 1 = running',
        )->set($this->status());
    }

    private function status(): int
    {
        if (! $masters = app(MasterSupervisorRepository::class)->all()) {
            return self::INACTIVE;
        }

        $isPaused = collect($masters)
            ->contains(fn ($master) => $master->status === 'paused');

        return $isPaused
            ? self::PAUSED
            : self::RUNNING;
    }
}
