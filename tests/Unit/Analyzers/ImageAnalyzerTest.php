<?php

declare(strict_types=1);

use LaravelVitals\Analyzers\ImageAnalyzer;
use LaravelVitals\Recommendations\AppContext;

beforeEach(function (): void {
    $this->ctx = new AppContext(
        basePath: __DIR__ . '/../../Fixtures/host-app',
        auditedPath: '/',
        assetUrls: ['/images/hero.jpg'],
        configSnapshot: [],
    );
});

it('supports modern-image-formats and offscreen-images', function (): void {
    $analyzer = new ImageAnalyzer();

    expect($analyzer->supports('modern-image-formats'))->toBeTrue()
        ->and($analyzer->supports('offscreen-images'))->toBeTrue()
        ->and($analyzer->supports('uses-responsive-images'))->toBeTrue()
        ->and($analyzer->supports('imaginary'))->toBeFalse();
});

it('finds <img> tags without loading="lazy" for offscreen-images', function (): void {
    $analyzer = new ImageAnalyzer();

    $refs = $analyzer->analyze(
        'offscreen-images',
        ['details' => ['items' => [['url' => '/images/hero.jpg']]]],
        $this->ctx,
    );

    expect($refs)->toHaveCount(1);
    expect($refs->all()[0]->snippet)->toContain('<img')
        ->and($refs->all()[0]->hint)->toContain('lazy');
});

it('finds JPG/PNG <img> tags for modern-image-formats', function (): void {
    $analyzer = new ImageAnalyzer();

    $refs = $analyzer->analyze(
        'modern-image-formats',
        ['details' => ['items' => [['url' => '/images/hero.jpg']]]],
        $this->ctx,
    );

    expect($refs->count())->toBeGreaterThanOrEqual(1);
    expect($refs->all()[0]->hint)->toMatch('/webp|avif/i');
});
