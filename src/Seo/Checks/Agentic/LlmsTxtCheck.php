<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Agentic;

use Illuminate\Support\Facades\Http;
use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

/**
 * Checks for a well-formed /llms.txt at the domain root.
 *
 * Mirrors Lighthouse's agentic-browsing llms.txt audit: the file should exist,
 * lead with an H1, carry enough content to be useful, and link to further pages.
 *
 * @see https://developer.chrome.com/docs/lighthouse/agentic-browsing/scoring
 */
final class LlmsTxtCheck implements SeoCheck
{
    private const DOC_URL = 'https://developer.chrome.com/docs/lighthouse/agentic-browsing/scoring';

    public function key(): string
    {
        return 'llms-txt';
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
        $minChars = (int) config('vitals.seo.agentic.llms_txt_min_chars', 200);
        $baseUrl  = rtrim((string) config('app.url', ''), '/');

        try {
            $response = Http::timeout(10)->get($baseUrl . '/llms.txt');
        } catch (\Exception) {
            return $this->missing('llms.txt unreachable');
        }

        if (! $response->successful()) {
            return $this->missing('No /llms.txt found');
        }

        $body       = trim($response->body());
        $hasH1      = (bool) preg_match('/^#\s+\S/m', $body);
        $hasLink    = str_contains($body, '](') || str_contains($body, 'http');
        $longEnough = mb_strlen($body) >= $minChars;

        if ($hasH1 && $hasLink && $longEnough) {
            return SeoCheckResult::pass(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.llms-txt.title',
                weight: $this->weight(),
                actual: 'Valid llms.txt (' . mb_strlen($body) . ' chars)',
            );
        }

        $problems = [];
        if (! $hasH1) {
            $problems[] = 'missing H1';
        }
        if (! $longEnough) {
            $problems[] = "under {$minChars} chars";
        }
        if (! $hasLink) {
            $problems[] = 'no links';
        }

        return SeoCheckResult::warning(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.llms-txt.title',
            weight: $this->weight(),
            actual: 'llms.txt present but weak: ' . implode(', ', $problems),
            expected: 'H1, at least one link, and ' . $minChars . '+ characters',
            hintKey: 'vitals::vitals.seo.checks.llms-txt.hint',
            docUrl: self::DOC_URL,
        );
    }

    private function missing(string $actual): SeoCheckResult
    {
        return SeoCheckResult::warning(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.llms-txt.title',
            weight: $this->weight(),
            actual: $actual,
            expected: 'A markdown /llms.txt summarising the site for AI agents',
            hintKey: 'vitals::vitals.seo.checks.llms-txt.hint',
            docUrl: self::DOC_URL,
        );
    }
}
