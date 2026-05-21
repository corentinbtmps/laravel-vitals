<?php

declare(strict_types=1);

namespace LaravelVitals\Tests\Unit\Seo;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Support\LighthouseReport;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Factory helpers for building SeoCheckContext in unit tests without HTTP.
 */
final class SeoTestHelper
{
    public static function makeContext(
        string $html = '<html lang="en"><head><title>Test</title></head><body><h1>Hello</h1></body></html>',
        int $statusCode = 200,
        array $headers = [],
        array $reportMetrics = [],
        string $rawJson = '{}',
    ): SeoCheckContext {
        $url = Url::create(['label' => 'test-' . Str::random(6), 'path' => '/']);
        $audit = Audit::create([
            'id'     => \Illuminate\Support\Str::uuid()->toString(),
            'url_id' => $url->id,
            'driver' => 'stub',
            'device' => 'mobile',
            'status' => 'completed',
        ]);

        $report = new LighthouseReport(
            scores: ['performance' => 80, 'accessibility' => 90, 'best_practices' => 95, 'seo' => 90],
            metrics: array_merge(['ttfb_ms' => 200.0, 'lcp_ms' => 1500.0, 'cls' => 0.02, 'inp_ms' => 100.0, 'fcp_ms' => 800.0, 'si_ms' => 1200.0, 'tbt_ms' => 50.0], $reportMetrics),
            audits: [],
            rawJson: $rawJson,
        );

        $mockResponse = new class($statusCode, $headers, $html) extends Response {
            public function __construct(
                private readonly int $statusCode,
                private readonly array $responseHeaders,
                private readonly string $bodyContent,
            ) {
                // Do not call parent constructor — we mock the behavior
            }

            public function status(): int { return $this->statusCode; }

            public function successful(): bool { return $this->statusCode >= 200 && $this->statusCode < 300; }

            public function body(): string { return $this->bodyContent; }

            public function header(string $header): string
            {
                return $this->responseHeaders[strtolower($header)] ?? $this->responseHeaders[$header] ?? '';
            }
        };

        return new SeoCheckContext(
            audit: $audit,
            url: $url,
            report: $report,
            response: $mockResponse,
            html: $html,
            crawler: new Crawler($html),
        );
    }
}
