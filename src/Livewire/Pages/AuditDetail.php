<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use LaravelVitals\Models\Audit;
use Livewire\Component;

final class AuditDetail extends Component
{
    public string $audit = '';

    public function mount(string $audit): void
    {
        $this->audit = $audit;
    }

    public function render(): View
    {
        $auditModel = Audit::query()
            ->with(['url', 'recommendations', 'telemetry'])
            ->findOrFail($this->audit);

        return view('vitals::livewire.pages.audit-detail', [
            'auditModel' => $auditModel,
        ])->layout('vitals::layouts.dashboard');
    }
}
