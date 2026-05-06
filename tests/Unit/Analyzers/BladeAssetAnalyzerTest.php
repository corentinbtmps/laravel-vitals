<?php

declare(strict_types=1);

use LaravelVitals\Analyzers\BladeAssetAnalyzer;
use LaravelVitals\Recommendations\AppContext;

beforeEach(function (): void {
    $this->basePath = __DIR__ . '/../../Fixtures/host-app';
    $this->ctx = new AppContext(
        basePath: $this->basePath,
        auditedPath: '/',
        assetUrls: ['https://example.test/build/assets/app-abc.js'],
        configSnapshot: [],
    );
});

it('declares which audit keys it supports', function (): void {
    $analyzer = new BladeAssetAnalyzer();

    expect($analyzer->supports('unused-javascript'))->toBeTrue()
        ->and($analyzer->supports('render-blocking-resources'))->toBeTrue()
        ->and($analyzer->supports('unused-css-rules'))->toBeTrue()
        ->and($analyzer->supports('imaginary'))->toBeFalse();
});

it('locates the Blade file emitting a flagged script src', function (): void {
    $analyzer = new BladeAssetAnalyzer();

    $refs = $analyzer->analyze(
        'unused-javascript',
        ['details' => ['items' => [['url' => 'https://example.test/build/assets/app-abc.js']]]],
        $this->ctx,
    );

    expect($refs)->toHaveCount(1);
    $ref = $refs->all()[0];

    expect($ref->file)->toContain('welcome.blade.php')
        ->and($ref->lineStart)->toBeGreaterThan(0)
        ->and($ref->snippet)->toContain('<script')
        ->and($ref->hint)->toContain('@vite');
});

it('flags raw <link rel=stylesheet> for render-blocking-resources', function (): void {
    $analyzer = new BladeAssetAnalyzer();

    $refs = $analyzer->analyze(
        'render-blocking-resources',
        ['details' => ['items' => [['url' => 'https://example.test/build/assets/app-abc.css']]]],
        $this->ctx,
    );

    expect($refs)->toHaveCount(1);
    expect($refs->all()[0]->snippet)->toContain('<link');
});

it('returns an empty collection when no view matches', function (): void {
    $analyzer = new BladeAssetAnalyzer();

    $refs = $analyzer->analyze(
        'unused-javascript',
        ['details' => ['items' => [['url' => 'https://example.test/missing.js']]]],
        $this->ctx,
    );

    expect($refs)->toHaveCount(0);
});
