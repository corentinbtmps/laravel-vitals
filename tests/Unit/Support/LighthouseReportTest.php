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
    expect(fn () => LighthouseReport::fromLighthouseJson('{not json'))
        ->toThrow(\JsonException::class);
});
