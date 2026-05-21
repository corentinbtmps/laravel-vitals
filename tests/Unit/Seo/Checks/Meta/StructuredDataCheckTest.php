<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Meta\StructuredDataCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes when valid JSON-LD is present', function (): void {
    $jsonLd = json_encode(['@context' => 'https://schema.org', '@type' => 'WebPage', 'name' => 'Test']);
    $html = "<html lang=\"en\"><head><title>T</title><script type=\"application/ld+json\">{$jsonLd}</script></head><body></body></html>";
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new StructuredDataCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('warns when no JSON-LD is present', function (): void {
    $html = '<html lang="en"><head><title>T</title></head><body></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new StructuredDataCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Warning);
});

it('fails when JSON-LD contains invalid JSON', function (): void {
    $html = '<html lang="en"><head><script type="application/ld+json">{invalid json!!!}</script></head><body></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new StructuredDataCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Fail);
});

it('is not optional', function (): void {
    expect((new StructuredDataCheck())->isOptional())->toBeFalse();
});
