<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;
use LaravelVitals\Seo\SeoAuditor;
use LaravelVitals\Seo\SeoCheckRegistry;
use LaravelVitals\Support\LighthouseReport;

function makeSeoReport(): LighthouseReport
{
    return new LighthouseReport(
        scores: ['performance' => 80, 'accessibility' => 90, 'best_practices' => 95, 'seo' => 90],
        metrics: ['ttfb_ms' => 200.0, 'lcp_ms' => 1500.0, 'cls' => 0.02, 'inp_ms' => 100.0, 'fcp_ms' => 800.0, 'si_ms' => 1200.0, 'tbt_ms' => 50.0],
        audits: [],
        rawJson: '{}',
    );
}

it('does nothing when seo is disabled in config', function (): void {
    config(['vitals.seo.enabled' => false]);

    $url = Url::create(['label' => 'seo-test', 'path' => '/']);
    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'stub',
        'device' => 'mobile',
        'status' => 'completed',
    ]);

    $auditor = app(SeoAuditor::class);
    $auditor->run($audit, makeSeoReport());

    expect(Recommendation::where('source', 'seo')->count())->toBe(0);
});

it('persists failing check recommendations with source=seo', function (): void {
    config(['vitals.seo.enabled' => true]);

    $url = Url::create(['label' => 'auditor-test', 'path' => '/test']);

    // Mock HTTP response with HTML missing meta description and canonical
    Http::fake([
        '*' => Http::response(
            '<html lang="en"><head><title>Short Title</title></head><body><h1>Hi</h1></body></html>',
            200,
            ['Content-Type' => 'text/html'],
        ),
    ]);

    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'stub',
        'device' => 'mobile',
        'status' => 'completed',
    ]);

    $auditor = app(SeoAuditor::class);
    $auditor->run($audit, makeSeoReport());

    $seoRecos = Recommendation::where('audit_id', $audit->id)->where('source', 'seo')->get();
    expect($seoRecos->count())->toBeGreaterThan(0);

    // All should have seo- prefix on audit_key
    foreach ($seoRecos as $reco) {
        expect($reco->audit_key)->toStartWith('seo-');
    }
});

it('skips checks when the page fetch fails', function (): void {
    config(['vitals.seo.enabled' => true]);

    $url = Url::create(['label' => 'fail-test', 'path' => '/fail']);

    Http::fake([
        '*' => function () { throw new \Illuminate\Http\Client\ConnectionException('Connection refused'); },
    ]);

    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'stub',
        'device' => 'mobile',
        'status' => 'completed',
    ]);

    $auditor = app(SeoAuditor::class);
    $auditor->run($audit, makeSeoReport()); // Should not throw

    expect(Recommendation::where('source', 'seo')->count())->toBe(0);
});

it('vitals_seo_score is computed correctly', function (): void {
    $url = Url::create(['label' => 'score-test', 'path' => '/']);
    $audit = Audit::create([
        'id'       => Str::uuid()->toString(),
        'url_id'   => $url->id,
        'driver'   => 'stub',
        'device'   => 'mobile',
        'status'   => 'completed',
        'score_seo' => 80,
    ]);

    // No SEO recommendations = full pass rate
    $score = $audit->vitals_seo_score;
    expect($score)->toBe((int) round(80 * 0.5 + 50)); // 90
});
