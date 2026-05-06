<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
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
        $auditModel = Audit::query()
            ->with(['url', 'recommendations', 'telemetry'])
            ->findOrFail($this->auditId);

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
            'groupedRecos'   => $groupedRecos,
            'breakdown'      => $breakdown,
            'isBackendBound' => $isBackendBound,
            'estimatedGain'  => $estimatedGain,
        ])->layout('vitals::layouts.dashboard');
    }
}
