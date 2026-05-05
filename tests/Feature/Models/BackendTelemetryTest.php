<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\BackendTelemetry;
use LaravelVitals\Models\Url;

it('persists a BackendTelemetry row with slow_queries cast as array', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);
    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'local',
        'device' => 'mobile',
        'status' => 'completed',
    ]);

    $telemetry = BackendTelemetry::create([
        'audit_id'           => $audit->id,
        'sampled_request'    => false,
        'route_name'         => 'home',
        'http_status'        => 200,
        'duration_ms'        => 245.7,
        'memory_peak_kb'     => 12_500,
        'queries_count'      => 87,
        'queries_time_ms'    => 142.3,
        'queries_unique'     => 4,
        'n_plus_one_suspect' => true,
        'views_rendered'     => 3,
        'views_time_ms'      => 18.4,
        'jobs_dispatched'    => 0,
        'events_fired'       => 12,
        'cache_hits'         => 24,
        'cache_misses'       => 2,
        'slow_queries'       => [['sql' => 'select * from users where id = ?', 'count' => 80, 'time_ms' => 120]],
        'truncated'          => false,
    ]);

    expect($telemetry->fresh())
        ->n_plus_one_suspect->toBeTrue()
        ->slow_queries->toBe([['sql' => 'select * from users where id = ?', 'count' => 80, 'time_ms' => 120]]);
});

it('belongs to an Audit', function (): void {
    expect((new BackendTelemetry())->audit())
        ->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
});
