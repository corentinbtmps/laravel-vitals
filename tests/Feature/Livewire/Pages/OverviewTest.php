<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Pages\Overview;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;
use Livewire\Livewire;

beforeEach(function (): void {
    Vitals::authorize(fn (): true => true);
});

it('renders the overview page with average scores when audits exist', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);

    Audit::create([
        'id'                   => Str::uuid()->toString(),
        'url_id'               => $url->id,
        'driver'               => 'stub',
        'device'               => 'mobile',
        'status'               => 'completed',
        'score_performance'    => 90,
        'score_accessibility'  => 95,
        'score_best_practices' => 88,
        'score_seo'            => 100,
        'completed_at'         => now(),
    ]);

    Livewire::test(Overview::class)
        ->assertOk()
        ->assertSee('Performance')
        ->assertSeeText('90');
});

it('renders gracefully when no audits exist', function (): void {
    Livewire::test(Overview::class)
        ->assertOk()
        ->assertSee('No audits');
});

it('passes metric trends and deltas to the view', function (): void {
    Livewire::test(Overview::class)
        ->assertViewHas('metricTrends')
        ->assertViewHas('metricDeltas');
});
