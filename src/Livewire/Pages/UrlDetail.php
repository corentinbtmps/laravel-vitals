<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use LaravelVitals\Models\Url;
use Livewire\Component;

final class UrlDetail extends Component
{
    public int $url = 0;

    public function mount(int $url): void
    {
        $this->url = $url;
    }

    public function render(): View
    {
        $urlModel = Url::query()->findOrFail($this->url);
        $history = $urlModel->audits()
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->limit(50)
            ->get();

        return view('vitals::livewire.pages.url-detail', [
            'urlModel' => $urlModel,
            'history'  => $history,
        ])->layout('vitals::layouts.dashboard');
    }
}
