<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Configuration\NofollowCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes when no nofollow directive is present', function (): void {
    $ctx = SeoTestHelper::makeContext('<html lang="en"><head><title>Test</title></head><body></body></html>');
    $result = (new NofollowCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('warns when meta robots contains nofollow', function (): void {
    $ctx = SeoTestHelper::makeContext('<html lang="en"><head><meta name="robots" content="nofollow"><title>Test</title></head><body></body></html>');
    $result = (new NofollowCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Warning);
});

it('warns when X-Robots-Tag header contains nofollow', function (): void {
    $ctx = SeoTestHelper::makeContext(
        '<html lang="en"><head><title>Test</title></head><body></body></html>',
        200,
        ['X-Robots-Tag' => 'nofollow'],
    );
    $result = (new NofollowCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Warning);
});
