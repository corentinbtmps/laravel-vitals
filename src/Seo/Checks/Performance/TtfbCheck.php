<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Performance;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class TtfbCheck implements SeoCheck
{
    public function key(): string
    {
        return 'ttfb';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Performance;
    }

    public function weight(): int
    {
        return 8;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        $maxMs = (int) config('vitals.seo.thresholds.ttfb_ms', 600);

        // Use Lighthouse TTFB value from the report if available, fall back to audit column
        $ttfbMs = $context->report->metrics['ttfb_ms'] ?? null;
        if ($ttfbMs === null) {
            $ttfbMs = $context->audit->ttfb_ms;
        }

        if ($ttfbMs === null) {
            return SeoCheckResult::pass(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.ttfb.title',
                weight: $this->weight(),
                actual: 'N/A',
            );
        }

        $ms = (int) round((float) $ttfbMs);

        if ($ms > $maxMs) {
            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.ttfb.title',
                weight: $this->weight(),
                actual: "{$ms}ms",
                expected: "≤ {$maxMs}ms",
                hintKey: 'vitals::vitals.seo.checks.ttfb.hint',
                docUrl: 'https://developers.google.com/search/docs/appearance/page-experience#ttfb',
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.ttfb.title',
            weight: $this->weight(),
            actual: "{$ms}ms",
        );
    }
}
