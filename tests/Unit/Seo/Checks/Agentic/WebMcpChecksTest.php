<?php

declare(strict_types=1);

use LaravelVitals\Seo\Checks\Agentic\WebMcpAvailableCheck;
use LaravelVitals\Seo\Checks\Agentic\WebMcpFormsCheck;
use LaravelVitals\Seo\Checks\Agentic\WebMcpSchemaValidCheck;
use LaravelVitals\Seo\Enums\SeoCheckStatus;
use LaravelVitals\Tests\Unit\Seo\SeoTestHelper;

/**
 * @param array<string, mixed> $webmcp
 */
function webMcpContext(array $webmcp): \LaravelVitals\Seo\SeoCheckContext
{
    return SeoTestHelper::makeContext(agentic: ['webmcp' => $webmcp]);
}

// ─── WebMcpAvailableCheck ───────────────────────────────────────────────────

it('passes without penalty when WebMCP was not measured', function (): void {
    // No agentic payload at all (e.g. local/pagespeed driver).
    $result = (new WebMcpAvailableCheck())->run(SeoTestHelper::makeContext());

    expect($result->status)->toBe(SeoCheckStatus::Pass)
        ->and($result->actual)->toContain('Not measured');
});

it('passes when at least one WebMCP tool is exposed', function (): void {
    $result = (new WebMcpAvailableCheck())->run(webMcpContext([
        'imperativeTools' => 1,
        'declarativeTools' => 0,
    ]));

    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('warns when WebMCP was measured but no tools are exposed', function (): void {
    $result = (new WebMcpAvailableCheck())->run(webMcpContext([
        'imperativeTools' => 0,
        'declarativeTools' => 0,
    ]));

    expect($result->status)->toBe(SeoCheckStatus::Warning);
});

// ─── WebMcpFormsCheck ───────────────────────────────────────────────────────

it('passes when there are no forms to annotate', function (): void {
    $result = (new WebMcpFormsCheck())->run(webMcpContext([
        'formsTotal' => 0,
        'formsAnnotated' => 0,
    ]));

    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('passes when every form is annotated', function (): void {
    $result = (new WebMcpFormsCheck())->run(webMcpContext([
        'formsTotal' => 2,
        'formsAnnotated' => 2,
    ]));

    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('warns when some forms miss declarative WebMCP', function (): void {
    $result = (new WebMcpFormsCheck())->run(webMcpContext([
        'formsTotal' => 3,
        'formsAnnotated' => 1,
    ]));

    expect($result->status)->toBe(SeoCheckStatus::Warning)
        ->and($result->actual)->toContain('2 of 3');
});

// ─── WebMcpSchemaValidCheck ─────────────────────────────────────────────────

it('passes when there are no imperative tools to validate', function (): void {
    $result = (new WebMcpSchemaValidCheck())->run(webMcpContext([
        'imperativeTools' => 0,
        'schemaValid' => false,
    ]));

    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('passes when all tools declare a schema', function (): void {
    $result = (new WebMcpSchemaValidCheck())->run(webMcpContext([
        'imperativeTools' => 2,
        'schemaValid' => true,
    ]));

    expect($result->status)->toBe(SeoCheckStatus::Pass);
});

it('warns when a tool has no input schema', function (): void {
    $result = (new WebMcpSchemaValidCheck())->run(webMcpContext([
        'imperativeTools' => 2,
        'schemaValid' => false,
    ]));

    expect($result->status)->toBe(SeoCheckStatus::Warning);
});
