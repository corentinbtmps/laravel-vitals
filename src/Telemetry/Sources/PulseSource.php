<?php

declare(strict_types=1);

namespace LaravelVitals\Telemetry\Sources;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LaravelVitals\Contracts\TelemetrySource;
use LaravelVitals\Telemetry\TrendStats;

/**
 * Reads aggregated request timings from Laravel Pulse's pulse_aggregates table.
 *
 * Pulse stores aggregates per `type` / `aggregate` / `key`. Routes show up under
 * type=`slow_request`, aggregate in `p50`/`p95`, key as JSON-encoded `"METHOD /path"`.
 *
 * This implementation only consults Pulse's daily window (period=86400) for
 * stable signal. Returns empty stats when no matching rows exist.
 */
final class PulseSource implements TelemetrySource
{
    public function isAvailable(): bool
    {
        return Schema::hasTable('pulse_aggregates');
    }

    public function getTrendsFor(string $routeName): TrendStats
    {
        if (! $this->isAvailable()) {
            return TrendStats::empty();
        }

        // Pulse keys look like `"GET /home"` — match by suffix to be tolerant of method.
        $rows = DB::table('pulse_aggregates')
            ->where('type', 'slow_request')
            ->whereIn('aggregate', ['p50', 'p95'])
            ->where('period', 86400)
            ->where('key', 'like', '%/' . ltrim($routeName, '/') . '"')
            ->get(['aggregate', 'value', 'count']);

        if ($rows->isEmpty()) {
            return TrendStats::empty();
        }

        $p50 = (float) ($rows->firstWhere('aggregate', 'p50')->value ?? 0);
        $p95 = (float) ($rows->firstWhere('aggregate', 'p95')->value ?? 0);
        $samples = (int) $rows->sum('count');

        return new TrendStats(
            p50Ttfb: $p50 > 0 ? $p50 : null,
            p95Ttfb: $p95 > 0 ? $p95 : null,
            p50Lcp:  null,
            p95Lcp:  null,
            sampleCount: $samples,
        );
    }
}
