<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Performance\StatusCodeCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes for HTTP 200', function (): void {
    $ctx = SeoTestHelper::makeContext(statusCode: 200);
    $result = (new StatusCodeCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
    expect($result->actual)->toBe('200');
});

it('fails for HTTP 404', function (): void {
    $ctx = SeoTestHelper::makeContext(statusCode: 404);
    $result = (new StatusCodeCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Fail);
});

it('warns for HTTP 301 redirect', function (): void {
    $ctx = SeoTestHelper::makeContext(statusCode: 301);
    $result = (new StatusCodeCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Warning);
    expect($result->actual)->toContain('redirect');
});

it('has weight 10 and is not optional', function (): void {
    $check = new StatusCodeCheck();
    expect($check->weight())->toBe(10)->and($check->isOptional())->toBeFalse();
});
