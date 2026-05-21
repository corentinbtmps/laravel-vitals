<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Content;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class H1Check implements SeoCheck
{
    public function key(): string
    {
        return 'h1';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Content;
    }

    public function weight(): int
    {
        return 8;
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        $h1Elements = $context->crawler->filter('h1');
        $count = $h1Elements->count();

        if ($count === 0) {
            return SeoCheckResult::fail(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.h1.title',
                weight: $this->weight(),
                actual: 'No H1 found',
                expected: 'Exactly 1 H1 element',
                hintKey: 'vitals::vitals.seo.checks.h1.hint',
                docUrl: 'https://developers.google.com/search/docs/appearance/title-link',
            );
        }

        if ($count > 1) {
            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.h1.title',
                weight: $this->weight(),
                actual: "{$count} H1 elements found",
                expected: 'Exactly 1 H1 element',
                hintKey: 'vitals::vitals.seo.checks.h1.hint_multiple',
                docUrl: 'https://developers.google.com/search/docs/appearance/title-link',
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.h1.title',
            weight: $this->weight(),
            actual: '1 H1 element',
        );
    }
}
