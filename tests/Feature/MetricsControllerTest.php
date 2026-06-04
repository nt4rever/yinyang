<?php

namespace Tests\Feature;

use Prometheus\RenderTextFormat;
use Tests\TestCase;

class MetricsControllerTest extends TestCase
{
    public function test_metrics_returns_ok_with_prometheus_content_type(): void
    {
        $response = $this->get('/metrics');

        $response->assertOk();
        $this->assertStringStartsWith(
            RenderTextFormat::MIME_TYPE,
            (string) $response->headers->get('Content-Type'),
        );
    }

    public function test_metrics_includes_request_count_after_tracked_request(): void
    {
        $this->get('/');

        $response = $this->get('/metrics');

        $response->assertOk();
        $this->assertStringContainsString('app_request_count', $response->getContent());
        $this->assertStringContainsString('app_request_duration_seconds', $response->getContent());
        $this->assertStringContainsString('app_horizon_recent_jobs', $response->getContent());
        $this->assertStringContainsString('app_horizon_master_supervisors', $response->getContent());
        $this->assertStringContainsString('app_horizon_current_processes', $response->getContent());
        $this->assertStringContainsString('app_horizon_current_workload', $response->getContent());
        $this->assertStringContainsString('app_horizon_failed_jobs_per_hour', $response->getContent());
        $this->assertStringContainsString('app_horizon_failed_recent_jobs', $response->getContent());
        $this->assertStringContainsString('app_horizon_status', $response->getContent());
        $this->assertStringContainsString('app_horizon_jobs_per_minute', $response->getContent());
        $this->assertStringContainsString('app_queue_delayed_jobs', $response->getContent());
        $this->assertStringContainsString('app_queue_oldest_pending_job_age', $response->getContent());
        $this->assertStringContainsString('app_queue_pending_jobs', $response->getContent());
        $this->assertStringContainsString('app_queue_reserved_jobs', $response->getContent());
        $this->assertStringContainsString('app_queue_size', $response->getContent());
    }
}
