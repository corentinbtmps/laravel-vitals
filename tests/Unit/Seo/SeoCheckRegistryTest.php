<?php

declare(strict_types=1);

use LaravelVitals\Seo\SeoCheckRegistry;

it('returns all 25 registered checks', function (): void {
    $registry = new SeoCheckRegistry();
    expect($registry->all())->toHaveCount(25);
});

it('returns 22 enabled checks by default (3 opinion checks disabled)', function (): void {
    config(['vitals.seo.enable_opinion_checks' => false, 'vitals.seo.disabled_checks' => []]);
    $registry = new SeoCheckRegistry();
    expect($registry->enabled())->toHaveCount(22);
});

it('returns 25 enabled checks when opinion checks are enabled', function (): void {
    config(['vitals.seo.enable_opinion_checks' => true, 'vitals.seo.disabled_checks' => []]);
    $registry = new SeoCheckRegistry();
    expect($registry->enabled())->toHaveCount(25);
});

it('respects disabled_checks config', function (): void {
    config(['vitals.seo.enable_opinion_checks' => false, 'vitals.seo.disabled_checks' => ['noindex', 'canonical']]);
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
