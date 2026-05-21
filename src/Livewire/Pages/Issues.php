<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

final class Issues extends Component
{
    #[Url(as: 'tab', keep: false)]
    public string $tab = 'top';  // 'top' | 'all'

    public function updatedTab(string $value): void
    {
        if (! in_array($value, ['top', 'all'], true)) {
            $this->tab = 'top';
        }
    }

    public function render(): View
    {
        return view('vitals::livewire.pages.issues')->layout('vitals::layouts.dashboard');
    }
}
