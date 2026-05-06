<?php

declare(strict_types=1);

use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use LaravelVitals\Models\Url;
use LaravelVitals\Telemetry\TelemetryRecorder;

beforeEach(function (): void {
    config()->set('vitals.telemetry', [
        'auto_register'           => true,
        'always_capture'          => false,
        'sample_rate'             => 0.05,
        'n_plus_one_threshold'    => 10,
        'slow_query_threshold_ms' => 50,
        'max_queries'             => 1000,
        'top_slow_queries'        => 5,
    ]);

    Url::create(['label' => 'home', 'path' => '/']);
});

it('starts inactive and becomes active after start()', function (): void {
    $recorder = new TelemetryRecorder();

    expect($recorder->isActive())->toBeFalse();

    $recorder->start('audit-uuid-1');

    expect($recorder->isActive())->toBeTrue();
});

it('captures query count and timing via DB::listen', function (): void {
    $recorder = new TelemetryRecorder();
    $recorder->start('audit-uuid-1');

    Url::query()->where('label', 'home')->get();
    Url::query()->where('label', 'home')->get();

    $snapshot = $recorder->snapshot(httpStatus: 200, routeName: 'home');

    expect($snapshot->queriesCount)->toBeGreaterThanOrEqual(2)
        ->and($snapshot->queriesUnique)->toBeGreaterThanOrEqual(1)
        ->and($snapshot->auditId)->toBe('audit-uuid-1');
});

it('counts cache hits and misses via Laravel Cache events', function (): void {
    $recorder = new TelemetryRecorder();
    $recorder->start('audit-uuid-1');

    event(new CacheHit('default', 'foo', 'bar'));
    event(new CacheHit('default', 'baz', 'qux'));
    event(new CacheMissed('default', 'missing'));

    $snapshot = $recorder->snapshot(200, 'home');

    expect($snapshot->cacheHits)->toBe(2)
        ->and($snapshot->cacheMisses)->toBe(1);
});

it('records sampled flag when start() is called with sampled=true', function (): void {
    $recorder = new TelemetryRecorder();
    $recorder->start('sampled-id', sampled: true);

    $snapshot = $recorder->snapshot(200, 'home');

    expect($snapshot->sampledRequest)->toBeTrue()
        ->and($snapshot->auditId)->toBe('sampled-id');
});

it('reports n_plus_one_suspect when same pattern repeats above threshold', function (): void {
    config()->set('vitals.telemetry.n_plus_one_threshold', 5);

    $recorder = new TelemetryRecorder();
    $recorder->start('audit-uuid-1');

    for ($i = 0; $i < 7; $i++) {
        Url::query()->where('label', 'home')->get();
    }

    $snapshot = $recorder->snapshot(200, 'home');

    expect($snapshot->nPlusOneSuspect)->toBeTrue();
});

it('produces a snapshot with non-negative duration and memory peak', function (): void {
    $recorder = new TelemetryRecorder();
    $recorder->start('audit-uuid-1');

    $snapshot = $recorder->snapshot(200, 'home');

    expect($snapshot->durationMs)->toBeGreaterThanOrEqual(0.0)
        ->and($snapshot->memoryPeakKb)->toBeGreaterThan(0);
});

it('can be reused across two start() cycles without leaking state', function (): void {
    $recorder = new TelemetryRecorder();

    $recorder->start('first');
    Url::query()->where('label', 'home')->get();
    $first = $recorder->snapshot(200, 'home');

    $recorder->start('second');
    $second = $recorder->snapshot(200, 'home');

    expect($first->queriesCount)->toBeGreaterThanOrEqual(1)
        ->and($second->queriesCount)->toBe(0)
        ->and($second->auditId)->toBe('second');
});
