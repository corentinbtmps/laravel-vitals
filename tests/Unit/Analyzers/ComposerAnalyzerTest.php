<?php

declare(strict_types=1);

use LaravelVitals\Analyzers\ComposerAnalyzer;
use LaravelVitals\Recommendations\AppContext;

it('returns no references when composer.json looks healthy', function (): void {
    $ctx = new AppContext(
        basePath: __DIR__ . '/../../Fixtures/host-app',
        auditedPath: '/',
        assetUrls: [],
        configSnapshot: [],
    );

    $refs = (new ComposerAnalyzer())->analyze('outdated-deps', [], $ctx);

    expect($refs)->toHaveCount(0);
});

it('flags missing PHP constraint', function (): void {
    $tmp = sys_get_temp_dir() . '/vitals-composer-test-' . uniqid();
    mkdir($tmp);
    file_put_contents($tmp . '/composer.json', json_encode([
        'name' => 'vendor/x',
        'require' => ['laravel/framework' => '^11.0'],
    ]));

    $ctx = new AppContext(basePath: $tmp, auditedPath: '/', assetUrls: [], configSnapshot: []);

    $refs = (new ComposerAnalyzer())->analyze('missing-php-version', [], $ctx);

    expect($refs->count())->toBe(1)
        ->and($refs->all()[0]->file)->toBe('composer.json')
        ->and($refs->all()[0]->hint)->toContain('php');

    unlink($tmp . '/composer.json');
    rmdir($tmp);
});

it('supports its custom keys', function (): void {
    $a = new ComposerAnalyzer();
    expect($a->supports('outdated-deps'))->toBeTrue()
        ->and($a->supports('missing-php-version'))->toBeTrue()
        ->and($a->supports('imaginary'))->toBeFalse();
});
