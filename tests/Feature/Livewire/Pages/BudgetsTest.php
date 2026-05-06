<?php

declare(strict_types=1);

use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Pages\Budgets;
use Livewire\Livewire;

beforeEach(fn () => Vitals::authorize(fn (): true => true));

it('renders the configured budgets', function (): void {
    config()->set('vitals.budgets', [
        'lcp_ms' => ['warning' => 2500, 'critical' => 4000],
        'per_url' => [],
    ]);

    Livewire::test(Budgets::class)
        ->assertOk()
        ->assertSeeText('lcp_ms')
        ->assertSeeText('2500');
});
