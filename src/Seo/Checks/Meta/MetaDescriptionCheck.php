<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Meta;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class MetaDescriptionCheck implements SeoCheck
{
    public function key(): string
    {
        return 'meta-description';
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
        $maxChars = (int) config('vitals.seo.thresholds.meta_description_max_chars', 160);

        $metaDesc = $context->crawler->filter('meta[name="description"]');

        if ($metaDesc->count() === 0) {
            return SeoCheckResult::fail(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.meta-description.title',
                weight: $this->weight(),
                actual: 'Missing',
                expected: "Present, ≤ {$maxChars} chars",
                hintKey: 'vitals::vitals.seo.checks.meta-description.hint',
                docUrl: 'https://developers.google.com/search/docs/appearance/snippet#meta-descriptions',
            );
        }

        $content = (string) ($metaDesc->first()->attr('content') ?? '');
        $length = mb_strlen(trim($content));

        if ($length === 0) {
            return SeoCheckResult::fail(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.meta-description.title',
                weight: $this->weight(),
                actual: 'Empty',
                expected: "≤ {$maxChars} chars",
                hintKey: 'vitals::vitals.seo.checks.meta-description.hint',
                docUrl: 'https://developers.google.com/search/docs/appearance/snippet#meta-descriptions',
            );
        }

        if ($length > $maxChars) {
            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.meta-description.title',
                weight: $this->weight(),
                actual: "{$length} chars",
                expected: "≤ {$maxChars} chars",
                hintKey: 'vitals::vitals.seo.checks.meta-description.hint_long',
                docUrl: 'https://developers.google.com/search/docs/appearance/snippet#meta-descriptions',
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.meta-description.title',
            weight: $this->weight(),
            actual: "{$length} chars",
        );
    }
}
