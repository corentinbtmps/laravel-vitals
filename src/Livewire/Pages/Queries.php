<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use LaravelVitals\Enums\Period;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\BackendTelemetry;
use LaravelVitals\Models\Recommendation;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Database query baseline page.
 *
 * Per-route stats with plain-language labels (Typical = p75, Worst case = p95).
 * Click a route to expand a panel with affected URLs, recent audits, and SQL patterns.
 */
final class Queries extends Component
{
    public Period $period = Period::D7;

    #[Url(as: 'route')]
    public ?string $selectedRoute = null;

    public function updatedPeriod(): void
    {
        $this->selectedRoute = null;
    }

    public function selectRoute(string $route): void
    {
        $this->selectedRoute = $this->selectedRoute === $route ? null : $route;
    }

    public function render(): View
    {
        $cutoff        = $this->period->cutoff() ?? now()->subDays(7);
        $periodSeconds = now()->getTimestamp() - $cutoff->getTimestamp();
        $prevCutoff    = $cutoff->copy()->subSeconds($periodSeconds);

        $currentRows = BackendTelemetry::query()
            ->where('created_at', '>=', $cutoff)
            ->whereNotNull('route_name')
            ->get(['audit_id', 'route_name', 'queries_count', 'queries_time_ms', 'n_plus_one_suspect']);

        $previousRows = BackendTelemetry::query()
            ->where('created_at', '>=', $prevCutoff)
            ->where('created_at', '<', $cutoff)
            ->whereNotNull('route_name')
            ->get(['route_name', 'queries_count']);

        $current  = $this->aggregateByRoute($currentRows->groupBy('route_name'));
        $previous = $this->aggregateByRoute($previousRows->groupBy('route_name'));

        // Routes with N+1 audits in the period
        $nPlusOneRoutes = $currentRows
            ->where('n_plus_one_suspect', true)
            ->pluck('route_name')
            ->unique()
            ->all();

        $routes = [];
        foreach ($current as $route => $stats) {
            $prevStats   = $previous[$route] ?? null;
            $regression  = false;
            $regressionLabel = null;

            $prevP75    = is_float($prevStats['queries_p75'] ?? null) ? $prevStats['queries_p75'] : null;
            $currentP75 = is_float($stats['queries_p75']) ? $stats['queries_p75'] : null;

            if ($prevP75 !== null && $currentP75 !== null && $prevP75 > 0 && $currentP75 > $prevP75 * 2) {
                $regression = true;
                $regressionLabel = sprintf('typical queries: %d → %d', (int) $prevP75, (int) $currentP75);
            }

            $routes[] = array_merge($stats, [
                'route'            => $route,
                'regression'       => $regression,
                'regression_label' => $regressionLabel,
                'has_n_plus_one'   => in_array($route, $nPlusOneRoutes, true),
            ]);
        }

        usort($routes, fn ($a, $b) => (($b['queries_p95'] ?? 0) <=> ($a['queries_p95'] ?? 0)));
        $routes = array_slice($routes, 0, 30);

        $memoryHogs = $this->memoryHogs($cutoff);

        // Per-route detail panel
        $routeDetail = null;
        if ($this->selectedRoute !== null) {
            $routeDetail = $this->buildRouteDetail($this->selectedRoute, $cutoff);
        }

        return view('vitals::livewire.pages.queries', [
            'routes'      => $routes,
            'memoryHogs'  => $memoryHogs,
            'routeDetail' => $routeDetail,
            'periodLabel' => $this->period->label(),
        ])->layout('vitals::layouts.dashboard');
    }

    /**
     * @param  \Illuminate\Support\Collection<string, \Illuminate\Database\Eloquent\Collection<int, BackendTelemetry>>  $grouped
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
                'route'         => (string) $route,
                'count'         => count($bytes),
                'memory_p75_mb' => round($p75 / 1024 / 1024, 1),
            ];
        }

        usort($result, fn ($a, $b) => $b['memory_p75_mb'] <=> $a['memory_p75_mb']);

        return array_slice($result, 0, 5);
    }

    /**
     * Build the per-route drill-down: affected URLs, recent audits, top SQL patterns.
     *
     * @return array{route: string, urls: array<int, array{id: int, label: string, path: string, audit_count: int}>, recent: array<int, array{audit_id: string, completed_at: \Illuminate\Support\Carbon|null, url_label: string|null, url_id: int|null, queries_count: int|null, queries_time_ms: float|null, n_plus_one: bool}>, patterns: array<int, array{sql: string, occurrences: int, caller: string|null}>}
     */
    private function buildRouteDetail(string $route, Carbon $cutoff): array
    {
        $audits = Audit::query()
            ->with('url')
            ->whereHas('telemetry', fn ($q) => $q->where('route_name', $route))
            ->where('completed_at', '>=', $cutoff)
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get();

        $telemetry = BackendTelemetry::query()
            ->whereIn('audit_id', $audits->pluck('id'))
            ->where('route_name', $route)
            ->get(['audit_id', 'queries_count', 'queries_time_ms', 'n_plus_one_suspect'])
            ->keyBy('audit_id');

        $recent = [];
        $urlCounts = [];

        foreach ($audits as $audit) {
            $tel = $telemetry->get($audit->id);
            $url = $audit->url;

            if ($url !== null) {
                $urlCounts[$url->id] ??= ['id' => $url->id, 'label' => $url->label, 'path' => $url->path, 'audit_count' => 0];
                $urlCounts[$url->id]['audit_count']++;
            }

            $recent[] = [
                'audit_id'        => $audit->id,
                'completed_at'    => $audit->completed_at,
                'url_label'       => $url?->label,
                'url_id'          => $url?->id,
                'queries_count'   => $tel?->queries_count !== null ? (int) $tel->queries_count : null,
                'queries_time_ms' => $tel?->queries_time_ms !== null ? (float) $tel->queries_time_ms : null,
                'n_plus_one'      => $tel !== null && (bool) $tel->n_plus_one_suspect,
            ];
        }

        // Aggregate top SQL patterns from N+1 recommendations on these audits
        $patterns = $this->aggregatePatterns($audits->pluck('id')->all());

        return [
            'route'    => $route,
            'urls'     => array_values($urlCounts),
            'recent'   => array_slice($recent, 0, 5),
            'patterns' => $patterns,
        ];
    }

    /**
     * @param  array<int, string>  $auditIds
     * @return array<int, array{sql: string, occurrences: int, caller: string|null}>
     */
    private function aggregatePatterns(array $auditIds): array
    {
        if ($auditIds === []) {
            return [];
        }

        $recos = Recommendation::query()
            ->whereIn('audit_id', $auditIds)
            ->where('audit_key', 'n-plus-one-detected')
            ->get(['translation_params']);

        $byPattern = [];

        foreach ($recos as $reco) {
            $params = is_array($reco->translation_params) ? $reco->translation_params : [];
            $top    = is_array($params['top_patterns'] ?? null) ? $params['top_patterns'] : [];

            foreach ($top as $entry) {
                if (! is_array($entry)) {
                    continue;
                }
                $sql = (string) ($entry['sql'] ?? '');
                if ($sql === '') {
                    continue;
                }

                $key = $sql;
                $byPattern[$key] ??= ['sql' => $sql, 'occurrences' => 0, 'caller' => $entry['caller'] ?? null];
                $byPattern[$key]['occurrences'] += (int) ($entry['occurrences'] ?? 1);
            }
        }

        $list = array_values($byPattern);
        usort($list, fn ($a, $b) => $b['occurrences'] <=> $a['occurrences']);

        return array_slice($list, 0, 5);
    }
}
