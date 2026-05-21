<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Performance;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

/**
 * Checks that no single CSS file exceeds the configured threshold.
 *
 * Note: the default 15 KB threshold is opinionated — it reflects good practice
 * for critical CSS budgets (inlined above-the-fold CSS) but a full stylesheet
 * serving a large app will naturally exceed this. Raise the threshold in
 * config('vitals.seo.thresholds.css_max_bytes') as appropriate for your project.
 */
final class CssSizeCheck implements SeoCheck
{
    public function key(): string
    {
        return 'css-size';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Performance;
    }

    public function weight(): int
    {
        return 5;
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        $maxBytes = (int) config('vitals.seo.thresholds.css_max_bytes', 15_000);

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

                    if ($type === 'Stylesheet' && $bytes > $maxBytes) {
                        $kb = round($bytes / 1024, 1);
                        $oversized[] = ['url' => $url, 'size' => "{$kb} KB"];
                    }
                }
            }
        } catch (\JsonException) {
            // No Lighthouse data
        }

        if ($oversized !== []) {
            $maxKb = round($maxBytes / 1024, 1);

            return SeoCheckResult::warning(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.css-size.title',
                weight: $this->weight(),
                actual: count($oversized) . " CSS file(s) > {$maxKb} KB",
                expected: "All CSS files ≤ {$maxKb} KB (opinionated — raise threshold if needed)",
                hintKey: 'vitals::vitals.seo.checks.css-size.hint',
                docUrl: 'https://developers.google.com/search/docs/appearance/page-experience',
                detailItems: $oversized,
            );
        }

        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.css-size.title',
            weight: $this->weight(),
        );
    }
}
