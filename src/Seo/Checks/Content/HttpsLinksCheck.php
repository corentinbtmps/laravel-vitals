<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Content;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class HttpsLinksCheck implements SeoCheck
{
    public function key(): string
    {
        return 'https-links';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Content;
    }

    public function weight(): int
    {
        return 6;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        /** @var array<int, array<string, string>> $httpLinks */
        $httpLinks = [];

        $context->crawler->filter('a[href]')->each(function ($node) use (&$httpLinks): void {
            $href = $node->attr('href') ?? '';
            if (str_starts_with($href, 'http://')) {
                $httpLinks[] = ['url' => $href];
            }
        });

        $context->crawler->filter('img[src]')->each(function ($node) use (&$httpLinks): void {
            $src = $node->attr('src') ?? '';
            if (str_starts_with($src, 'http://')) {
                $httpLinks[] = ['url' => $src];
            }
        });

        $count = count($httpLinks);

        if ($count > 0) {
            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.https-links.title',
                weight: $this->weight(),
                actual: "{$count} HTTP resource(s) found",
                expected: 'All resources served over HTTPS',
                hintKey: 'vitals::vitals.seo.checks.https-links.hint',
                docUrl: 'https://developers.google.com/search/docs/crawling-indexing/security/https',
                detailItems: array_slice($httpLinks, 0, 10),
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.https-links.title',
            weight: $this->weight(),
        );
    }
}
