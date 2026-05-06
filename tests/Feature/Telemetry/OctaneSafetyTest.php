<?php

declare(strict_types=1);

use LaravelVitals\Models\Url;
use LaravelVitals\Telemetry\TelemetryRecorder;

beforeEach(function (): void {
    Url::create(['label' => 'home', 'path' => '/']);
});

it('a fresh recorder for each request does not see prior request queries', function (): void {
    // Simulate two consecutive requests in a long-running worker.

    $first = new TelemetryRecorder();
    $first->start('first');
    app()->instance('vitals.active-recorder', $first);
    Url::query()->where('label', 'home')->get();
    Url::query()->where('label', 'home')->get();
    $firstSnap = $first->snapshot(200, 'home');
    app()->forgetInstance('vitals.active-recorder');

    expect($firstSnap->queriesCount)->toBeGreaterThanOrEqual(2);

    // New request → new recorder bound as the active recorder. The global
    // listeners (registered once at boot) now delegate to this new instance.
    $second = new TelemetryRecorder();
    $second->start('second');
    app()->instance('vitals.active-recorder', $second);
    // Don't run any queries this time.
    $secondSnap = $second->snapshot(200, 'home');
    app()->forgetInstance('vitals.active-recorder');

    expect($secondSnap->queriesCount)->toBe(0)
        ->and($secondSnap->auditId)->toBe('second');
});

it('a recorder can be reused across two start() cycles in the same instance', function (): void {
    $recorder = new TelemetryRecorder();

    $recorder->start('first');
    app()->instance('vitals.active-recorder', $recorder);
    Url::query()->where('label', 'home')->get();
    $a = $recorder->snapshot(200, 'home');

    $recorder->start('second');
    $b = $recorder->snapshot(200, 'home');
    app()->forgetInstance('vitals.active-recorder');

    expect($a->auditId)->toBe('first')
        ->and($a->queriesCount)->toBeGreaterThanOrEqual(1)
        ->and($b->auditId)->toBe('second')
        ->and($b->queriesCount)->toBe(0);
});
