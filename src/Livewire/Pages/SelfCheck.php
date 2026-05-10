<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\BackendTelemetry;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\RumEvent;
use LaravelVitals\Models\Url;
use Livewire\Component;

/**
 * Self-monitoring admin panel — shows internal Vitals health metrics.
 *
 * Route: GET /vitals/admin/self-check (requires auth gate, same as other dashboard pages)
 */
final class SelfCheck extends Component
{
    public function render(): View
    {
        $tableSizes  = $this->tableSizes();
        $recentRuns  = Audit::query()
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->limit(5)
            ->get(['id', 'url_id', 'driver', 'device', 'completed_at', 'score_performance']);

        $slowTelemetry = BackendTelemetry::query()
            ->orderByDesc('duration_ms')
            ->limit(10)
            ->get(['id', 'route_name', 'duration_ms', 'queries_count', 'created_at']);

        return view('vitals::livewire.pages.self-check', [
            'tableSizes'    => $tableSizes,
            'recentRuns'    => $recentRuns,
            'slowTelemetry' => $slowTelemetry,
            'checkedAt'     => now(),
        ])->layout('vitals::layouts.dashboard');
    }

    /**
     * @return array<string, int>
     */
    private function tableSizes(): array
    {
        $tables = [
            'vitals_audits',
            'vitals_audit_recommendations',
            'vitals_backend_telemetry',
            'vitals_rum_events',
            'vitals_urls',
        ];

        $sizes = [];

        foreach ($tables as $table) {
            try {
                $sizes[$table] = (int) DB::connection(config('vitals.database'))->table($table)->count();
            } catch (\Throwable) {
                $sizes[$table] = -1;
            }
        }

        return $sizes;
    }
}
