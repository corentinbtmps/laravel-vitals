<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Performance;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class JavaScriptSizeCheck implements SeoCheck
{
    public function key(): string
    {
        return 'js-size';
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
        $maxBytes = (int) config('vitals.seo.thresholds.js_max_bytes', 1_000_000);

        // Use Lighthouse network-requests data to find JS file sizes
        $rawJson = $context->report->rawJson;

        /** @var array<int, array<string, string>> $oversized */
        $oversized = [];

        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($rawJson, true, 512, JSON_THROW_ON_ERROR);
            $items = $decoded['audits']['network-requests']['details']['items'] ?? [];

            if (is_array($items)) {
                foreach ($items as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    $type = (string) ($item['resourceType'] ?? '');
                    $url = (string) ($item['url'] ?? '');
                    $bytes = (int) ($item['transferSize'] ?? $item['resourceSize'] ?? 0);

                    if ($type === 'Script' && $bytes > $maxBytes) {
                        $mb = round($bytes / 1_048_576, 1);
                        $oversized[] = ['url' => $url, 'size' => "{$mb} MB"];
                    }
                }
            }
        } catch (\JsonException) {
            // No Lighthouse data available
        }

        if ($oversized !== []) {
            $maxMb = round($maxBytes / 1_048_576, 1);

            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.js-size.title',
                weight: $this->weight(),
                actual: count($oversized) . ' JS file(s) > ' . $maxMb . ' MB',
                expected: "All JS files ≤ {$maxMb} MB",
                hintKey: 'vitals::vitals.seo.checks.js-size.hint',
                docUrl: 'https://developers.google.com/search/docs/appearance/page-experience',
                detailItems: $oversized,
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.js-size.title',
            weight: $this->weight(),
        );
    }
}
