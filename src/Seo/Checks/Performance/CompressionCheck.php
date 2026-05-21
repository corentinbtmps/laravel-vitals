<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Performance;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class CompressionCheck implements SeoCheck
{
    public function key(): string
    {
        return 'compression';
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
        $encoding = strtolower((string) ($context->response->header('Content-Encoding') ?? ''));

        $compressed = str_contains($encoding, 'gzip')
            || str_contains($encoding, 'br')
            || str_contains($encoding, 'zstd')
            || str_contains($encoding, 'deflate');

        if (! $compressed) {
            $actual = $encoding !== '' ? "Content-Encoding: {$encoding}" : 'No Content-Encoding header';

            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.compression.title',
                weight: $this->weight(),
                actual: $actual,
                expected: 'gzip or br',
                hintKey: 'vitals::vitals.seo.checks.compression.hint',
                docUrl: 'https://developers.google.com/search/docs/appearance/page-experience',
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.compression.title',
            weight: $this->weight(),
            actual: "Content-Encoding: {$encoding}",
        );
    }
}
