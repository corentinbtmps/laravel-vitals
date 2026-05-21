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
 * Checks whether the configured primary keyword appears in the first paragraph.
 * Keyword is configured via config('vitals.seo.keywords.{url_label}').
 * Skipped if no keyword is configured for this URL.
 */
final class KeywordInFirstParagraphCheck implements SeoCheck
{
    public function key(): string
    {
        return 'keyword-in-first-paragraph';
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
        $keyword = $this->keywordFor($context);

        if ($keyword === null) {
            return SeoCheckResult::pass(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.keyword-in-first-paragraph.title',
                weight: $this->weight(),
                actual: 'No keyword configured for this URL',
            );
        }

        $paragraphs = $context->crawler->filter('p');

        if ($paragraphs->count() === 0) {
            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.keyword-in-first-paragraph.title',
                weight: $this->weight(),
                actual: 'No <p> elements found',
                expected: "First paragraph contains keyword \"{$keyword}\"",
                hintKey: 'vitals::vitals.seo.checks.keyword-in-first-paragraph.hint',
                docUrl: 'https://developers.google.com/search/docs/fundamentals/creating-helpful-content',
            );
        }

        $firstParagraph = strtolower($paragraphs->first()->text(''));

        if (! str_contains($firstParagraph, strtolower($keyword))) {
            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.keyword-in-first-paragraph.title',
                weight: $this->weight(),
                actual: "Keyword \"{$keyword}\" not in first paragraph",
                expected: "First paragraph mentions keyword \"{$keyword}\"",
                hintKey: 'vitals::vitals.seo.checks.keyword-in-first-paragraph.hint',
                docUrl: 'https://developers.google.com/search/docs/fundamentals/creating-helpful-content',
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.keyword-in-first-paragraph.title',
            weight: $this->weight(),
            actual: "Keyword \"{$keyword}\" found in first paragraph",
        );
    }

    private function keywordFor(SeoCheckContext $context): ?string
    {
        $keywords = config('vitals.seo.keywords', []);
        if (! is_array($keywords)) {
            return null;
        }

        $label = $context->url->label ?? '';
        $keyword = $keywords[$label] ?? null;

        return is_string($keyword) && trim($keyword) !== '' ? trim($keyword) : null;
    }
}
