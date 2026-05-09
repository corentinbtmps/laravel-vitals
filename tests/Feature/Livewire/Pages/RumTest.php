<?php

declare(strict_types=1);

use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Pages\Rum;
use LaravelVitals\Models\RumEvent;
use Livewire\Livewire;

beforeEach(fn () => Vitals::authorize(fn (): true => true));

it('renders the RUM page without error when empty', function (): void {
    Livewire::test(Rum::class)
        ->assertOk()
        ->assertSee('No RUM data yet');
});

it('shows metric cards when events exist', function (): void {
    RumEvent::create(['url' => '/', 'metric' => 'LCP', 'value' => 1500.0, 'rating' => 'good', 'device' => 'desktop', 'occurred_at' => now()]);
    RumEvent::create(['url' => '/', 'metric' => 'LCP', 'value' => 3000.0, 'rating' => 'needs-improvement', 'device' => 'desktop', 'occurred_at' => now()]);

    Livewire::test(Rum::class)
        ->assertOk()
        ->assertViewHas('totalEvents', 2)
        ->assertDontSee('No RUM data yet');
});

it('period filter changes the dataset', function (): void {
    // Old event outside 24h
    RumEvent::create(['url' => '/old', 'metric' => 'LCP', 'value' => 1000.0, 'rating' => 'good', 'device' => 'mobile', 'occurred_at' => now()->subDays(3)]);
    // Recent event
    RumEvent::create(['url' => '/new', 'metric' => 'LCP', 'value' => 2000.0, 'rating' => 'good', 'device' => 'mobile', 'occurred_at' => now()->subHours(1)]);

    Livewire::test(Rum::class)
        ->call('setPeriod', '24h')
        ->assertViewHas('totalEvents', 1);
});

it('device filter works', function (): void {
    RumEvent::create(['url' => '/', 'metric' => 'FCP', 'value' => 800.0, 'rating' => 'good', 'device' => 'mobile', 'occurred_at' => now()]);
    RumEvent::create(['url' => '/', 'metric' => 'FCP', 'value' => 600.0, 'rating' => 'good', 'device' => 'desktop', 'occurred_at' => now()]);

    Livewire::test(Rum::class)
        ->call('setDevice', 'mobile')
        ->assertViewHas('totalEvents', 1);
});

it('ignores invalid period values', function (): void {
    $component = Livewire::test(Rum::class)
        ->call('setPeriod', 'invalid');

    expect($component->get('period'))->toBe('7d');
});

it('calculates p75 correctly', function (): void {
    // 4 events → p75 is 3rd value (ceil(4*0.75) - 1 = 2nd index) = 1800
    $values = [1000, 1500, 1800, 3500];
    foreach ($values as $v) {
        RumEvent::create(['url' => '/', 'metric' => 'LCP', 'value' => $v, 'rating' => 'good', 'device' => 'desktop', 'occurred_at' => now()]);
    }

    Livewire::test(Rum::class)
        ->assertViewHas('metricCards', fn ($cards) => $cards['LCP']['p75'] === 1800.0);
});
