<?php

declare(strict_types=1);

use LaravelVitals\Analyzers\BladeViewAnalyzer;
use LaravelVitals\Recommendations\AppContext;

it('produces a CodeReference for each slow view declared in the snapshot', function (): void {
    $ctx = new AppContext(
        basePath: __DIR__ . '/../../Fixtures/host-app',
        auditedPath: '/',
        assetUrls: [],
        configSnapshot: [
            'slow_views' => [
                ['name' => 'welcome', 'time_ms' => 124.5],
            ],
        ],
    );

    $refs = (new BladeViewAnalyzer())->analyze('slow-views', [], $ctx);

    expect($refs->count())->toBe(1)
        ->and($refs->all()[0]->file)->toContain('welcome.blade.php');
});

it('returns no refs when no slow views are reported', function (): void {
    $ctx = new AppContext(
        basePath: __DIR__ . '/../../Fixtures/host-app',
        auditedPath: '/',
        assetUrls: [],
        configSnapshot: ['slow_views' => []],
    );

    expect((new BladeViewAnalyzer())->analyze('slow-views', [], $ctx))->toHaveCount(0);
});

it('supports its keys', function (): void {
    $a = new BladeViewAnalyzer();

    expect($a->supports('slow-views'))->toBeTrue()
        ->and($a->supports('imaginary'))->toBeFalse();
});
