<?php

declare(strict_types=1);

use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Pages\UrlsList;
use LaravelVitals\Models\Url;
use Livewire\Livewire;

beforeEach(fn () => Vitals::authorize(fn (): true => true));

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

it('renders a link to the url-detail page for each Url', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);

    Livewire::test(UrlsList::class)
        ->assertOk()
        ->assertSee("/vitals/urls/{$url->id}");
});

it('toggles pin status on a URL', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);

    Livewire::test(UrlsList::class)
        ->call('togglePin', $url->id);

    expect($url->fresh()->pinned_at)->not->toBeNull();

    Livewire::test(UrlsList::class)
        ->call('togglePin', $url->id);

    expect($url->fresh()->pinned_at)->toBeNull();
});

it('lists pinned urls in a separate group', function (): void {
    $pinned = Url::create(['label' => 'home', 'path' => '/', 'pinned_at' => now()]);
    $other  = Url::create(['label' => 'about', 'path' => '/about']);

    Livewire::test(UrlsList::class)
        ->assertViewHas('pinnedUrls', fn ($urls) => $urls->contains('id', $pinned->id) && ! $urls->contains('id', $other->id));
});
