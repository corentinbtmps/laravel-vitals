<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Enums;

/**
 * Category grouping for SEO checks — maps to the display sections on /vitals/audits/{id}/seo.
 *
 * "Agentic" groups agent-readiness signals (llms.txt, AI-bot rules, WebMCP, etc.)
 * aligned with Lighthouse's agentic-browsing audits and Cloudflare's agent-ready checks.
 */
enum SeoCheckCategory: string
{
    case Configuration = 'configuration';
    case Content       = 'content';
    case Meta          = 'meta';
    case Performance   = 'performance';
    case Agentic       = 'agentic';

    public function label(): string
    {
        return __('vitals::vitals.seo.categories.' . $this->value);
    }
}
