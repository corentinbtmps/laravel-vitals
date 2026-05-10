<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Pages;

use Illuminate\Contracts\View\View;
use LaravelVitals\Models\Audit;
use Livewire\Component;

/**
 * SEO deep-dive page — linked from the audit-detail SEO score card.
 *
 * Route: GET /vitals/audits/{audit}/seo
 *
 * Surfaces all SEO-related Lighthouse audit results alongside two extra
 * checks that run against the URL's HTTP response:
 *  - SitemapAnalyzer: can we find /sitemap.xml?
 *  - RobotsTxtAnalyzer: can we find /robots.txt?
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

        $details = $auditModel->details ?? [];

        // Lighthouse SEO audits embedded in details
        $seoAudits = $details['seo_audits'] ?? [];

        // Extra checks computed from details or defaults
        $checks = $this->buildChecks($auditModel, $seoAudits);

        $seoRecos = $auditModel->recommendations->filter(
            fn ($r) => $r->category === 'seo'
        )->values();

        return view('vitals::livewire.pages.audit-seo', [
            'audit'     => $auditModel,
            'checks'    => $checks,
            'seoRecos'  => $seoRecos,
        ])->layout('vitals::layouts.dashboard');
    }

    /**
     * Build a list of SEO check results from what we know about the audit.
     *
     * @param  array<string, mixed>  $seoAudits
     * @return array<int, array{key: string, label: string, status: string, value: string|null}>
     */
    private function buildChecks(Audit $audit, array $seoAudits): array
    {
        $details = $audit->details ?? [];

        $pass = 'pass';
        $fail = 'fail';
        $warn = 'warn';
        $skip = 'skip';

        $checks = [];

        // Meta description
        $metaDesc = $seoAudits['meta-description'] ?? null;
        $checks[] = [
            'key'    => 'meta_description',
            'label'  => __('vitals::vitals.seo.meta_description'),
            'status' => isset($metaDesc['score']) ? ($metaDesc['score'] >= 1 ? $pass : $fail) : $skip,
            'value'  => null,
        ];

        // Canonical URL
        $canonical = $seoAudits['canonical'] ?? null;
        $checks[] = [
            'key'    => 'canonical',
            'label'  => __('vitals::vitals.seo.canonical'),
            'status' => isset($canonical['score']) ? ($canonical['score'] >= 1 ? $pass : $fail) : $skip,
            'value'  => null,
        ];

        // Structured data
        $structuredData = $seoAudits['structured-data'] ?? null;
        $checks[] = [
            'key'    => 'structured_data',
            'label'  => __('vitals::vitals.seo.structured_data'),
            'status' => isset($structuredData['score']) ? ($structuredData['score'] >= 1 ? $pass : $warn) : $skip,
            'value'  => null,
        ];

        // Lang attribute (hreflang audit or html-has-lang)
        $htmlLang = $seoAudits['html-has-lang'] ?? null;
        $checks[] = [
            'key'    => 'lang_attribute',
            'label'  => __('vitals::vitals.seo.lang_attribute'),
            'status' => isset($htmlLang['score']) ? ($htmlLang['score'] >= 1 ? $pass : $fail) : $skip,
            'value'  => null,
        ];

        // Title length
        $titleTag = $details['title_length'] ?? null;
        $checks[] = [
            'key'    => 'title_length',
            'label'  => __('vitals::vitals.seo.title_length'),
            'status' => $titleTag === null ? $skip : (($titleTag >= 50 && $titleTag <= 60) ? $pass : $warn),
            'value'  => $titleTag !== null ? $titleTag . ' chars' : null,
        ];

        // H1 present (document-title audit)
        $docTitle = $seoAudits['document-title'] ?? null;
        $checks[] = [
            'key'    => 'h1_present',
            'label'  => __('vitals::vitals.seo.h1_present'),
            'status' => isset($docTitle['score']) ? ($docTitle['score'] >= 1 ? $pass : $fail) : $skip,
            'value'  => null,
        ];

        // Sitemap — inferred from details or assume skip
        $sitemapOk = $details['sitemap_accessible'] ?? null;
        $checks[] = [
            'key'    => 'sitemap',
            'label'  => __('vitals::vitals.seo.sitemap'),
            'status' => $sitemapOk === null ? $skip : ($sitemapOk ? $pass : $warn),
            'value'  => null,
        ];

        // Robots.txt — inferred from details or assume skip
        $robotsOk = $details['robots_txt_accessible'] ?? null;
        $checks[] = [
            'key'    => 'robots_txt',
            'label'  => __('vitals::vitals.seo.robots_txt'),
            'status' => $robotsOk === null ? $skip : ($robotsOk ? $pass : $warn),
            'value'  => null,
        ];

        return $checks;
    }
}
