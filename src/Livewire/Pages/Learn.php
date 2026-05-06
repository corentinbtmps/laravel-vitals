<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use LaravelVitals\Recommendations\RecommendationDocs;
use LaravelVitals\Recommendations\RecommendationRegistry;
use Livewire\Component;

final class Learn extends Component
{
    public string $filter = 'all';

    /** @var array<int, string> */
    public array $availableFilters = ['all', 'performance', 'accessibility', 'best_practices', 'seo'];

    public function setFilter(string $filter): void
    {
        if (in_array($filter, $this->availableFilters, true)) {
            $this->filter = $filter;
        }
    }

    public function render(): View
    {
        $registry = new RecommendationRegistry();
        $entries = [];

        foreach ($registry->allKeys() as $key) {
            $descriptor = $registry->get($key);
            if ($descriptor === null) {
                continue;
            }

            if ($this->filter !== 'all' && $descriptor->category !== $this->filter) {
                continue;
            }

            $entries[] = [
                'key'        => $key,
                'descriptor' => $descriptor,
                'docs'       => RecommendationDocs::for($key),
            ];
        }

        // Group by category for display
        $grouped = collect($entries)->groupBy(fn ($e) => $e['descriptor']->category);

        return view('vitals::livewire.pages.learn', [
            'grouped'  => $grouped,
            'filter'   => $this->filter,
            'allCount' => count($registry->allKeys()),
        ])->layout('vitals::layouts.dashboard');
    }
}
