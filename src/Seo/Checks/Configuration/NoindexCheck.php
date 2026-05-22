<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Configuration;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class NoindexCheck implements SeoCheck
{
    public function key(): string
    {
        return 'noindex';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Configuration;
    }

    public function weight(): int
    {
        return 10;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        // Check meta robots tag
        $metaRobots = $context->crawler->filter('meta[name="robots"]');
        $metaContent = '';

        if ($metaRobots->count() > 0) {
            $metaContent = strtolower($metaRobots->first()->attr('content') ?? '');
        }

        // Check X-Robots-Tag header
        $xRobotsTag = strtolower($context->response->header('X-Robots-Tag'));

        $hasNoindex = str_contains($metaContent, 'noindex')
            || str_contains($xRobotsTag, 'noindex');

        if ($hasNoindex) {
            $source = str_contains($metaContent, 'noindex') ? 'meta tag' : 'X-Robots-Tag header';

            return SeoCheckResult::fail(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.noindex.title',
                weight: $this->weight(),
                actual: "noindex found in {$source}",
                expected: 'No noindex directive',
                hintKey: 'vitals::vitals.seo.checks.noindex.hint',
                docUrl: 'https://developers.google.com/search/docs/crawling-indexing/block-indexing',
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.noindex.title',
            weight: $this->weight(),
        );
    }
}
