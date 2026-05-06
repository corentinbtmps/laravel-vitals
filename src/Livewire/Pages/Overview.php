<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use LaravelVitals\Models\Audit;
use Livewire\Component;

final class Overview extends Component
{
    public function render(): View
    {
        $recent = Audit::query()
            ->with('url')
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays(7))
            ->orderByDesc('completed_at')
            ->limit(20)
            ->get();

        $averages = [
            'performance'    => (int) round((float) $recent->avg('score_performance')),
            'accessibility'  => (int) round((float) $recent->avg('score_accessibility')),
            'best_practices' => (int) round((float) $recent->avg('score_best_practices')),
            'seo'            => (int) round((float) $recent->avg('score_seo')),
        ];

        return view('vitals::livewire.pages.overview', [
            'recent'   => $recent,
            'averages' => $averages,
        ])->layout('vitals::layouts.dashboard');
    }
}
