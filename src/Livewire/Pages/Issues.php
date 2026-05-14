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

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['top', 'all'], true)) {
            $this->tab = $tab;
        }
    }

    public function render(): View
    {
        return view('vitals::livewire.pages.issues')->layout('vitals::layouts.dashboard');
    }
}
