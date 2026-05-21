<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Content\HttpsLinksCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes when all links use HTTPS', function (): void {
    $html = '<html lang="en"><head><title>T</title></head><body><a href="https://example.com">Link</a><img src="https://example.com/img.jpg"></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new HttpsLinksCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('warns when HTTP links are present', function (): void {
    $html = '<html lang="en"><head><title>T</title></head><body><a href="http://example.com">Link</a></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new HttpsLinksCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Warning);
    expect($result->detailItems)->toHaveCount(1);
});

it('warns when HTTP image src is present', function (): void {
    $html = '<html lang="en"><head><title>T</title></head><body><img src="http://cdn.example.com/img.jpg"></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new HttpsLinksCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Warning);
});

it('passes for relative links', function (): void {
    $html = '<html lang="en"><head><title>T</title></head><body><a href="/about">About</a></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new HttpsLinksCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});
