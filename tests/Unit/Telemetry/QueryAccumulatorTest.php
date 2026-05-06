<?php

declare(strict_types=1);

use LaravelVitals\Telemetry\QueryAccumulator;

function makeQueryEvent(string $sql, float $timeMs): object
{
    return new class($sql, $timeMs) {
        public function __construct(public string $sql, public float $time)
        {
        }
    };
}

it('counts queries and accumulates total time', function (): void {
    $acc = new QueryAccumulator(maxQueries: 100, slowQueryThresholdMs: 50, topSlow: 5);

    $acc->record(makeQueryEvent('select * from users where id = ?', 12.5));
    $acc->record(makeQueryEvent('select * from users where id = ?', 8.0));

    expect($acc->count())->toBe(2)
        ->and($acc->totalTimeMs())->toBe(20.5);
});

it('counts unique normalised patterns', function (): void {
    $acc = new QueryAccumulator(maxQueries: 100, slowQueryThresholdMs: 50, topSlow: 5);

    $acc->record(makeQueryEvent('select * from users where id = 1', 1.0));
    $acc->record(makeQueryEvent('select * from users where id = 2', 1.0));
    $acc->record(makeQueryEvent('select * from users where id = 3', 1.0));
    $acc->record(makeQueryEvent('select * from products', 1.0));

    expect($acc->uniqueCount())->toBe(2);
});

it('flags N+1 when a pattern repeats above threshold', function (): void {
    $acc = new QueryAccumulator(maxQueries: 100, slowQueryThresholdMs: 50, topSlow: 5);

    for ($i = 0; $i < 15; $i++) {
        $acc->record(makeQueryEvent("select * from users where id = $i", 1.0));
    }

    expect($acc->isNPlusOneSuspect(threshold: 10))->toBeTrue();
});

it('does not flag N+1 below threshold', function (): void {
    $acc = new QueryAccumulator(maxQueries: 100, slowQueryThresholdMs: 50, topSlow: 5);

    for ($i = 0; $i < 5; $i++) {
        $acc->record(makeQueryEvent("select * from users where id = $i", 1.0));
    }

    expect($acc->isNPlusOneSuspect(threshold: 10))->toBeFalse();
});

it('keeps a top-N of slow queries and ignores ones below threshold', function (): void {
    $acc = new QueryAccumulator(maxQueries: 100, slowQueryThresholdMs: 50, topSlow: 3);

    $acc->record(makeQueryEvent('select 1', 5.0));    // below threshold
    $acc->record(makeQueryEvent('select 2', 200.0));
    $acc->record(makeQueryEvent('select 3', 100.0));
    $acc->record(makeQueryEvent('select 4', 300.0));
    $acc->record(makeQueryEvent('select 5', 60.0));

    $slow = $acc->slowQueries();

    expect($slow)->toHaveCount(3);
    $times = array_column($slow, 'time_ms');
    expect($times)->toContain(300.0)
        ->and($times)->toContain(200.0)
        ->and($times)->toContain(100.0)
        ->and($times)->not->toContain(5.0);
});

it('truncates after max_queries and flags truncated', function (): void {
    $acc = new QueryAccumulator(maxQueries: 3, slowQueryThresholdMs: 50, topSlow: 5);

    $acc->record(makeQueryEvent('a', 1.0));
    $acc->record(makeQueryEvent('b', 1.0));
    $acc->record(makeQueryEvent('c', 1.0));
    $acc->record(makeQueryEvent('d', 1.0)); // dropped
    $acc->record(makeQueryEvent('e', 1.0)); // dropped

    expect($acc->count())->toBe(3)
        ->and($acc->isTruncated())->toBeTrue();
});

it('normalises numeric and quoted literals when computing patterns', function (): void {
    $acc = new QueryAccumulator(maxQueries: 100, slowQueryThresholdMs: 50, topSlow: 5);

    $acc->record(makeQueryEvent("select * from users where id = 1 and name = 'alice'", 1.0));
    $acc->record(makeQueryEvent("select * from users where id = 999 and name = 'bob'", 1.0));

    expect($acc->uniqueCount())->toBe(1);
});
