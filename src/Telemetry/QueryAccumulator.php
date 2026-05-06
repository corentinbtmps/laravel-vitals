<?php

declare(strict_types=1);

namespace LaravelVitals\Telemetry;

/**
 * Accumulates DB::listen events for a single instrumented request.
 *
 * Tracks: total count, total time, unique patterns (for N+1 heuristic), top-N
 * slow queries, and a truncation flag. All operations are O(1) per recorded
 * query, except for slow-query insertion which is O(topSlow) in the worst case.
 */
final class QueryAccumulator
{
    private int $count = 0;

    private float $totalTimeMs = 0.0;

    /** @var array<string, int> */
    private array $patternCounts = [];

    /** @var array<int, array{sql: string, time_ms: float}> */
    private array $slowQueries = [];

    private bool $truncated = false;

    public function __construct(
        private readonly int $maxQueries,
        private readonly float $slowQueryThresholdMs,
        private readonly int $topSlow,
    ) {
    }

    /**
     * Record a query event. The event must expose `sql` (string) and `time`
     * (float, ms). Both Laravel's QueryExecuted event and our test fixture
     * shape match this contract.
     *
     * @param object{sql: string, time: float} $event
     */
    public function record(object $event): void
    {
        if ($this->count >= $this->maxQueries) {
            $this->truncated = true;
            return;
        }

        $this->count++;
        $this->totalTimeMs += $event->time;

        $pattern = $this->normalise($event->sql);
        $this->patternCounts[$pattern] = ($this->patternCounts[$pattern] ?? 0) + 1;

        if ($event->time >= $this->slowQueryThresholdMs) {
            $this->insertSlow($pattern, $event->time);
        }
    }

    public function count(): int
    {
        return $this->count;
    }

    public function totalTimeMs(): float
    {
        return $this->totalTimeMs;
    }

    public function uniqueCount(): int
    {
        return count($this->patternCounts);
    }

    public function isNPlusOneSuspect(int $threshold): bool
    {
        foreach ($this->patternCounts as $count) {
            if ($count > $threshold) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array<int, array{sql: string, time_ms: float}>
     */
    public function slowQueries(): array
    {
        return array_values($this->slowQueries);
    }

    public function isTruncated(): bool
    {
        return $this->truncated;
    }

    /**
     * Replace numeric and quoted literals so logically-similar queries hash
     * to the same pattern.
     */
    private function normalise(string $sql): string
    {
        $sql = preg_replace("/'[^']*'/", '?', $sql) ?? $sql;
        $sql = preg_replace('/"[^"]*"/', '?', $sql) ?? $sql;
        $sql = preg_replace('/\b\d+(\.\d+)?\b/', '?', $sql) ?? $sql;

        return $sql;
    }

    private function insertSlow(string $pattern, float $timeMs): void
    {
        if (count($this->slowQueries) < $this->topSlow) {
            $this->slowQueries[] = ['sql' => $pattern, 'time_ms' => $timeMs];
            return;
        }

        // Find the entry with the lowest time; replace it only if the new query is slower.
        $minIndex = null;
        $minTime = PHP_FLOAT_MAX;
        foreach ($this->slowQueries as $idx => $entry) {
            if ($entry['time_ms'] < $minTime) {
                $minTime = $entry['time_ms'];
                $minIndex = $idx;
            }
        }

        if ($timeMs > $minTime && $minIndex !== null) {
            $this->slowQueries[$minIndex] = ['sql' => $pattern, 'time_ms' => $timeMs];
        }
    }
}
