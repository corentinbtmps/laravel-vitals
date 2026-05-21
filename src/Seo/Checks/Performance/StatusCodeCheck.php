<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Performance;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class StatusCodeCheck implements SeoCheck
{
    public function key(): string
    {
        return 'status-code';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Performance;
    }

    public function weight(): int
    {
        return 10;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        $status = $context->response->status();

        if ($status >= 400) {
            return SeoCheckResult::fail(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.status-code.title',
                weight: $this->weight(),
                actual: (string) $status,
                expected: '200 OK',
                hintKey: 'vitals::vitals.seo.checks.status-code.hint',
                docUrl: 'https://developers.google.com/search/docs/crawling-indexing/http-network-errors',
            );
        }

        if ($status >= 300) {
            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.status-code.title',
                weight: $this->weight(),
                actual: "{$status} (redirect)",
                expected: '200 OK (redirect is acceptable but adds latency)',
                hintKey: 'vitals::vitals.seo.checks.status-code.hint_redirect',
                docUrl: 'https://developers.google.com/search/docs/crawling-indexing/http-network-errors',
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.status-code.title',
            weight: $this->weight(),
            actual: (string) $status,
        );
    }
}
