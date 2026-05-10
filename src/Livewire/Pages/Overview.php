<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;
use Livewire\Component;

final class Overview extends Component
{
    public string $period = '7d';

    public function setPeriod(string $period): void
    {
        if (! in_array($period, ['24h', '7d', '30d', '90d', '1y', 'all'], true)) {
            return;
        }
        $this->period = $period;
        $this->dispatch('sparklineUpdated', trends: $this->metricTrends());
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
            default => now()->subDays(7),
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
            default => 'Last 7 days',
        };
    }

    public function render(): View
    {
        $cutoff = $this->periodCutoff();

        $recentQuery = Audit::query()
            ->with('url')
            ->where('status', 'completed');

        if ($cutoff !== null) {
            $recentQuery->where('completed_at', '>=', $cutoff);
        }

        $recent = $recentQuery
            ->orderByDesc('completed_at')
            ->limit(20)
            ->get();

        $averages = [
            'performance'    => self::avgScore($recent, 'score_performance'),
            'accessibility'  => self::avgScore($recent, 'score_accessibility'),
            'best_practices' => self::avgScore($recent, 'score_best_practices'),
            'seo'            => self::avgScore($recent, 'score_seo'),
        ];

        $overall = $averages['performance'] !== null
            ? (int) round(array_sum(array_filter($averages, fn ($v) => $v !== null)) / max(1, count(array_filter($averages, fn ($v) => $v !== null))))
            : null;

        // Active alerts: recent budget violations (last 24h) + regressions (perf score dropped >10% vs 7-day baseline)
        $activeAlerts = $this->detectAlerts();

        // Top 3 recommendation keys (most occurrences across recent audits)
        $topRecommendations = Recommendation::query()
            ->whereIn('audit_id', $recent->pluck('id'))
            ->selectRaw('audit_key, severity, title_key, count(*) as occurrences')
            ->groupBy('audit_key', 'severity', 'title_key')
            ->orderByDesc('occurrences')
            ->limit(3)
            ->get();

        // URLs configured count
        $urlsCount = Url::query()->where('enabled', true)->count();

        $metricTrends         = $this->metricTrends();
        $previousMetricTrends = $this->previousMetricTrends();
        $metricDeltas         = $this->metricDeltas($averages);
        $previousDeltas       = $this->previousPeriodDeltas($averages);
        $dailySummary         = $this->dailySummary();
        $apiUsage             = $this->apiUsageThisMonth();

        return view('vitals::livewire.pages.overview', [
            'recent'               => $recent,
            'averages'             => $averages,
            'overall'              => $overall,
            'overallGrade'         => \LaravelVitals\Support\Health::grade($overall),
            'overallColor'         => \LaravelVitals\Support\Health::colorForScore($overall),
            'activeAlerts'         => $activeAlerts,
            'topRecommendations'   => $topRecommendations,
            'urlsCount'            => $urlsCount,
            'metricTrends'         => $metricTrends,
            'previousMetricTrends' => $previousMetricTrends,
            'metricDeltas'         => $metricDeltas,
            'previousDeltas'       => $previousDeltas,
            'dailySummary'         => $dailySummary,
            'apiUsage'             => $apiUsage,
            'periodLabel'          => $this->periodLabel(),
        ])->layout('vitals::layouts.dashboard');
    }

    /**
     * Returns sparkline trend data for all 4 score metrics over the current period.
     *
     * @return array<string, array<int, int>>
     */
    private function metricTrends(): array
    {
        $cutoff = $this->periodCutoff();

        $query = Audit::query()
            ->where('status', 'completed');

        if ($cutoff !== null) {
            $query->where('completed_at', '>=', $cutoff);
        }

        $bucket = $this->bucketExpression();

        $rows = $query
            ->selectRaw("{$bucket} as bucket, AVG(score_performance) as p, AVG(score_accessibility) as a, AVG(score_best_practices) as b, AVG(score_seo) as s")
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        /** @var array<int, int> $perf */
        $perf = $rows->pluck('p')->map(fn ($v) => (int) round((float) $v))->values()->all();
        /** @var array<int, int> $access */
        $access = $rows->pluck('a')->map(fn ($v) => (int) round((float) $v))->values()->all();
        /** @var array<int, int> $bp */
        $bp = $rows->pluck('b')->map(fn ($v) => (int) round((float) $v))->values()->all();
        /** @var array<int, int> $seo */
        $seo = $rows->pluck('s')->map(fn ($v) => (int) round((float) $v))->values()->all();

        return [
            'performance'    => $perf,
            'accessibility'  => $access,
            'best_practices' => $bp,
            'seo'            => $seo,
        ];
    }

    /**
     * Database-portable SQL expression that buckets `completed_at` by hour for
     * the 24h period and by day otherwise. Returns a string usable inside selectRaw.
     */
    private function bucketExpression(): string
    {
        $hourly = $this->period === '24h';
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();

        return match ($driver) {
            'sqlite' => $hourly
                ? "strftime('%Y-%m-%d %H:00', completed_at)"
                : "strftime('%Y-%m-%d', completed_at)",
            'mysql', 'mariadb' => $hourly
                ? "DATE_FORMAT(completed_at, '%Y-%m-%d %H:00')"
                : "DATE(completed_at)",
            'pgsql' => $hourly
                ? "to_char(completed_at, 'YYYY-MM-DD HH24:00')"
                : "to_char(completed_at, 'YYYY-MM-DD')",
            default => 'DATE(completed_at)',
        };
    }

    /**
     * @param  array<string, int|null>  $averages
     * @return array<string, int|null>
     */
    private function metricDeltas(array $averages): array
    {
        $cutoff = $this->periodCutoff();

        if ($cutoff === null) {
            return ['performance' => null, 'accessibility' => null, 'best_practices' => null, 'seo' => null];
        }

        $periodSeconds = now()->getTimestamp() - $cutoff->getTimestamp();
        $previousStart = $cutoff->copy()->subSeconds($periodSeconds);

        $previous = Audit::query()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$previousStart, $cutoff])
            ->selectRaw('AVG(score_performance) as p, AVG(score_accessibility) as a, AVG(score_best_practices) as b, AVG(score_seo) as s')
            ->first();

        $prevP = $previous?->getAttribute('p');
        $prevA = $previous?->getAttribute('a');
        $prevB = $previous?->getAttribute('b');
        $prevS = $previous?->getAttribute('s');

        if ($previous === null || $prevP === null) {
            return ['performance' => null, 'accessibility' => null, 'best_practices' => null, 'seo' => null];
        }

        return [
            'performance'    => $averages['performance'] !== null ? (int) round($averages['performance'] - (float) $prevP) : null,
            'accessibility'  => $averages['accessibility'] !== null && $prevA !== null ? (int) round($averages['accessibility'] - (float) $prevA) : null,
            'best_practices' => $averages['best_practices'] !== null && $prevB !== null ? (int) round($averages['best_practices'] - (float) $prevB) : null,
            'seo'            => $averages['seo'] !== null && $prevS !== null ? (int) round($averages['seo'] - (float) $prevS) : null,
        ];
    }

    /**
     * Returns sparkline trend data for the PREVIOUS period (for comparison overlay).
     *
     * @return array<string, array<int, int>>
     */
    private function previousMetricTrends(): array
    {
        $cutoff = $this->periodCutoff();

        if ($cutoff === null) {
            return ['performance' => [], 'accessibility' => [], 'best_practices' => [], 'seo' => []];
        }

        $periodSeconds = now()->getTimestamp() - $cutoff->getTimestamp();
        $previousStart = $cutoff->copy()->subSeconds($periodSeconds);

        $bucket = $this->bucketExpression();

        $rows = Audit::query()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$previousStart, $cutoff])
            ->selectRaw("{$bucket} as bucket, AVG(score_performance) as p, AVG(score_accessibility) as a, AVG(score_best_practices) as b, AVG(score_seo) as s")
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        return [
            'performance'    => $rows->pluck('p')->map(fn ($v) => (int) round((float) $v))->values()->all(),
            'accessibility'  => $rows->pluck('a')->map(fn ($v) => (int) round((float) $v))->values()->all(),
            'best_practices' => $rows->pluck('b')->map(fn ($v) => (int) round((float) $v))->values()->all(),
            'seo'            => $rows->pluck('s')->map(fn ($v) => (int) round((float) $v))->values()->all(),
        ];
    }

    /**
     * Computes the "previous period vs current period" delta for the Overview summary.
     *
     * @param  array<string, int|null>  $currentAverages
     * @return array<string, int|null>
     */
    private function previousPeriodDeltas(array $currentAverages): array
    {
        return $this->metricDeltas($currentAverages);
    }

    /**
     * Computes the "Yesterday" narrative summary shown as a horizontal card on the Overview.
     *
     * @return array{audits: int, regressions: int, fixed: int, lcp_improvement_pct: int|null}
     */
    private function dailySummary(): array
    {
        $yesterday = now()->subDay();
        $dayBefore  = now()->subDays(2);

        $todayAudits = Audit::query()
            ->where('status', 'completed')
            ->where('completed_at', '>=', $yesterday)
            ->count();

        // Regressions: audits where score dropped > 5 vs. previous audit for same URL
        $regressions = 0;
        $fixed = 0;
        $lcpImprovements = [];

        $recentAudits = Audit::query()
            ->with('url')
            ->where('status', 'completed')
            ->where('completed_at', '>=', $yesterday)
            ->get();

        foreach ($recentAudits as $audit) {
            $previous = Audit::query()
                ->where('url_id', $audit->url_id)
                ->where('device', $audit->device)
                ->where('status', 'completed')
                ->where('completed_at', '<', $yesterday)
                ->orderByDesc('completed_at')
                ->first();

            if ($previous === null) {
                continue;
            }

            $currentScore  = (int) ($audit->score_performance ?? 0);
            $previousScore = (int) ($previous->score_performance ?? 0);

            if ($previousScore > 0 && ($previousScore - $currentScore) > 5) {
                $regressions++;
            } elseif ($previousScore > 0 && ($currentScore - $previousScore) > 5) {
                $fixed++;
            }

            // LCP improvement
            if ($audit->lcp_ms !== null && $previous->lcp_ms !== null && (float) $previous->lcp_ms > 0) {
                $lcpImprovements[] = (((float) $previous->lcp_ms - (float) $audit->lcp_ms) / (float) $previous->lcp_ms) * 100;
            }
        }

        $avgLcpImprovement = $lcpImprovements !== []
            ? (int) round(array_sum($lcpImprovements) / count($lcpImprovements))
            : null;

        return [
            'audits'              => $todayAudits,
            'regressions'         => $regressions,
            'fixed'               => $fixed,
            'lcp_improvement_pct' => $avgLcpImprovement,
        ];
    }

    /**
     * Returns PageSpeed API usage for the current calendar month.
     *
     * @return array{calls: int, limit: int}
     */
    private function apiUsageThisMonth(): array
    {
        $calls = (int) Audit::query()
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('api_call_count');

        return [
            'calls' => $calls,
            'limit' => 25_000,
        ];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection<int, Audit> $audits
     */
    private static function avgScore($audits, string $col): ?int
    {
        $avg = $audits->avg($col);
        return $avg !== null ? (int) round((float) $avg) : null;
    }

    /**
     * @return array<int, array{type: string, severity: string, title: string, when: \Illuminate\Support\Carbon, link: string|null}>
     */
    private function detectAlerts(): array
    {
        $alerts = [];

        // Budget violations: any audit in last 24h whose perf < 70 (rough proxy without re-evaluating budgets here)
        $criticalAudits = Audit::query()
            ->with('url')
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDay())
            ->where(function ($q): void {
                $q->where('score_performance', '<', 70)
                  ->orWhere('lcp_ms', '>', 4000);
            })
            ->orderByDesc('completed_at')
            ->limit(5)
            ->get();

        foreach ($criticalAudits as $a) {
            if ($a->completed_at === null) {
                continue;
            }
            $alerts[] = [
                'type'     => 'critical',
                'severity' => 'danger',
                'title'    => "Performance critical on {$a->url?->label} (score {$a->score_performance})",
                'when'     => $a->completed_at,
                'link'     => route('vitals.audit', $a),
            ];
        }

        // Regressions: latest score vs 7-day baseline drop > 10%
        $threshold = 10.0;
        foreach (Url::query()->where('enabled', true)->get() as $url) {
            $latest = $url->audits()->where('status', 'completed')->orderByDesc('completed_at')->first();
            $baseline = $url->audits()->where('status', 'completed')
                ->where('completed_at', '<=', now()->subDays(7))
                ->orderByDesc('completed_at')->first();

            if ($latest === null || $baseline === null || $latest->completed_at === null) {
                continue;
            }

            $latestScore   = (int) ($latest->score_performance ?? 0);
            $baselineScore = (int) ($baseline->score_performance ?? 0);

            if ($baselineScore > 0 && (($baselineScore - $latestScore) / $baselineScore) * 100 > $threshold) {
                $alerts[] = [
                    'type'     => 'regression',
                    'severity' => 'warning',
                    'title'    => "Regression on {$url->label}: {$baselineScore} → {$latestScore}",
                    'when'     => $latest->completed_at,
                    'link'     => route('vitals.audit', $latest),
                ];
            }
        }

        // Sort by recency
        usort($alerts, fn (array $a, array $b): int => $b['when']->getTimestamp() <=> $a['when']->getTimestamp());

        return array_slice($alerts, 0, 5);
    }
}
