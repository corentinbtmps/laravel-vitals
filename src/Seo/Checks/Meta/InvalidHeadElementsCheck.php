<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Meta;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

/**
 * Checks for elements that should not appear inside <head> per the HTML spec.
 *
 * Common culprits: <div>, <p>, <img>, <a> inside <head> — these cause the browser
 * to implicitly close the head element early, which can break meta tag parsing.
 */
final class InvalidHeadElementsCheck implements SeoCheck
{
    /** @var list<string> */
    private const INVALID_HEAD_ELEMENTS = ['div', 'p', 'span', 'img', 'a', 'ul', 'ol', 'table', 'form', 'section', 'article'];

    public function key(): string
    {
        return 'invalid-head-elements';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Meta;
    }

    public function weight(): int
    {
        return 5;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        /** @var array<int, array<string, string>> $found */
        $found = [];

        foreach (self::INVALID_HEAD_ELEMENTS as $tag) {
            $elements = $context->crawler->filter("head {$tag}");
            if ($elements->count() > 0) {
                $found[] = ['url' => "<{$tag}> ({$elements->count()} found in <head>)"];
            }
        }

        if ($found !== []) {
            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.invalid-head-elements.title',
                weight: $this->weight(),
                actual: count($found) . ' invalid element type(s) in <head>',
                expected: 'Only valid head elements (meta, link, script, style, title)',
                hintKey: 'vitals::vitals.seo.checks.invalid-head-elements.hint',
                docUrl: 'https://developers.google.com/search/docs/crawling-indexing/special-tags',
                detailItems: $found,
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.invalid-head-elements.title',
            weight: $this->weight(),
        );
    }
}
