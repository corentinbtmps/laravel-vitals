<?php

declare(strict_types=1);

use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Pages\UrlsList;
use LaravelVitals\Models\Url;
use Livewire\Livewire;

beforeEach(fn () => Vitals::authorize(fn () => true));

it('lists configured URLs', function (): void {
    Url::create(['label' => 'home', 'path' => '/']);
    Url::create(['label' => 'product', 'path' => '/products/1']);

    Livewire::test(UrlsList::class)
        ->assertOk()
        ->assertSeeText('home')
        ->assertSeeText('product')
        ->assertSeeText('/products/1');
});

it('renders a CTA when no URLs exist', function (): void {
    Livewire::test(UrlsList::class)
        ->assertSee('No URLs configured');
});
