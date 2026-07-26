<?php

declare(strict_types=1);

namespace LaravelVitals\Database;

use Illuminate\Support\Facades\Schema;

/**
 * Reads the live schema to answer two questions per table: does a column exist,
 * and is it already usefully indexed (the leading column of some index, or part
 * of the primary key). Results are memoised per table for the audit run.
 *
 * All introspection is wrapped defensively — if the driver cannot be inspected
 * (permissions, unsupported grammar), the table is treated as "unknown" and the
 * advisor skips it rather than emitting a false suggestion.
 */
final class SchemaIndexInspector
{
    /** @var array<string, list<string>|null> table => existing columns (lowercased), or null when unreadable */
    private array $columns = [];

    /** @var array<string, list<string>> table => leading-indexed columns (lowercased) */
    private array $indexed = [];

    public function __construct(
        private readonly ?string $connection = null,
    ) {}

    public function tableExists(string $table): bool
    {
        return $this->columnsFor($table) !== null;
    }

    public function hasColumn(string $table, string $column): bool
    {
        $columns = $this->columnsFor($table);

        return $columns !== null && in_array(strtolower($column), $columns, true);
    }

    public function isIndexed(string $table, string $column): bool
    {
        return in_array(strtolower($column), $this->indexedFor($table), true);
    }

    /**
     * @return list<string>|null
     */
    private function columnsFor(string $table): ?array
    {
        if (array_key_exists($table, $this->columns)) {
            return $this->columns[$table];
        }

        try {
            $schema = Schema::connection($this->connection);

            if (! $schema->hasTable($table)) {
                return $this->columns[$table] = null;
            }

            $cols = array_map(strtolower(...), $schema->getColumnListing($table));

            return $this->columns[$table] = $cols;
        } catch (\Throwable) {
            return $this->columns[$table] = null;
        }
    }

    /**
     * @return list<string>
     */
    private function indexedFor(string $table): array
    {
        if (isset($this->indexed[$table])) {
            return $this->indexed[$table];
        }

        $leading = [];

        try {
            foreach (Schema::connection($this->connection)->getIndexes($table) as $index) {
                $cols = $index['columns'] ?? [];
                if (is_array($cols) && $cols !== []) {
                    // Only the leading column of an index accelerates a standalone filter.
                    $leading[] = strtolower($cols[0]);
                }
            }
        } catch (\Throwable) {
            // Leave $leading empty — nothing is considered indexed, but tableExists()
            // still gates whether we emit anything at all.
        }

        return $this->indexed[$table] = array_values(array_unique($leading));
    }
}
