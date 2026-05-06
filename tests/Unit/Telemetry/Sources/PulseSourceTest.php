<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LaravelVitals\Telemetry\Sources\PulseSource;
use LaravelVitals\Telemetry\TrendStats;

it('reports unavailable when pulse_aggregates table is missing', function (): void {
    expect((new PulseSource())->isAvailable())->toBeFalse();
});

it('returns TrendStats from pulse_aggregates when available', function (): void {
    Schema::create('pulse_aggregates', function ($table): void {
        $table->id();
        $table->string('type');
        $table->string('aggregate');
        $table->string('key_hash', 16);
        $table->text('key');
        $table->double('value')->nullable();
        $table->unsignedInteger('count')->default(0);
        $table->timestamp('bucket')->nullable();
        $table->unsignedInteger('period');
    });

    DB::table('pulse_aggregates')->insert([
        ['type' => 'slow_request', 'aggregate' => 'p95', 'key_hash' => 'h', 'key' => '"GET /home"', 'value' => 800.5, 'count' => 100, 'period' => 86400],
        ['type' => 'slow_request', 'aggregate' => 'p50', 'key_hash' => 'h', 'key' => '"GET /home"', 'value' => 200.0, 'count' => 100, 'period' => 86400],
    ]);

    $stats = (new PulseSource())->getTrendsFor('/home');

    expect($stats)->toBeInstanceOf(TrendStats::class)
        ->and($stats->sampleCount)->toBeGreaterThan(0)
        ->and($stats->p95Ttfb)->toBe(800.5)
        ->and($stats->p50Ttfb)->toBe(200.0);

    Schema::drop('pulse_aggregates');
});
