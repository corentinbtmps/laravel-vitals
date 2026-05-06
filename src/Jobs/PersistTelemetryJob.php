<?php

declare(strict_types=1);

namespace LaravelVitals\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\BackendTelemetry;
use LaravelVitals\Support\BackendTelemetrySnapshot;

/**
 * Persists a BackendTelemetrySnapshot to vitals_backend_telemetry.
 *
 * Dispatched after the response is sent so it does not impact TTFB.
 *
 * Silently no-ops when audit_id is set but doesn't reference a real audit
 * (this can happen if the audit row was pruned between dispatch and execution
 * in long-running workers).
 */
final class PersistTelemetryJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly BackendTelemetrySnapshot $snapshot,
    ) {
    }

    public function handle(): void
    {
        $auditId = $this->snapshot->auditId;

        if ($auditId !== null && ! Audit::query()->whereKey($auditId)->exists()) {
            return;
        }

        BackendTelemetry::create([
            'audit_id'           => $auditId,
            'sampled_request'    => $this->snapshot->sampledRequest,
            'route_name'         => $this->snapshot->routeName,
            'http_status'        => $this->snapshot->httpStatus,
            'duration_ms'        => $this->snapshot->durationMs,
            'memory_peak_kb'     => $this->snapshot->memoryPeakKb,
            'queries_count'      => $this->snapshot->queriesCount,
            'queries_time_ms'    => $this->snapshot->queriesTimeMs,
            'queries_unique'     => $this->snapshot->queriesUnique,
            'n_plus_one_suspect' => $this->snapshot->nPlusOneSuspect,
            'views_rendered'     => $this->snapshot->viewsRendered,
            'views_time_ms'      => $this->snapshot->viewsTimeMs,
            'jobs_dispatched'    => $this->snapshot->jobsDispatched,
            'events_fired'       => $this->snapshot->eventsFired,
            'cache_hits'         => $this->snapshot->cacheHits,
            'cache_misses'       => $this->snapshot->cacheMisses,
            'slow_queries'       => $this->snapshot->slowQueries,
            'truncated'          => $this->snapshot->truncated,
        ]);
    }
}
