<?php

declare(strict_types=1);

namespace LaravelVitals\Search;

use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

/**
 * A lightweight value object that makes arbitrary data searchable
 * without requiring a full Eloquent model.
 */
final class SearchableItem implements Searchable
{
    public function __construct(
        private readonly string $title,
        private readonly string $url,
    ) {}

    public function getSearchResult(): SearchResult
    {
        return new SearchResult($this, $this->title, $this->url);
    }
}
