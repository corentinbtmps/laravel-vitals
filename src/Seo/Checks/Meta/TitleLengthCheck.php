<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Meta;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class TitleLengthCheck implements SeoCheck
{
    public function key(): string
    {
        return 'title-length';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Meta;
    }

    public function weight(): int
    {
        return 9;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        $maxChars = (int) config('vitals.seo.thresholds.title_max_chars', 60);

        $titleEl = $context->crawler->filter('title');

        if ($titleEl->count() === 0) {
            return SeoCheckResult::fail(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.title-length.title',
                weight: $this->weight(),
                actual: 'Missing',
                expected: "1–{$maxChars} chars",
                hintKey: 'vitals::vitals.seo.checks.title-length.hint',
                docUrl: 'https://developers.google.com/search/docs/appearance/title-link',
            );
        }

        $titleText = trim($titleEl->first()->text(''));
        $length = mb_strlen($titleText);

        if ($length === 0) {
            return SeoCheckResult::fail(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.title-length.title',
                weight: $this->weight(),
                actual: 'Empty',
                expected: "1–{$maxChars} chars",
                hintKey: 'vitals::vitals.seo.checks.title-length.hint',
                docUrl: 'https://developers.google.com/search/docs/appearance/title-link',
            );
        }

        if ($length > $maxChars) {
            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.title-length.title',
                weight: $this->weight(),
                actual: "{$length} chars",
                expected: "≤ {$maxChars} chars",
                hintKey: 'vitals::vitals.seo.checks.title-length.hint_long',
                docUrl: 'https://developers.google.com/search/docs/appearance/title-link',
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.title-length.title',
            weight: $this->weight(),
            actual: "{$length} chars",
        );
    }
}
