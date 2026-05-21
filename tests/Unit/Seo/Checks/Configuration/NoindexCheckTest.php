<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Configuration\NoindexCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes when no noindex directive is present', function (): void {
    $ctx = SeoTestHelper::makeContext('<html lang="en"><head><title>Test</title></head><body><h1>OK</h1></body></html>');
    $result = (new NoindexCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('fails when meta robots contains noindex', function (): void {
    $ctx = SeoTestHelper::makeContext('<html lang="en"><head><meta name="robots" content="noindex, nofollow"><title>Test</title></head><body></body></html>');
    $result = (new NoindexCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Fail);
});

it('fails when X-Robots-Tag header contains noindex', function (): void {
    $ctx = SeoTestHelper::makeContext(
        '<html lang="en"><head><title>Test</title></head><body></body></html>',
        200,
        ['X-Robots-Tag' => 'noindex'],
    );
    $result = (new NoindexCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Fail);
});

it('has the correct key, category and weight', function (): void {
    $check = new NoindexCheck();
    expect($check->key())->toBe('noindex')
        ->and($check->weight())->toBe(10);
});
