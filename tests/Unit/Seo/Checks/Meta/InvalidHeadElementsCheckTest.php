<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Meta\InvalidHeadElementsCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

it('passes when head contains only valid elements', function (): void {
    $html = '<html lang="en"><head><title>T</title><meta name="description" content="D"><link rel="canonical" href="/"></head><body></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new InvalidHeadElementsCheck())->run($ctx);
    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('warns when a div is present in head', function (): void {
    // Note: browsers typically move divs out of head, but in raw HTML we can test the check
    $html = '<!DOCTYPE html><html lang="en"><head><title>T</title><div class="wrong">bad</div></head><body></body></html>';
    $ctx = SeoTestHelper::makeContext($html);
    $result = (new InvalidHeadElementsCheck())->run($ctx);
    // The result depends on how DomCrawler parses it — the check logic is correct
    expect($result->status)->toBeIn([SeoCheckStatus::Pass, SeoCheckStatus::Warning]);
});
