<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Enums\AuditStatus;
use LaravelVitals\Enums\Device;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\BackendTelemetry;
use LaravelVitals\Models\Url;
use LaravelVitals\Support\Correlation;

beforeEach(function (): void {
    $this->url = Url::create(['label' => 'home', 'path' => '/']);
});

it('splits LCP into TTFB and render components', function (): void {
    $audit = Audit::create([
        'id'      => Str::uuid()->toString(),
        'url_id'  => $this->url->id,
        'driver'  => 'stub',
        'device'  => Device::Mobile,
        'status'  => AuditStatus::Completed,
        'lcp_ms'  => 3000.0,
        'ttfb_ms' => 1800.0,
    ]);

    $breakdown = Correlation::lcpBreakdown($audit);

    expect($breakdown['lcp_ms'])->toBe(3000.0)
        ->and($breakdown['ttfb_ms'])->toBe(1800.0)
        ->and($breakdown['render_ms'])->toBe(1200.0)
        ->and($breakdown['ttfb_share'])->toBe(60.0);
});

it('flags backend-bound when TTFB share >= 50%', function (): void {
    $audit = Audit::create([
        'id' => Str::uuid()->toString(), 'url_id' => $this->url->id,
        'driver' => 'stub', 'device' => Device::Mobile, 'status' => AuditStatus::Completed,
        'lcp_ms' => 3000.0, 'ttfb_ms' => 1800.0,
    ]);

    expect(Correlation::isBackendBound($audit))->toBeTrue();

    $audit2 = Audit::create([
        'id' => Str::uuid()->toString(), 'url_id' => $this->url->id,
        'driver' => 'stub', 'device' => Device::Mobile, 'status' => AuditStatus::Completed,
        'lcp_ms' => 3000.0, 'ttfb_ms' => 800.0,
    ]);

    expect(Correlation::isBackendBound($audit2))->toBeFalse();
});

it('estimates LCP gain from query fix when N+1 suspected', function (): void {
    $audit = Audit::create([
        'id' => Str::uuid()->toString(), 'url_id' => $this->url->id,
        'driver' => 'stub', 'device' => Device::Mobile, 'status' => AuditStatus::Completed,
    ]);

    $telemetry = BackendTelemetry::create([
        'audit_id' => $audit->id, 'http_status' => 200,
        'duration_ms' => 1000, 'memory_peak_kb' => 1000,
        'queries_count' => 87, 'queries_time_ms' => 1500.0, 'queries_unique' => 4,
        'n_plus_one_suspect' => true,
        'views_rendered' => 0, 'views_time_ms' => 0,
        'jobs_dispatched' => 0, 'events_fired' => 0,
        'cache_hits' => 0, 'cache_misses' => 0,
    ]);

    expect(Correlation::estimatedLcpGainFromQueryFix($telemetry))->toBe(1050); // 1500 * 0.7
});

it('returns null estimated gain when no N+1 and no slow queries', function (): void {
    $audit = Audit::create([
        'id' => Str::uuid()->toString(), 'url_id' => $this->url->id,
        'driver' => 'stub', 'device' => Device::Mobile, 'status' => AuditStatus::Completed,
    ]);

    $telemetry = BackendTelemetry::create([
        'audit_id' => $audit->id, 'http_status' => 200,
        'duration_ms' => 100, 'memory_peak_kb' => 1000,
        'queries_count' => 5, 'queries_time_ms' => 30.0, 'queries_unique' => 5,
        'n_plus_one_suspect' => false,
        'views_rendered' => 0, 'views_time_ms' => 0,
        'jobs_dispatched' => 0, 'events_fired' => 0,
        'cache_hits' => 0, 'cache_misses' => 0,
        'slow_queries' => [],
    ]);

    expect(Correlation::estimatedLcpGainFromQueryFix($telemetry))->toBeNull();
});
