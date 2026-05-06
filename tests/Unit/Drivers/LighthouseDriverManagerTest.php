<?php

declare(strict_types=1);

use LaravelVitals\Contracts\LighthouseDriver;
use LaravelVitals\Drivers\LighthouseDriverManager;
use LaravelVitals\Drivers\LocalLighthouseDriver;
use LaravelVitals\Drivers\PageSpeedApiDriver;
use LaravelVitals\Drivers\PlaywrightDriver;
use LaravelVitals\Drivers\Stubs\StubLighthouseDriver;

it('resolves a driver by name from the registered map', function (): void {
    $manager = app(LighthouseDriverManager::class);

    expect($manager->driver('local'))->toBeInstanceOf(LocalLighthouseDriver::class)
        ->and($manager->driver('playwright'))->toBeInstanceOf(PlaywrightDriver::class)
        ->and($manager->driver('pagespeed'))->toBeInstanceOf(PageSpeedApiDriver::class);
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

    // local is unavailable (binary missing). playwright is available (node is in PATH).
    $manager = app(LighthouseDriverManager::class);

    expect($manager->resolve())->toBeInstanceOf(PlaywrightDriver::class);
});

it('falls back to a stub-bound driver when one is wired into the container', function (): void {
    config()->set('vitals.driver', 'auto');
    config()->set('vitals.drivers.pagespeed.api_key');
    config()->set('vitals.drivers.local.lighthouse_binary', '/nonexistent/lighthouse');

    // Inject a stub for playwright so it returns isAvailable() = true.
    app()->bind(PlaywrightDriver::class, fn (): StubLighthouseDriver => new StubLighthouseDriver());

    $manager = app(LighthouseDriverManager::class);

    expect($manager->resolve())->toBeInstanceOf(StubLighthouseDriver::class);
});

it('throws when no driver is available in auto mode', function (): void {
    config()->set('vitals.driver', 'auto');
    config()->set('vitals.drivers.pagespeed.api_key');
    config()->set('vitals.drivers.local.lighthouse_binary', '/nonexistent/lighthouse');
    config()->set('vitals.drivers.playwright.node_binary', '/nonexistent/node');

    $manager = app(LighthouseDriverManager::class);

    expect(fn () => $manager->resolve())->toThrow(InvalidArgumentException::class);
});

it('resolves playwright by name', function (): void {
    $manager = app(LighthouseDriverManager::class);

    expect($manager->driver('playwright'))->toBeInstanceOf(PlaywrightDriver::class);
});
