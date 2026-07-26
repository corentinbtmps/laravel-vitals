<?php

declare(strict_types=1);

namespace LaravelVitals\Database;

/**
 * The index-relevant shape of a single SQL statement: the primary table and the
 * columns it filters, joins, or sorts on — each of which is a candidate for an index.
 */
final readonly class ParsedQuery
{
    /**
     * @param array<string, string> $columns  column name => reason ('where' | 'join' | 'order')
     */
    public function __construct(
        public string $table,
        public array $columns,
    ) {}

    public function hasCandidates(): bool
    {
        return $this->columns !== [];
    }
}
