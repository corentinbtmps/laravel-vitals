<?php

declare(strict_types=1);

namespace LaravelVitals\Telemetry;

use Illuminate\Database\Events\QueryExecuted;
use LaravelVitals\Support\BackendTelemetrySnapshot;

/**
 * Request-scoped telemetry recorder.
 *
 * Lifecycle:
 *   1. Middleware constructs (or makes) a fresh instance.
 *   2. Middleware calls start($auditId) to attach listeners.
 *   3. Request runs.
 *   4. Middleware calls snapshot() to obtain the data and stop tracking.
 *
 * The recorder MUST NOT be a container singleton: in long-running workers
 * (Octane) we rely on a fresh instance per request to ensure listeners
 * attached during one request don't bleed into the next.
 */
final class TelemetryRecorder
{
    private const QUERIES_LOG_CAP = 200;

    private bool $active = false;

    private ?string $auditId = null;

    private bool $sampled = false;

    private float $startedAtNs = 0;

    private QueryAccumulator $queries;

    private int $cacheHits = 0;

    private int $cacheMisses = 0;

    private int $jobsDispatched = 0;

    /** @var array<int, array{sql: string, bindings_count: int, time_ms: float, caller_file: string|null, caller_line: int|null}> */
    private array $queriesLog = [];

    public function __construct()
    {
        $this->queries = $this->makeAccumulator();
    }

    public function start(?string $auditId, bool $sampled = false): void
    {
        // Reset all state so a recorder instance can be reused across requests
        // (defence in depth for Octane workers).
        $this->active = true;
        $this->auditId = $auditId;
        $this->sampled = $sampled;
        $this->startedAtNs = (float) hrtime(true);
        $this->queries = $this->makeAccumulator();
        $this->cacheHits = 0;
        $this->cacheMisses = 0;
        $this->jobsDispatched = 0;
        $this->queriesLog = [];
    }

    public function recordQuery(QueryExecuted $event): void
    {
        if ($this->active) {
            $this->queries->record($event);

            if (count($this->queriesLog) < self::QUERIES_LOG_CAP) {
                $caller = $this->resolveCaller();
                $this->queriesLog[] = [
                    'sql'            => $this->normalizeSql($event->sql),
                    'bindings_count' => count($event->bindings),
                    'time_ms'        => $event->time,
                    'caller_file'    => $caller['file'],
                    'caller_line'    => $caller['line'],
                ];
            }
        }
    }

    /**
     * Walk the backtrace and return the first frame that belongs to the host
     * application (i.e. not vendor/ and not the package's own src/).
     *
     * @return array{file: string|null, line: int|null}
     */
    private function resolveCaller(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 25);

        foreach ($trace as $frame) {
            $file = $frame['file'] ?? '';

            if ($file === '') {
                continue;
            }

            if (
                str_contains($file, '/vendor/') ||
                str_contains($file, '/laravel-vitals/src/')
            ) {
                continue;
            }

            $relativeFile = str_replace(base_path() . '/', '', $file);

            return [
                'file' => $relativeFile,
                'line' => $frame['line'] ?? null,
            ];
        }

        return ['file' => null, 'line' => null];
    }

    /**
     * Normalize SQL by replacing numeric literals with ? for pattern grouping.
     * Caps at 500 chars.
     */
    private function normalizeSql(string $sql): string
    {
        $normalized = preg_replace('/\b\d+\b/', '?', $sql) ?? $sql;

        return mb_substr($normalized, 0, 500);
    }

    public function incrementCacheHits(): void
    {
        if ($this->active) {
            $this->cacheHits++;
        }
    }

    public function incrementCacheMisses(): void
    {
        if ($this->active) {
            $this->cacheMisses++;
        }
    }

    public function incrementJobsDispatched(): void
    {
        if ($this->active) {
            $this->jobsDispatched++;
        }
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function snapshot(int $httpStatus, ?string $routeName): BackendTelemetrySnapshot
    {
        $durationMs = $this->active
            ? ((float) hrtime(true) - $this->startedAtNs) / 1_000_000.0
            : 0.0;

        $peakMemoryBytes = memory_get_peak_usage(true);
        $memoryPeakKb = (int) round($peakMemoryBytes / 1024);

        $threshold = (int) config('vitals.telemetry.n_plus_one_threshold', 10);

        $snapshot = new BackendTelemetrySnapshot(
            auditId:          $this->auditId,
            sampledRequest:   $this->sampled,
            routeName:        $routeName,
            httpStatus:       $httpStatus,
            durationMs:       $durationMs,
            memoryPeakKb:     $memoryPeakKb,
            queriesCount:     $this->queries->count(),
            queriesTimeMs:    $this->queries->totalTimeMs(),
            queriesUnique:    $this->queries->uniqueCount(),
            nPlusOneSuspect:  $this->queries->isNPlusOneSuspect($threshold),
            viewsRendered:    0,
            viewsTimeMs:      0.0,
            jobsDispatched:   $this->jobsDispatched,
            eventsFired:      0,
            cacheHits:        $this->cacheHits,
            cacheMisses:      $this->cacheMisses,
            slowQueries:      $this->queries->slowQueries(),
            truncated:        $this->queries->isTruncated(),
            peakMemoryBytes:  $peakMemoryBytes,
            queriesLog:       $this->queriesLog,
        );

        $this->active = false;

        return $snapshot;
    }

    private function makeAccumulator(): QueryAccumulator
    {
        return new QueryAccumulator(
            maxQueries: (int) config('vitals.telemetry.max_queries', 10_000),
            slowQueryThresholdMs: (float) config('vitals.telemetry.slow_query_threshold_ms', 50),
            topSlow: (int) config('vitals.telemetry.top_slow_queries', 10),
        );
    }
}
