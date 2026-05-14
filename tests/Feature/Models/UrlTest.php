<?php

declare(strict_types=1);

use LaravelVitals\Enums\Device;
use LaravelVitals\Models\Url;

it('persists a Url with the expected casts', function (): void {
    $url = Url::create([
        'label'   => 'home',
        'path'    => '/',
        'device'  => 'both',
        'options' => ['categories' => ['performance']],
        'enabled' => true,
    ]);

    expect($url->fresh())
        ->label->toBe('home')
        ->path->toBe('/')
        ->device->toBe(Device::Both)
        ->options->toBe(['categories' => ['performance']])
        ->enabled->toBeTrue();
});

it('exposes audits relation', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);

    expect($url->audits())->toBeInstanceOf(
        \Illuminate\Database\Eloquent\Relations\HasMany::class
    );
});
