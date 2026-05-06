<?php

declare(strict_types=1);

use LaravelVitals\Models\Url;
use LaravelVitals\Support\UrlSeeder;

it('creates Url rows for labels declared in config', function (): void {
    config()->set('vitals.urls', [
        'home'    => '/',
        'product' => '/products/42',
    ]);

    (new UrlSeeder())->sync();

    expect(Url::count())->toBe(2)
        ->and(Url::where('label', 'home')->value('path'))->toBe('/')
        ->and(Url::where('label', 'product')->value('path'))->toBe('/products/42');
});

it('updates the path of an existing label when config changes', function (): void {
    Url::create(['label' => 'home', 'path' => '/old']);

    config()->set('vitals.urls', ['home' => '/']);

    (new UrlSeeder())->sync();

    expect(Url::where('label', 'home')->value('path'))->toBe('/')
        ->and(Url::count())->toBe(1);
});

it('leaves labels not present in config untouched', function (): void {
    Url::create(['label' => 'manual', 'path' => '/manual']);
    config()->set('vitals.urls', ['home' => '/']);

    (new UrlSeeder())->sync();

    expect(Url::where('label', 'manual')->exists())->toBeTrue()
        ->and(Url::count())->toBe(2);
});
