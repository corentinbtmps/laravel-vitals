<?php

declare(strict_types=1);

use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Pages\Learn;
use Livewire\Livewire;

beforeEach(fn () => Vitals::authorize(fn () => true));

it('lists all known recommendations grouped by category', function (): void {
    Livewire::test(Learn::class)
        ->assertOk()
        ->assertSeeText('Reduce unused JavaScript')
        ->assertSeeText('performance');
});

it('filters by category', function (): void {
    Livewire::test(Learn::class)
        ->set('filter', 'accessibility')
        ->assertSet('filter', 'accessibility')
        ->assertSeeText('color-contrast')
        ->assertDontSeeText('config-cache-disabled');
});

it('rejects unknown filter values', function (): void {
    Livewire::test(Learn::class)
        ->set('filter', 'imaginary')
        ->assertSet('filter', 'all');
});
