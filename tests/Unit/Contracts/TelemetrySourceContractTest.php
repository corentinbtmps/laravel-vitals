<?php

declare(strict_types=1);

use LaravelVitals\Contracts\TelemetrySource;
use LaravelVitals\Telemetry\TrendStats;

it('declares the TelemetrySource contract', function (): void {
    $r = new ReflectionClass(TelemetrySource::class);

    expect($r->isInterface())->toBeTrue()
        ->and($r->hasMethod('isAvailable'))->toBeTrue()
        ->and($r->hasMethod('getTrendsFor'))->toBeTrue();

    $isAvailable = $r->getMethod('isAvailable');
    expect($isAvailable->getReturnType()?->__toString())->toBe('bool');

    $getTrends = $r->getMethod('getTrendsFor');
    expect($getTrends->getReturnType()?->__toString())->toBe(\LaravelVitals\Telemetry\TrendStats::class);
});

it('produces a TrendStats with named accessors', function (): void {
    $stats = new TrendStats(p50Ttfb: 200.0, p95Ttfb: 800.0, p50Lcp: 1500.0, p95Lcp: 3000.0, sampleCount: 1234);

    expect($stats->p50Ttfb)->toBe(200.0)
        ->and($stats->p95Ttfb)->toBe(800.0)
        ->and($stats->p50Lcp)->toBe(1500.0)
        ->and($stats->p95Lcp)->toBe(3000.0)
        ->and($stats->sampleCount)->toBe(1234);
});

it('exposes an empty() factory for sources with no data', function (): void {
    expect(TrendStats::empty()->sampleCount)->toBe(0);
});
