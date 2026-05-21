<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Performance\TtfbCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes when TTFB is below threshold', function (): void {
    $ctx = SeoTestHelper::makeContext(reportMetrics: ['ttfb_ms' => 300.0]);
    config(['vitals.seo.thresholds.ttfb_ms' => 600]);
    $result = (new TtfbCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
    expect($result->actual)->toContain('300ms');
});

it('warns when TTFB exceeds threshold', function (): void {
    $ctx = SeoTestHelper::makeContext(reportMetrics: ['ttfb_ms' => 800.0]);
    config(['vitals.seo.thresholds.ttfb_ms' => 600]);
    $result = (new TtfbCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Warning);
    expect($result->actual)->toContain('800ms');
});

it('passes when TTFB data is unavailable', function (): void {
    $ctx = SeoTestHelper::makeContext(reportMetrics: ['ttfb_ms' => null]);
    $result = (new TtfbCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('has weight 8 and is not optional', function (): void {
    $check = new TtfbCheck();
    expect($check->weight())->toBe(8);
});
