<?php

declare(strict_types=1);

use LaravelVitals\Analyzers\ViteConfigAnalyzer;
use LaravelVitals\Recommendations\AppContext;

beforeEach(function (): void {
    $this->ctx = new AppContext(
        basePath: __DIR__ . '/../../Fixtures/host-app',
        auditedPath: '/',
        assetUrls: [],
        configSnapshot: [],
    );
});

it('flags minify: false', function (): void {
    $refs = (new ViteConfigAnalyzer())->analyze('unminified-javascript', [], $this->ctx);

    expect($refs->count())->toBeGreaterThanOrEqual(1);
    expect($refs->all()[0]->file)->toContain('vite.config.js')
        ->and($refs->all()[0]->snippet)->toContain('minify');
});

it('returns no references when no vite config exists', function (): void {
    $ctx = new AppContext(
        basePath: '/nonexistent',
        auditedPath: '/',
        assetUrls: [],
        configSnapshot: [],
    );

    expect((new ViteConfigAnalyzer())->analyze('unminified-javascript', [], $ctx)->count())->toBe(0);
});
