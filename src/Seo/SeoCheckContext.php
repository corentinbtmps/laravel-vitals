<?php

declare(strict_types=1);

namespace LaravelVitals\Seo;

use Illuminate\Http\Client\Response;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;
use LaravelVitals\Support\LighthouseReport;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Shared context passed to every SeoCheck::run() call.
 *
 * Holds the fetched HTML, the parsed DOM crawler, the raw HTTP response
 * (for headers), the LighthouseReport (for derived metrics), and the Url model.
 *
 * `agentic` carries agent-readiness signals captured by the driver in a live
 * browser session (e.g. WebMCP tool registration). It is empty for drivers that
 * cannot observe a live page — agentic checks degrade to a warning in that case.
 */
final readonly class SeoCheckContext
{
    /**
     * @param array<string, mixed> $agentic
     */
    public function __construct(
        public Audit $audit,
        public Url $url,
        public LighthouseReport $report,
        public Response $response,
        public string $html,
        public Crawler $crawler,
        public array $agentic = [],
    ) {}
}
