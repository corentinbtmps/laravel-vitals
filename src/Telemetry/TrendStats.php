<?php

declare(strict_types=1);

namespace LaravelVitals\Telemetry;

/**
 * Aggregated real-traffic metrics for a single route.
 *
 * Returned by TelemetrySource implementations so the recommendation builder
 * can compare synthetic Lighthouse measurements against real-world data.
 */
final readonly class TrendStats
{
    public function __construct(
        public ?float $p50Ttfb,
        public ?float $p95Ttfb,
        public ?float $p50Lcp,
        public ?float $p95Lcp,
        public int $sampleCount,
    ) {
    }

    public static function empty(): self
    {
        return new self(null, null, null, null, 0);
    }
}
