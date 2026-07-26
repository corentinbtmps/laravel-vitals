<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Agentic\AccessibleNamesCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes when every interactive element has an accessible name', function (): void {
    $html = '<html lang="en"><body>'
        . '<a href="/">Home</a>'
        . '<button aria-label="Close">×</button>'
        . '<input type="text" placeholder="Search">'
        . '</body></html>';

    $result = (new AccessibleNamesCheck())->run(SeoTestHelper::makeContext($html));

    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('passes when there are no interactive elements', function (): void {
    $result = (new AccessibleNamesCheck())->run(
        SeoTestHelper::makeContext('<html lang="en"><body><p>Just text</p></body></html>'),
    );

    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('warns when an interactive element has no accessible name', function (): void {
    $html = '<html lang="en"><body>'
        . '<a href="/">Home</a>'
        . '<button></button>'
        . '</body></html>';

    $result = (new AccessibleNamesCheck())->run(SeoTestHelper::makeContext($html));

    expect($result->status)->toBe(SeoCheckStatus::Warning)
        ->and($result->actual)->toContain('1 of 2');
});
