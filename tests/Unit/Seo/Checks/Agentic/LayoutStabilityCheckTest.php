<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Agentic\LayoutStabilityCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes when CLS is within the threshold', function (): void {
    $ctx = SeoTestHelper::makeContext(reportMetrics: ['cls' => 0.03]);

    $result = (new LayoutStabilityCheck())->run($ctx);

    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('warns when CLS exceeds the threshold', function (): void {
    $ctx = SeoTestHelper::makeContext(reportMetrics: ['cls' => 0.42]);

    $result = (new LayoutStabilityCheck())->run($ctx);

    expect($result->status)->toBe(SeoCheckStatus::Warning)
        ->and($result->actual)->toContain('0.42');
});

it('does not penalise when CLS was not measured', function (): void {
    $ctx = SeoTestHelper::makeContext(reportMetrics: ['cls' => null]);

    $result = (new LayoutStabilityCheck())->run($ctx);

    expect($result->status)->toBe(SeoCheckStatus::Pass);
});
