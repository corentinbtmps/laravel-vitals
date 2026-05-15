<?php

declare(strict_types=1);

namespace LaravelVitals\Search;

use Illuminate\Support\Collection;
use LaravelVitals\Models\Recommendation;
use Spatie\Searchable\SearchAspect;

/**
 * Searches persisted recommendations by audit_key.
 * Results are deduplicated by audit_key so the same issue from
 * multiple audits appears only once.
 */
final class RecommendationSearchAspect extends SearchAspect
{
    /** @return Collection<int, \LaravelVitals\Search\SearchableItem> */
    public function getResults(string $term): Collection
    {
        return Recommendation::query()
            ->where('audit_key', 'like', "%{$term}%")
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->unique('audit_key')
            ->take(10)
            ->values()
            ->map(fn (\LaravelVitals\Models\Recommendation $reco): \LaravelVitals\Search\SearchableItem => new \LaravelVitals\Search\SearchableItem(
                title: __($reco->title_key) . ' (' . $reco->audit_key . ')',
                url: route('vitals.issue.detail', ['auditKey' => $reco->audit_key]),
            ));
    }

    public function getType(): string
    {
        return 'recommendations';
    }
}
