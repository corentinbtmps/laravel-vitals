<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LaravelVitals\Telemetry\Sources\TelescopeSource;
use LaravelVitals\Telemetry\TrendStats;

it('reports unavailable when telescope_entries table is missing', function (): void {
    expect((new TelescopeSource())->isAvailable())->toBeFalse();
});

it('aggregates request durations from telescope_entries', function (): void {
    Schema::create('telescope_entries', function ($table): void {
        $table->id();
        $table->uuid('uuid');
        $table->string('type');
        $table->json('content');
        $table->timestamp('created_at')->nullable();
    });

    foreach ([100, 150, 200, 250, 800, 1200, 1500, 2000, 2500, 3000] as $duration) {
        DB::table('telescope_entries')->insert([
            'uuid'       => \Illuminate\Support\Str::uuid()->toString(),
            'type'       => 'request',
            'content'    => json_encode(['uri' => '/home', 'duration' => $duration]),
            'created_at' => now(),
        ]);
    }

    $stats = (new TelescopeSource())->getTrendsFor('/home');

    expect($stats->sampleCount)->toBe(10)
        ->and($stats->p50Ttfb)->not->toBeNull()
        ->and($stats->p95Ttfb)->not->toBeNull();

    Schema::drop('telescope_entries');
});
