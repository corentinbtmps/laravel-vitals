<?php

declare(strict_types=1);

namespace LaravelVitals\Analyzers;

use LaravelVitals\Contracts\CodeAnalyzer;
use LaravelVitals\Recommendations\AppContext;
use LaravelVitals\Support\CodeReference;
use LaravelVitals\Support\CodeReferenceCollection;

/**
 * Security headers analyzer.
 *
 * Checks the HTTP response headers captured in the Lighthouse report for the
 * presence of key security headers. Each missing or weak header generates a
 * recommendation entry with a link to the relevant web.dev or MDN documentation.
 *
 * Headers checked:
 *  - Content-Security-Policy
 *  - Strict-Transport-Security (HSTS)
 *  - X-Frame-Options (or CSP frame-ancestors)
 *  - X-Content-Type-Options: nosniff
 *  - Referrer-Policy
 *  - Permissions-Policy
 */
final class SecurityHeadersAnalyzer implements CodeAnalyzer
{
    /** @var array<int, string> */
    private const SUPPORTED = ['security-headers', 'uses-https'];

    /**
     * @var array<string, array{label: string, hint: string, doc: string}>
     */
    private const HEADERS = [
        'content-security-policy' => [
            'label' => 'Content-Security-Policy',
            'hint'  => 'Add a Content-Security-Policy header to prevent XSS attacks. Start with a restrictive policy and relax as needed. See: https://web.dev/csp/',
            'doc'   => 'https://web.dev/csp/',
        ],
        'strict-transport-security' => [
            'label' => 'Strict-Transport-Security (HSTS)',
            'hint'  => 'Add a Strict-Transport-Security header (min-age: 31536000; includeSubDomains) to enforce HTTPS. See: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Strict-Transport-Security',
            'doc'   => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Strict-Transport-Security',
        ],
        'x-frame-options' => [
            'label' => 'X-Frame-Options / CSP frame-ancestors',
            'hint'  => 'Add X-Frame-Options: DENY or a CSP frame-ancestors directive to prevent clickjacking. See: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options',
            'doc'   => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options',
        ],
        'x-content-type-options' => [
            'label' => 'X-Content-Type-Options: nosniff',
            'hint'  => 'Add X-Content-Type-Options: nosniff to prevent MIME type sniffing. See: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options',
            'doc'   => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options',
        ],
        'referrer-policy' => [
            'label' => 'Referrer-Policy',
            'hint'  => 'Add a Referrer-Policy header to control how much referrer information is sent. Recommended: strict-origin-when-cross-origin. See: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referrer-Policy',
            'doc'   => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referrer-Policy',
        ],
        'permissions-policy' => [
            'label' => 'Permissions-Policy',
            'hint'  => 'Add a Permissions-Policy header to restrict browser feature access (camera, microphone, geolocation). See: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Permissions-Policy',
            'doc'   => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Permissions-Policy',
        ],
    ];

    public function supports(string $auditKey): bool
    {
        return in_array($auditKey, self::SUPPORTED, true);
    }

    public function analyze(string $auditKey, array $auditData, AppContext $ctx): CodeReferenceCollection
    {
        // Try to extract response headers from Lighthouse report data.
        $headers = $this->extractHeaders($auditData);

        if ($headers === null) {
            // No header data available — return a generic recommendation.
            return new CodeReferenceCollection([
                new CodeReference(
                    file: 'app/Http/Middleware/SecurityHeaders.php',
                    lineStart: 1,
                    lineEnd: 1,
                    snippet: '// Add security headers middleware',
                    hint: 'Consider creating a SecurityHeaders middleware that sets CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, and Permissions-Policy.',
                ),
            ]);
        }

        $refs = [];

        foreach (self::HEADERS as $headerKey => $meta) {
            if ($this->headerPresent($headerKey, $headers)) {
                continue;
            }

            // Special case: X-Frame-Options can be replaced by CSP frame-ancestors
            if ($headerKey === 'x-frame-options' && $this->hasCspFrameAncestors($headers)) {
                continue;
            }

            $refs[] = new CodeReference(
                file: 'app/Http/Middleware',
                lineStart: 1,
                lineEnd: 1,
                snippet: "// Missing: {$meta['label']}",
                hint: $meta['hint'],
            );
        }

        return new CodeReferenceCollection($refs);
    }

    /**
     * Extract response headers from Lighthouse audit data.
     *
     * @return array<string, string>|null
     */
    private function extractHeaders(array $auditData): ?array
    {
        // Lighthouse embeds response headers in the 'details.items' of some audits,
        // or in a top-level 'response_headers' key we may have added ourselves.
        if (isset($auditData['response_headers']) && is_array($auditData['response_headers'])) {
            /** @var array<string, string> $h */
            $h = $auditData['response_headers'];
            return array_change_key_case($h, CASE_LOWER);
        }

        if (isset($auditData['details']['items']) && is_array($auditData['details']['items'])) {
            $headers = [];
            foreach ($auditData['details']['items'] as $item) {
                if (isset($item['header']['text'], $item['value']['value'])) {
                    $name = strtolower((string) $item['header']['text']);
                    $headers[$name] = (string) $item['value']['value'];
                }
            }
            return $headers !== [] ? $headers : null;
        }

        return null;
    }

    /**
     * @param array<string, string> $headers
     */
    private function headerPresent(string $key, array $headers): bool
    {
        return isset($headers[$key]) && $headers[$key] !== '';
    }

    /**
     * @param array<string, string> $headers
     */
    private function hasCspFrameAncestors(array $headers): bool
    {
        $csp = $headers['content-security-policy'] ?? '';
        return str_contains(strtolower($csp), 'frame-ancestors');
    }
}
