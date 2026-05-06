<?php

declare(strict_types=1);

use LaravelVitals\Support\LighthouseReport;

it('parses a Lighthouse JSON report into a normalised value object', function (): void {
    $json = file_get_contents(__DIR__ . '/../../Fixtures/lighthouse-report.json');
    expect($json)->not->toBeFalse();

    $report = LighthouseReport::fromLighthouseJson($json);

    expect($report->scores)->toMatchArray([
        'performance'     => 92,
        'accessibility'   => 88,
        'best_practices'  => 96,
        'seo'             => 100,
    ]);

    expect($report->metrics)->toMatchArray([
        'lcp_ms'   => 1850.4,
        'cls'      => 0.03,
        'inp_ms'   => 120.0,
        'ttfb_ms'  => 220.0,
        'fcp_ms'   => 980.0,
        'si_ms'    => 1450.0,
        'tbt_ms'   => 80.0,
    ]);

    // The non-passed Lighthouse audits should be exposed for the recommendation builder (Plan 4).
    expect($report->audits)->toBeArray();
    $ids = array_column($report->audits, 'id');
    expect($ids)->toContain('unused-javascript');

    expect($report->rawJson)->toBe($json);
});

it('survives missing categories or metrics gracefully', function (): void {
    $report = LighthouseReport::fromLighthouseJson(json_encode([
        'categories' => [],
        'audits' => [],
    ], JSON_THROW_ON_ERROR));

    expect($report->scores)->toBe([
        'performance'    => null,
        'accessibility'  => null,
        'best_practices' => null,
        'seo'            => null,
    ])->and($report->metrics)->toBe([
        'lcp_ms'  => null,
        'cls'     => null,
        'inp_ms'  => null,
        'ttfb_ms' => null,
        'fcp_ms'  => null,
        'si_ms'   => null,
        'tbt_ms'  => null,
    ])->and($report->audits)->toBe([]);
});

it('throws on malformed JSON', function (): void {
    expect(fn (): \LaravelVitals\Support\LighthouseReport => LighthouseReport::fromLighthouseJson('{not json'))
        ->toThrow(\JsonException::class);
});

// ─── extractDetails ───────────────────────────────────────────────────────────

it('returns null from extractDetails when given malformed JSON', function (): void {
    expect(LighthouseReport::extractDetails('{not json'))->toBeNull();
});

it('extracts page weight from total-byte-weight', function (): void {
    $json    = file_get_contents(__DIR__ . '/../../Fixtures/lighthouse-report-rich.json');
    $details = LighthouseReport::extractDetails($json);

    expect($details)->not->toBeNull();
    expect($details['page_weight_bytes'])->toBe(1234567.0);
});

it('counts network requests', function (): void {
    $json    = file_get_contents(__DIR__ . '/../../Fixtures/lighthouse-report-rich.json');
    $details = LighthouseReport::extractDetails($json);

    expect($details['request_count'])->toBe(3);
});

it('extracts dom size', function (): void {
    $json    = file_get_contents(__DIR__ . '/../../Fixtures/lighthouse-report-rich.json');
    $details = LighthouseReport::extractDetails($json);

    expect($details['dom_size'])->toBe(1450.0);
});

it('extracts render blocking time', function (): void {
    $json    = file_get_contents(__DIR__ . '/../../Fixtures/lighthouse-report-rich.json');
    $details = LighthouseReport::extractDetails($json);

    expect($details['render_blocking_time_ms'])->toBe(450.0);
});

it('extracts lcp element snippet and selector', function (): void {
    $json    = file_get_contents(__DIR__ . '/../../Fixtures/lighthouse-report-rich.json');
    $details = LighthouseReport::extractDetails($json);

    expect($details['lcp_element'])->toMatchArray([
        'snippet'  => '<img src="hero.jpg">',
        'selector' => 'img.hero',
    ]);
});

it('extracts resource summary and excludes the total row', function (): void {
    $json    = file_get_contents(__DIR__ . '/../../Fixtures/lighthouse-report-rich.json');
    $details = LighthouseReport::extractDetails($json);

    $types = array_column($details['resource_summary'], 'type');
    expect($types)->toContain('script')
        ->toContain('stylesheet')
        ->toContain('image')
        ->toContain('font')
        ->not->toContain('total');

    expect(count($details['resource_summary']))->toBe(4);

    $script = collect($details['resource_summary'])->firstWhere('type', 'script');
    expect($script['count'])->toBe(8);
    expect($script['bytes'])->toBe(600000);
});

it('extracts third party entities with text entity format', function (): void {
    $json    = file_get_contents(__DIR__ . '/../../Fixtures/lighthouse-report-rich.json');
    $details = LighthouseReport::extractDetails($json);

    expect(count($details['third_parties']))->toBe(2);

    $ga = collect($details['third_parties'])->firstWhere('entity', 'Google Analytics');
    expect($ga)->not->toBeNull();
    expect($ga['transfer_bytes'])->toBe(45000);
    expect($ga['blocking_ms'])->toBe(120.0);
    expect($ga['main_thread_ms'])->toBe(180.0);
});

it('extracts main thread breakdown', function (): void {
    $json    = file_get_contents(__DIR__ . '/../../Fixtures/lighthouse-report-rich.json');
    $details = LighthouseReport::extractDetails($json);

    expect(count($details['main_thread']))->toBe(4);

    $scriptEval = collect($details['main_thread'])->firstWhere('category', 'Script Evaluation');
    expect($scriptEval['duration_ms'])->toBe(850.5);
});

it('extracts bootup time top scripts', function (): void {
    $json    = file_get_contents(__DIR__ . '/../../Fixtures/lighthouse-report-rich.json');
    $details = LighthouseReport::extractDetails($json);

    expect(count($details['bootup_time']))->toBe(2);
    expect($details['bootup_time'][0]['url'])->toBe('https://example.test/app.js');
    expect($details['bootup_time'][0]['total_ms'])->toBe(540.5);
});

it('extracts cache policy resources and converts ms to seconds', function (): void {
    $json    = file_get_contents(__DIR__ . '/../../Fixtures/lighthouse-report-rich.json');
    $details = LighthouseReport::extractDetails($json);

    expect(count($details['cache_policy']))->toBe(2);

    $old = collect($details['cache_policy'])->firstWhere('url', 'https://example.test/old.js');
    expect($old['ttl_seconds'])->toBe(3600);
});

it('returns slow requests sorted by duration descending', function (): void {
    $json    = file_get_contents(__DIR__ . '/../../Fixtures/lighthouse-report-rich.json');
    $details = LighthouseReport::extractDetails($json);

    // app.js: endTime 800 - startTime 100 = 700 ms (longest)
    expect($details['slow_requests'][0]['url'])->toBe('https://example.test/app.js');
    expect($details['slow_requests'][0]['duration_ms'])->toBe(700.0);
    expect($details['slow_requests'][0]['transfer_bytes'])->toBe(250000);
    expect($details['slow_requests'][0]['resource_type'])->toBe('Script');
});

it('calculates critical chain depth', function (): void {
    $json    = file_get_contents(__DIR__ . '/../../Fixtures/lighthouse-report-rich.json');
    $details = LighthouseReport::extractDetails($json);

    // chains: abc -> def -> ghi = depth 3
    expect($details['critical_chain_depth'])->toBe(3);
});

it('returns nulls and empty arrays for audits absent from the report', function (): void {
    $json    = json_encode([
        'categories' => [],
        'audits'     => [],
    ], JSON_THROW_ON_ERROR);

    $details = LighthouseReport::extractDetails($json);

    expect($details)->not->toBeNull();
    expect($details['page_weight_bytes'])->toBeNull();
    expect($details['request_count'])->toBeNull();
    expect($details['dom_size'])->toBeNull();
    expect($details['lcp_element'])->toBeNull();
    expect($details['resource_summary'])->toBe([]);
    expect($details['third_parties'])->toBe([]);
    expect($details['main_thread'])->toBe([]);
    expect($details['bootup_time'])->toBe([]);
    expect($details['cache_policy'])->toBe([]);
    expect($details['slow_requests'])->toBe([]);
    expect($details['critical_chain_depth'])->toBeNull();
});
