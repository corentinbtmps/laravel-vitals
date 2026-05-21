<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use LaravelVitals\Enums\AuditStatus;
use LaravelVitals\Enums\Period;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;
use Livewire\Component;

/**
 * Cross-URL SEO overview page — /vitals/seo.
 *
 * Shows aggregated SEO scores, top failing checks across all URLs,
 * per-URL SEO score table, and a category filter.
 */
final class Seo extends Component
{
    public Period $period = Period::D7;

    public string $category = 'all';

    public function render(): View
    {
        $cutoff = $this->period->cutoff();

        // Audits in the period
        $auditQuery = Audit::query()
            ->with(['url', 'recommendations'])
            ->where('status', AuditStatus::Completed);

        if ($cutoff !== null) {
            $auditQuery->where('completed_at', '>=', $cutoff);
        }

        $audits = $auditQuery->orderByDesc('completed_at')->get();

        // Compute per-URL stats (latest audit per URL)
        $perUrl = $this->buildPerUrlStats($audits);

        // Average vitals_seo_score across URLs
        $avgScore = $perUrl !== [] ? (int) round(
            array_sum(array_column($perUrl, 'vitals_seo_score')) / count($perUrl)
        ) : null;

        $urlsWithFailures = count(array_filter($perUrl, fn ($u) => $u['failing_checks'] > 0));

        // Top failing checks — counts unique URLs (one row per latest-audit-per-URL),
        // so badges align with the per-URL table above (40 URLs → max 40 occurrences).
        $latestAuditIds = array_map(static fn (array $row): string => $row['audit']->id, $perUrl);

        $topFailingQuery = Recommendation::query()
            ->whereIn('audit_id', $latestAuditIds)
            ->where('source', 'seo');

        if ($this->category !== 'all') {
            $registry = app(\LaravelVitals\Seo\SeoCheckRegistry::class);
            $keysInCategory = array_map(
                fn ($c) => 'seo-' . $c->key(),
                array_filter(
                    $registry->all(),
                    fn ($c) => $c->category()->value === $this->category,
                ),
            );

            $topFailingQuery->whereIn('audit_key', $keysInCategory);
        }

        $topFailing = $topFailingQuery
            ->selectRaw('audit_key, title_key, severity, COUNT(*) as occurrences, MAX(audit_id) as sample_audit_id')
            ->groupBy('audit_key', 'title_key', 'severity')
            ->orderByDesc('occurrences')
            ->limit(15)
            ->get();

        // Period selector options
        $availablePeriods = Period::availableFor(Period::effectiveRetentionDays());

        return view('vitals::livewire.pages.seo', [
            'perUrl'          => $perUrl,
            'avgScore'        => $avgScore,
            'urlsWithFailures' => $urlsWithFailures,
            'topFailing'      => $topFailing,
            'availablePeriods' => $availablePeriods,
        ])->layout('vitals::layouts.dashboard');
    }

    /**
     * Build per-URL stats array: latest audit's vitals_seo_score + failing check count.
     *
     * @param  Collection<int, Audit>  $audits
     * @return array<int, array{url: Url, audit: Audit, vitals_seo_score: int|null, lighthouse_seo: int|null, failing_checks: int, grade: string|null}>
     */
    private function buildPerUrlStats(Collection $audits): array
    {
        $seenUrls = [];
        $result = [];

        foreach ($audits as $audit) {
            if ($audit->url === null) {
                continue;
            }

            $urlId = $audit->url->id;

            if (isset($seenUrls[$urlId])) {
                continue; // only latest audit per URL
            }
            $seenUrls[$urlId] = true;

            $seoRecos = $audit->recommendations->filter(fn ($r) => $r->source === 'seo');

            $result[] = [
                'url'             => $audit->url,
                'audit'           => $audit,
                'vitals_seo_score' => $audit->vitals_seo_score,
                'lighthouse_seo'  => $audit->score_seo,
                'failing_checks'  => $seoRecos->count(),
                'grade'           => $audit->vitals_seo_grade,
            ];
        }

        // Sort by vitals_seo_score ascending (worst first)
        usort($result, fn ($a, $b) => ($a['vitals_seo_score'] ?? 0) <=> ($b['vitals_seo_score'] ?? 0));

        return $result;
    }
}
