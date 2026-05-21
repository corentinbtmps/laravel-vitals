<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Meta\MetaDescriptionCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes when meta description is present and within length', function (): void {
    $html = '<html lang="en"><head><title>T</title><meta name="description" content="A good description under 160 chars."></head><body></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new MetaDescriptionCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('fails when meta description is missing', function (): void {
    $html = '<html lang="en"><head><title>T</title></head><body></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new MetaDescriptionCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Fail);
    expect($result->actual)->toBe('Missing');
});

it('fails when meta description is empty', function (): void {
    $html = '<html lang="en"><head><meta name="description" content=""></head><body></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new MetaDescriptionCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Fail);
});

it('warns when meta description exceeds 160 chars', function (): void {
    $longDesc = str_repeat('x', 165);
    $html = "<html lang=\"en\"><head><meta name=\"description\" content=\"{$longDesc}\"></head><body></body></html>";
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new MetaDescriptionCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Warning);
});

it('has weight 9 and is not optional', function (): void {
    $check = new MetaDescriptionCheck();
    expect($check->weight())->toBe(9)->and($check->isOptional())->toBeFalse();
});
