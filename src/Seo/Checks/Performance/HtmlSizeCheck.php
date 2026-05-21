<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Performance;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class HtmlSizeCheck implements SeoCheck
{
    public function key(): string
    {
        return 'html-size';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Performance;
    }

    public function weight(): int
    {
        return 6;
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        $maxBytes = (int) config('vitals.seo.thresholds.html_max_bytes', 100_000);

        $bytes = strlen($context->html);

        if ($bytes > $maxBytes) {
            $kb = round($bytes / 1024, 1);
            $maxKb = round($maxBytes / 1024);

            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.html-size.title',
                weight: $this->weight(),
                actual: "{$kb} KB",
                expected: "≤ {$maxKb} KB",
                hintKey: 'vitals::vitals.seo.checks.html-size.hint',
                docUrl: 'https://developers.google.com/search/docs/crawling-indexing/large-site-managing-crawl-budget',
            );
        }

        $kb = round($bytes / 1024, 1);

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.html-size.title',
            weight: $this->weight(),
            actual: "{$kb} KB",
        );
    }
}
