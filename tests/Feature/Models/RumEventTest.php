<?php

declare(strict_types=1);

use Carbon\Carbon;
use LaravelVitals\Models\RumEvent;

it('casts value to float', function (): void {
    $event = RumEvent::create([
        'url'         => '/test',
        'metric'      => 'LCP',
        'value'       => '1234.5678',
        'device'      => 'mobile',
        'occurred_at' => now(),
    ]);
    expect($event->value)->toBeFloat()->toBe(1234.5678);
});

it('casts occurred_at to Carbon', function (): void {
    $event = RumEvent::create([
        'url'         => '/test',
        'metric'      => 'CLS',
        'value'       => 0.05,
        'device'      => 'desktop',
        'occurred_at' => '2026-01-15 10:00:00',
    ]);
    expect($event->occurred_at)->toBeInstanceOf(Carbon::class);
});

it('casts attribution to array', function (): void {
    $event = RumEvent::create([
        'url'         => '/test',
        'metric'      => 'INP',
        'value'       => 220.0,
        'device'      => 'mobile',
        'attribution' => ['interactionTarget' => 'button', 'interactionType' => 'click'],
        'occurred_at' => now(),
    ]);
    expect($event->fresh()->attribution)->toBeArray()
        ->toHaveKey('interactionTarget', 'button');
});

it('uses custom connection name from config', function (): void {
    expect((new RumEvent())->getConnectionName())->toBeNull();
});

it('prunable scope returns events older than retention_days', function (): void {
    config()->set('vitals.rum.retention_days', 90);

    RumEvent::create(['url' => '/old', 'metric' => 'LCP', 'value' => 1000, 'device' => 'mobile', 'occurred_at' => now()->subDays(91)]);
    RumEvent::create(['url' => '/new', 'metric' => 'LCP', 'value' => 1000, 'device' => 'mobile', 'occurred_at' => now()->subDays(30)]);

    $prunable = (new RumEvent())->prunable()->get();
    expect($prunable)->toHaveCount(1);
    expect($prunable->first()->url)->toBe('/old');
});

it('stores all five metric types', function (): void {
    foreach (['LCP', 'INP', 'CLS', 'TTFB', 'FCP'] as $metric) {
        RumEvent::create([
            'url'         => '/test',
            'metric'      => $metric,
            'value'       => 100.0,
            'device'      => 'desktop',
            'occurred_at' => now(),
        ]);
    }
    expect(RumEvent::query()->count())->toBe(5);
});
