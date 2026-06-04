<?php

namespace App\Prometheus\Collectors\Queue;

use App\Prometheus\Collectors\Collector;
use App\Prometheus\Facades\Prometheus;
use Exception;
use Illuminate\Contracts\Queue\Factory;

class QueueOldestPendingJobCollector implements Collector
{
    protected string $connection;

    /** @var array<int, string> */
    protected array $queues;

    /**
     * @param  array<int, string>  $queues
     */
    public function __construct(?string $connection = null, array $queues = [])
    {
        $this->connection = $connection ?? config('queue.default');
        $this->queues = $queues === []
            ? [config("queue.connections.{$this->connection}.queue", 'default')]
            : $queues;
    }

    public function register(): void
    {
        $manager = app(Factory::class);

        foreach ($this->queues as $queueName) {
            try {
                $queueConnection = $manager->connection($this->connection);
                $oldestJobTime = method_exists($queueConnection, 'creationTimeOfOldestPendingJob')
                    ? $queueConnection->creationTimeOfOldestPendingJob($queueName)
                    : null;

                $ageInSeconds = $oldestJobTime === null
                    ? 0
                    : now()->timestamp - $oldestJobTime;

                Prometheus::getOrRegisterGauge(
                    config('prometheus.default_namespace'),
                    'queue_oldest_pending_job_age',
                    'The age of the oldest pending job in the queue (in seconds)',
                    ['connection', 'queue'],
                )->set($ageInSeconds, [$this->connection, $queueName]);
            } catch (Exception) {
                continue;
            }
        }
    }
}
