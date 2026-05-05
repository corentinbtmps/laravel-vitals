<?php

declare(strict_types=1);

it('loads the package config with expected top-level keys', function (): void {
    expect(config('vitals'))
        ->toBeArray()
        ->toHaveKeys([
            'driver',
            'drivers',
            'database',
            'storage',
            'retention',
            'telemetry',
            'analyzers',
            'budgets',
            'ui',
            'dashboard',
            'urls',
        ]);
});

it('defaults the dashboard middleware to the web group', function (): void {
    expect(config('vitals.dashboard.middleware'))->toBe(['web']);
});

it('defaults retention to 90 days', function (): void {
    expect(config('vitals.retention.days'))->toBe(90);
});
