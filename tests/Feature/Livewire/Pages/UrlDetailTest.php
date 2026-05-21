<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Pages\UrlDetail;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;
use Livewire\Livewire;

beforeEach(fn () => Vitals::authorize(fn (): true => true));

it('renders the metric chart when there are no audits', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);

    Livewire::test(UrlDetail::class, ['url' => $url->id])
        ->assertSet('metric', 'performance')
        ->assertOk();
});

it('switches metric without errors', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);

    Livewire::test(UrlDetail::class, ['url' => $url->id])
        ->set('metric', 'lcp')
        ->assertSet('metric', 'lcp')
        ->set('metric', 'cls')
        ->assertSet('metric', 'cls')
        ->assertOk();
});

it('metricSeries returns empty array when no audits exist', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);

    $component = Livewire::test(UrlDetail::class, ['url' => $url->id]);
    $instance = $component->instance();

    expect($instance->metricSeries())->toBeArray()->toBeEmpty();
});

it('shows audit history for a single URL', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);
    Audit::create(['id' => Str::uuid()->toString(), 'url_id' => $url->id, 'driver' => 'stub', 'device' => 'mobile', 'status' => 'completed', 'completed_at' => now(), 'score_performance' => 92]);
    Audit::create(['id' => Str::uuid()->toString(), 'url_id' => $url->id, 'driver' => 'stub', 'device' => 'mobile', 'status' => 'completed', 'completed_at' => now()->subDay(), 'score_performance' => 88]);

    Livewire::test(UrlDetail::class, ['url' => $url->id])
        ->assertOk()
        ->assertSeeText('home')
        ->assertSeeText('92')
        ->assertSeeText('88');
});
