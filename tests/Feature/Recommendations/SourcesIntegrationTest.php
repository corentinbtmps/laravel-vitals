<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Contracts\TelemetrySource;
use LaravelVitals\Enums\AuditStatus;
use LaravelVitals\Enums\Device;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;
use LaravelVitals\Recommendations\RecommendationBuilder;
use LaravelVitals\Support\LighthouseReport;
use LaravelVitals\Telemetry\TrendStats;

beforeEach(function (): void {
    $this->url = Url::create(['label' => 'home', 'path' => '/']);
    $this->audit = Audit::create([
        'id'                => Str::uuid()->toString(),
        'url_id'            => $this->url->id,
        'driver'            => 'stub',
        'device'            => Device::Mobile,
        'status'            => AuditStatus::Completed,
        'score_performance' => 95,
        'lcp_ms'            => 1500.0,
        'ttfb_ms'           => 200.0,
    ]);
});

function makeReportFresh(): LighthouseReport
{
    return new LighthouseReport(
        scores:  ['performance' => 95, 'accessibility' => 95, 'best_practices' => 95, 'seo' => 100],
        metrics: ['lcp_ms' => 1500.0, 'cls' => 0.02, 'inp_ms' => 100.0, 'ttfb_ms' => 200.0, 'fcp_ms' => 800.0, 'si_ms' => 1200.0, 'tbt_ms' => 50.0],
        audits:  [],
        rawJson: '{}',
    );
}

it('adds real-world-perf-degraded when source reports significantly higher P95 TTFB', function (): void {
    $highP95Source = new class implements TelemetrySource {
        public function isAvailable(): bool { return true; }
        public function getTrendsFor(string $routeName): TrendStats {
            return new TrendStats(p50Ttfb: 500.0, p95Ttfb: 2500.0, p50Lcp: null, p95Lcp: null, sampleCount: 100);
        }
    };

    app()->instance('vitals.telemetry-sources', [$highP95Source]);

    app(RecommendationBuilder::class)->buildFor($this->audit, makeReportFresh(), null);

    $reco = Recommendation::where('audit_key', 'real-world-perf-degraded')->first();
    expect($reco)->not->toBeNull();
});

it('does not add real-world-perf-degraded when sources report similar values', function (): void {
    $similarSource = new class implements TelemetrySource {
        public function isAvailable(): bool { return true; }
        public function getTrendsFor(string $routeName): TrendStats {
            return new TrendStats(p50Ttfb: 200.0, p95Ttfb: 250.0, p50Lcp: null, p95Lcp: null, sampleCount: 100);
        }
    };

    app()->instance('vitals.telemetry-sources', [$similarSource]);

    app(RecommendationBuilder::class)->buildFor($this->audit, makeReportFresh(), null);

    expect(Recommendation::where('audit_key', 'real-world-perf-degraded')->count())->toBe(0);
});

it('does nothing when no sources are available', function (): void {
    app()->instance('vitals.telemetry-sources', []);

    app(RecommendationBuilder::class)->buildFor($this->audit, makeReportFresh(), null);

    expect(Recommendation::where('audit_key', 'real-world-perf-degraded')->count())->toBe(0);
});
