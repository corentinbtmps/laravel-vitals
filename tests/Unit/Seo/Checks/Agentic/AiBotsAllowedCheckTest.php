<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LaravelVitals\Seo\Checks\Agentic\AiBotsAllowedCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

beforeEach(function (): void {
    config(['app.url' => 'https://example.test']);
    config(['vitals.seo.agentic.ai_bots' => ['GPTBot', 'ClaudeBot']]);
});

it('passes when robots.txt does not block AI bots', function (): void {
    Http::fake(['*/robots.txt' => Http::response("User-agent: *\nAllow: /\n", 200)]);

    $result = (new AiBotsAllowedCheck())->run(SeoTestHelper::makeContext());

    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('passes when there is no robots.txt', function (): void {
    Http::fake(['*/robots.txt' => Http::response('', 404)]);

    $result = (new AiBotsAllowedCheck())->run(SeoTestHelper::makeContext());

    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('warns when an AI bot is disallowed at the root', function (): void {
    Http::fake(['*/robots.txt' => Http::response(
        "User-agent: GPTBot\nDisallow: /\n\nUser-agent: *\nAllow: /\n",
        200,
    )]);

    $result = (new AiBotsAllowedCheck())->run(SeoTestHelper::makeContext());

    expect($result->status)->toBe(SeoCheckStatus::Warning)
        ->and($result->actual)->toContain('GPTBot');
});
