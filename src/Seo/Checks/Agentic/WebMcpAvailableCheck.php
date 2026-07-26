<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Agentic;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

/**
 * Reports whether the page exposes any WebMCP tools (declarative or imperative).
 *
 * WebMCP registration can only be observed in a live browser session, so this
 * check depends on the Playwright driver's agent-readiness probe. When the signal
 * is absent (any other driver, or the probe found nothing measurable) it passes
 * with a "not measured" note rather than penalising an unobservable feature.
 *
 * @see https://developer.chrome.com/docs/lighthouse/agentic-browsing/scoring
 */
final class WebMcpAvailableCheck implements SeoCheck
{
    use ReadsWebMcpSignal;

    private const DOC_URL = 'https://developer.chrome.com/docs/lighthouse/agentic-browsing/scoring';

    public function key(): string
    {
        return 'webmcp-available';
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
        $webmcp = $this->webMcpSignal($context);

        if ($webmcp === null) {
            return $this->notMeasured();
        }

        $tools = (int) ($webmcp['imperativeTools'] ?? 0) + (int) ($webmcp['declarativeTools'] ?? 0);

        if ($tools > 0) {
            return SeoCheckResult::pass(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.webmcp-available.title',
                weight: $this->weight(),
                actual: "{$tools} WebMCP tool(s) exposed",
            );
        }

        return SeoCheckResult::warning(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.webmcp-available.title',
            weight: $this->weight(),
            actual: 'No WebMCP tools exposed',
            expected: 'At least one declarative or imperative WebMCP tool',
            hintKey: 'vitals::vitals.seo.checks.webmcp-available.hint',
            docUrl: self::DOC_URL,
        );
    }

    private function notMeasured(): SeoCheckResult
    {
        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.webmcp-available.title',
            weight: $this->weight(),
            actual: 'Not measured (requires the Playwright driver)',
        );
    }
}
