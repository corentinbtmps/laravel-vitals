<?php

declare(strict_types=1);

namespace LaravelVitals\Telemetry;

/**
 * Stateless helpers for the X-Vitals-Audit-Id signed header.
 *
 * Format: "{audit_id}.{hmac_sha256(audit_id, APP_KEY)}"
 *
 * Verification uses hash_equals to avoid timing attacks.
 */
final class SignedHeader
{
    public static function sign(string $auditId): string
    {
        $key = (string) config('app.key', '');
        $signature = hash_hmac('sha256', $auditId, $key);

        return $auditId . '.' . $signature;
    }

    public static function verify(?string $header): ?string
    {
        if ($header === null || $header === '' || ! str_contains($header, '.')) {
            return null;
        }

        $key = (string) config('app.key', '');
        if ($key === '') {
            return null;
        }

        [$auditId, $signature] = explode('.', $header, 2);

        $expected = hash_hmac('sha256', $auditId, $key);

        if (! hash_equals($expected, $signature)) {
            return null;
        }

        return $auditId;
    }
}
