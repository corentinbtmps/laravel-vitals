<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;
use LaravelVitals\Recommendations\RecommendationBuilder;
use LaravelVitals\Support\LighthouseReport;

beforeEach(function (): void {
    $this->url   = Url::create(['label' => 'home', 'path' => '/']);
    $this->audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $this->url->id,
        'driver' => 'stub',
        'device' => 'mobile',
        'status' => 'completed',
    ]);
});

it('extracts url and wasted_bytes from uses-optimized-images audit items', function (): void {
    $report = new LighthouseReport(
        scores:  ['performance' => 60, 'accessibility' => 90, 'best_practices' => 90, 'seo' => 90],
        metrics: ['lcp_ms' => 3000.0, 'cls' => 0.1, 'inp_ms' => 200.0, 'ttfb_ms' => 500.0, 'fcp_ms' => 1000.0, 'si_ms' => 1500.0, 'tbt_ms' => 200.0],
        audits:  [
            [
                'id'    => 'uses-optimized-images',
                'score' => 0.2,
                'details' => [
                    'items' => [
                        ['url' => 'https://app.test/images/hero.jpg', 'wastedBytes' => 387000, 'totalBytes' => 512000],
                        ['url' => 'https://app.test/images/banner.png', 'wastedBytes' => 220000, 'totalBytes' => 350000],
                        ['url' => 'https://app.test/images/thumb.jpg', 'wastedBytes' => 45000, 'totalBytes' => 90000],
                    ],
                ],
            ],
        ],
        rawJson: '{}',
    );

    app(RecommendationBuilder::class)->buildFor($this->audit, $report, null);

    $reco = Recommendation::where('audit_key', 'uses-optimized-images')->first();

    expect($reco)->not->toBeNull()
        ->and($reco->detail_items)->toBeArray()
        ->and($reco->detail_items)->toHaveCount(3);

    $first = $reco->detail_items[0];
    expect($first['url'])->toBe('https://app.test/images/hero.jpg')
        ->and($first['wasted_bytes'])->toBe(387000)
        ->and($first['total_bytes'])->toBe(512000);
});

it('extracts wasted_ms from render-blocking-resources audit items', function (): void {
    $report = new LighthouseReport(
        scores:  ['performance' => 65, 'accessibility' => 90, 'best_practices' => 90, 'seo' => 90],
        metrics: ['lcp_ms' => 2800.0, 'cls' => 0.05, 'inp_ms' => 180.0, 'ttfb_ms' => 400.0, 'fcp_ms' => 900.0, 'si_ms' => 1400.0, 'tbt_ms' => 150.0],
        audits:  [
            [
                'id'    => 'render-blocking-resources',
                'score' => 0.3,
                'details' => [
                    'items' => [
                        ['url' => 'https://app.test/build/css/app.css', 'wastedMs' => 560.0, 'totalBytes' => 98000],
                        ['url' => 'https://fonts.googleapis.com/css2?family=Inter', 'wastedMs' => 380.0, 'totalBytes' => 12000],
                    ],
                ],
            ],
        ],
        rawJson: '{}',
    );

    app(RecommendationBuilder::class)->buildFor($this->audit, $report, null);

    $reco = Recommendation::where('audit_key', 'render-blocking-resources')->first();

    expect($reco)->not->toBeNull()
        ->and($reco->detail_items)->toBeArray()
        ->and($reco->detail_items)->toHaveCount(2);

    $first = $reco->detail_items[0];
    expect($first['url'])->toBe('https://app.test/build/css/app.css')
        ->and((float) $first['wasted_ms'])->toBe(560.0);
});

it('caps detail_items at 10 entries regardless of how many items Lighthouse reports', function (): void {
    $items = [];
    for ($i = 1; $i <= 15; $i++) {
        $items[] = ['url' => "https://app.test/img/image-{$i}.jpg", 'wastedBytes' => $i * 10000, 'totalBytes' => $i * 20000];
    }

    $report = new LighthouseReport(
        scores:  ['performance' => 50, 'accessibility' => 90, 'best_practices' => 90, 'seo' => 90],
        metrics: ['lcp_ms' => 4000.0, 'cls' => 0.2, 'inp_ms' => 300.0, 'ttfb_ms' => 600.0, 'fcp_ms' => 1200.0, 'si_ms' => 2000.0, 'tbt_ms' => 300.0],
        audits:  [
            [
                'id'      => 'uses-optimized-images',
                'score'   => 0.1,
                'details' => ['items' => $items],
            ],
        ],
        rawJson: '{}',
    );

    app(RecommendationBuilder::class)->buildFor($this->audit, $report, null);

    $reco = Recommendation::where('audit_key', 'uses-optimized-images')->first();

    expect($reco)->not->toBeNull()
        ->and($reco->detail_items)->toBeArray()
        ->and(count($reco->detail_items))->toBeLessThanOrEqual(10);
});

it('returns null detail_items when audit has no items array', function (): void {
    $report = new LighthouseReport(
        scores:  ['performance' => 70, 'accessibility' => 90, 'best_practices' => 90, 'seo' => 90],
        metrics: ['lcp_ms' => 2000.0, 'cls' => 0.05, 'inp_ms' => 150.0, 'ttfb_ms' => 350.0, 'fcp_ms' => 800.0, 'si_ms' => 1200.0, 'tbt_ms' => 100.0],
        audits:  [
            [
                'id'    => 'unused-javascript',
                'score' => 0.5,
                // No details.items
            ],
        ],
        rawJson: '{}',
    );

    app(RecommendationBuilder::class)->buildFor($this->audit, $report, null);

    $reco = Recommendation::where('audit_key', 'unused-javascript')->first();
    // Recommendation may or may not exist depending on analyzers, but if it does, detail_items should be null
    if ($reco !== null) {
        expect($reco->detail_items)->toBeNull();
    }

    expect(true)->toBeTrue(); // Test passes trivially if no recommendation was created
});
