<?php

declare(strict_types=1);

use LaravelVitals\Analyzers\LaravelConfigAnalyzer;
use LaravelVitals\Recommendations\AppContext;

it('reports a config-cache-disabled CodeReference when no compiled config exists', function (): void {
    $ctx = new AppContext(
        basePath: '/tmp/host',
        auditedPath: '/',
        assetUrls: [],
        configSnapshot: [
            'config_cached' => false,
            'route_cached'  => true,
            'view_cached'   => true,
            'app_debug'     => false,
            'app_env'       => 'production',
        ],
    );

    $refs = (new LaravelConfigAnalyzer())->analyze('config-cache-disabled', [], $ctx);

    expect($refs)->toHaveCount(1);
    expect($refs->all()[0]->file)->toBe('artisan')
        ->and($refs->all()[0]->hint)->toContain('config:cache');
});

it('reports debug-on-prod when app_debug=true and env=production', function (): void {
    $ctx = new AppContext(
        basePath: '/tmp/host',
        auditedPath: '/',
        assetUrls: [],
        configSnapshot: [
            'app_debug' => true,
            'app_env'   => 'production',
        ],
    );

    $refs = (new LaravelConfigAnalyzer())->analyze('debug-on-prod', [], $ctx);

    expect($refs)->toHaveCount(1);
    expect($refs->all()[0]->file)->toBe('.env')
        ->and($refs->all()[0]->hint)->toContain('APP_DEBUG=false');
});

it('returns an empty collection when nothing is misconfigured', function (): void {
    $ctx = new AppContext(
        basePath: '/tmp/host',
        auditedPath: '/',
        assetUrls: [],
        configSnapshot: [
            'config_cached' => true, 'route_cached' => true, 'view_cached' => true,
            'app_debug' => false, 'app_env' => 'production',
        ],
    );

    expect((new LaravelConfigAnalyzer())->analyze('config-cache-disabled', [], $ctx))->toHaveCount(0)
        ->and((new LaravelConfigAnalyzer())->analyze('debug-on-prod', [], $ctx))->toHaveCount(0);
});

it('supports the four custom keys', function (): void {
    $a = new LaravelConfigAnalyzer();

    expect($a->supports('config-cache-disabled'))->toBeTrue()
        ->and($a->supports('route-cache-disabled'))->toBeTrue()
        ->and($a->supports('view-cache-disabled'))->toBeTrue()
        ->and($a->supports('debug-on-prod'))->toBeTrue()
        ->and($a->supports('imaginary'))->toBeFalse();
});
