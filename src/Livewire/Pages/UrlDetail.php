<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use LaravelVitals\Models\Url;
use Livewire\Component;

final class UrlDetail extends Component
{
    public int $url = 0;

    public function mount(int $url): void
    {
        $this->url = $url;
    }

    public function render(): View
    {
        $urlModel = Url::query()->findOrFail($this->url);

        $history = $urlModel->audits()
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->limit(50)
            ->get();

        // 30-day stats
        $thirtyDaysAgo = now()->subDays(30);
        $thirtyDayAudits = $urlModel->audits()
            ->where('status', 'completed')
            ->where('completed_at', '>=', $thirtyDaysAgo)
            ->get(['score_performance', 'score_accessibility', 'score_best_practices', 'score_seo', 'lcp_ms']);

        $avgScores = [
            'performance'    => self::avg($thirtyDayAudits, 'score_performance'),
            'accessibility'  => self::avg($thirtyDayAudits, 'score_accessibility'),
            'best_practices' => self::avg($thirtyDayAudits, 'score_best_practices'),
            'seo'            => self::avg($thirtyDayAudits, 'score_seo'),
        ];

        // Most frequent recommendations on this URL (top 5)
        $frequentRecos = \LaravelVitals\Models\Recommendation::query()
            ->whereIn('audit_id', $history->pluck('id'))
            ->selectRaw('audit_key, severity, title_key, count(*) as occurrences')
            ->groupBy('audit_key', 'severity', 'title_key')
            ->orderByDesc('occurrences')
            ->limit(5)
            ->get();

        // Failed audits in the last 30 days
        $failedAudits = $urlModel->audits()
            ->where('status', 'failed')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'driver', 'device', 'error', 'created_at']);

        return view('vitals::livewire.pages.url-detail', [
            'urlModel'       => $urlModel,
            'history'        => $history,
            'avgScores'      => $avgScores,
            'thirtyDayCount' => $thirtyDayAudits->count(),
            'frequentRecos'  => $frequentRecos,
            'failedAudits'   => $failedAudits,
        ])->layout('vitals::layouts.dashboard');
    }

    /** @param \Illuminate\Database\Eloquent\Collection<int, \LaravelVitals\Models\Audit> $audits */
    private static function avg($audits, string $col): ?int
    {
        $avg = $audits->avg($col);

        return $avg !== null ? (int) round((float) $avg) : null;
    }
}
