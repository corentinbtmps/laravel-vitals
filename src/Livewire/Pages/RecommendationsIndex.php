<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use LaravelVitals\Models\Recommendation;
use Livewire\Component;

final class RecommendationsIndex extends Component
{
    public function render(): View
    {
        $aggregated = Recommendation::query()
            ->selectRaw('audit_key, category, severity, title_key, count(*) as occurrences')
            ->groupBy('audit_key', 'category', 'severity', 'title_key')
            ->orderByDesc('occurrences')
            ->limit(50)
            ->get();

        return view('vitals::livewire.pages.recommendations-index', ['rows' => $aggregated])
            ->layout('vitals::layouts.dashboard');
    }
}
