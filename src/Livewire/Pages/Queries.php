<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use LaravelVitals\Models\BackendTelemetry;
use Livewire\Component;

/**
 * Database query baseline page.
 *
 * Shows avg/p75/p95 of queries_count and query_time_ms per route
 * and flags routes where the current period p75 is > 2× the prior period p75.
 */
final class Queries extends Component
{
    public string $period = '7d';

    public function setPeriod(string $period): void
    {
        if (! in_array($period, ['24h', '7d', '30d', '90d'], true)) {
            return;
        }
        $this->period = $period;
    }

    private function periodCutoff(): Carbon
    {
        return match ($this->period) {
            '24h' => now()->subDay(),
            '7d'  => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            default => now()->subDays(7),
        };
    }

    private function periodLabel(): string
    {
        return match ($this->period) {
            '24h' => __('vitals::vitals.rum.period_24h'),
            '7d'  => __('vitals::vitals.rum.period_7d'),
            '30d' => __('vitals::vitals.rum.period_30d'),
            '90d' => __('vitals::vitals.rum.period_90d'),
            default => __('vitals::vitals.rum.period_7d'),
        };
    }

    public function render(): View
    {
        $cutoff        = $this->periodCutoff();
        $periodSeconds = now()->getTimestamp() - $cutoff->getTimestamp();
        $prevCutoff    = $cutoff->copy()->subSeconds($periodSeconds);

        $currentRows = BackendTelemetry::query()
            ->where('created_at', '>=', $cutoff)
            ->whereNotNull('route_name')
            ->get(['route_name', 'queries_count', 'queries_time_ms']);

        $previousRows = BackendTelemetry::query()
            ->where('created_at', '>=', $prevCutoff)
            ->where('created_at', '<', $cutoff)
            ->whereNotNull('route_name')
            ->get(['route_name', 'queries_count', 'queries_time_ms']);

        $current  = $this->aggregateByRoute($currentRows->groupBy('route_name'));
        $previous = $this->aggregateByRoute($previousRows->groupBy('route_name'));

        // Merge and flag regressions
        $routes = [];
        foreach ($current as $route => $stats) {
            $prevStats   = $previous[$route] ?? null;
            $regression  = false;
            $regressionLabel = null;

            $prevP75   = is_float($prevStats['queries_p75'] ?? null) ? $prevStats['queries_p75'] : null;
            $currentP75 = is_float($stats['queries_p75']) ? $stats['queries_p75'] : null;

            if ($prevP75 !== null && $currentP75 !== null && $prevP75 > 0) {
                if ($currentP75 > $prevP75 * 2) {
                    $regression = true;
                    $regressionLabel = sprintf(
                        'queries p75: %d → %d',
                        (int) $prevP75,
                        (int) $currentP75,
                    );
                }
            }

            $routes[] = array_merge($stats, [
                'route'            => $route,
                'regression'       => $regression,
                'regression_label' => $regressionLabel,
            ]);
        }

        // Sort by queries_p95 descending
        usort($routes, fn ($a, $b) => (($b['queries_p95'] ?? 0) <=> ($a['queries_p95'] ?? 0)));

        $routes = array_slice($routes, 0, 30);

        // Memory hogs: top 5 routes by p75 peak_memory_bytes
        $memoryHogs = $this->memoryHogs($cutoff);

        return view('vitals::livewire.pages.queries', [
            'routes'      => $routes,
            'memoryHogs'  => $memoryHogs,
            'periodLabel' => $this->periodLabel(),
        ])->layout('vitals::layouts.dashboard');
    }

    /**
     * @param  \Illuminate\Support\Collection<string, \Illuminate\Database\Eloquent\Collection<int, BackendTelemetry>> $grouped
     * @return array<string, array{count: int, queries_avg: float|null, queries_p75: float|null, queries_p95: float|null, time_avg: float|null, time_p75: float|null, time_p95: float|null}>
     */
    private function aggregateByRoute($grouped): array
    {
        $result = [];

        foreach ($grouped as $route => $rows) {
            $counts = $rows->pluck('queries_count')->sort()->values()->all();
            $times  = $rows->pluck('queries_time_ms')->sort()->values()->all();

            $result[(string) $route] = [
                'count'        => count($counts),
                'queries_avg'  => count($counts) > 0 ? round(array_sum($counts) / count($counts), 1) : null,
                'queries_p75'  => $this->percentile($counts, 75),
                'queries_p95'  => $this->percentile($counts, 95),
                'time_avg'     => count($times) > 0 ? round(array_sum($times) / count($times), 2) : null,
                'time_p75'     => $this->percentile($times, 75),
                'time_p95'     => $this->percentile($times, 95),
            ];
        }

        return $result;
    }

    /**
     * @param  array<int, float|int>  $sortedValues  Must already be sorted ascending
     */
    private function percentile(array $sortedValues, int $pct): ?float
    {
        $count = count($sortedValues);
        if ($count === 0) {
            return null;
        }
        $index = (int) ceil($count * $pct / 100) - 1;
        return (float) ($sortedValues[$index] ?? end($sortedValues));
    }

    /**
     * Top 5 routes by p75 peak_memory_bytes.
     *
     * @return array<int, array{route: string, count: int, memory_p75_mb: float}>
     */
    private function memoryHogs(Carbon $cutoff): array
    {
        $rows = BackendTelemetry::query()
            ->where('created_at', '>=', $cutoff)
            ->whereNotNull('route_name')
            ->whereNotNull('peak_memory_bytes')
            ->get(['route_name', 'peak_memory_bytes']);

        $grouped = $rows->groupBy('route_name');
        $result  = [];

        foreach ($grouped as $route => $routeRows) {
            $bytes = $routeRows->pluck('peak_memory_bytes')->sort()->values()->all();
            $p75   = $this->percentile($bytes, 75);

            if ($p75 === null) {
                continue;
            }

            $result[] = [
                'route'          => (string) $route,
                'count'          => count($bytes),
                'memory_p75_mb'  => round($p75 / 1024 / 1024, 1),
            ];
        }

        usort($result, fn ($a, $b) => $b['memory_p75_mb'] <=> $a['memory_p75_mb']);

        return array_slice($result, 0, 5);
    }
}
