<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Content;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class ImageAltCheck implements SeoCheck
{
    public function key(): string
    {
        return 'image-alt';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Content;
    }

    public function weight(): int
    {
        return 7;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        /** @var array<int, array<string, string>> $missing */
        $missing = [];

        $context->crawler->filter('img')->each(function ($node) use (&$missing): void {
            // Images without alt attribute or with empty alt that aren't decorative
            // (decorative images should have alt="" and role="presentation" or aria-hidden)
            $alt = $node->attr('alt');
            $ariaHidden = $node->attr('aria-hidden');
            $role = $node->attr('role');

            $isDecorative = ($ariaHidden === 'true') || ($role === 'presentation');

            if ($alt === null && ! $isDecorative) {
                $src = (string) ($node->attr('src') ?? '');
                if ($src !== '' && ! str_starts_with($src, 'data:')) {
                    $missing[] = ['url' => $src];
                }
            }
        });

        $count = count($missing);

        if ($count > 0) {
            return SeoCheckResult::fail(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.image-alt.title',
                weight: $this->weight(),
                actual: "{$count} image(s) missing alt attribute",
                expected: 'All meaningful images have alt text',
                hintKey: 'vitals::vitals.seo.checks.image-alt.hint',
                docUrl: 'https://developers.google.com/search/docs/appearance/google-images#use-descriptive-alt-text',
                detailItems: array_slice($missing, 0, 10),
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.image-alt.title',
            weight: $this->weight(),
        );
    }
}
