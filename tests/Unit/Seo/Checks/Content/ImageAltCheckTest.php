<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Content\ImageAltCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes when all images have alt attributes', function (): void {
    $html = '<html lang="en"><head><title>T</title></head><body><img src="/img.jpg" alt="A photo"><img src="/b.jpg" alt=""></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new ImageAltCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('fails when images are missing alt attributes', function (): void {
    $html = '<html lang="en"><head><title>T</title></head><body><img src="/img.jpg"></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new ImageAltCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Fail);
    expect($result->detailItems)->toHaveCount(1);
});

it('passes for decorative images with aria-hidden', function (): void {
    $html = '<html lang="en"><head><title>T</title></head><body><img src="/dec.jpg" aria-hidden="true"></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new ImageAltCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('passes for data URI images', function (): void {
    $html = '<html lang="en"><head><title>T</title></head><body><img src="data:image/gif;base64,abc"></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new ImageAltCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});
