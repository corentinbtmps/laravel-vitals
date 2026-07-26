<?php

declare(strict_types=1);

namespace LaravelVitals\Mcp;

use Laravel\Mcp\Server;
use LaravelVitals\Mcp\Tools\LatestAuditTool;
use LaravelVitals\Mcp\Tools\ListRecommendationsTool;
use LaravelVitals\Mcp\Tools\RunAuditTool;

/**
 * MCP server exposing Laravel Vitals to AI agents (Claude Code, etc.).
 *
 * Agents can read the latest audit for any monitored URL, list its prioritised
 * recommendations (each with the exact file:line references the dashboard uses),
 * and trigger a fresh audit. Registered from VitalsServiceProvider as a local
 * (stdio) server and, optionally, a web server.
 */
final class VitalsServer extends Server
{
    protected string $name = 'Laravel Vitals';

    protected string $version = '1.0.0';

    protected string $instructions = <<<'MARKDOWN'
        Access Laravel Vitals performance data for this application.

        Use `latest_audit` to read the most recent Lighthouse scores, Core Web
        Vitals, and backend telemetry for a monitored URL (referenced by its
        label). Use `list_recommendations` to get the prioritised, actionable
        findings for that URL — each carries file:line references you can act on.
        Use `run_audit` to trigger a fresh audit when you need current data.

        URL labels are defined in the host app's config/vitals.php.
        MARKDOWN;

    /** @var array<int, class-string<\Laravel\Mcp\Server\Tool>> */
    protected array $tools = [
        LatestAuditTool::class,
        ListRecommendationsTool::class,
        RunAuditTool::class,
    ];
}
