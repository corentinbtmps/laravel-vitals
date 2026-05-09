<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use LaravelVitals\Models\RumEvent;
use Livewire\Component;

/**
 * Real User Monitoring dashboard page.
 *
 * Shows per-metric p75 distributions and per-URL breakdowns of
 * CWV collected from real visitors via the @vitalsRum directive.
 */
final class Rum extends Component
{
    public string $period = '7d';

    public string $device = 'all';

    public function setPeriod(string $period): void
    {
        if (! in_array($period, ['24h', '7d', '30d', '90d'], true)) {
            return;
        }
        $this->period = $period;
    }

    public function setDevice(string $device): void
    {
        if (! in_array($device, ['all', 'mobile', 'desktop'], true)) {
            return;
        }
        $this->device = $device;
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
        $cutoff = $this->periodCutoff();

        $baseQuery = RumEvent::query()->where('occurred_at', '>=', $cutoff);
        if ($this->device !== 'all') {
            $baseQuery->where('device', $this->device);
        }

        $totalEvents = (clone $baseQuery)->count();

        // Per-metric summary: sample count + rating distribution + p75
        $metrics = ['LCP', 'INP', 'CLS', 'TTFB', 'FCP'];
        $metricCards = [];

        foreach ($metrics as $metric) {
            $rows = (clone $baseQuery)
                ->where('metric', $metric)
                ->orderBy('value')
                ->get(['value', 'rating']);

            $count = $rows->count();

            if ($count === 0) {
                $metricCards[$metric] = [
                    'count'  => 0,
                    'p75'    => null,
                    'good'   => 0,
                    'ni'     => 0,
                    'poor'   => 0,
                ];
                continue;
            }

            $p75Index  = (int) ceil($count * 0.75) - 1;
            $p75        = $rows->values()[$p75Index]->value ?? null;
            $good       = $rows->where('rating', 'good')->count();
            $ni         = $rows->where('rating', 'needs-improvement')->count();
            $poor       = $rows->where('rating', 'poor')->count();

            $metricCards[$metric] = [
                'count' => $count,
                'p75'   => $p75,
                'good'  => $good,
                'ni'    => $ni,
                'poor'  => $poor,
            ];
        }

        // Per-URL breakdown: sample count + p75 for LCP, INP, CLS
        $urlStats = $this->perUrlStats($cutoff);

        // INP attribution breakdown (top interactions from attribution JSON)
        $inpAttributions = $this->inpAttributions($cutoff);

        return view('vitals::livewire.pages.rum', [
            'totalEvents'     => $totalEvents,
            'metricCards'     => $metricCards,
            'urlStats'        => $urlStats,
            'inpAttributions' => $inpAttributions,
            'periodLabel'     => $this->periodLabel(),
        ])->layout('vitals::layouts.dashboard');
    }

    /**
     * Returns per-URL p75 for LCP, INP, CLS.
     *
     * @return array<int, array{url: string, count: int, lcp_p75: float|null, inp_p75: float|null, cls_p75: float|null}>
     */
    private function perUrlStats(Carbon $cutoff): array
    {
        $baseQuery = RumEvent::query()
            ->where('occurred_at', '>=', $cutoff)
            ->whereIn('metric', ['LCP', 'INP', 'CLS']);

        if ($this->device !== 'all') {
            $baseQuery->where('device', $this->device);
        }

        $rows = $baseQuery
            ->get(['url', 'metric', 'value'])
            ->groupBy('url');

        $result = [];
        foreach ($rows as $url => $events) {
            $lcpValues = $events->where('metric', 'LCP')->pluck('value')->sort()->values();
            $inpValues = $events->where('metric', 'INP')->pluck('value')->sort()->values();
            $clsValues = $events->where('metric', 'CLS')->pluck('value')->sort()->values();

            $result[] = [
                'url'     => $url,
                'count'   => $events->count(),
                'lcp_p75' => $this->p75($lcpValues->all()),
                'inp_p75' => $this->p75($inpValues->all()),
                'cls_p75' => $this->p75($clsValues->all()),
            ];
        }

        // Sort by count descending
        usort($result, fn ($a, $b) => $b['count'] <=> $a['count']);

        return array_slice($result, 0, 20);
    }

    /**
     * INP attribution: extract element selectors and event types from attribution JSON.
     *
     * @return array<int, array{selector: string, event_type: string, count: int, inp_p75: float|null}>
     */
    private function inpAttributions(Carbon $cutoff): array
    {
        $inpEvents = RumEvent::query()
            ->where('occurred_at', '>=', $cutoff)
            ->where('metric', 'INP')
            ->whereNotNull('attribution')
            ->orderBy('value')
            ->get(['value', 'attribution']);

        $buckets = [];
        foreach ($inpEvents as $event) {
            $attr     = is_array($event->attribution) ? $event->attribution : [];
            $selector = (string) ($attr['interactionTarget'] ?? $attr['element'] ?? 'unknown');
            $evType   = (string) ($attr['interactionType'] ?? $attr['eventType'] ?? 'unknown');
            $key      = $selector . '|' . $evType;

            $buckets[$key] ??= ['selector' => $selector, 'event_type' => $evType, 'values' => []];
            $buckets[$key]['values'][] = $event->value;
        }

        $result = [];
        foreach ($buckets as $bucket) {
            $values = $bucket['values'];
            sort($values);
            $result[] = [
                'selector'  => $bucket['selector'],
                'event_type' => $bucket['event_type'],
                'count'     => count($values),
                'inp_p75'   => $this->p75($values),
            ];
        }

        usort($result, fn ($a, $b) => $b['count'] <=> $a['count']);

        return array_slice($result, 0, 10);
    }

    /**
     * Compute p75 from a sorted array of floats.
     *
     * @param array<int, float> $values Must be sorted ascending
     */
    private function p75(array $values): ?float
    {
        $count = count($values);
        if ($count === 0) {
            return null;
        }
        $index = (int) ceil($count * 0.75) - 1;
        return $values[$index] ?? null;
    }
}
