<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Content;

use Illuminate\Support\Facades\Http;
use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class BrokenLinksCheck implements SeoCheck
{
    private const MAX_LINKS = 30;

    public function key(): string
    {
        return 'broken-links';
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
        $links = [];

        $context->crawler->filter('a[href]')->each(function ($node) use (&$links): void {
            $href = (string) ($node->attr('href') ?? '');
            if (
                $href === ''
                || str_starts_with($href, '#')
                || str_starts_with($href, 'mailto:')
                || str_starts_with($href, 'tel:')
                || str_starts_with($href, 'javascript:')
            ) {
                return;
            }
            // Normalise relative URLs
            if (str_starts_with($href, '/')) {
                $href = rtrim((string) config('app.url', ''), '/') . $href;
            }
            if (str_starts_with($href, 'http')) {
                $links[] = $href;
            }
        });

        $links = array_unique($links);
        $links = array_slice($links, 0, self::MAX_LINKS);

        /** @var array<int, array<string, string>> $broken */
        $broken = [];

        foreach ($links as $link) {
            try {
                $res = Http::timeout(8)->head($link);
                $statusCode = $res->status();
                if ($statusCode >= 400) {
                    $broken[] = ['url' => $link, 'status' => (string) $statusCode];
                }
            } catch (\Exception) {
                $broken[] = ['url' => $link, 'status' => 'unreachable'];
            }
        }

        $count = count($broken);

        if ($count > 0) {
            return SeoCheckResult::fail(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.broken-links.title',
                weight: $this->weight(),
                actual: "{$count} broken link(s) found",
                expected: 'All links return 2xx or 3xx',
                hintKey: 'vitals::vitals.seo.checks.broken-links.hint',
                docUrl: 'https://developers.google.com/search/docs/crawling-indexing/fix-search-crawling-issues',
                detailItems: $broken,
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.broken-links.title',
            weight: $this->weight(),
            actual: count($links) . ' link(s) checked, none broken',
        );
    }
}
