<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Content;

use Illuminate\Support\Facades\Http;
use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class BrokenImagesCheck implements SeoCheck
{
    private const MAX_IMAGES = 20;

    public function key(): string
    {
        return 'broken-images';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Content;
    }

    public function weight(): int
    {
        return 7;
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        $images = [];

        $context->crawler->filter('img[src]')->each(function ($node) use (&$images): void {
            $src = (string) ($node->attr('src') ?? '');
            if ($src === '' || str_starts_with($src, 'data:')) {
                return;
            }
            if (str_starts_with($src, '/')) {
                $src = rtrim((string) config('app.url', ''), '/') . $src;
            }
            if (str_starts_with($src, 'http')) {
                $images[] = $src;
            }
        });

        $images = array_unique($images);
        $images = array_slice($images, 0, self::MAX_IMAGES);

        /** @var array<int, array<string, string>> $broken */
        $broken = [];

        foreach ($images as $src) {
            try {
                $res = Http::timeout(8)->head($src);
                if ($res->status() >= 400) {
                    $broken[] = ['url' => $src, 'status' => (string) $res->status()];
                }
            } catch (\Exception) {
                $broken[] = ['url' => $src, 'status' => 'unreachable'];
            }
        }

        $count = count($broken);

        if ($count > 0) {
            return SeoCheckResult::fail(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.broken-images.title',
                weight: $this->weight(),
                actual: "{$count} broken image(s) found",
                expected: 'All images load successfully',
                hintKey: 'vitals::vitals.seo.checks.broken-images.hint',
                docUrl: 'https://developers.google.com/search/docs/appearance/google-images',
                detailItems: $broken,
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.broken-images.title',
            weight: $this->weight(),
            actual: count($images) . ' image(s) checked, none broken',
        );
    }
}
