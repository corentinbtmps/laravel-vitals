<?php

declare(strict_types=1);

use Illuminate\Database\Events\QueryExecuted;
use LaravelVitals\Telemetry\TelemetryRecorder;

/**
 * Unit-level tests for the queries_log capture feature.
 *
 * Tests use the TelemetryRecorder in isolation; no real DB queries needed
 * because we dispatch fake QueryExecuted events directly.
 */
function fakeQueryEvent(string $sql, float $timeMs = 2.0, array $bindings = []): QueryExecuted
{
    $event = new QueryExecuted($sql, $bindings, $timeMs, app('db')->connection());

    return $event;
}

it('captures query entries in the queries_log when recording is active', function (): void {
    $recorder = new TelemetryRecorder();
    $recorder->start('audit-abc');

    $recorder->recordQuery(fakeQueryEvent('SELECT * FROM posts WHERE id = 1', 3.0));
    $recorder->recordQuery(fakeQueryEvent('SELECT * FROM users WHERE id = 2', 1.5));

    $snapshot = $recorder->snapshot(200, 'home');

    expect($snapshot->queriesLog)->toHaveCount(2);

    $first = $snapshot->queriesLog[0];
    expect($first)->toHaveKey('sql')
        ->toHaveKey('bindings_count')
        ->toHaveKey('time_ms')
        ->toHaveKey('caller_file')
        ->toHaveKey('caller_line');

    expect($first['time_ms'])->toBe(3.0);
});

it('does not capture queries when recorder is inactive', function (): void {
    $recorder = new TelemetryRecorder();
    // Not started — isActive() is false

    $recorder->recordQuery(fakeQueryEvent('SELECT 1'));

    $snapshot = $recorder->snapshot(200, null);

    expect($snapshot->queriesLog)->toBeEmpty();
});

it('caps the queries_log at 200 entries', function (): void {
    $recorder = new TelemetryRecorder();
    $recorder->start('audit-cap');

    for ($i = 0; $i < 250; $i++) {
        $recorder->recordQuery(fakeQueryEvent("SELECT * FROM t WHERE id = {$i}"));
    }

    $snapshot = $recorder->snapshot(200, null);

    expect(count($snapshot->queriesLog))->toBeLessThanOrEqual(200);
});

it('normalizes numeric literals in SQL to ?', function (): void {
    $recorder = new TelemetryRecorder();
    $recorder->start('audit-normalize');

    $recorder->recordQuery(fakeQueryEvent('SELECT * FROM posts WHERE user_id = 42 AND status = 1'));

    $snapshot = $recorder->snapshot(200, null);

    expect($snapshot->queriesLog[0]['sql'])->toContain('?')
        ->not->toContain('42')
        ->not->toContain(' 1');
});

it('resets the queries_log between starts', function (): void {
    $recorder = new TelemetryRecorder();

    $recorder->start('audit-1');
    $recorder->recordQuery(fakeQueryEvent('SELECT 1'));
    $recorder->snapshot(200, null);

    $recorder->start('audit-2');
    $snapshot2 = $recorder->snapshot(200, null);

    expect($snapshot2->queriesLog)->toBeEmpty();
});

it('skips vendor and package frames in caller resolution', function (): void {
    $recorder = new TelemetryRecorder();
    $recorder->start('audit-caller');

    $recorder->recordQuery(fakeQueryEvent('SELECT * FROM users'));

    $snapshot = $recorder->snapshot(200, null);

    // The caller file (if resolved) should not point into vendor/ or laravel-vitals/src/
    foreach ($snapshot->queriesLog as $entry) {
        if ($entry['caller_file'] !== null) {
            expect($entry['caller_file'])
                ->not->toContain('/vendor/')
                ->not->toContain('/laravel-vitals/src/');
        }
    }
    expect(true)->toBeTrue(); // test ran
});
