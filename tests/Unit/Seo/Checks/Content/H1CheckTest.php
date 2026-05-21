<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Content\H1Check;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes when exactly one H1 is present', function (): void {
    $ctx = SeoTestHelper::makeContext('<html lang="en"><head><title>T</title></head><body><h1>Main</h1><p>Content</p></body></html>');
    $result = (new H1Check())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
    expect($result->actual)->toContain('1 H1');
});

it('fails when no H1 is present', function (): void {
    $ctx = SeoTestHelper::makeContext('<html lang="en"><head><title>T</title></head><body><h2>Sub</h2></body></html>');
    $result = (new H1Check())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Fail);
});

it('warns when multiple H1 elements are present', function (): void {
    $ctx = SeoTestHelper::makeContext('<html lang="en"><head><title>T</title></head><body><h1>A</h1><h1>B</h1></body></html>');
    $result = (new H1Check())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Warning);
    expect($result->actual)->toContain('2');
});

it('has correct weight', function (): void {
    $check = new H1Check();
    expect($check->weight())->toBe(8);
});
