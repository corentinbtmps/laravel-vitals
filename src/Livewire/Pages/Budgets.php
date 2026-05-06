<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use Livewire\Component;

final class Budgets extends Component
{
    public function render(): View
    {
        $budgets = (array) config('vitals.budgets', []);
        $perUrl  = (array) ($budgets['per_url'] ?? []);
        unset($budgets['per_url']);

        return view('vitals::livewire.pages.budgets', [
            'budgets' => $budgets,
            'perUrl'  => $perUrl,
        ])->layout('vitals::layouts.dashboard');
    }
}
