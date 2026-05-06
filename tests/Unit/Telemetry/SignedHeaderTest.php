<?php

declare(strict_types=1);

use LaravelVitals\Telemetry\SignedHeader;

beforeEach(function (): void {
    config()->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
});

it('signs an audit id with HMAC-SHA256 of APP_KEY', function (): void {
    $header = SignedHeader::sign('audit-uuid-1');

    expect($header)->toStartWith('audit-uuid-1.')
        ->and(strlen($header))->toBeGreaterThan(64);
});

it('verifies a valid header and returns the audit id', function (): void {
    $header = SignedHeader::sign('audit-uuid-1');

    expect(SignedHeader::verify($header))->toBe('audit-uuid-1');
});

it('returns null for a tampered signature', function (): void {
    [$id, $sig] = explode('.', SignedHeader::sign('audit-uuid-1'), 2);
    $tampered = $id . '.' . substr($sig, 0, -1) . 'x';

    expect(SignedHeader::verify($tampered))->toBeNull();
});

it('returns null for a missing or malformed header', function (): void {
    expect(SignedHeader::verify(null))->toBeNull()
        ->and(SignedHeader::verify(''))->toBeNull()
        ->and(SignedHeader::verify('no-dot'))->toBeNull();
});

it('returns null when APP_KEY is empty', function (): void {
    config()->set('app.key', '');

    expect(SignedHeader::verify('id.sig'))->toBeNull();
});
