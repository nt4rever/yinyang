<?php

namespace App\Prometheus;

use App\Prometheus\Collectors\Horizon\CurrentProcessesCollector;
use App\Prometheus\Collectors\Horizon\CurrentWorkloadCollector;
use App\Prometheus\Collectors\Horizon\FailedJobsPerHourCollector;
use App\Prometheus\Collectors\Horizon\FailedRecentJobsCollector;
use App\Prometheus\Collectors\Horizon\HorizonStatusCollector;
use App\Prometheus\Collectors\Horizon\JobsPerMinuteCollector;
use App\Prometheus\Collectors\Horizon\MasterSupervisorsCollector;
use App\Prometheus\Collectors\Horizon\RecentJobsCollector;
use App\Prometheus\Collectors\Queue\QueueDelayedJobsCollector;
use App\Prometheus\Collectors\Queue\QueueOldestPendingJobCollector;
use App\Prometheus\Collectors\Queue\QueuePendingJobsCollector;
use App\Prometheus\Collectors\Queue\QueueReservedJobsCollector;
use App\Prometheus\Collectors\Queue\QueueSizeCollector;
use App\Prometheus\Facades\Prometheus;

class PrometheusMetricsRegistrar
{
    public function registerHorizonMetrics(): void
    {
        Prometheus::registerCollectorClasses([
            CurrentProcessesCollector::class,
            CurrentWorkloadCollector::class,
            FailedJobsPerHourCollector::class,
            FailedRecentJobsCollector::class,
            JobsPerMinuteCollector::class,
            MasterSupervisorsCollector::class,
            RecentJobsCollector::class,
            HorizonStatusCollector::class,
        ]);
    }

    /**
     * @param  array<int, string>  $queues
     */
    public function registerQueueCollectors(array $queues = [], ?string $connection = null): void
    {
        Prometheus::registerCollectorClasses([
            QueueDelayedJobsCollector::class,
            QueueOldestPendingJobCollector::class,
            QueuePendingJobsCollector::class,
            QueueReservedJobsCollector::class,
            QueueSizeCollector::class,
        ], compact('connection', 'queues'));
    }
}
