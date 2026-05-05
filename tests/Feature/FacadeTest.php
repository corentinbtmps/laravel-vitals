<?php

declare(strict_types=1);

use LaravelVitals\Facades\Vitals as VitalsFacade;
use LaravelVitals\Vitals;

it('resolves the Vitals service through the container and the facade', function (): void {
    expect(app(Vitals::class))->toBeInstanceOf(Vitals::class)
        ->and(app('vitals'))->toBe(app(Vitals::class));
});

it('lets the host app override the dashboard authorize closure', function (): void {
    VitalsFacade::authorize(fn ($user): bool => $user?->is_admin === true);

    $closure = app(Vitals::class)->authorizeCallback();

    expect($closure)->toBeInstanceOf(Closure::class);
});
