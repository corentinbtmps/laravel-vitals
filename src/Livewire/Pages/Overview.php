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

        // Perf trend (one data point per day, average across all URLs)
        $perfTrendQuery = Audit::query()
            ->where('status', 'completed');

        if ($cutoff !== null) {
            $perfTrendQuery->where('completed_at', '>=', $cutoff);
        }

        $perfTrend = $perfTrendQuery
            ->selectRaw('DATE(completed_at) as day, AVG(score_performance) as avg')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('avg', 'day')
            ->map(fn ($v) => (int) round((float) $v))
            ->all();

        return view('vitals::livewire.pages.overview', [
            'recent'             => $recent,
            'averages'           => $averages,
            'overall'            => $overall,
            'overallGrade'       => \LaravelVitals\Support\Health::grade($overall),
            'overallColor'       => \LaravelVitals\Support\Health::colorForScore($overall),
            'activeAlerts'       => $activeAlerts,
            'topRecommendations' => $topRecommendations,
            'urlsCount'          => $urlsCount,
            'perfTrend'          => $perfTrend,
            'periodLabel'        => $this->periodLabel(),
        ])->layout('vitals::layouts.dashboard');
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
