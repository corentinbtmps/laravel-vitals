<?php

declare(strict_types=1);

use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Pages\Insights;
use Livewire\Livewire;

beforeEach(fn () => Vitals::authorize(fn (): true => true));

it('shows empty state when no audit history exists', function (): void {
    Livewire::test(Insights::class)
        ->assertOk()
        ->assertSee('Not enough audit history');
});

it('renders without error when no data is present', function (): void {
    Livewire::test(Insights::class)
        ->assertOk()
        ->assertViewHas('quickWins')
        ->assertViewHas('worsening')
        ->assertViewHas('improving');
});
