<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use LaravelVitals\Enums\AuditStatus;
use LaravelVitals\Enums\Period;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;
use LaravelVitals\Support\TopFailingCheck;
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

        if ($cutoff instanceof \Carbon\Carbon) {
            $auditQuery->where('completed_at', '>=', $cutoff);
        }

        $audits = $auditQuery->orderByDesc('completed_at')->get();

        // Compute per-URL stats (latest audit per URL)
        $perUrl = $this->buildPerUrlStats($audits);

        // Average vitals_seo_score across URLs
        $avgScore = $perUrl !== [] ? (int) round(
            array_sum(array_column($perUrl, 'vitals_seo_score')) / count($perUrl)
        ) : null;

        $urlsWithFailures = count(array_filter($perUrl, fn (array $u): bool => $u['failing_checks'] > 0));

        // Top failing checks — counts unique URLs (one row per latest-audit-per-URL),
        // so badges align with the per-URL table above (40 URLs → max 40 occurrences).
        // Aggregated in PHP from the already eager-loaded recommendations: a grouped
        // SQL query with MAX(audit_id) blows up on PostgreSQL, which has no max(uuid)
        // aggregate (MySQL/SQLite store UUIDs as text, so it silently works there).
        $topFailing = $this->buildTopFailing($perUrl);

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
     * Aggregate the top failing SEO checks across the latest audit per URL.
     *
     * Counts one occurrence per URL (the per-URL rows already collapse to the
     * latest audit), groups by check identity, and keeps a sample audit id for
     * the "view" link. Done in PHP so it stays portable across database drivers
     * (PostgreSQL has no max(uuid) aggregate).
     *
     * @param  array<int, array{url: Url, audit: Audit, vitals_seo_score: int|null, lighthouse_seo: int|null, failing_checks: int, grade: string|null}>  $perUrl
     * @return \Illuminate\Support\Collection<int, TopFailingCheck>
     */
    private function buildTopFailing(array $perUrl): \Illuminate\Support\Collection
    {
        $keysInCategory = null;

        if ($this->category !== 'all') {
            $registry = app(\LaravelVitals\Seo\SeoCheckRegistry::class);
            $keysInCategory = array_map(
                fn (\LaravelVitals\Seo\Contracts\SeoCheck $c): string => 'seo-' . $c->key(),
                array_filter(
                    $registry->all(),
                    fn (\LaravelVitals\Seo\Contracts\SeoCheck $c): bool => $c->category()->value === $this->category,
                ),
            );
        }

        $meta = [];
        $counts = [];

        foreach ($perUrl as $row) {
            foreach ($row['audit']->recommendations as $reco) {
                if ($reco->source !== 'seo') {
                    continue;
                }

                if ($keysInCategory !== null && ! in_array($reco->audit_key, $keysInCategory, true)) {
                    continue;
                }

                $key = $reco->audit_key . '|' . $reco->title_key . '|' . $reco->severity->value;

                $meta[$key] ??= [
                    'audit_key'       => $reco->audit_key,
                    'title_key'       => $reco->title_key,
                    'severity'        => $reco->severity,
                    'sample_audit_id' => $reco->audit_id,
                ];

                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }
        }

        $rows = [];

        foreach ($meta as $key => $info) {
            $rows[] = new TopFailingCheck(
                audit_key: $info['audit_key'],
                title_key: $info['title_key'],
                severity: $info['severity'],
                occurrences: $counts[$key],
                sample_audit_id: $info['sample_audit_id'],
            );
        }

        return collect($rows)
            ->sortByDesc('occurrences')
            ->take(15)
            ->values();
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

            $seoRecos = $audit->recommendations->filter(fn ($r): bool => $r->source === 'seo');

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
        usort($result, fn (array $a, array $b): int => ($a['vitals_seo_score'] ?? 0) <=> ($b['vitals_seo_score'] ?? 0));

        return $result;
    }
}
