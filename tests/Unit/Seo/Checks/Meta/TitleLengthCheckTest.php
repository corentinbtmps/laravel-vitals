<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Meta\TitleLengthCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes when title is present and within 60 chars', function (): void {
    $html = '<html lang="en"><head><title>My Perfect 50 Char Title Here OK!</title></head><body></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new TitleLengthCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('fails when title is missing', function (): void {
    $html = '<html lang="en"><head></head><body></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new TitleLengthCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Fail);
});

it('fails when title is empty', function (): void {
    $html = '<html lang="en"><head><title></title></head><body></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new TitleLengthCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Fail);
});

it('warns when title exceeds 60 chars', function (): void {
    $longTitle = str_repeat('A', 65);
    $html = "<html lang=\"en\"><head><title>{$longTitle}</title></head><body></body></html>";
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new TitleLengthCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Warning);
    expect($result->actual)->toContain('65 chars');
});
