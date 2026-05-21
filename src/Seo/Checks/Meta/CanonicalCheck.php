<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Meta;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class CanonicalCheck implements SeoCheck
{
    public function key(): string
    {
        return 'canonical';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Meta;
    }

    public function weight(): int
    {
        return 9;
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        $canonical = $context->crawler->filter('link[rel="canonical"]');

        if ($canonical->count() === 0) {
            return SeoCheckResult::fail(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.canonical.title',
                weight: $this->weight(),
                actual: 'Missing',
                expected: '<link rel="canonical" href="...">',
                hintKey: 'vitals::vitals.seo.checks.canonical.hint',
                docUrl: 'https://developers.google.com/search/docs/crawling-indexing/canonicalization',
            );
        }

        $href = (string) ($canonical->first()->attr('href') ?? '');
        if (trim($href) === '') {
            return SeoCheckResult::fail(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.canonical.title',
                weight: $this->weight(),
                actual: 'Empty href',
                expected: 'Valid absolute URL',
                hintKey: 'vitals::vitals.seo.checks.canonical.hint',
                docUrl: 'https://developers.google.com/search/docs/crawling-indexing/canonicalization',
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.canonical.title',
            weight: $this->weight(),
            actual: $href,
        );
    }
}
