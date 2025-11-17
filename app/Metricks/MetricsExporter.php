<?php

namespace App\Metricks;

use Prometheus\CollectorRegistry;

class MetricsExporter
{
    public function __construct(private CollectorRegistry $registry)
    {
    }

    public function incrementHttpRequest(string $method, string $route, string $status): void
    {
        $counter = $this->registry->getOrRegisterCounter(
            'app',
            'http_requests_total',
            'Total HTTP requests',
            ['method', 'route', 'status']
        );
        $counter->inc([$method, $route, $status]);
    }

    public function observeHttpDuration(string $method, string $route, float $duration): void
    {
        $histogram = $this->registry->getOrRegisterHistogram(
            'app',
            'http_request_duration_seconds',
            'Request duration in seconds',
            ['method', 'route'],
            [0.005, 0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5, 10]
        );
        $histogram->observe($duration, [$method, $route]);
    }

    public function render(): string
    {
        $renderer = new \Prometheus\RenderTextFormat();
        return $renderer->render($this->registry->getMetricFamilySamples());
    }
}
