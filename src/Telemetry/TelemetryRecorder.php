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
    private bool $active = false;

    private ?string $auditId = null;

    private bool $sampled = false;

    private float $startedAtNs = 0;

    private QueryAccumulator $queries;

    private int $cacheHits = 0;

    private int $cacheMisses = 0;

    private int $jobsDispatched = 0;

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

    }

    public function recordQuery(QueryExecuted $event): void
    {
        if ($this->active) {
            $this->queries->record($event);
        }
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

        $memoryPeakKb = (int) round(memory_get_peak_usage(true) / 1024);

        $threshold = (int) config('vitals.telemetry.n_plus_one_threshold', 10);

        $snapshot = new BackendTelemetrySnapshot(
            auditId:         $this->auditId,
            sampledRequest:  $this->sampled,
            routeName:       $routeName,
            httpStatus:      $httpStatus,
            durationMs:      $durationMs,
            memoryPeakKb:    $memoryPeakKb,
            queriesCount:    $this->queries->count(),
            queriesTimeMs:   $this->queries->totalTimeMs(),
            queriesUnique:   $this->queries->uniqueCount(),
            nPlusOneSuspect: $this->queries->isNPlusOneSuspect($threshold),
            viewsRendered:   0,
            viewsTimeMs:     0.0,
            jobsDispatched:  $this->jobsDispatched,
            eventsFired:     0,
            cacheHits:       $this->cacheHits,
            cacheMisses:     $this->cacheMisses,
            slowQueries:     $this->queries->slowQueries(),
            truncated:       $this->queries->isTruncated(),
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
