<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Meta\HtmlLangCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes when html lang attribute is set', function (): void {
    $ctx = SeoTestHelper::makeContext('<html lang="en"><head><title>T</title></head><body></body></html>');
    $result = (new HtmlLangCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
    expect($result->actual)->toContain('en');
});

it('fails when lang attribute is missing', function (): void {
    $ctx = SeoTestHelper::makeContext('<html><head><title>T</title></head><body></body></html>');
    $result = (new HtmlLangCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Fail);
});

it('passes for other valid BCP47 codes', function (): void {
    $ctx = SeoTestHelper::makeContext('<html lang="pt-BR"><head><title>T</title></head><body></body></html>');
    $result = (new HtmlLangCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});
