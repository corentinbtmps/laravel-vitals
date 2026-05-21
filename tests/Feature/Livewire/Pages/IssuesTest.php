<?php

declare(strict_types=1);

use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Pages\Issues;
use Livewire\Livewire;

beforeEach(fn () => Vitals::authorize(fn (): true => true));

it('renders the issues page with default tab (top)', function (): void {
    Livewire::test(Issues::class)
        ->assertOk()
        ->assertSet('tab', 'top');
});

it('can switch to the all-recommendations tab', function (): void {
    Livewire::test(Issues::class)
        ->assertSet('tab', 'top')
        ->set('tab', 'all')
        ->assertSet('tab', 'all');
});

it('ignores unknown tab values', function (): void {
    Livewire::test(Issues::class)
        ->set('tab', 'nonexistent')
        ->assertSet('tab', 'top');
});

it('url param tab=all switches to all-recommendations tab', function (): void {
    Livewire::withQueryParams(['tab' => 'all'])
        ->test(Issues::class)
        ->assertSet('tab', 'all');
});

it('url param tab=top keeps default tab', function (): void {
    Livewire::withQueryParams(['tab' => 'top'])
        ->test(Issues::class)
        ->assertSet('tab', 'top');
});
