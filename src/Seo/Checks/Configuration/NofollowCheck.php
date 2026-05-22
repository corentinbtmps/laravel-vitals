<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Configuration;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class NofollowCheck implements SeoCheck
{
    public function key(): string
    {
        return 'nofollow';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Configuration;
    }

    public function weight(): int
    {
        return 7;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        $metaRobots = $context->crawler->filter('meta[name="robots"]');
        $metaContent = '';

        if ($metaRobots->count() > 0) {
            $metaContent = strtolower($metaRobots->first()->attr('content') ?? '');
        }

        $xRobotsTag = strtolower($context->response->header('X-Robots-Tag'));

        $hasNofollow = str_contains($metaContent, 'nofollow')
            || str_contains($xRobotsTag, 'nofollow');

        if ($hasNofollow) {
            $source = str_contains($metaContent, 'nofollow') ? 'meta tag' : 'X-Robots-Tag header';

            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.nofollow.title',
                weight: $this->weight(),
                actual: "nofollow found in {$source}",
                expected: 'No nofollow directive (unless intentional)',
                hintKey: 'vitals::vitals.seo.checks.nofollow.hint',
                docUrl: 'https://developers.google.com/search/docs/crawling-indexing/qualify-outbound-links',
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.nofollow.title',
            weight: $this->weight(),
        );
    }
}
