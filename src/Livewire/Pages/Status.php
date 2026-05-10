<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\RumEvent;
use Livewire\Component;

/**
 * Public status page — opt-in via config('vitals.status.enabled', false).
 *
 * Shows:
 *  - App name + description
 *  - Uptime % over the last 30 days (derived from whether RUM TTFB events exist)
 *  - Aggregated CWV split (good / needs improvement / poor) last 7 days
 *  - Recent incidents (audits where any score < 70 in last 7 days)
 *  - Last updated timestamp
 *
 * Uses the minimal `vitals::layouts.public` layout — NOT the dashboard layout.
 */
final class Status extends Component
{
    public function render(): View
    {
        $uptime     = $this->computeUptime();
        $cwvSplit   = $this->computeCwvSplit();
        $incidents  = $this->recentIncidents();

        return view('vitals::livewire.pages.status', [
            'appName'     => config('vitals.status.title', config('app.name', 'Laravel')),
            'description' => config('vitals.status.description', __('vitals::vitals.status.default_description')),
            'logoUrl'     => config('vitals.status.logo_url'),
            'uptime'      => $uptime,
            'cwvSplit'    => $cwvSplit,
            'incidents'   => $incidents,
            'updatedAt'   => now(),
        ])->layout('vitals::layouts.public');
    }

    /**
     * Uptime % over the last 30 days, based on daily presence of RUM TTFB events.
     * A day is considered "up" if at least one RUM event was recorded on that day.
     */
    private function computeUptime(): float
    {
        $totalDays = 30;

        $activeDays = RumEvent::query()
            ->where('occurred_at', '>=', now()->subDays($totalDays))
            ->selectRaw("DATE(occurred_at) as day")
            ->groupBy('day')
            ->count();

        if ($activeDays === 0) {
            // No RUM data — compute from audits as fallback.
            $activeDays = Audit::query()
                ->where('status', 'completed')
                ->where('completed_at', '>=', now()->subDays($totalDays))
                ->selectRaw("DATE(completed_at) as day")
                ->groupBy('day')
                ->count();
        }

        return min(100.0, round($activeDays / $totalDays * 100, 2));
    }

    /**
     * CWV distribution (good / needs_improvement / poor) for the last 7 days.
     *
     * @return array{good: int, needs_improvement: int, poor: int, total: int}
     */
    private function computeCwvSplit(): array
    {
        $audits = Audit::query()
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays(7))
            ->get(['lcp_ms', 'cls', 'inp_ms']);

        $good = 0;
        $needs = 0;
        $poor = 0;

        foreach ($audits as $a) {
            $lcpStatus  = \LaravelVitals\Support\Health::cwvStatus('lcp_ms', $a->lcp_ms);
            $clsStatus  = \LaravelVitals\Support\Health::cwvStatus('cls', $a->cls);
            $inpStatus  = \LaravelVitals\Support\Health::cwvStatus('inp_ms', $a->inp_ms);

            $statuses = [$lcpStatus, $clsStatus, $inpStatus];

            if (in_array('poor', $statuses, true)) {
                $poor++;
            } elseif (in_array('needs_improvement', $statuses, true)) {
                $needs++;
            } else {
                $good++;
            }
        }

        $needs_improvement = $needs;
        $total = $good + $needs_improvement + $poor;

        return compact('good', 'needs_improvement', 'poor', 'total');
    }

    /**
     * Recent incidents: audits where performance < 70 in the last 7 days.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Audit>
     */
    private function recentIncidents()
    {
        return Audit::query()
            ->with('url')
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays(7))
            ->where('score_performance', '<', 70)
            ->orderByDesc('completed_at')
            ->limit(5)
            ->get();
    }
}
