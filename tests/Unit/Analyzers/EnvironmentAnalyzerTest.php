<?php

declare(strict_types=1);

use LaravelVitals\Analyzers\EnvironmentAnalyzer;
use LaravelVitals\Recommendations\AppContext;

it('flags queue.default = sync in production', function (): void {
    $ctx = new AppContext(
        basePath: '/tmp',
        auditedPath: '/',
        assetUrls: [],
        configSnapshot: [
            'app_env'       => 'production',
            'session_driver'=> 'redis',
            'cache_driver'  => 'redis',
            'queue_default' => 'sync',
        ],
    );

    $refs = (new EnvironmentAnalyzer())->analyze('queue-driver-sync-prod', [], $ctx);

    expect($refs)->toHaveCount(1)
        ->and($refs->all()[0]->file)->toBe('.env')
        ->and($refs->all()[0]->snippet)->toContain('QUEUE');
});

it('does not flag queue.default = sync in local', function (): void {
    $ctx = new AppContext(
        basePath: '/tmp',
        auditedPath: '/',
        assetUrls: [],
        configSnapshot: ['app_env' => 'local', 'queue_default' => 'sync'],
    );

    expect((new EnvironmentAnalyzer())->analyze('queue-driver-sync-prod', [], $ctx))->toHaveCount(0);
});

it('flags session-driver-file in non-local', function (): void {
    $ctx = new AppContext(
        basePath: '/tmp',
        auditedPath: '/',
        assetUrls: [],
        configSnapshot: ['app_env' => 'production', 'session_driver' => 'file'],
    );

    expect((new EnvironmentAnalyzer())->analyze('session-driver-file', [], $ctx))->toHaveCount(1);
});

it('supports its keys', function (): void {
    $a = new EnvironmentAnalyzer();

    expect($a->supports('session-driver-file'))->toBeTrue()
        ->and($a->supports('cache-driver-file'))->toBeTrue()
        ->and($a->supports('queue-driver-sync-prod'))->toBeTrue()
        ->and($a->supports('imaginary'))->toBeFalse();
});
