<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

/**
 * In-memory snapshot of telemetry collected during a single instrumented
 * HTTP request. Persisted to vitals_backend_telemetry by Plan 3.
 */
final readonly class BackendTelemetrySnapshot
{
    /**
     * @param array<int, array{sql: string, count: int, time_ms: float}> $slowQueries
     */
    public function __construct(
        public ?string $auditId,
        public bool $sampledRequest,
        public ?string $routeName,
        public int $httpStatus,
        public float $durationMs,
        public int $memoryPeakKb,
        public int $queriesCount,
        public float $queriesTimeMs,
        public int $queriesUnique,
        public bool $nPlusOneSuspect,
        public int $viewsRendered,
        public float $viewsTimeMs,
        public int $jobsDispatched,
        public int $eventsFired,
        public int $cacheHits,
        public int $cacheMisses,
        public array $slowQueries,
        public bool $truncated,
    ) {
    }
}
