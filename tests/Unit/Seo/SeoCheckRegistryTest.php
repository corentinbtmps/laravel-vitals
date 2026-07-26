<?php

declare(strict_types=1);

use LaravelVitals\Seo\SeoCheckRegistry;

it('returns all 30 registered checks', function (): void {
    $registry = new SeoCheckRegistry();
    expect($registry->all())->toHaveCount(30);
});

it('returns 30 enabled checks by default', function (): void {
    config(['vitals.seo.disabled_checks' => []]);
    $registry = new SeoCheckRegistry();
    expect($registry->enabled())->toHaveCount(30);
});

it('excludes the 8 agentic checks when agentic is disabled', function (): void {
    config(['vitals.seo.disabled_checks' => [], 'vitals.seo.agentic.enabled' => false]);
    $registry = new SeoCheckRegistry();

    $categories = array_map(
        fn (\LaravelVitals\Seo\Contracts\SeoCheck $c) => $c->category(),
        $registry->enabled(),
    );

    expect($registry->enabled())->toHaveCount(22)
        ->and($categories)->not->toContain(\LaravelVitals\Seo\Enums\SeoCheckCategory::Agentic);
});

it('respects disabled_checks config', function (): void {
    config(['vitals.seo.disabled_checks' => ['noindex', 'canonical']]);
    $registry = new SeoCheckRegistry();
    $keys = array_map(fn (\LaravelVitals\Seo\Contracts\SeoCheck $c): string => $c->key(), $registry->enabled());
    expect($keys)->not->toContain('noindex')
        ->and($keys)->not->toContain('canonical');
});

it('all check keys are unique', function (): void {
    $registry = new SeoCheckRegistry();
    $keys = array_map(fn (\LaravelVitals\Seo\Contracts\SeoCheck $c): string => $c->key(), $registry->all());
    expect(array_unique($keys))->toHaveCount(count($keys));
});
