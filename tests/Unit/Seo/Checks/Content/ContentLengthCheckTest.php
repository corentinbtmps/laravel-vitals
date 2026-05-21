<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Content\ContentLengthCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('is optional (opt-in)', function (): void {
    expect((new ContentLengthCheck())->isOptional())->toBeTrue();
});

it('passes when content exceeds threshold', function (): void {
    $longText = str_repeat('Lorem ipsum dolor sit amet. ', 20);
    $html = "<html lang=\"en\"><head><title>T</title></head><body><p>{$longText}</p></body></html>";
    $ctx = SeoTestHelper::makeContext($html);
    config(['vitals.seo.thresholds.content_min_chars' => 100]);
    $result = (new ContentLengthCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('warns when content is below threshold', function (): void {
    $html = '<html lang="en"><head><title>T</title></head><body><p>Short text.</p></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    config(['vitals.seo.thresholds.content_min_chars' => 300]);
    $result = (new ContentLengthCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Warning);
});
