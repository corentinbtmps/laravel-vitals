<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LaravelVitals\Seo\Checks\Agentic\LlmsTxtCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

beforeEach(function (): void {
    config(['app.url' => 'https://example.test']);
});

it('passes when llms.txt is present and well-formed', function (): void {
    Http::fake(['*/llms.txt' => Http::response(
        "# Example\n\nA helpful summary of the site for AI agents. "
        . str_repeat('More detail here. ', 20)
        . "\n\n- [Docs](https://example.test/docs)\n",
        200,
    )]);

    $result = (new LlmsTxtCheck())->run(SeoTestHelper::makeContext());

    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('warns when llms.txt is missing', function (): void {
    Http::fake(['*/llms.txt' => Http::response('', 404)]);

    $result = (new LlmsTxtCheck())->run(SeoTestHelper::makeContext());

    expect($result->status)->toBe(SeoCheckStatus::Warning);
});

it('warns when llms.txt is present but too weak', function (): void {
    Http::fake(['*/llms.txt' => Http::response('no heading and too short', 200)]);

    $result = (new LlmsTxtCheck())->run(SeoTestHelper::makeContext());

    expect($result->status)->toBe(SeoCheckStatus::Warning);
});

it('has the expected key, category and weight', function (): void {
    $check = new LlmsTxtCheck();
    expect($check->key())->toBe('llms-txt')
        ->and($check->category())->toBe(SeoCheckCategory::Agentic)
        ->and($check->weight())->toBe(4);
});
