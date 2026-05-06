<?php

declare(strict_types=1);

use LaravelVitals\Recommendations\RecommendationDocs;
use LaravelVitals\Recommendations\RecommendationRegistry;

it('returns null for an unknown audit key', function (): void {
    expect(RecommendationDocs::for('imaginary-key'))->toBeNull();
});

it('provides docs for unused-javascript with a canonical doc link', function (): void {
    $entry = RecommendationDocs::for('unused-javascript');

    expect($entry)->not->toBeNull()
        ->and($entry['why'])->toBeString()
        ->and($entry['docs'])->toBeArray()
        ->and($entry['docs'][0]['url'])->toStartWith('https://');
});

it('every key in the docs registry exists in the recommendation registry', function (): void {
    $registry = new RecommendationRegistry();
    $registryKeys = $registry->allKeys();

    foreach (array_keys(RecommendationDocs::all()) as $docKey) {
        expect(in_array($docKey, $registryKeys, true))
            ->toBeTrue("Docs key [$docKey] is not registered in RecommendationRegistry");
    }
});

it('every entry has at least one doc link', function (): void {
    foreach (RecommendationDocs::all() as $key => $entry) {
        expect($entry['docs'])->toBeArray()
            ->and($entry['docs'])->not->toBeEmpty("Entry [$key] has no doc links");

        foreach ($entry['docs'] as $doc) {
            expect($doc)->toHaveKeys(['label', 'url']);
            expect($doc['url'])->toStartWith('http');
        }
    }
});

it('covers all custom audit keys (not just lighthouse)', function (): void {
    $covered = array_keys(RecommendationDocs::all());

    foreach (['n-plus-one-detected', 'config-cache-disabled', 'debug-on-prod', 'excessive-dom-size', 'large-payload'] as $criticalKey) {
        expect($covered)->toContain($criticalKey);
    }
});
