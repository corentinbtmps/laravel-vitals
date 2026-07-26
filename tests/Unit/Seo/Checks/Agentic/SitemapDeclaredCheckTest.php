<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LaravelVitals\Seo\Checks\Agentic\SitemapDeclaredCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

beforeEach(function (): void {
    config(['app.url' => 'https://example.test']);
});

it('passes when robots.txt declares a sitemap', function (): void {
    Http::fake([
        '*/robots.txt'  => Http::response("User-agent: *\nSitemap: https://example.test/sitemap.xml\n", 200),
        '*/sitemap.xml' => Http::response('', 404),
    ]);

    $result = (new SitemapDeclaredCheck())->run(SeoTestHelper::makeContext());

    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('passes when sitemap.xml is directly reachable', function (): void {
    Http::fake([
        '*/robots.txt'  => Http::response("User-agent: *\nAllow: /\n", 200),
        '*/sitemap.xml' => Http::response('<urlset></urlset>', 200),
    ]);

    $result = (new SitemapDeclaredCheck())->run(SeoTestHelper::makeContext());

    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('warns when no sitemap is discoverable', function (): void {
    Http::fake([
        '*/robots.txt'  => Http::response("User-agent: *\nAllow: /\n", 200),
        '*/sitemap.xml' => Http::response('', 404),
    ]);

    $result = (new SitemapDeclaredCheck())->run(SeoTestHelper::makeContext());

    expect($result->status)->toBe(SeoCheckStatus::Warning);
});
