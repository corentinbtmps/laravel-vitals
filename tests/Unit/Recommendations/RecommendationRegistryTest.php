<?php

declare(strict_types=1);

use LaravelVitals\Recommendations\RecommendationRegistry;

it('exposes descriptors for known Lighthouse audit keys', function (): void {
    $reg = new RecommendationRegistry();

    $unused = $reg->get('unused-javascript');
    expect($unused)->not->toBeNull()
        ->and($unused->category)->toBe('performance')
        ->and($unused->severity)->toBeIn(['info', 'warning', 'critical'])
        ->and($unused->titleKey)->toBe('vitals::vitals.recommendations.unused-javascript.title');
});

it('exposes descriptors for custom (config) audit keys', function (): void {
    $reg = new RecommendationRegistry();

    expect($reg->get('config-cache-disabled'))->not->toBeNull()
        ->and($reg->get('debug-on-prod'))->not->toBeNull()
        ->and($reg->get('n-plus-one-detected'))->not->toBeNull();
});

it('returns null for an unknown audit key', function (): void {
    expect((new RecommendationRegistry())->get('imaginary'))->toBeNull();
});

it('lists every key currently registered', function (): void {
    $reg = new RecommendationRegistry();

    $keys = $reg->allKeys();

    expect($keys)->toBeArray()
        ->and(count($keys))->toBeGreaterThan(15);
});
