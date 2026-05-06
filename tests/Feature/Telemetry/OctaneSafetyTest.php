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
    Url::query()->where('label', 'home')->get();
    Url::query()->where('label', 'home')->get();
    $firstSnap = $first->snapshot(200, 'home');

    expect($firstSnap->queriesCount)->toBeGreaterThanOrEqual(2);

    // New request → new recorder. Old DB::listen listeners may still be attached
    // to the connection, but the new recorder is a different instance — its
    // counters are fresh. The first recorder is no longer active, so even if its
    // listener fires, it discards the event.
    $second = new TelemetryRecorder();
    $second->start('second');
    // Don't run any queries this time.
    $secondSnap = $second->snapshot(200, 'home');

    expect($secondSnap->queriesCount)->toBe(0)
        ->and($secondSnap->auditId)->toBe('second');
});

it('a recorder can be reused across two start() cycles in the same instance', function (): void {
    $recorder = new TelemetryRecorder();

    $recorder->start('first');
    Url::query()->where('label', 'home')->get();
    $a = $recorder->snapshot(200, 'home');

    $recorder->start('second');
    $b = $recorder->snapshot(200, 'home');

    expect($a->auditId)->toBe('first')
        ->and($a->queriesCount)->toBeGreaterThanOrEqual(1)
        ->and($b->auditId)->toBe('second')
        ->and($b->queriesCount)->toBe(0);
});
