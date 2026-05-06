<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use LaravelVitals\Models\Url;
use Livewire\Component;

final class UrlDetail extends Component
{
    public int $url = 0;

    public string $period = '30d';

    public string $metric = 'performance';

    public function mount(int $url): void
    {
        $this->url = $url;
    }

    public function setPeriod(string $period): void
    {
        if (! in_array($period, ['24h', '7d', '30d', '90d', '1y', 'all'], true)) {
            return;
        }
        $this->period = $period;
        $this->dispatchChartUpdate();
    }

    public function setMetric(string $metric): void
    {
        if (! in_array($metric, ['performance', 'lcp', 'inp', 'cls', 'ttfb'], true)) {
            return;
        }
        $this->metric = $metric;
        $this->dispatchChartUpdate();
    }

    private function dispatchChartUpdate(): void
    {
        $this->dispatch('chartUpdated', metric: $this->metric, series: $this->metricSeries());
    }

    private function periodCutoff(): ?Carbon
    {
        return match ($this->period) {
            '24h'   => now()->subDay(),
            '7d'    => now()->subDays(7),
            '30d'   => now()->subDays(30),
            '90d'   => now()->subDays(90),
            '1y'    => now()->subYear(),
            'all'   => null,
            default => now()->subDays(30),
        };
    }

    private function periodLabel(): string
    {
        return match ($this->period) {
            '24h' => 'Last 24 hours',
            '7d'  => 'Last 7 days',
            '30d' => 'Last 30 days',
            '90d' => 'Last 90 days',
            '1y'  => 'Last year',
            'all' => 'All time',
            default => 'Last 30 days',
        };
    }

    /**
     * @return array<int, array{x: int, y: int|float|null}>
     */
    public function metricSeries(): array
    {
        $urlModel = Url::query()->findOrFail($this->url);
        $cutoff = $this->periodCutoff();

        $query = $urlModel->audits()
            ->where('status', 'completed');

        if ($cutoff !== null) {
            $query->where('completed_at', '>=', $cutoff);
        }

        $audits = $query->orderBy('completed_at')->get(['completed_at', 'score_performance', 'lcp_ms', 'inp_ms', 'cls', 'ttfb_ms']);

        return $audits->map(function ($a): array {
            $ts = $a->completed_at?->getTimestampMs() ?? 0;
            $value = match ($this->metric) {
                'performance' => $a->score_performance !== null ? (int) $a->score_performance : null,
                'lcp'         => $a->lcp_ms !== null ? (int) round((float) $a->lcp_ms) : null,
                'inp'         => $a->inp_ms !== null ? (int) round((float) $a->inp_ms) : null,
                'cls'         => $a->cls !== null ? round((float) $a->cls, 3) : null,
                'ttfb'        => $a->ttfb_ms !== null ? (int) round((float) $a->ttfb_ms) : null,
                default       => null,
            };
            return ['x' => $ts, 'y' => $value];
        })->values()->all();
    }

    public function render(): View
    {
        $urlModel = Url::query()->findOrFail($this->url);
        $cutoff = $this->periodCutoff();

        $historyQuery = $urlModel->audits()
            ->where('status', 'completed');

        if ($cutoff !== null) {
            $historyQuery->where('completed_at', '>=', $cutoff);
        }

        $history = $historyQuery
            ->orderByDesc('completed_at')
            ->limit(50)
            ->get();

        $periodAudits = $urlModel->audits()
            ->where('status', 'completed');

        if ($cutoff !== null) {
            $periodAudits->where('completed_at', '>=', $cutoff);
        }

        $periodAuditsCollection = $periodAudits->get(['score_performance', 'score_accessibility', 'score_best_practices', 'score_seo', 'lcp_ms']);

        $avgScores = [
            'performance'    => self::avg($periodAuditsCollection, 'score_performance'),
            'accessibility'  => self::avg($periodAuditsCollection, 'score_accessibility'),
            'best_practices' => self::avg($periodAuditsCollection, 'score_best_practices'),
            'seo'            => self::avg($periodAuditsCollection, 'score_seo'),
        ];

        // Most frequent recommendations on this URL (top 5)
        $frequentRecos = \LaravelVitals\Models\Recommendation::query()
            ->whereIn('audit_id', $history->pluck('id'))
            ->selectRaw('audit_key, severity, title_key, count(*) as occurrences')
            ->groupBy('audit_key', 'severity', 'title_key')
            ->orderByDesc('occurrences')
            ->limit(5)
            ->get();

        // Failed audits in the selected period
        $failedQuery = $urlModel->audits()
            ->where('status', 'failed');

        if ($cutoff !== null) {
            $failedQuery->where('created_at', '>=', $cutoff);
        }

        $failedAudits = $failedQuery
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'driver', 'device', 'error', 'created_at']);

        $chartSeries = $this->metricSeries();

        return view('vitals::livewire.pages.url-detail', [
            'urlModel'       => $urlModel,
            'history'        => $history,
            'avgScores'      => $avgScores,
            'periodCount'    => $periodAuditsCollection->count(),
            'frequentRecos'  => $frequentRecos,
            'failedAudits'   => $failedAudits,
            'periodLabel'    => $this->periodLabel(),
            'chartSeries'    => $chartSeries,
        ])->layout('vitals::layouts.dashboard');
    }

    /** @param \Illuminate\Database\Eloquent\Collection<int, \LaravelVitals\Models\Audit> $audits */
    private static function avg($audits, string $col): ?int
    {
        $avg = $audits->avg($col);

        return $avg !== null ? (int) round((float) $avg) : null;
    }
}
