<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Performance\CompressionCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes when Content-Encoding is gzip', function (): void {
    $ctx = SeoTestHelper::makeContext(headers: ['Content-Encoding' => 'gzip']);
    $result = (new CompressionCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('passes when Content-Encoding is br', function (): void {
    $ctx = SeoTestHelper::makeContext(headers: ['Content-Encoding' => 'br']);
    $result = (new CompressionCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('warns when no Content-Encoding header', function (): void {
    $ctx = SeoTestHelper::makeContext();
    $result = (new CompressionCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Warning);
    expect($result->actual)->toContain('No Content-Encoding');
});

it('warns when Content-Encoding is identity', function (): void {
    $ctx = SeoTestHelper::makeContext(headers: ['Content-Encoding' => 'identity']);
    $result = (new CompressionCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Warning);
});
