<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use LaravelVitals\Models\Url;
use Livewire\Component;

final class UrlsList extends Component
{
    public function render(): View
    {
        $urls = Url::query()
            ->withCount('audits')
            ->orderBy('label')
            ->get();

        return view('vitals::livewire.pages.urls-list', ['urls' => $urls])
            ->layout('vitals::layouts.dashboard');
    }
}
