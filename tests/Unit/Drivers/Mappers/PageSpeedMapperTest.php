<?php

declare(strict_types=1);

use LaravelVitals\Drivers\Mappers\PageSpeedMapper;
use LaravelVitals\Support\AuditException;

it('extracts the lighthouseResult from a PSI response and parses it', function (): void {
    $json = file_get_contents(__DIR__ . '/../../../Fixtures/pagespeed-response.json');

    $report = PageSpeedMapper::fromPageSpeedJson($json);

    expect($report->scores['performance'])->toBe(92)
        ->and($report->metrics['lcp_ms'])->toBe(1850.4);
});

it('throws AuditException when the response has no lighthouseResult', function (): void {
    $json = json_encode(['kind' => 'pagespeedonline#result']);

    expect(fn (): \LaravelVitals\Support\LighthouseReport => PageSpeedMapper::fromPageSpeedJson($json))
        ->toThrow(AuditException::class);
});

it('throws AuditException on invalid JSON', function (): void {
    expect(fn (): \LaravelVitals\Support\LighthouseReport => PageSpeedMapper::fromPageSpeedJson('{not json'))
        ->toThrow(AuditException::class);
});
