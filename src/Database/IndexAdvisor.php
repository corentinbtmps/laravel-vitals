<?php

declare(strict_types=1);

namespace LaravelVitals\Database;

use Illuminate\Support\Facades\Log;
use LaravelVitals\Enums\Severity;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\BackendTelemetry;
use LaravelVitals\Models\Recommendation;

/**
 * Inspects the slow queries captured during an audit and suggests database
 * indexes for columns that are filtered, joined, or sorted on but not indexed.
 *
 * Static analysis only — it never executes the captured SQL. Suggestions are
 * advisory: composite-column order and write-amplification tradeoffs mean a
 * human must confirm each one before shipping a migration.
 */
final readonly class IndexAdvisor
{
    public function __construct(
        private SqlIndexParser $parser,
    ) {}

    public function run(Audit $audit, ?BackendTelemetry $telemetry): void
    {
        if (! (bool) config('vitals.database_advisor.enabled', true)) {
            return;
        }

        if (!$telemetry instanceof \LaravelVitals\Models\BackendTelemetry) {
            return;
        }

        $slowQueries = $telemetry->slow_queries ?? [];
        if ($slowQueries === []) {
            return;
        }

        $inspector = new SchemaIndexInspector(config('vitals.database_advisor.connection'));
        $ignore    = array_values(array_map(strtolower(...), (array) config('vitals.database_advisor.ignore_tables', [])));
        $minTime   = (float) config('vitals.database_advisor.min_query_time_ms', 0.0);
        $maxItems  = (int) config('vitals.database_advisor.max_suggestions', 20);

        /** @var array<string, array<string, string>> $suggestions  "table.col" => detail item */
        $suggestions = [];

        foreach ($slowQueries as $entry) {
            $sql  = (string) ($entry['sql'] ?? '');
            $time = (float) ($entry['time_ms'] ?? 0.0);

            if ($sql === '' || $time < $minTime) {
                continue;
            }

            try {
                $parsed = $this->parser->parse($sql);
            } catch (\Throwable $e) {
                Log::debug('[LaravelVitals] IndexAdvisor could not parse a query', ['error' => $e->getMessage()]);
                continue;
            }

            if (!$parsed instanceof \LaravelVitals\Database\ParsedQuery || ! $parsed->hasCandidates()) {
                continue;
            }

            $table = $parsed->table;
            if ($this->isIgnored($table, $ignore) || ! $inspector->tableExists($table)) {
                continue;
            }

            foreach ($parsed->columns as $column => $reason) {
                $key = $table . '.' . $column;

                if (isset($suggestions[$key])) {
                    continue;
                }
                if (! $inspector->hasColumn($table, $column) || $inspector->isIndexed($table, $column)) {
                    continue;
                }

                $suggestions[$key] = $this->detailItem($table, $column, $reason, $sql, $time);

                if (count($suggestions) >= $maxItems) {
                    break 2;
                }
            }
        }

        if ($suggestions === []) {
            return;
        }

        $this->persist($audit, array_values($suggestions));
    }

    /**
     * @param array<int, array<string, string>> $detailItems
     */
    private function persist(Audit $audit, array $detailItems): void
    {
        Recommendation::create([
            'audit_id'           => $audit->id,
            'source'             => 'backend',
            'audit_key'          => 'missing-index',
            'category'           => 'performance',
            'severity'           => Severity::Warning,
            'title_key'          => 'vitals::vitals.recommendations.missing-index.title',
            'description_key'    => 'vitals::vitals.recommendations.missing-index.description',
            'translation_params' => ['count' => (string) count($detailItems)],
            'metrics'            => ['count' => count($detailItems)],
            'code_references'    => [],
            'detail_items'       => $detailItems,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function detailItem(string $table, string $column, string $reason, string $sql, float $timeMs): array
    {
        $reasonLabel = match ($reason) {
            'where' => "filtered on {$table}.{$column} (WHERE)",
            'join'  => "joined on {$table}.{$column}",
            default => "sorted on {$table}.{$column} (ORDER BY)",
        };

        return [
            'url'          => "Schema::table('{$table}', fn (Blueprint \$t) => \$t->index('{$column}'));",
            'hint'         => ucfirst($reasonLabel) . ' — no index on this column. ' . $this->truncate($sql),
            'wasted_label' => number_format($timeMs, 0) . ' ms',
        ];
    }

    /**
     * @param list<string> $ignore
     */
    private function isIgnored(string $table, array $ignore): bool
    {
        $table = strtolower($table);

        // Never advise on the package's own tables.
        return str_starts_with($table, 'vitals_') || in_array($table, $ignore, true);
    }

    private function truncate(string $sql, int $limit = 120): string
    {
        return strlen($sql) > $limit ? substr($sql, 0, $limit - 1) . '…' : $sql;
    }
}
