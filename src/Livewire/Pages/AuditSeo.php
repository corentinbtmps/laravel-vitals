<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use LaravelVitals\Models\Audit;
use LaravelVitals\Seo\SeoCheckRegistry;
use Livewire\Component;

/**
 * SEO deep-dive page for a single audit — /vitals/audits/{audit}/seo.
 *
 * Shows:
 * - Score breakdown card (Lighthouse SEO vs Custom SEO combined score)
 * - Category-grouped check list with status icons + actual/expected + hint + Google docs link
 * - Inline detail items for checks that produce per-resource data (broken links etc.)
 */
final class AuditSeo extends Component
{
    public string $auditId = '';

    public function mount(string $audit): void
    {
        $this->auditId = $audit;
    }

    public function render(): View
    {
        $auditModel = Audit::query()->with(['url', 'recommendations'])->findOrFail($this->auditId);

        $seoRecos = $auditModel->recommendations
            ->filter(fn ($r): bool => $r->source === 'seo')
            ->values();

        // Build category-grouped check results for display
        $registry = app(SeoCheckRegistry::class);
        $checksGrouped = $this->buildGroupedChecks($seoRecos, $registry);

        return view('vitals::livewire.pages.audit-seo', [
            'audit'          => $auditModel,
            'seoRecos'       => $seoRecos,
            'checksGrouped'  => $checksGrouped,
            'lighthouseScore' => $auditModel->score_seo,
            'vitalsSeoScore' => $auditModel->vitals_seo_score,
            'vitalsSeoGrade' => $auditModel->vitals_seo_grade,
        ])->layout('vitals::layouts.dashboard');
    }

    /**
     * Build category-grouped check display data.
     * Each check either shows as "passing" or "failing/warning" with details.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, \LaravelVitals\Models\Recommendation>  $seoRecos
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function buildGroupedChecks(
        \Illuminate\Database\Eloquent\Collection $seoRecos,
        SeoCheckRegistry $registry,
    ): array {
        $failedByKey = [];
        foreach ($seoRecos as $reco) {
            $key = str_replace('seo-', '', $reco->audit_key);
            $failedByKey[$key] = $reco;
        }

        $grouped = [];

        foreach ($registry->enabled() as $check) {
            $category = $check->category()->value;

            if (! isset($grouped[$category])) {
                $grouped[$category] = [];
            }

            $reco = $failedByKey[$check->key()] ?? null;

            $entry = [
                'key'         => $check->key(),
                'category'    => $category,
                'weight'      => $check->weight(),
                'status'      => $reco instanceof \Illuminate\Database\Eloquent\Model ? $reco->severity->value : 'pass',
                'title_key'   => 'vitals::vitals.seo.checks.' . $check->key() . '.title',
                'actual'      => null,
                'expected'    => null,
                'hint_key'    => null,
                'doc_url'     => null,
                'detail_items' => null,
            ];

            if ($reco instanceof \Illuminate\Database\Eloquent\Model) {
                $params = is_array($reco->translation_params) ? $reco->translation_params : [];
                $entry['actual']       = $params['actual'] ?? null;
                $entry['expected']     = $params['expected'] ?? null;
                $entry['hint_key']     = 'vitals::vitals.seo.checks.' . $check->key() . '.hint';
                $entry['doc_url']      = $this->docUrlFor($check->key());
                $entry['detail_items'] = $reco->detail_items;
            }

            $grouped[$category][] = $entry;
        }

        return $grouped;
    }

    private function docUrlFor(string $key): ?string
    {
        return match ($key) {
            'noindex'              => 'https://developers.google.com/search/docs/crawling-indexing/block-indexing',
            'nofollow'             => 'https://developers.google.com/search/docs/crawling-indexing/qualify-outbound-links',
            'robots-txt-indexable' => 'https://developers.google.com/search/docs/crawling-indexing/robots/robots_txt',
            'h1'                   => 'https://developers.google.com/search/docs/appearance/title-link',
            'https-links'          => 'https://developers.google.com/search/docs/crawling-indexing/security/https',
            'image-alt'            => 'https://developers.google.com/search/docs/appearance/google-images#use-descriptive-alt-text',
            'broken-links'         => 'https://developers.google.com/search/docs/crawling-indexing/fix-search-crawling-issues',
            'broken-images'        => 'https://developers.google.com/search/docs/appearance/google-images',
            'content-length'       => 'https://developers.google.com/search/docs/fundamentals/creating-helpful-content',
            'keyword-in-first-paragraph' => 'https://developers.google.com/search/docs/fundamentals/creating-helpful-content',
            'meta-description'     => 'https://developers.google.com/search/docs/appearance/snippet#meta-descriptions',
            'title-length'         => 'https://developers.google.com/search/docs/appearance/title-link',
            'og-image'             => 'https://developers.google.com/search/docs/appearance/structured-data/article',
            'html-lang'            => 'https://developers.google.com/search/docs/specialty/international/localized-versions#html',
            'canonical'            => 'https://developers.google.com/search/docs/crawling-indexing/canonicalization',
            'structured-data'      => 'https://developers.google.com/search/docs/appearance/structured-data/intro-structured-data',
            'invalid-head-elements' => 'https://developers.google.com/search/docs/crawling-indexing/special-tags',
            'keyword-in-title'     => 'https://developers.google.com/search/docs/appearance/title-link',
            'ttfb'                 => 'https://developers.google.com/search/docs/appearance/page-experience#ttfb',
            'status-code'          => 'https://developers.google.com/search/docs/crawling-indexing/http-network-errors',
            'html-size'            => 'https://developers.google.com/search/docs/crawling-indexing/large-site-managing-crawl-budget',
            'image-size'           => 'https://developers.google.com/search/docs/appearance/google-images#provide-good-context',
            'js-size'              => 'https://developers.google.com/search/docs/appearance/page-experience',
            'css-size'             => 'https://developers.google.com/search/docs/appearance/page-experience',
            'compression'          => 'https://developers.google.com/search/docs/appearance/page-experience',
            default                => null,
        };
    }
}
