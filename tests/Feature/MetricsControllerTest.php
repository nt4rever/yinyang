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
    }
}
