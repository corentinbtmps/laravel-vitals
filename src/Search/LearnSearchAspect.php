<?php

declare(strict_types=1);

namespace LaravelVitals\Search;

use Illuminate\Support\Collection;
use LaravelVitals\Recommendations\RecommendationRegistry;
use Spatie\Searchable\SearchAspect;

/**
 * Searches the in-memory RecommendationRegistry for known audit keys
 * and their translated titles.  Returns results as SearchableItem wrappers.
 */
final class LearnSearchAspect extends SearchAspect
{
    /** @return Collection<int, SearchableItem> */
    public function getResults(string $term): Collection
    {
        $term = mb_strtolower($term);
        $registry = new RecommendationRegistry();

        return collect($registry->allKeys())
            ->filter(function (string $key) use ($term): bool {
                if (str_contains($key, $term)) {
                    return true;
                }

                $title = __('vitals::vitals.recommendations.' . $key . '.title');

                return is_string($title) && str_contains(mb_strtolower($title), $term);
            })
            ->take(5)
            ->map(fn (string $key): SearchableItem => new SearchableItem(
                title: __('vitals::vitals.recommendations.' . $key . '.title') . ' (' . $key . ')',
                url: route('vitals.learn') . '#' . $key,
            ))
            ->values();
    }

    public function getType(): string
    {
        return 'learn';
    }
}
