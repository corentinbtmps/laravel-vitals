<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Agentic;

use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Checks that interactive elements expose an accessible name.
 *
 * Agents drive a page through the accessibility tree, so every actionable element
 * needs a programmatic name (text, aria-label, aria-labelledby, title, or — for
 * form controls — an associated label / placeholder). Mirrors the "names and
 * labels" subset of Lighthouse's agent-centric accessibility audits.
 *
 * @see https://developer.chrome.com/docs/lighthouse/agentic-browsing/scoring
 */
final class AccessibleNamesCheck implements SeoCheck
{
    private const DOC_URL = 'https://developer.chrome.com/docs/lighthouse/agentic-browsing/scoring';

    public function key(): string
    {
        return 'accessible-names';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Agentic;
    }

    public function weight(): int
    {
        return 4;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        $selector = 'a[href], button, input:not([type="hidden"]), select, textarea, [role="button"], [role="link"]';

        $unnamed = 0;
        $total   = 0;

        $context->crawler->filter($selector)->each(function (Crawler $node) use (&$unnamed, &$total): void {
            $total++;
            if (! $this->hasAccessibleName($node)) {
                $unnamed++;
            }
        });

        if ($total === 0 || $unnamed === 0) {
            return SeoCheckResult::pass(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.accessible-names.title',
                weight: $this->weight(),
                actual: $total === 0 ? 'No interactive elements found' : "All {$total} interactive elements named",
            );
        }

        return SeoCheckResult::warning(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.accessible-names.title',
            weight: $this->weight(),
            actual: "{$unnamed} of {$total} interactive elements have no accessible name",
            expected: 'Every interactive element exposes a programmatic name',
            hintKey: 'vitals::vitals.seo.checks.accessible-names.hint',
            docUrl: self::DOC_URL,
        );
    }

    private function hasAccessibleName(Crawler $node): bool
    {
        if (trim($node->text('')) !== '') {
            return true;
        }

        foreach (['aria-label', 'aria-labelledby', 'title', 'alt'] as $attr) {
            if (trim((string) $node->attr($attr)) !== '') {
                return true;
            }
        }

        // Form controls also derive a name from a value or placeholder.
        foreach (['placeholder', 'value'] as $attr) {
            if (trim((string) $node->attr($attr)) !== '') {
                return true;
            }
        }
        // An <img alt> inside a link/button provides the name.
        return $node->filter('img[alt]:not([alt=""])')->count() > 0;
    }
}
