<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;
use LaravelVitals\Contracts\LighthouseDriver;
use LaravelVitals\Drivers\Stubs\StubLighthouseDriver;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;
use LaravelVitals\Support\LighthouseReport;

it('produces enriched recommendations rows after a full audit run', function (): void {
    Storage::fake('vitals');

    $report = new LighthouseReport(
        scores: ['performance' => 80, 'accessibility' => 90, 'best_practices' => 95, 'seo' => 100],
        metrics: ['lcp_ms' => 1500.0, 'cls' => 0.02, 'inp_ms' => 100.0, 'ttfb_ms' => 200.0, 'fcp_ms' => 800.0, 'si_ms' => 1200.0, 'tbt_ms' => 50.0],
        audits: [
            ['id' => 'unused-javascript', 'score' => 0.4, 'details' => ['items' => [['url' => 'https://example.test/build/assets/app.js']]]],
        ],
        rawJson: '{"lighthouseVersion":"12.0.0"}',
    );

    $this->app->bind(LighthouseDriver::class, fn () => new StubLighthouseDriver($report));

    Url::create(['label' => 'home', 'path' => '/']);

    $this->artisan('vitals:audit', ['label' => 'home', '--sync' => true])->assertSuccessful();

    $audit = Audit::first();
    expect($audit->status)->toBe('completed');

    $recos = Recommendation::where('audit_id', $audit->id)->get();
    expect($recos->count())->toBeGreaterThanOrEqual(1);

    $unused = $recos->firstWhere('audit_key', 'unused-javascript');
    expect($unused)->not->toBeNull()
        ->and($unused->category)->toBe('performance');
});
