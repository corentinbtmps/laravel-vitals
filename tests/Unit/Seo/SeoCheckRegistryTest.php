<?php

declare(strict_types=1);

use LaravelVitals\Seo\SeoCheckRegistry;

it('returns all 22 registered checks', function (): void {
    $registry = new SeoCheckRegistry();
    expect($registry->all())->toHaveCount(22);
});

it('returns 22 enabled checks by default', function (): void {
    config(['vitals.seo.disabled_checks' => []]);
    $registry = new SeoCheckRegistry();
    expect($registry->enabled())->toHaveCount(22);
});

it('respects disabled_checks config', function (): void {
    config(['vitals.seo.disabled_checks' => ['noindex', 'canonical']]);
    $registry = new SeoCheckRegistry();
    $keys = array_map(fn ($c) => $c->key(), $registry->enabled());
    expect($keys)->not->toContain('noindex')
        ->and($keys)->not->toContain('canonical');
});

it('all check keys are unique', function (): void {
    $registry = new SeoCheckRegistry();
    $keys = array_map(fn ($c) => $c->key(), $registry->all());
    expect(array_unique($keys))->toHaveCount(count($keys));
});
