<?php

declare(strict_types=1);

use LaravelVitals\Contracts\LighthouseDriver;
use LaravelVitals\Drivers\BrowsershotDriver;
use LaravelVitals\Drivers\LighthouseDriverManager;
use LaravelVitals\Drivers\LocalLighthouseDriver;
use LaravelVitals\Drivers\PageSpeedApiDriver;
use LaravelVitals\Drivers\Stubs\StubLighthouseDriver;

it('resolves a driver by name from the registered map', function (): void {
    $manager = app(LighthouseDriverManager::class);

    expect($manager->driver('local'))->toBeInstanceOf(LocalLighthouseDriver::class)
        ->and($manager->driver('pagespeed'))->toBeInstanceOf(PageSpeedApiDriver::class)
        ->and($manager->driver('browsershot'))->toBeInstanceOf(BrowsershotDriver::class);
});

it('throws when an unknown driver name is requested', function (): void {
    $manager = app(LighthouseDriverManager::class);

    expect(fn () => $manager->driver('imaginary'))
        ->toThrow(InvalidArgumentException::class);
});

it('honours an explicit driver setting in config', function (): void {
    config()->set('vitals.driver', 'pagespeed');
    config()->set('vitals.drivers.pagespeed.api_key', 'k');

    $manager = app(LighthouseDriverManager::class);

    expect($manager->resolve())->toBeInstanceOf(PageSpeedApiDriver::class);
});

it('throws when the explicit driver is unavailable', function (): void {
    config()->set('vitals.driver', 'pagespeed');
    config()->set('vitals.drivers.pagespeed.api_key');

    $manager = app(LighthouseDriverManager::class);

    expect(fn () => $manager->resolve())->toThrow(InvalidArgumentException::class);
});

it('auto-resolves to the first available driver in priority order', function (): void {
    config()->set('vitals.driver', 'auto');
    config()->set('vitals.drivers.pagespeed.api_key', 'k_present');
    config()->set('vitals.drivers.local.lighthouse_binary', '/nonexistent/lighthouse');

    // Browsershot v5 stock is unavailable (no lighthouseAudit), local is unavailable
    // (binary missing). pagespeed is the only one available.
    $manager = app(LighthouseDriverManager::class);

    expect($manager->resolve())->toBeInstanceOf(PageSpeedApiDriver::class);
});

it('falls back to a stub-bound driver when one is wired into the container', function (): void {
    config()->set('vitals.driver', 'auto');
    config()->set('vitals.drivers.pagespeed.api_key');
    config()->set('vitals.drivers.local.lighthouse_binary', '/nonexistent/lighthouse');

    // Inject a stub for browsershot so it returns isAvailable() = true.
    app()->bind(BrowsershotDriver::class, fn (): \LaravelVitals\Drivers\Stubs\StubLighthouseDriver => new StubLighthouseDriver());

    $manager = app(LighthouseDriverManager::class);

    expect($manager->resolve())->toBeInstanceOf(StubLighthouseDriver::class);
});

it('throws when no driver is available in auto mode', function (): void {
    config()->set('vitals.driver', 'auto');
    config()->set('vitals.drivers.pagespeed.api_key');
    config()->set('vitals.drivers.local.lighthouse_binary', '/nonexistent/lighthouse');
    // Browsershot v5 stock is already unavailable.

    $manager = app(LighthouseDriverManager::class);

    expect(fn () => $manager->resolve())->toThrow(InvalidArgumentException::class);
});
