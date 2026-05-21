<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Content;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

/**
 * Opinion check (opt-in only).
 *
 * Google has never specified a minimum content length as an official ranking signal.
 * This check is inspired by Yoast SEO and is gated behind enable_opinion_checks.
 * Default threshold: 300 chars (configurable via vitals.seo.thresholds.content_min_chars).
 */
final class ContentLengthCheck implements SeoCheck
{
    public function key(): string
    {
        return 'content-length';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Content;
    }

    public function weight(): int
    {
        return 4;
    }

    public function isOptional(): bool
    {
        return true;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        $minChars = (int) config('vitals.seo.thresholds.content_min_chars', 300);

        // Extract visible text from body, strip tags
        $body = $context->crawler->filter('body');
        if ($body->count() === 0) {
            return SeoCheckResult::pass(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.content-length.title',
                weight: $this->weight(),
            );
        }

        // Get text, strip scripts/styles first
        $bodyHtml = $body->html();
        $bodyHtml = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $bodyHtml) ?? $bodyHtml;
        $bodyHtml = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $bodyHtml) ?? $bodyHtml;
        $text = strip_tags($bodyHtml);
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;
        $length = mb_strlen(trim($text));

        if ($length < $minChars) {
            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.content-length.title',
                weight: $this->weight(),
                actual: "{$length} chars",
                expected: "≥ {$minChars} chars",
                hintKey: 'vitals::vitals.seo.checks.content-length.hint',
                docUrl: 'https://developers.google.com/search/docs/fundamentals/creating-helpful-content',
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.content-length.title',
            weight: $this->weight(),
            actual: "{$length} chars",
        );
    }
}
