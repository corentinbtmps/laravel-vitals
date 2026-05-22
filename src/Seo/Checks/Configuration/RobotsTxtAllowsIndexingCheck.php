<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Configuration;

use Illuminate\Support\Facades\Http;
use LaravelVitals\Seo\Contracts\SeoCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

final class RobotsTxtAllowsIndexingCheck implements SeoCheck
{
    public function key(): string
    {
        return 'robots-txt-indexable';
    }

    public function category(): SeoCheckCategory
    {
        return SeoCheckCategory::Configuration;
    }

    public function weight(): int
    {
        return 8;
    }

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        $baseUrl = rtrim((string) config('app.url', ''), '/');
        $robotsUrl = $baseUrl . '/robots.txt';

        try {
            $response = Http::timeout(10)->get($robotsUrl);

            if (! $response->successful()) {
                // No robots.txt = indexing is implicitly allowed
                return SeoCheckResult::pass(
                    key: $this->key(),
                    category: $this->category(),
                    messageKey: 'vitals::vitals.seo.checks.robots-txt-indexable.title',
                    weight: $this->weight(),
                    actual: 'No robots.txt (allowed)',
                );
            }

            $content = $response->body();
            $path = $context->url->path ?? '/';

            // Check if Googlebot or * is disallowed from the path
            if ($this->isDisallowed($content, $path)) {
                return SeoCheckResult::fail(
                    key: $this->key(),
                    category: $this->category(),
                    messageKey: 'vitals::vitals.seo.checks.robots-txt-indexable.title',
                    weight: $this->weight(),
                    actual: "robots.txt disallows Googlebot on {$path}",
                    expected: 'Googlebot allowed on all public pages',
                    hintKey: 'vitals::vitals.seo.checks.robots-txt-indexable.hint',
                    docUrl: 'https://developers.google.com/search/docs/crawling-indexing/robots/robots_txt',
                );
            }

            return SeoCheckResult::pass(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.robots-txt-indexable.title',
                weight: $this->weight(),
            );
        } catch (\Exception) {
            return SeoCheckResult::pass(
                key: $this->key(),
                category: $this->category(),
                messageKey: 'vitals::vitals.seo.checks.robots-txt-indexable.title',
                weight: $this->weight(),
                actual: 'robots.txt unreachable (assumed allowed)',
            );
        }
    }

    private function isDisallowed(string $content, string $path): bool
    {
        $lines = explode("\n", $content);
        $currentAgent = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if (str_starts_with(strtolower($line), 'user-agent:')) {
                $currentAgent = strtolower(trim(substr($line, 11)));
                continue;
            }

            if ($currentAgent !== null && in_array($currentAgent, ['googlebot', '*'], true) && str_starts_with(strtolower($line), 'disallow:')) {
                $disallowed = trim(substr($line, 9));
                if ($disallowed !== '' && str_starts_with($path, $disallowed)) {
                    return true;
                }
            }
        }

        return false;
    }
}
