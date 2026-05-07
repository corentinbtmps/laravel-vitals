<?php

declare(strict_types=1);

use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Components\Spotlight;
use LaravelVitals\Models\Url;
use Livewire\Livewire;

beforeEach(fn () => Vitals::authorize(fn (): true => true));

it('does not search for queries shorter than 2 characters', function (): void {
    Livewire::test(Spotlight::class)
        ->set('query', 'a')
        ->assertSee('Type at least');
});

it('shows hint text when query is empty', function (): void {
    Livewire::test(Spotlight::class)
        ->set('query', '')
        ->assertSee('Type at least');
});

it('returns matching URLs for a query', function (): void {
    Url::create(['label' => 'homepage', 'path' => '/', 'device' => 'desktop', 'enabled' => true]);

    Livewire::test(Spotlight::class)
        ->set('query', 'home')
        ->assertSee('homepage');
});

it('returns Learn results for known audit_keys', function (): void {
    Livewire::test(Spotlight::class)
        ->set('query', 'unused-j')
        ->assertSee('unused-javascript');
});

it('shows no-results message when query matches nothing', function (): void {
    Livewire::test(Spotlight::class)
        ->set('query', 'xyzzy-no-match-ever')
        ->assertSee('No results');
});
