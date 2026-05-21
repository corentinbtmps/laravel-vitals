<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Meta;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class OpenGraphImageCheck implements SeoCheck
{
    public function key(): string
    {
        return 'og-image';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Meta;
    }

    public function weight(): int
    {
        return 6;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        $ogImage = $context->crawler->filter('meta[property="og:image"]');

        if ($ogImage->count() === 0) {
            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.og-image.title',
                weight: $this->weight(),
                actual: 'Missing',
                expected: 'og:image meta tag present',
                hintKey: 'vitals::vitals.seo.checks.og-image.hint',
                docUrl: 'https://developers.google.com/search/docs/appearance/structured-data/article',
            );
        }

        $content = (string) ($ogImage->first()->attr('content') ?? '');
        if (trim($content) === '') {
            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.og-image.title',
                weight: $this->weight(),
                actual: 'Empty value',
                expected: 'Valid image URL',
                hintKey: 'vitals::vitals.seo.checks.og-image.hint',
                docUrl: 'https://developers.google.com/search/docs/appearance/structured-data/article',
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.og-image.title',
            weight: $this->weight(),
        );
    }
}
