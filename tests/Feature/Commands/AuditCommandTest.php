<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;
use LaravelVitals\Contracts\LighthouseDriver;
use LaravelVitals\Drivers\Stubs\StubLighthouseDriver;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;

beforeEach(function (): void {
    Storage::fake('vitals');
    config()->set('vitals.storage', ['disk' => 'vitals', 'path' => 'reports']);
    $this->app->bind(LighthouseDriver::class, fn () => new StubLighthouseDriver());
    config()->set('vitals.urls', ['home' => '/']);
});

it('audits a single URL by label', function (): void {
    $this->artisan('vitals:audit', ['label' => 'home', '--sync' => true])
        ->expectsOutputToContain('home')
        ->expectsOutputToContain('completed')
        ->assertSuccessful();

    expect(Audit::count())->toBe(1)
        ->and(Audit::first()->status)->toBe('completed');
});

it('errors when the label is unknown and not in config', function (): void {
    $this->artisan('vitals:audit', ['label' => 'imaginary', '--sync' => true])
        ->expectsOutputToContain('not found')
        ->assertFailed();
});

it('audits all URLs when --all and --sync are passed', function (): void {
    config()->set('vitals.urls', ['home' => '/', 'product' => '/products/1']);

    $this->artisan('vitals:audit', ['--all' => true, '--sync' => true])
        ->assertSuccessful();

    expect(Audit::count())->toBe(2);
});

it('runs successfully with --format=json', function (): void {
    $this->artisan('vitals:audit', ['label' => 'home', '--format' => 'json', '--sync' => true])
        ->assertSuccessful();

    expect(Audit::first()->status)->toBe('completed');
});
