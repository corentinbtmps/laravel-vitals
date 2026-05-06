<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;
use LaravelVitals\Contracts\LighthouseDriver;
use LaravelVitals\Drivers\Stubs\StubLighthouseDriver;

beforeEach(function (): void {
    Storage::fake('vitals');
    config()->set('vitals.urls', ['home' => '/']);
});

it('exits 0 when no budget is violated', function (): void {
    config()->set('vitals.budgets', [
        'lcp_ms'            => ['warning' => 5000, 'critical' => 10000],
        'score_performance' => ['warning' => 50, 'critical' => 30],
        'per_url'           => [],
    ]);
    $this->app->bind(LighthouseDriver::class, fn (): \LaravelVitals\Drivers\Stubs\StubLighthouseDriver => new StubLighthouseDriver());

    $this->artisan('vitals:audit', ['label' => 'home', '--sync' => true, '--fail-on-budget' => true])
        ->assertExitCode(0);
});

it('exits 1 on a warning violation', function (): void {
    config()->set('vitals.budgets', [
        'lcp_ms'            => ['warning' => 1000, 'critical' => 5000],
        'score_performance' => ['warning' => 99, 'critical' => 30],
        'per_url'           => [],
    ]);
    $this->app->bind(LighthouseDriver::class, fn (): \LaravelVitals\Drivers\Stubs\StubLighthouseDriver => new StubLighthouseDriver());

    $this->artisan('vitals:audit', ['label' => 'home', '--sync' => true, '--fail-on-budget' => true])
        ->assertExitCode(1);
});

it('exits 2 on a critical violation', function (): void {
    config()->set('vitals.budgets', [
        'lcp_ms'            => ['warning' => 100, 'critical' => 200],
        'score_performance' => ['warning' => 99, 'critical' => 50],
        'per_url'           => [],
    ]);
    $this->app->bind(LighthouseDriver::class, fn (): \LaravelVitals\Drivers\Stubs\StubLighthouseDriver => new StubLighthouseDriver());

    $this->artisan('vitals:audit', ['label' => 'home', '--sync' => true, '--fail-on-budget' => true])
        ->assertExitCode(2);
});
