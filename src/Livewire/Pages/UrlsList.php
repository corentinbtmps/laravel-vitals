<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use LaravelVitals\Enums\AuditStatus;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;
use Livewire\Component;

final class UrlsList extends Component
{
    public function togglePin(int $id): void
    {
        $url = Url::findOrFail($id);
        $url->pinned_at = $url->pinned_at ? null : now();
        $url->save();
    }

    public function render(): View
    {
        $allUrls = Url::query()
            ->withCount('audits')
            ->orderBy('label')
            ->get();

        $pinnedUrls = $allUrls->filter(fn (Url $u): bool => $u->pinned_at !== null)
            ->sortByDesc('pinned_at')
            ->values();

        if ($allUrls->isEmpty()) {
            return view('vitals::livewire.pages.urls-list', [
                'urls'        => $allUrls,
                'allUrls'     => $allUrls,
                'pinnedUrls'  => $pinnedUrls,
                'lastAudits'  => collect(),
                'sparklines'  => [],
            ])->layout('vitals::layouts.dashboard');
        }

        $urlIds = $allUrls->pluck('id')->all();

        // Latest completed audit per url_id, regardless of device.
        $lastAudits = Audit::query()
            ->whereIn('url_id', $urlIds)
            ->where('status', AuditStatus::Completed)
            ->orderByDesc('completed_at')
            ->get([
                'url_id', 'score_performance', 'score_accessibility',
                'score_best_practices', 'score_seo', 'lcp_ms', 'completed_at', 'id',
            ])
            ->groupBy('url_id')
            ->map(fn ($group) => $group->first());

        // 7-day perf trend per URL (one point per day, average across all devices).
        $sevenDaysAgo = now()->subDays(7);
        $trendRows = Audit::query()
            ->whereIn('url_id', $urlIds)
            ->where('status', AuditStatus::Completed)
            ->where('completed_at', '>=', $sevenDaysAgo)
            ->selectRaw('url_id, DATE(completed_at) as day, AVG(score_performance) as avg_score')
            ->groupBy('url_id', 'day')
            ->orderBy('day')
            ->get();

        $sparklines = [];
        foreach ($urlIds as $urlId) {
            $points = $trendRows->where('url_id', $urlId)->pluck('avg_score')->map(fn ($v): int => (int) round((float) $v))->all();
            $sparklines[$urlId] = $points;
        }

        return view('vitals::livewire.pages.urls-list', [
            'urls'        => $allUrls,
            'allUrls'     => $allUrls,
            'pinnedUrls'  => $pinnedUrls,
            'lastAudits'  => $lastAudits,
            'sparklines'  => $sparklines,
        ])->layout('vitals::layouts.dashboard');
    }
}
