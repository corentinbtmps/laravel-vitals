<?php

declare(strict_types=1);

namespace LaravelVitals\Drivers\Stubs;

use LaravelVitals\Contracts\LighthouseDriver;
use LaravelVitals\Models\Url;
use LaravelVitals\Support\AuditOptions;
use LaravelVitals\Support\LighthouseReport;

/**
 * Deterministic LighthouseDriver used in tests. Produces a high-quality
 * fictional report with all metrics filled and zero non-passed audits.
 *
 * Tests bind this in place of the real driver via:
 *
 *     $this->app->bind(LighthouseDriver::class, fn () => new StubLighthouseDriver());
 */
final readonly class StubLighthouseDriver implements LighthouseDriver
{
    public function __construct(
        private ?LighthouseReport $report = null,
    ) {
    }

    public function audit(Url $url, AuditOptions $options): LighthouseReport
    {
        return $this->report ?? new LighthouseReport(
            scores: [
                'performance'    => 95,
                'accessibility'  => 92,
                'best_practices' => 100,
                'seo'            => 100,
            ],
            metrics: [
                'lcp_ms'  => 1500.0,
                'cls'     => 0.02,
                'inp_ms'  => 100.0,
                'ttfb_ms' => 200.0,
                'fcp_ms'  => 800.0,
                'si_ms'   => 1200.0,
                'tbt_ms'  => 50.0,
            ],
            audits: [],
            rawJson: json_encode([
                'lighthouseVersion' => '12.0.0-stub',
                'requestedUrl'      => 'stub://' . $url->path,
                'fetchTime'         => '2026-01-01T00:00:00.000Z',
            ], JSON_THROW_ON_ERROR),
        );
    }

    public function isAvailable(): bool
    {
        return true;
    }
}
