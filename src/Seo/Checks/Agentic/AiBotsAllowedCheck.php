<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Agentic;

use Illuminate\Support\Facades\Http;
use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

/**
 * Checks whether known AI-agent crawlers are blocked at the site root in robots.txt.
 *
 * Cloudflare's "agent ready" bot-access-control category rewards sites that let
 * AI agents in. Blocking may be intentional, so a blanket disallow is surfaced as
 * a warning (not a failure) naming the blocked agents.
 *
 * @see https://isitagentready.com/
 */
final class AiBotsAllowedCheck implements SeoCheck
{
    private const DOC_URL = 'https://isitagentready.com/';

    public function key(): string
    {
        return 'ai-bots-allowed';
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
        /** @var list<string> $bots */
        $bots    = (array) config('vitals.seo.agentic.ai_bots', ['GPTBot', 'ClaudeBot', 'PerplexityBot', 'Google-Extended']);
        $baseUrl = rtrim((string) config('app.url', ''), '/');

        try {
            $response = Http::timeout(10)->get($baseUrl . '/robots.txt');
        } catch (\Exception) {
            return $this->pass('robots.txt unreachable (agents assumed allowed)');
        }

        if (! $response->successful()) {
            return $this->pass('No robots.txt (agents allowed)');
        }

        $blocked = array_values(array_filter(
            $bots,
            fn (string $bot): bool => $this->isBlocked($response->body(), $bot),
        ));

        if ($blocked === []) {
            return $this->pass('No AI agents blocked in robots.txt');
        }

        return SeoCheckResult::warning(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.ai-bots-allowed.title',
            weight: $this->weight(),
            actual: 'robots.txt blocks: ' . implode(', ', $blocked),
            expected: 'AI agents allowed to crawl (unless intentionally blocked)',
            hintKey: 'vitals::vitals.seo.checks.ai-bots-allowed.hint',
            docUrl: self::DOC_URL,
        );
    }

    private function pass(string $actual): SeoCheckResult
    {
        return SeoCheckResult::pass(
            key: $this->key(),
            category: $this->category(),
            messageKey: 'vitals::vitals.seo.checks.ai-bots-allowed.title',
            weight: $this->weight(),
            actual: $actual,
        );
    }

    /**
     * True when the named user-agent group carries a root-level `Disallow: /`.
     */
    private function isBlocked(string $robots, string $bot): bool
    {
        $lines     = preg_split('/\r\n|\r|\n/', $robots) ?: [];
        $inGroup   = false;

        foreach ($lines as $line) {
            $line = trim($line);

            if (stripos($line, 'user-agent:') === 0) {
                $agent   = strtolower(trim(substr($line, 11)));
                $inGroup = $agent === strtolower($bot);
                continue;
            }

            if ($inGroup && stripos($line, 'disallow:') === 0) {
                $path = trim(substr($line, 9));
                if ($path === '/') {
                    return true;
                }
            }
        }

        return false;
    }
}
