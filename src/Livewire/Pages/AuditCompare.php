<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use LaravelVitals\Models\Audit;
use Livewire\Component;

/**
 * Side-by-side comparison of two audits.
 *
 * Route: GET /vitals/audits/{a}/compare/{b}
 *
 * Typically used to compare consecutive audits of the same URL and device, but
 * any two audit IDs are accepted.  The UrlDetail page provides a "Compare with
 * previous" button that links here with the previous audit as {a} and the
 * current one as {b}.
 */
final class AuditCompare extends Component
{
    public string $auditAId = '';
    public string $auditBId = '';

    public function mount(string $a, string $b): void
    {
        $this->auditAId = $a;
        $this->auditBId = $b;
    }

    public function render(): View
    {
        $auditA = Audit::query()->with(['url', 'recommendations', 'telemetry'])->findOrFail($this->auditAId);
        $auditB = Audit::query()->with(['url', 'recommendations', 'telemetry'])->findOrFail($this->auditBId);

        $scoreDelta = $this->scoreDeltas($auditA, $auditB);
        $cwvDelta   = $this->cwvDeltas($auditA, $auditB);

        $recoKeysA = $auditA->recommendations->pluck('audit_key')->all();
        $recoKeysB = $auditB->recommendations->pluck('audit_key')->all();

        // Resolved in B: was in A, not in B.
        $resolved = $auditA->recommendations->filter(
            fn ($r): bool => ! in_array($r->audit_key, $recoKeysB, true)
        )->values();

        // New in B: was not in A, is in B.
        $newInB = $auditB->recommendations->filter(
            fn ($r): bool => ! in_array($r->audit_key, $recoKeysA, true)
        )->values();

        return view('vitals::livewire.pages.audit-compare', [
            'auditA'     => $auditA,
            'auditB'     => $auditB,
            'scoreDelta' => $scoreDelta,
            'cwvDelta'   => $cwvDelta,
            'resolved'   => $resolved,
            'newInB'     => $newInB,
        ])->layout('vitals::layouts.dashboard');
    }

    /**
     * @return array<string, array{a: int|null, b: int|null, delta: int|null}>
     */
    private function scoreDeltas(Audit $a, Audit $b): array
    {
        $metrics = ['score_performance', 'score_accessibility', 'score_best_practices', 'score_seo'];
        $result  = [];

        foreach ($metrics as $m) {
            $va = $a->getAttribute($m) !== null ? (int) $a->getAttribute($m) : null;
            $vb = $b->getAttribute($m) !== null ? (int) $b->getAttribute($m) : null;
            $result[$m] = [
                'a'     => $va,
                'b'     => $vb,
                'delta' => ($va !== null && $vb !== null) ? ($vb - $va) : null,
            ];
        }

        return $result;
    }

    /**
     * @return array<string, array{a: float|null, b: float|null, delta: float|null}>
     */
    private function cwvDeltas(Audit $a, Audit $b): array
    {
        $metrics = ['lcp_ms', 'inp_ms', 'cls', 'ttfb_ms'];
        $result  = [];

        foreach ($metrics as $m) {
            $va = $a->getAttribute($m) !== null ? (float) $a->getAttribute($m) : null;
            $vb = $b->getAttribute($m) !== null ? (float) $b->getAttribute($m) : null;
            $result[$m] = [
                'a'     => $va,
                'b'     => $vb,
                'delta' => ($va !== null && $vb !== null) ? ($vb - $va) : null,
            ];
        }

        return $result;
    }
}
