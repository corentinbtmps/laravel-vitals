<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Meta\CanonicalCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes when canonical link is present with href', function (): void {
    $html = '<html lang="en"><head><link rel="canonical" href="https://example.com/page"></head><body></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new CanonicalCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
    expect($result->actual)->toBe('https://example.com/page');
});

it('fails when canonical link is missing', function (): void {
    $html = '<html lang="en"><head><title>T</title></head><body></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new CanonicalCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Fail);
});

it('fails when canonical link has empty href', function (): void {
    $html = '<html lang="en"><head><link rel="canonical" href=""></head><body></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new CanonicalCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Fail);
});

it('has weight 9 and is not optional', function (): void {
    $check = new CanonicalCheck();
    expect($check->weight())->toBe(9);
});
