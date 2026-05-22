<?php

declare(strict_types=1);

use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Pages\Queries;
use LaravelVitals\Models\BackendTelemetry;
use Livewire\Livewire;

beforeEach(fn () => Vitals::authorize(fn (): true => true));

it('renders the queries page without error when empty', function (): void {
    Livewire::test(Queries::class)
        ->assertOk()
        ->assertSee('No query data yet');
});

it('shows routes when telemetry data exists', function (): void {
    BackendTelemetry::create([
        'route_name'         => 'home',
        'http_status'        => 200,
        'duration_ms'        => 120.0,
        'memory_peak_kb'     => 8192,
        'queries_count'      => 15,
        'queries_time_ms'    => 45.0,
        'queries_unique'     => 10,
        'n_plus_one_suspect' => false,
        'views_rendered'     => 1,
        'views_time_ms'      => 5.0,
        'jobs_dispatched'    => 0,
        'events_fired'       => 0,
        'cache_hits'         => 0,
        'cache_misses'       => 0,
        'slow_queries'       => null,
        'truncated'          => false,
        'sampled_request'    => true,
        'created_at'         => now(),
    ]);

    Livewire::test(Queries::class)
        ->assertOk()
        ->assertDontSee('No query data yet')
        ->assertViewHas('routes', fn ($routes): bool => count($routes) === 1);
});

it('period filter works', function (): void {
    BackendTelemetry::create([
        'route_name'         => 'old.route',
        'http_status'        => 200,
        'duration_ms'        => 50.0,
        'memory_peak_kb'     => 4096,
        'queries_count'      => 5,
        'queries_time_ms'    => 10.0,
        'queries_unique'     => 4,
        'n_plus_one_suspect' => false,
        'views_rendered'     => 1,
        'views_time_ms'      => 2.0,
        'jobs_dispatched'    => 0,
        'events_fired'       => 0,
        'cache_hits'         => 0,
        'cache_misses'       => 0,
        'slow_queries'       => null,
        'truncated'          => false,
        'sampled_request'    => true,
        'created_at'         => now()->subDays(10),
    ]);

    Livewire::test(Queries::class)
        ->set('period', '24h')
        ->assertViewHas('routes', fn ($routes): bool => count($routes) === 0);
});

it('flags regression when current p75 > 2x previous period p75', function (): void {
    // Previous period: 5 queries p75
    BackendTelemetry::create([
        'route_name' => 'api.data', 'http_status' => 200, 'duration_ms' => 50.0,
        'memory_peak_kb' => 4096, 'queries_count' => 5, 'queries_time_ms' => 10.0,
        'queries_unique' => 4, 'n_plus_one_suspect' => false, 'views_rendered' => 0,
        'views_time_ms' => 0.0, 'jobs_dispatched' => 0, 'events_fired' => 0,
        'cache_hits' => 0, 'cache_misses' => 0, 'slow_queries' => null, 'truncated' => false,
        'sampled_request' => true, 'created_at' => now()->subDays(10),
    ]);
    // Current period: 15 queries p75 (> 2× of 5)
    BackendTelemetry::create([
        'route_name' => 'api.data', 'http_status' => 200, 'duration_ms' => 100.0,
        'memory_peak_kb' => 4096, 'queries_count' => 15, 'queries_time_ms' => 30.0,
        'queries_unique' => 12, 'n_plus_one_suspect' => false, 'views_rendered' => 0,
        'views_time_ms' => 0.0, 'jobs_dispatched' => 0, 'events_fired' => 0,
        'cache_hits' => 0, 'cache_misses' => 0, 'slow_queries' => null, 'truncated' => false,
        'sampled_request' => true, 'created_at' => now()->subDays(2),
    ]);

    Livewire::test(Queries::class)
        ->set('period', '7d')
        ->assertViewHas('routes', fn ($routes): bool => isset($routes[0]) && $routes[0]['regression'] === true);
});

it('shows memory hogs panel when peak_memory_bytes is present', function (): void {
    BackendTelemetry::create([
        'route_name' => 'heavy.route', 'http_status' => 200, 'duration_ms' => 200.0,
        'memory_peak_kb' => 204800, 'peak_memory_bytes' => 209715200, // 200MB
        'queries_count' => 10, 'queries_time_ms' => 20.0, 'queries_unique' => 8,
        'n_plus_one_suspect' => false, 'views_rendered' => 0, 'views_time_ms' => 0.0,
        'jobs_dispatched' => 0, 'events_fired' => 0, 'cache_hits' => 0, 'cache_misses' => 0,
        'slow_queries' => null, 'truncated' => false, 'sampled_request' => true,
        'created_at' => now(),
    ]);

    Livewire::test(Queries::class)
        ->assertViewHas('memoryHogs', fn ($hogs): bool => count($hogs) === 1 && $hogs[0]['memory_p75_mb'] > 0);
});

