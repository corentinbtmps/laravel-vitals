<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Agentic;

use Illuminate\Support\Facades\Http;
use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

/**
 * Checks that a sitemap is discoverable — declared in robots.txt or reachable at
 * /sitemap.xml. A sitemap is a core discoverability signal for AI agents crawling
 * a site, per Cloudflare's agent-ready discoverability category.
 *
 * @see https://isitagentready.com/
 */
final class SitemapDeclaredCheck implements SeoCheck
{
    private const DOC_URL = 'https://isitagentready.com/';

    public function key(): string
    {
        return 'sitemap-declared';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Agentic;
    }

    public function weight(): int
    {
        return 3;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        $baseUrl = rtrim((string) config('app.url', ''), '/');

        try {
            $robots = Http::timeout(10)->get($baseUrl . '/robots.txt');
            if ($robots->successful() && stripos($robots->body(), 'sitemap:') !== false) {
                return $this->pass('Sitemap declared in robots.txt');
            }
        } catch (\Exception) {
            // Fall through to the direct probe.
        }

        try {
            $sitemap = Http::timeout(10)->get($baseUrl . '/sitemap.xml');
            if ($sitemap->successful()) {
                return $this->pass('Sitemap reachable at /sitemap.xml');
            }
        } catch (\Exception) {
            // Treated as missing below.
        }

        return SeoCheckResult::warning(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.sitemap-declared.title',
            weight: $this->weight(),
            actual: 'No sitemap declared in robots.txt or at /sitemap.xml',
            expected: 'A Sitemap: directive in robots.txt or a reachable /sitemap.xml',
            hintKey: 'vitals::vitals.seo.checks.sitemap-declared.hint',
            docUrl: self::DOC_URL,
        );
    }

    private function pass(string $actual): SeoCheckResult
    {
        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.sitemap-declared.title',
            weight: $this->weight(),
            actual: $actual,
        );
    }
}
