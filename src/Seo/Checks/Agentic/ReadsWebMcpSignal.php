<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Checks\Agentic;

use LaravelVitals\Seo\SeoCheckContext;

/**
 * Shared accessor for the WebMCP portion of the driver's agent-readiness probe.
 *
 * Returns null when WebMCP was not measured (any driver other than Playwright,
 * or a probe that produced no payload), letting each check pass without penalty
 * in that case.
 */
trait ReadsWebMcpSignal
{
    /**
     * @return array<string, mixed>|null
     */
    private function webMcpSignal(SeoCheckContext $context): ?array
    {
        $webmcp = $context->agentic['webmcp'] ?? null;

        return is_array($webmcp) ? $webmcp : null;
    }
}
