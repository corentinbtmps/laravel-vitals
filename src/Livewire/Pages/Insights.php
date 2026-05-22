<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use LaravelVitals\Enums\AuditStatus;
use LaravelVitals\Enums\Severity;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;
use Livewire\Component;

final class Insights extends Component
{
    public function render(): View
    {
        $sevenDaysAgo = now()->subDays(7);
        $fourteenDaysAgo = now()->subDays(14);

        $recentAudits = Audit::query()
            ->where('status', AuditStatus::Completed)
            ->where('completed_at', '>=', $sevenDaysAgo)
            ->get(['id', 'url_id', 'score_performance', 'completed_at', 'details']);

        // Quick wins: top 5 audit_keys (warning/critical) sorted by occurrences across URLs
        $quickWins = Recommendation::query()
            ->whereIn('audit_id', $recentAudits->pluck('id'))
            ->whereIn('severity', [Severity::Warning->value, Severity::Critical->value])
            ->selectRaw('audit_key, severity, title_key, count(*) as occurrences, count(distinct audit_id) as audit_count')
            ->groupBy('audit_key', 'severity', 'title_key')
            ->orderByDesc('occurrences')
            ->limit(5)
            ->get();

        // Worsening URLs: latest 7d avg perf vs prior 7d avg perf
        $worsening = [];
        foreach (Url::query()->where('enabled', true)->get() as $url) {
            $latest = $url->audits()->where('status', AuditStatus::Completed)
                ->where('completed_at', '>=', $sevenDaysAgo)
                ->avg('score_performance');
            $prior = $url->audits()->where('status', AuditStatus::Completed)
                ->whereBetween('completed_at', [$fourteenDaysAgo, $sevenDaysAgo])
                ->avg('score_performance');

            if ($latest === null || $prior === null) {
                continue;
            }

            $delta = (int) round((float) $latest - (float) $prior);

            if ($delta < -2) {
                $worsening[] = [
                    'url'    => $url,
                    'latest' => (int) round((float) $latest),
                    'prior'  => (int) round((float) $prior),
                    'delta'  => $delta,
                ];
            }
        }
        usort($worsening, fn (array $a, array $b): int => $a['delta'] <=> $b['delta']);
        $worsening = array_slice($worsening, 0, 5);

        // Improving URLs (mirror of worsening)
        $improving = [];
        foreach (Url::query()->where('enabled', true)->get() as $url) {
            $latest = $url->audits()->where('status', AuditStatus::Completed)
                ->where('completed_at', '>=', $sevenDaysAgo)
                ->avg('score_performance');
            $prior = $url->audits()->where('status', AuditStatus::Completed)
                ->whereBetween('completed_at', [$fourteenDaysAgo, $sevenDaysAgo])
                ->avg('score_performance');

            if ($latest === null || $prior === null) {
                continue;
            }

            $delta = (int) round((float) $latest - (float) $prior);

            if ($delta > 2) {
                $improving[] = [
                    'url'    => $url,
                    'latest' => (int) round((float) $latest),
                    'prior'  => (int) round((float) $prior),
                    'delta'  => $delta,
                ];
            }
        }
        usort($improving, fn (array $a, array $b): int => $b['delta'] <=> $a['delta']);
        $improving = array_slice($improving, 0, 5);

        // Top third parties across audits (aggregated from details)
        $tpAggregate = [];
        foreach ($recentAudits as $a) {
            $tps = is_array($a->details['third_parties'] ?? null) ? $a->details['third_parties'] : [];
            foreach ($tps as $tp) {
                if (! is_array($tp)) {
                    continue;
                }
                $name = (string) ($tp['entity'] ?? 'unknown');
                if ($name === '' || $name === 'unknown') {
                    continue;
                }
                $tpAggregate[$name] ??= ['name' => $name, 'occurrences' => 0, 'total_blocking_ms' => 0.0];
                $tpAggregate[$name]['occurrences']++;
                $tpAggregate[$name]['total_blocking_ms'] += (float) ($tp['blocking_ms'] ?? 0);
            }
        }
        usort($tpAggregate, fn (array $a, array $b): int => $b['total_blocking_ms'] <=> $a['total_blocking_ms']);
        $topThirdParties = array_slice($tpAggregate, 0, 5);

        return view('vitals::livewire.pages.insights', [
            'quickWins'       => $quickWins,
            'worsening'       => $worsening,
            'improving'       => $improving,
            'topThirdParties' => $topThirdParties,
        ])->layout('vitals::layouts.dashboard');
    }
}
