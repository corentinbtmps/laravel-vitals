<?php

declare(strict_types=1);

use LaravelVitals\VitalsServiceProvider;

it('registers the Vitals service provider', function (): void {
    expect(app()->getLoadedProviders())
        ->toHaveKey(VitalsServiceProvider::class);
});
