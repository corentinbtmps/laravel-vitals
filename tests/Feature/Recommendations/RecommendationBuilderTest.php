<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\BackendTelemetry;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;
use LaravelVitals\Recommendations\RecommendationBuilder;
use LaravelVitals\Support\LighthouseReport;

beforeEach(function (): void {
    $this->url = Url::create(['label' => 'home', 'path' => '/']);
    $this->audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $this->url->id,
        'driver' => 'stub',
        'device' => 'mobile',
        'status' => 'completed',
    ]);
});

function makeReport(): LighthouseReport
{
    return new LighthouseReport(
        scores:  ['performance' => 80, 'accessibility' => 90, 'best_practices' => 95, 'seo' => 100],
        metrics: ['lcp_ms' => 1500.0, 'cls' => 0.02, 'inp_ms' => 100.0, 'ttfb_ms' => 200.0, 'fcp_ms' => 800.0, 'si_ms' => 1200.0, 'tbt_ms' => 50.0],
        audits:  [
            [
                'id'    => 'unused-javascript',
                'score' => 0.4,
                'details' => ['items' => [['url' => 'https://example.test/build/assets/app-abc.js']]],
            ],
        ],
        rawJson: '{}',
    );
}

it('persists a Recommendation row for a Lighthouse non-passed audit', function (): void {
    $builder = app(RecommendationBuilder::class);

    $builder->buildFor($this->audit, makeReport(), null);

    expect(Recommendation::count())->toBeGreaterThanOrEqual(1);
    $reco = Recommendation::where('audit_key', 'unused-javascript')->first();

    expect($reco)->not->toBeNull()
        ->and($reco->category)->toBe('performance')
        ->and($reco->source)->toBe('lighthouse')
        ->and($reco->title_key)->toContain('unused-javascript')
        ->and($reco->code_references)->toBeArray();
});

it('emits an n-plus-one-detected recommendation when telemetry flags it', function (): void {
    $tel = BackendTelemetry::create([
        'audit_id'           => $this->audit->id,
        'http_status'        => 200,
        'duration_ms'        => 100,
        'memory_peak_kb'     => 1000,
        'queries_count'      => 87,
        'queries_time_ms'    => 142.3,
        'queries_unique'     => 4,
        'n_plus_one_suspect' => true,
        'views_rendered'     => 3,
        'views_time_ms'      => 18.4,
        'jobs_dispatched'    => 0,
        'events_fired'       => 12,
        'cache_hits'         => 24,
        'cache_misses'       => 2,
    ]);

    $builder = app(RecommendationBuilder::class);
    $builder->buildFor($this->audit, makeReport(), $tel);

    $reco = Recommendation::where('audit_key', 'n-plus-one-detected')->first();
    expect($reco)->not->toBeNull()
        ->and($reco->source)->toBe('backend');
});

it('skips audit keys with no descriptor in the registry', function (): void {
    $report = new LighthouseReport(
        scores: ['performance' => 80, 'accessibility' => 90, 'best_practices' => 95, 'seo' => 100],
        metrics: ['lcp_ms' => null, 'cls' => null, 'inp_ms' => null, 'ttfb_ms' => null, 'fcp_ms' => null, 'si_ms' => null, 'tbt_ms' => null],
        audits: [['id' => 'imaginary-audit', 'score' => 0.5]],
        rawJson: '{}',
    );

    app(RecommendationBuilder::class)->buildFor($this->audit, $report, null);

    expect(Recommendation::where('audit_key', 'imaginary-audit')->count())->toBe(0);
});

it('emits excessive-dom-size when dom_size > 1500', function (): void {
    $audit = \LaravelVitals\Models\Audit::create([
        'id' => \Illuminate\Support\Str::uuid()->toString(), 'url_id' => $this->url->id,
        'driver' => 'stub', 'device' => 'mobile', 'status' => 'completed',
        'details' => ['dom_size' => 2000],
    ]);

    $report = new \LaravelVitals\Support\LighthouseReport(
        scores: ['performance' => 95, 'accessibility' => 95, 'best_practices' => 95, 'seo' => 100],
        metrics: ['lcp_ms' => 1500.0, 'cls' => 0.02, 'inp_ms' => 100.0, 'ttfb_ms' => 200.0, 'fcp_ms' => 800.0, 'si_ms' => 1200.0, 'tbt_ms' => 50.0],
        audits: [],
        rawJson: '{}',
    );

    app(\LaravelVitals\Recommendations\RecommendationBuilder::class)->buildFor($audit, $report, null);

    expect(\LaravelVitals\Models\Recommendation::where('audit_key', 'excessive-dom-size')->count())->toBe(1);
});

it('emits large-payload when page_weight_bytes > 2MB', function (): void {
    $audit = \LaravelVitals\Models\Audit::create([
        'id' => \Illuminate\Support\Str::uuid()->toString(), 'url_id' => $this->url->id,
        'driver' => 'stub', 'device' => 'mobile', 'status' => 'completed',
        'details' => ['page_weight_bytes' => 3_000_000],
    ]);

    $report = new \LaravelVitals\Support\LighthouseReport(
        scores: ['performance' => 95, 'accessibility' => 95, 'best_practices' => 95, 'seo' => 100],
        metrics: ['lcp_ms' => null, 'cls' => null, 'inp_ms' => null, 'ttfb_ms' => null, 'fcp_ms' => null, 'si_ms' => null, 'tbt_ms' => null],
        audits: [],
        rawJson: '{}',
    );

    app(\LaravelVitals\Recommendations\RecommendationBuilder::class)->buildFor($audit, $report, null);

    expect(\LaravelVitals\Models\Recommendation::where('audit_key', 'large-payload')->count())->toBe(1);
});

it('emits octane-not-running when no Octane indicators exist', function (): void {
    putenv('OCTANE_SERVER=');
    unset($_ENV['OCTANE_SERVER']);

    $audit = \LaravelVitals\Models\Audit::create([
        'id' => \Illuminate\Support\Str::uuid()->toString(), 'url_id' => $this->url->id,
        'driver' => 'stub', 'device' => 'mobile', 'status' => 'completed',
    ]);

    $report = new \LaravelVitals\Support\LighthouseReport(
        scores: ['performance' => 95, 'accessibility' => 95, 'best_practices' => 95, 'seo' => 100],
        metrics: ['lcp_ms' => null, 'cls' => null, 'inp_ms' => null, 'ttfb_ms' => null, 'fcp_ms' => null, 'si_ms' => null, 'tbt_ms' => null],
        audits: [],
        rawJson: '{}',
    );

    app(\LaravelVitals\Recommendations\RecommendationBuilder::class)->buildFor($audit, $report, null);

    expect(\LaravelVitals\Models\Recommendation::where('audit_key', 'octane-not-running')->count())->toBe(1);
});

it('does not flag view-cache-disabled when compiled views exist', function (): void {
    $tmp = sys_get_temp_dir() . '/vitals-view-cache-' . uniqid();
    mkdir($tmp, 0755, true);
    file_put_contents($tmp . '/abc.php', '<?php // compiled');
    config()->set('view.compiled', $tmp);

    $newAudit = Audit::create([
        'id' => Str::uuid()->toString(), 'url_id' => $this->url->id, 'driver' => 'stub',
        'device' => 'mobile', 'status' => 'completed',
    ]);

    $report = new LighthouseReport(
        scores: ['performance' => 95, 'accessibility' => 95, 'best_practices' => 95, 'seo' => 100],
        metrics: ['lcp_ms' => 1500.0, 'cls' => 0.02, 'inp_ms' => 100.0, 'ttfb_ms' => 200.0, 'fcp_ms' => 800.0, 'si_ms' => 1200.0, 'tbt_ms' => 50.0],
        audits: [],
        rawJson: '{}',
    );

    app(RecommendationBuilder::class)->buildFor($newAudit, $report, null);

    expect(Recommendation::where('audit_key', 'view-cache-disabled')->count())->toBe(0);

    unlink($tmp . '/abc.php');
    rmdir($tmp);
});
