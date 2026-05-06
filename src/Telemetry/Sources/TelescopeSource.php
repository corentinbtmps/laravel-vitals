<?php

declare(strict_types=1);

namespace LaravelVitals\Telemetry\Sources;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LaravelVitals\Contracts\TelemetrySource;
use LaravelVitals\Telemetry\TrendStats;

/**
 * Reads request durations from Laravel Telescope's telescope_entries table.
 *
 * Telescope stores fine-grained per-request events with content.uri and
 * content.duration. We compute P50/P95 across recent entries matching the route.
 */
final class TelescopeSource implements TelemetrySource
{
    public function isAvailable(): bool
    {
        return Schema::hasTable('telescope_entries');
    }

    public function getTrendsFor(string $routeName): TrendStats
    {
        if (! $this->isAvailable()) {
            return TrendStats::empty();
        }

        $rows = DB::table('telescope_entries')
            ->where('type', 'request')
            ->where('content', 'like', '%/' . ltrim($routeName, '/') . '%')
            ->where('created_at', '>=', now()->subDays(7))
            ->limit(5000)
            ->get(['content']);

        if ($rows->isEmpty()) {
            return TrendStats::empty();
        }

        $durations = [];
        foreach ($rows as $row) {
            try {
                /** @var array{uri?: string, duration?: int|float} $content */
                $content = json_decode((string) $row->content, true, flags: JSON_THROW_ON_ERROR);
                $duration = $content['duration'] ?? null;
                if (is_numeric($duration)) {
                    $durations[] = (float) $duration;
                }
            } catch (\JsonException) {
                continue;
            }
        }

        if ($durations === []) {
            return TrendStats::empty();
        }

        sort($durations);
        $count = count($durations);
        $p50Index = (int) floor(0.50 * $count);
        $p95Index = (int) floor(0.95 * $count);

        return new TrendStats(
            p50Ttfb: $durations[$p50Index] ?? null,
            p95Ttfb: $durations[min($p95Index, $count - 1)] ?? null,
            p50Lcp:  null,
            p95Lcp:  null,
            sampleCount: $count,
        );
    }
}
