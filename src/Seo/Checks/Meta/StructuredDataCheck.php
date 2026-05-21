<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Meta;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class StructuredDataCheck implements SeoCheck
{
    public function key(): string
    {
        return 'structured-data';
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
        $scripts = $context->crawler->filter('script[type="application/ld+json"]');

        if ($scripts->count() === 0) {
            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.structured-data.title',
                weight: $this->weight(),
                actual: 'No JSON-LD found',
                expected: 'At least one valid JSON-LD block',
                hintKey: 'vitals::vitals.seo.checks.structured-data.hint',
                docUrl: 'https://developers.google.com/search/docs/appearance/structured-data/intro-structured-data',
            );
        }

        // Validate JSON is parseable
        $invalidCount = 0;
        $scripts->each(function ($node) use (&$invalidCount): void {
            $json = trim($node->text(''));
            if ($json === '') {
                $invalidCount++;
                return;
            }
            try {
                json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $invalidCount++;
            }
        });

        if ($invalidCount > 0) {
            return SeoCheckResult::fail(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.structured-data.title',
                weight: $this->weight(),
                actual: "{$invalidCount} invalid JSON-LD block(s)",
                expected: 'All JSON-LD blocks are valid JSON',
                hintKey: 'vitals::vitals.seo.checks.structured-data.hint_invalid',
                docUrl: 'https://developers.google.com/search/docs/appearance/structured-data/intro-structured-data',
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.structured-data.title',
            weight: $this->weight(),
            actual: $scripts->count() . ' JSON-LD block(s) found',
        );
    }
}
