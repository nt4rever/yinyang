<?php

namespace App\Providers;

use App\Prometheus\Adapters\LaravelCacheAdapter;
use App\Prometheus\PrometheusMetricsRegistrar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Adapter;
use Prometheus\Storage\InMemory;

class PrometheusServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->scoped(CollectorRegistry::class, function () {
            return new CollectorRegistry(
                $this->buildStorageAdapter(config('prometheus.cache')),
                false
            );
        });

        $this->app->singleton(PrometheusMetricsRegistrar::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    protected function buildStorageAdapter(?string $adapter): Adapter
    {
        if ($adapter === null) {
            return new InMemory;
        }

        return new LaravelCacheAdapter(Cache::resolve($adapter));
    }
}
