<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Recommendations\RecommendationDocs;
use LaravelVitals\Recommendations\RecommendationRegistry;
use Livewire\Component;

/**
 * Deep "Where in my code" view for a single audit_key.
 *
 * Shows:
 *  - The recommendation title + description from RecommendationDocs
 *  - Severity badge + occurrence count
 *  - Every distinct (url, file, line) triple grouped by URL
 *  - N+1 repeated query patterns when applicable
 *  - Doc links
 */
final class IssueDetail extends Component
{
    public string $auditKey = '';

    public function mount(string $auditKey): void
    {
        $this->auditKey = $auditKey;
    }

    public function render(): View
    {
        $registry   = new RecommendationRegistry();
        $descriptor = $registry->get($this->auditKey);

        /** @var Collection<int, Recommendation> $recommendations */
        $recommendations = Recommendation::query()
            ->where('audit_key', $this->auditKey)
            ->with('audit.url')
            ->orderByDesc('created_at')
            ->get();

        // If the registry doesn't know this audit_key, fall back to the first
        // stored recommendation row so Lighthouse audits we haven't curated
        // still get a usable deep view. Only 404 when neither source has data.
        if ($descriptor === null) {
            $first = $recommendations->first();

            if ($first === null) {
                abort(404);
            }

            $descriptor = new \LaravelVitals\Recommendations\RecommendationDescriptor(
                auditKey:       $this->auditKey,
                source:         'lighthouse',
                category:       'performance',
                severity:       $first->severity,
                titleKey:       $first->title_key,
                descriptionKey: $first->description_key,
            );
        }

        $docs = RecommendationDocs::for($this->auditKey);

        $occurrenceCount = $recommendations->count();

        // Group by URL, collecting all occurrences (audit + code refs + query patterns)
        $byUrl = $recommendations->groupBy(function (Recommendation $reco): string {
            $url = $reco->audit?->url;

            return (string) ($url !== null ? $url->path : '/');
        });

        // Build a flat list of occurrences for the view
        /** @var array<string, array{url_label: string, url_path: string, url_id: int|null, occurrences: list<array{audit_id: string, audit_date: string|null, code_references: array<int, mixed>, top_patterns: array<int, mixed>}>}> $grouped */
        $grouped = [];

        foreach ($byUrl as $path => $recos) {
            $firstReco  = $recos->first();
            $firstUrl   = $firstReco?->audit?->url;
            $urlPath    = (string) ($firstUrl !== null ? $firstUrl->path : '/');
            $urlLabel   = (string) ($firstUrl !== null ? ($firstUrl->label ?? $urlPath) : $urlPath);
            $urlId      = $firstUrl?->id;

            $occurrences = [];
            foreach ($recos as $reco) {
                $patterns = [];
                if ($reco->audit_key === 'n-plus-one-detected') {
                    $params = is_array($reco->translation_params) ? $reco->translation_params : [];
                    $patterns = is_array($params['top_patterns'] ?? null) ? $params['top_patterns'] : [];
                }

                $occurrences[] = [
                    'audit_id'        => $reco->audit_id ?? '',
                    'audit_date'      => $reco->audit?->completed_at?->toDayDateTimeString(),
                    'code_references' => is_array($reco->code_references) ? $reco->code_references : [],
                    'top_patterns'    => $patterns,
                ];
            }

            $grouped[$urlPath] = [
                'url_label'   => $urlLabel,
                'url_path'    => $urlPath,
                'url_id'      => $urlId,
                'occurrences' => $occurrences,
            ];
        }

        return view('vitals::livewire.pages.issue-detail', [
            'auditKey'        => $this->auditKey,
            'descriptor'      => $descriptor,
            'docs'            => $docs,
            'occurrenceCount' => $occurrenceCount,
            'grouped'         => $grouped,
        ])->layout('vitals::layouts.dashboard');
    }
}
