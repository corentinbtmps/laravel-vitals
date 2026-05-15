<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use LaravelVitals\Models\Recommendation;
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

    /**
     * @return array<string, array{label: string, count: int, active: int, color: string, icon: string}>
     */
    public function categoryTiles(): array
    {
        $registry = new RecommendationRegistry();
        $allKeys = $registry->allKeys();

        // Active = how many distinct audit_keys of this category exist in the recommendations table
        $activeCounts = Recommendation::query()
            ->selectRaw('audit_key, count(*) as occurrences')
            ->groupBy('audit_key')
            ->pluck('occurrences', 'audit_key')
            ->all();

        /** @var array<string, array{label: string, color: string, icon: string}> $definitions */
        $definitions = [
            'performance'    => ['label' => 'Performance',    'color' => 'accent',  'icon' => 'bolt'],
            'accessibility'  => ['label' => 'Accessibility',  'color' => 'emerald', 'icon' => 'eye'],
            'best_practices' => ['label' => 'Best Practices', 'color' => 'sky',     'icon' => 'shield-check'],
            'seo'            => ['label' => 'SEO',            'color' => 'violet',  'icon' => 'magnifying-glass'],
        ];

        $tiles = [];
        foreach ($definitions as $cat => $def) {
            $keysInCat = array_values(array_filter($allKeys, function (string $k) use ($registry, $cat): bool {
                $descriptor = $registry->get($k);

                return $descriptor !== null && $descriptor->category === $cat;
            }));

            $tiles[$cat] = [
                'label'  => $def['label'],
                'color'  => $def['color'],
                'icon'   => $def['icon'],
                'count'  => count($keysInCat),
                'active' => (int) array_sum(array_intersect_key($activeCounts, array_flip($keysInCat))),
            ];
        }

        return $tiles;
    }

    public function render(): View
    {
        $registry = new RecommendationRegistry();

        // Active counts per audit_key for the "X active in your app" badge
        /** @var array<string, int> $activeCounts */
        $activeCounts = Recommendation::query()
            ->selectRaw('audit_key, count(*) as occurrences')
            ->groupBy('audit_key')
            ->pluck('occurrences', 'audit_key')
            ->all();

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
                'key'           => $key,
                'descriptor'    => $descriptor,
                'docs'          => RecommendationDocs::for($key),
                'active_count'  => (int) ($activeCounts[$key] ?? 0),
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
