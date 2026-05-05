<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\BackendTelemetry;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;

it('prunes audits older than the retention window and cascades to children', function (): void {
    config()->set('vitals.retention.days', 30);

    $url = Url::create(['label' => 'home', 'path' => '/']);

    $oldAudit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'local',
        'device' => 'mobile',
        'status' => 'completed',
    ]);
    Recommendation::create([
        'audit_id' => $oldAudit->id, 'source' => 'lighthouse', 'audit_key' => 'k',
        'category' => 'performance', 'severity' => 'info',
        'title_key' => 't', 'description_key' => 'd',
    ]);
    BackendTelemetry::create([
        'audit_id' => $oldAudit->id, 'http_status' => 200, 'duration_ms' => 1, 'memory_peak_kb' => 1,
        'queries_count' => 0, 'queries_time_ms' => 0, 'queries_unique' => 0,
        'views_rendered' => 0, 'views_time_ms' => 0, 'jobs_dispatched' => 0,
        'events_fired' => 0, 'cache_hits' => 0, 'cache_misses' => 0,
    ]);

    // Backdate the audit (and dependent rows) past the retention window.
    Carbon::setTestNow(now()->addDays(31));

    $exitCode = $this->artisan('model:prune', [
        '--model' => [Audit::class, Recommendation::class, BackendTelemetry::class],
    ])->run();

    expect($exitCode)->toBe(0)
        ->and(Audit::count())->toBe(0)
        ->and(Recommendation::count())->toBe(0)
        ->and(BackendTelemetry::count())->toBe(0);

    Carbon::setTestNow();
});
