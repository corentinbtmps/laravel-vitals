<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Jobs\PersistTelemetryJob;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\BackendTelemetry;
use LaravelVitals\Models\Url;
use LaravelVitals\Support\BackendTelemetrySnapshot;

function makeSnapshot(?string $auditId): BackendTelemetrySnapshot
{
    return new BackendTelemetrySnapshot(
        auditId:         $auditId,
        sampledRequest:  $auditId === null,
        routeName:       'home',
        httpStatus:      200,
        durationMs:      245.7,
        memoryPeakKb:    12500,
        queriesCount:    87,
        queriesTimeMs:   142.3,
        queriesUnique:   4,
        nPlusOneSuspect: true,
        viewsRendered:   3,
        viewsTimeMs:     18.4,
        jobsDispatched:  0,
        eventsFired:     12,
        cacheHits:       24,
        cacheMisses:     2,
        slowQueries:     [['sql' => 'select * from users', 'time_ms' => 120.0]],
        truncated:       false,
    );
}

it('writes a BackendTelemetry row linked to an existing audit', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);
    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'stub',
        'device' => 'mobile',
        'status' => 'completed',
    ]);

    (new PersistTelemetryJob(makeSnapshot($audit->id)))->handle();

    $row = BackendTelemetry::first();

    expect(BackendTelemetry::count())->toBe(1)
        ->and($row->audit_id)->toBe($audit->id)
        ->and($row->queries_count)->toBe(87)
        ->and($row->n_plus_one_suspect)->toBeTrue()
        ->and($row->slow_queries[0]['sql'])->toContain('select');
});

it('writes a sampled (no audit) telemetry row', function (): void {
    (new PersistTelemetryJob(makeSnapshot(null)))->handle();

    $row = BackendTelemetry::first();

    expect($row)->not->toBeNull()
        ->and($row->audit_id)->toBeNull()
        ->and($row->sampled_request)->toBeTrue();
});

it('silently no-ops if the referenced audit_id does not exist', function (): void {
    $missingId = Str::uuid()->toString();

    (new PersistTelemetryJob(makeSnapshot($missingId)))->handle();

    expect(BackendTelemetry::count())->toBe(0);
});
