<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Performance;

use Illuminate\Support\Facades\Http;
use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class ImageSizeCheck implements SeoCheck
{
    private const MAX_IMAGES_TO_CHECK = 15;

    public function key(): string
    {
        return 'image-size';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Performance;
    }

    public function weight(): int
    {
        return 7;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        $maxBytes = (int) config('vitals.seo.thresholds.image_max_bytes', 1_000_000);

        $images = [];
        $context->crawler->filter('img[src]')->each(function ($node) use (&$images): void {
            $src = $node->attr('src') ?? '';
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
        $images = array_slice($images, 0, self::MAX_IMAGES_TO_CHECK);

        /** @var array<int, array<string, string>> $oversized */
        $oversized = [];

        foreach ($images as $src) {
            try {
                $res = Http::timeout(8)->head($src);
                $contentLength = (int) ($res->header('Content-Length') ?? 0);

                if ($contentLength > $maxBytes) {
                    $mb = round($contentLength / 1_048_576, 1);
                    $oversized[] = ['url' => $src, 'size' => "{$mb} MB"];
                }
            } catch (\Exception) {
                // Skip unreachable images — handled by BrokenImagesCheck
            }
        }

        if ($oversized !== []) {
            $maxMb = round($maxBytes / 1_048_576, 1);

            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.image-size.title',
                weight: $this->weight(),
                actual: count($oversized) . ' image(s) > ' . $maxMb . ' MB',
                expected: "All images ≤ {$maxMb} MB",
                hintKey: 'vitals::vitals.seo.checks.image-size.hint',
                docUrl: 'https://developers.google.com/search/docs/appearance/google-images#provide-good-context',
                detailItems: $oversized,
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.image-size.title',
            weight: $this->weight(),
        );
    }
}
