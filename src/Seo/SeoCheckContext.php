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
 */
final readonly class SeoCheckContext
{
    public function __construct(
        public Audit $audit,
        public Url $url,
        public LighthouseReport $report,
        public Response $response,
        public string $html,
        public Crawler $crawler,
    ) {}
}
