<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Agentic;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

/**
 * Flags layout instability (CLS) as an agent-readiness signal.
 *
 * Agents locate and click elements by position; content that shifts during load
 * makes their screenshots and coordinates unreliable. Reuses the CLS value already
 * captured in the Lighthouse report rather than re-measuring.
 *
 * @see https://developer.chrome.com/docs/lighthouse/agentic-browsing/scoring
 */
final class LayoutStabilityCheck implements SeoCheck
{
    private const DOC_URL = 'https://web.dev/articles/cls';

    public function key(): string
    {
        return 'layout-stability';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Agentic;
    }

    public function weight(): int
    {
        return 3;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        $cls = $context->report->metrics['cls'] ?? null;
        $max = (float) config('vitals.seo.agentic.cls_max', 0.1);

        if (! is_numeric($cls)) {
            // No CLS available (e.g. driver could not measure) — do not penalise.
            return SeoCheckResult::pass(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.layout-stability.title',
                weight: $this->weight(),
                actual: 'CLS not measured',
            );
        }

        $cls = (float) $cls;

        if ($cls <= $max) {
            return SeoCheckResult::pass(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.layout-stability.title',
                weight: $this->weight(),
                actual: 'CLS ' . number_format($cls, 3),
            );
        }

        return SeoCheckResult::warning(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.layout-stability.title',
            weight: $this->weight(),
            actual: 'CLS ' . number_format($cls, 3),
            expected: 'CLS at or below ' . number_format($max, 2),
            hintKey: 'vitals::vitals.seo.checks.layout-stability.hint',
            docUrl: self::DOC_URL,
        );
    }
}
