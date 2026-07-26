<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Agentic;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

/**
 * Checks that registered imperative WebMCP tools declare an input schema.
 *
 * A tool without a schema is hard for an agent to call correctly. Depends on the
 * Playwright driver's live probe; passes with a "not measured" note otherwise, and
 * when there are no imperative tools to validate.
 *
 * @see https://developer.chrome.com/docs/lighthouse/agentic-browsing/scoring
 */
final class WebMcpSchemaValidCheck implements SeoCheck
{
    use ReadsWebMcpSignal;

    private const DOC_URL = 'https://developer.chrome.com/docs/lighthouse/agentic-browsing/scoring';

    public function key(): string
    {
        return 'webmcp-schema';
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

        if ((int) ($webmcp['imperativeTools'] ?? 0) === 0) {
            return $this->pass('No imperative WebMCP tools to validate');
        }

        if (($webmcp['schemaValid'] ?? false) === true) {
            return $this->pass('All WebMCP tools declare an input schema');
        }

        return SeoCheckResult::warning(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.webmcp-schema.title',
            weight: $this->weight(),
            actual: 'One or more WebMCP tools have no input schema',
            expected: 'Every registered WebMCP tool declares an input schema',
            hintKey: 'vitals::vitals.seo.checks.webmcp-schema.hint',
            docUrl: self::DOC_URL,
        );
    }

    private function pass(string $actual): SeoCheckResult
    {
        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.webmcp-schema.title',
            weight: $this->weight(),
            actual: $actual,
        );
    }
}
