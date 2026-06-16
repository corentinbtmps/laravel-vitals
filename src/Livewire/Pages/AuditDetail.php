<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use LaravelVitals\Enums\AuditStatus;
use LaravelVitals\Models\Audit;
use Livewire\Component;

final class AuditDetail extends Component
{
    public string $auditId = '';

    public function mount(string $audit): void
    {
        $this->auditId = $audit;
    }

    public function render(): View
    {
        // A malformed (non-uuid) id crashes a strict driver (PostgreSQL 22P02)
        // before findOrFail can turn it into a clean 404.
        abort_unless(Str::isUuid($this->auditId), 404);

        $auditModel = Audit::query()
            ->with(['url', 'recommendations', 'telemetry'])
            ->findOrFail($this->auditId);

        // Find previous audit for the same URL + device, completed before this one.
        $previous = Audit::query()
            ->where('url_id', $auditModel->url_id)
            ->where('device', $auditModel->device)
            ->where('status', AuditStatus::Completed)
            ->where('id', '!=', $auditModel->id)
            ->where(function ($q) use ($auditModel): void {
                if ($auditModel->completed_at !== null) {
                    $q->where('completed_at', '<', $auditModel->completed_at);
                }
            })
            ->orderByDesc('completed_at')
            ->first();

        // Group recommendations by category for the UI
        $groupedRecos = $auditModel->recommendations->groupBy('category');

        // Front-end / back-end breakdown
        $breakdown = \LaravelVitals\Support\Correlation::lcpBreakdown($auditModel);
        $isBackendBound = \LaravelVitals\Support\Correlation::isBackendBound($auditModel);
        $estimatedGain = $auditModel->telemetry !== null
            ? \LaravelVitals\Support\Correlation::estimatedLcpGainFromQueryFix($auditModel->telemetry)
            : null;

        return view('vitals::livewire.pages.audit-detail', [
            'audit'          => $auditModel,
            'previous'       => $previous,
            'groupedRecos'   => $groupedRecos,
            'breakdown'      => $breakdown,
            'isBackendBound' => $isBackendBound,
            'estimatedGain'  => $estimatedGain,
        ])->layout('vitals::layouts.dashboard');
    }
}
