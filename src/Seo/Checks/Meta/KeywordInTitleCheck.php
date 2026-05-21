<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Meta;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

/**
 * Opinion check (opt-in only).
 *
 * Checks whether the configured primary keyword for the URL appears in the page title.
 * Keyword is configured via config('vitals.seo.keywords.{url_label}').
 * Skipped if no keyword is configured for this URL.
 */
final class KeywordInTitleCheck implements SeoCheck
{
    public function key(): string
    {
        return 'keyword-in-title';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Meta;
    }

    public function weight(): int
    {
        return 5;
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
                messageKey: 'vitals::vitals.seo.checks.keyword-in-title.title',
                weight: $this->weight(),
                actual: 'No keyword configured for this URL',
            );
        }

        $titleEl = $context->crawler->filter('title');
        if ($titleEl->count() === 0) {
            return SeoCheckResult::fail(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.keyword-in-title.title',
                weight: $this->weight(),
                actual: 'No title tag',
                expected: "Title contains keyword \"{$keyword}\"",
                hintKey: 'vitals::vitals.seo.checks.keyword-in-title.hint',
                docUrl: 'https://developers.google.com/search/docs/appearance/title-link',
            );
        }

        $title = strtolower($titleEl->first()->text(''));

        if (! str_contains($title, strtolower($keyword))) {
            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.keyword-in-title.title',
                weight: $this->weight(),
                actual: "Keyword \"{$keyword}\" not found in title",
                expected: "Title contains keyword \"{$keyword}\"",
                hintKey: 'vitals::vitals.seo.checks.keyword-in-title.hint',
                docUrl: 'https://developers.google.com/search/docs/appearance/title-link',
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.keyword-in-title.title',
            weight: $this->weight(),
            actual: "Keyword \"{$keyword}\" present in title",
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
