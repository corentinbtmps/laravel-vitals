<?php

declare(strict_types=1);

namespace LaravelVitals\Search;

use Illuminate\Support\Collection;
use LaravelVitals\Enums\AuditStatus;
use LaravelVitals\Models\Audit;
use Spatie\Searchable\SearchAspect;

/**
 * Searches completed audits by the label/path of their associated URL.
 */
final class AuditSearchAspect extends SearchAspect
{
    /** @return Collection<int, \LaravelVitals\Models\Audit> */
    public function getResults(string $term): Collection
    {
        return Audit::query()
            ->with('url')
            ->whereHas('url', function ($q) use ($term): void {
                $q->where('label', 'like', "%{$term}%")
                  ->orWhere('path', 'like', "%{$term}%");
            })
            ->where('status', AuditStatus::Completed)
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get();
    }

    public function getType(): string
    {
        return 'audits';
    }
}
