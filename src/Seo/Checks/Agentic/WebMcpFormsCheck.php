<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Agentic;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

/**
 * Flags forms that lack a declarative WebMCP annotation.
 *
 * Mirrors Lighthouse's "forms missing declarative WebMCP" audit: an annotated
 * form lets an agent submit it reliably. Depends on the Playwright driver's live
 * probe; passes with a "not measured" note on other drivers.
 *
 * @see https://developer.chrome.com/docs/lighthouse/agentic-browsing/scoring
 */
final class WebMcpFormsCheck implements SeoCheck
{
    use ReadsWebMcpSignal;

    private const DOC_URL = 'https://developer.chrome.com/docs/lighthouse/agentic-browsing/scoring';

    public function key(): string
    {
        return 'webmcp-forms';
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
            return $this->pass('Not measured (requires the Playwright driver)');
        }

        $total     = (int) ($webmcp['formsTotal'] ?? 0);
        $annotated = (int) ($webmcp['formsAnnotated'] ?? 0);

        if ($total === 0) {
            return $this->pass('No forms to annotate');
        }

        if ($annotated >= $total) {
            return $this->pass("All {$total} form(s) expose declarative WebMCP");
        }

        return SeoCheckResult::warning(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.webmcp-forms.title',
            weight: $this->weight(),
            actual: ($total - $annotated) . " of {$total} form(s) missing declarative WebMCP",
            expected: 'Every form carries a declarative WebMCP annotation',
            hintKey: 'vitals::vitals.seo.checks.webmcp-forms.hint',
            docUrl: self::DOC_URL,
        );
    }

    private function pass(string $actual): SeoCheckResult
    {
        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.webmcp-forms.title',
            weight: $this->weight(),
            actual: $actual,
        );
    }
}
