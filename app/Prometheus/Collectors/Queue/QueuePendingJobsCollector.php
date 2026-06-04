<?php

namespace App\Prometheus\Collectors\Queue;

use App\Prometheus\Collectors\Collector;
use App\Prometheus\Facades\Prometheus;
use Exception;
use Illuminate\Contracts\Queue\Factory;

class QueuePendingJobsCollector implements Collector
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
                $pendingSize = method_exists($queueConnection, 'pendingSize')
                    ? $queueConnection->pendingSize($queueName)
                    : 0;

                Prometheus::getOrRegisterGauge(
                    config('prometheus.default_namespace'),
                    'queue_pending_jobs',
                    'The number of pending jobs in the queue',
                    ['connection', 'queue'],
                )->set($pendingSize, [$this->connection, $queueName]);
            } catch (Exception) {
                continue;
            }
        }
    }
}
