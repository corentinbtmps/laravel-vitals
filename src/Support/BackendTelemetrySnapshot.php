<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

/**
 * In-memory snapshot of telemetry collected during a single instrumented
 * HTTP request. Persisted to vitals_backend_telemetry by Plan 3.
 */
final class BackendTelemetrySnapshot
{
    /**
     * @param array<int, array{sql: string, count: int, time_ms: float}> $slowQueries
     */
    public function __construct(
        public readonly ?string $auditId,
        public readonly bool $sampledRequest,
        public readonly ?string $routeName,
        public readonly int $httpStatus,
        public readonly float $durationMs,
        public readonly int $memoryPeakKb,
        public readonly int $queriesCount,
        public readonly float $queriesTimeMs,
        public readonly int $queriesUnique,
        public readonly bool $nPlusOneSuspect,
        public readonly int $viewsRendered,
        public readonly float $viewsTimeMs,
        public readonly int $jobsDispatched,
        public readonly int $eventsFired,
        public readonly int $cacheHits,
        public readonly int $cacheMisses,
        public readonly array $slowQueries,
        public readonly bool $truncated,
    ) {
    }
}
