<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Meta\OpenGraphImageCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes when og:image is present', function (): void {
    $html = '<html lang="en"><head><meta property="og:image" content="https://example.com/og.jpg"></head><body></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new OpenGraphImageCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('warns when og:image is missing', function (): void {
    $html = '<html lang="en"><head><title>T</title></head><body></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new OpenGraphImageCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Warning);
});

it('warns when og:image has empty content', function (): void {
    $html = '<html lang="en"><head><meta property="og:image" content=""></head><body></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new OpenGraphImageCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Warning);
});
