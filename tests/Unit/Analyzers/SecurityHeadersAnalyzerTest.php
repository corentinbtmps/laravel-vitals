<?php

declare(strict_types=1);

use LaravelVitals\Analyzers\SecurityHeadersAnalyzer;
use LaravelVitals\Recommendations\AppContext;

function makeCtx(): AppContext
{
    return new AppContext(basePath: base_path(), auditedPath: '/', assetUrls: [], configSnapshot: []);
}

it('supports security-headers audit key', function (): void {
    $analyzer = new SecurityHeadersAnalyzer();

    expect($analyzer->supports('security-headers'))->toBeTrue();
    expect($analyzer->supports('uses-https'))->toBeTrue();
    expect($analyzer->supports('unused-javascript'))->toBeFalse();
});

it('returns recommendations when response headers are missing', function (): void {
    $analyzer = new SecurityHeadersAnalyzer();
    $ctx = makeCtx();

    // Empty headers — all security headers missing.
    $result = $analyzer->analyze('security-headers', [
        'response_headers' => [],
    ], $ctx);

    expect($result->count())->toBeGreaterThan(0);
});

it('returns empty collection when all security headers are present', function (): void {
    $analyzer = new SecurityHeadersAnalyzer();
    $ctx = makeCtx();

    $result = $analyzer->analyze('security-headers', [
        'response_headers' => [
            'content-security-policy'   => "default-src 'self'",
            'strict-transport-security' => 'max-age=31536000; includeSubDomains',
            'x-frame-options'           => 'DENY',
            'x-content-type-options'    => 'nosniff',
            'referrer-policy'           => 'strict-origin-when-cross-origin',
            'permissions-policy'        => 'camera=(), microphone=()',
        ],
    ], $ctx);

    expect($result->count())->toBe(0);
});

it('accepts csp frame-ancestors as replacement for x-frame-options', function (): void {
    $analyzer = new SecurityHeadersAnalyzer();
    $ctx = makeCtx();

    $result = $analyzer->analyze('security-headers', [
        'response_headers' => [
            'content-security-policy'   => "default-src 'self'; frame-ancestors 'none'",
            'strict-transport-security' => 'max-age=31536000',
            'x-content-type-options'    => 'nosniff',
            'referrer-policy'           => 'no-referrer',
            'permissions-policy'        => 'camera=()',
        ],
    ], $ctx);

    // X-Frame-Options missing but CSP frame-ancestors present — should not flag it.
    $keys = array_map(
        fn ($ref) => $ref->snippet,
        iterator_to_array($result),
    );
    $flaggedXFrame = array_filter($keys, fn ($k) => str_contains($k, 'X-Frame-Options'));
    expect($flaggedXFrame)->toBeEmpty();
});

it('returns a generic recommendation when no header data is available', function (): void {
    $analyzer = new SecurityHeadersAnalyzer();
    $ctx = makeCtx();

    // No response_headers key — falls through to generic recommendation.
    $result = $analyzer->analyze('security-headers', [], $ctx);

    expect($result->count())->toBe(1);
});
