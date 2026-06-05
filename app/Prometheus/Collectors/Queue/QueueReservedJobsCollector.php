<?php

namespace App\Prometheus\Collectors\Queue;

use App\Prometheus\Collectors\Collector;
use App\Prometheus\Facades\Prometheus;
use Exception;
use Illuminate\Contracts\Queue\Factory;

class QueueReservedJobsCollector implements Collector
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
                $reservedSize = method_exists($queueConnection, 'reservedSize')
                    ? $queueConnection->reservedSize($queueName)
                    : 0;

                Prometheus::getOrRegisterGauge(
                    config('prometheus.default_namespace'),
                    'queue_reserved_jobs',
                    'The number of reserved jobs in the queue',
                    ['connection', 'queue'],
                )->set($reservedSize, [$this->connection, $queueName]);
            } catch (Exception) {
                continue;
            }
        }
    }
}
