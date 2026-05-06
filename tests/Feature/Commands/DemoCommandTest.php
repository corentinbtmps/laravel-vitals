<?php

declare(strict_types=1);

use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;

it('seeds 4 demo URLs marked is_demo=true', function (): void {
    $this->artisan('vitals:demo')->assertSuccessful();

    expect(Url::where('is_demo', true)->count())->toBe(4);
    expect(Url::pluck('label')->all())->toContain('home', 'product', 'blog', 'dashboard');
});

it('seeds 14 days of audits across the 4 URLs', function (): void {
    $this->artisan('vitals:demo')->assertSuccessful();

    expect(Audit::where('is_demo', true)->count())->toBeGreaterThan(50);
});

it('attaches recommendations and telemetry to demo audits', function (): void {
    $this->artisan('vitals:demo')->assertSuccessful();

    expect(Recommendation::where('is_demo', true)->count())->toBeGreaterThan(0);
});

it('vitals:purge --demo removes only demo records', function (): void {
    $this->artisan('vitals:demo');

    Url::create(['label' => 'real', 'path' => '/real']);

    $this->artisan('vitals:purge', ['--demo' => true])->assertSuccessful();

    expect(Url::where('is_demo', true)->count())->toBe(0);
    expect(Url::where('label', 'real')->count())->toBe(1);
});
