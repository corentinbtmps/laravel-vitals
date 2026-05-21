<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Performance\HtmlSizeCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes when HTML is within limit', function (): void {
    $html = '<html lang="en"><head><title>T</title></head><body><p>' . str_repeat('x', 1000) . '</p></body></html>';
    config(['vitals.seo.thresholds.html_max_bytes' => 100_000]);
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new HtmlSizeCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('warns when HTML exceeds limit', function (): void {
    $html = '<html lang="en"><head><title>T</title></head><body><p>' . str_repeat('x', 5000) . '</p></body></html>';
    config(['vitals.seo.thresholds.html_max_bytes' => 1000]);
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new HtmlSizeCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Warning);
});
