<?php

declare(strict_types=1);

namespace LaravelVitals\Seo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use LaravelVitals\Enums\Severity;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Support\LighthouseReport;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Orchestrator that fetches the page HTML, parses it, runs all enabled SEO checks,
 * and persists non-passing results as Recommendation rows with source='seo'.
 */
final class SeoAuditor
{
    public function __construct(
        private readonly SeoCheckRegistry $registry,
    ) {}

    public function run(Audit $audit, LighthouseReport $report): void
    {
        if (! (bool) config('vitals.seo.enabled', true)) {
            return;
        }

        $url = $audit->url;
        if ($url === null) {
            return;
        }

        $path = $url->path ?? '/';
        $baseUrl = rtrim((string) config('app.url', ''), '/');
        $fullUrl = $baseUrl . $path;

        try {
            $response = Http::timeout(15)
                ->withHeaders(['Accept-Encoding' => 'gzip, br'])
                ->get($fullUrl);

            $html    = $response->body();
            $crawler = new Crawler($html);
        } catch (\Exception $e) {
            Log::warning('[LaravelVitals] SeoAuditor could not fetch page HTML', [
                'url'   => $fullUrl,
                'error' => $e->getMessage(),
            ]);
            return;
        }

        $context = new SeoCheckContext(
            audit: $audit,
            url: $url,
            report: $report,
            response: $response,
            html: $html,
            crawler: $crawler,
        );

        foreach ($this->registry->enabled() as $check) {
            try {
                $result = $check->run($context);
            } catch (\Exception $e) {
                Log::warning('[LaravelVitals] SeoCheck threw an exception', [
                    'check' => $check->key(),
                    'error' => $e->getMessage(),
                ]);
                continue;
            }

            if ($result->status === SeoCheckStatus::Pass) {
                continue;
            }

            $this->persistResult($audit, $result);
        }
    }

    private function persistResult(Audit $audit, SeoCheckResult $result): void
    {
        $severity = match ($result->status) {
            SeoCheckStatus::Fail    => Severity::Critical,
            SeoCheckStatus::Warning => Severity::Warning,
            default                 => Severity::Info,
        };

        Recommendation::create([
            'audit_id'           => $audit->id,
            'source'             => 'seo',
            'audit_key'          => 'seo-' . $result->key,
            'category'           => 'seo',
            'severity'           => $severity,
            'title_key'          => $result->messageKey,
            'description_key'    => 'vitals::vitals.seo.checks.' . $result->key . '.description',
            'translation_params' => array_filter([
                'actual'   => $result->actual,
                'expected' => $result->expected,
            ]),
            'metrics'            => ['weight' => $result->weight],
            'code_references'    => [],
            'detail_items'       => $result->detailItems,
        ]);
    }
}
