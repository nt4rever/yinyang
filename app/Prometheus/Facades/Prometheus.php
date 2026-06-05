<?php

namespace App\Prometheus\Facades;

use App\Prometheus\Collectors\Collector;
use Illuminate\Support\Facades\Facade;
use Prometheus\CollectorRegistry;

class Prometheus extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CollectorRegistry::class;
    }

    public static function registerCollectorClasses(array $collectors, array $constructorParameters = []): void
    {
        collect($collectors)
            ->map(fn (string $collectorClass) => empty($constructorParameters)
                ? app($collectorClass)
                : new $collectorClass(...$constructorParameters)
            )
            ->each(fn (Collector $collector) => $collector->register());
    }
}
