<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Meta;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class HtmlLangCheck implements SeoCheck
{
    public function key(): string
    {
        return 'html-lang';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Meta;
    }

    public function weight(): int
    {
        return 8;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        $html = $context->crawler->filter('html');

        if ($html->count() === 0) {
            return SeoCheckResult::fail(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.html-lang.title',
                weight: $this->weight(),
                actual: 'No <html> element found',
                expected: '<html lang="en"> (valid BCP47)',
                hintKey: 'vitals::vitals.seo.checks.html-lang.hint',
                docUrl: 'https://developers.google.com/search/docs/specialty/international/localized-versions#html',
            );
        }

        $lang = $html->first()->attr('lang') ?? '';

        if (trim($lang) === '') {
            return SeoCheckResult::fail(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.html-lang.title',
                weight: $this->weight(),
                actual: 'Missing lang attribute',
                expected: 'Valid BCP47 code (e.g. "en", "fr", "pt-BR")',
                hintKey: 'vitals::vitals.seo.checks.html-lang.hint',
                docUrl: 'https://developers.google.com/search/docs/specialty/international/localized-versions#html',
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.html-lang.title',
            weight: $this->weight(),
            actual: "lang=\"{$lang}\"",
        );
    }
}
