<?php

declare(strict_types=1);

use LaravelVitals\Support\AuditOptions;

it('exposes immutable defaults', function (): void {
    $options = AuditOptions::default();

    expect($options->device)->toBe('mobile')
        ->and($options->categories)->toBe(['performance', 'accessibility', 'best_practices', 'seo'])
        ->and($options->extraHeaders)->toBe([]);
});

it('produces a copy with extra headers via withExtraHeader', function (): void {
    $options = AuditOptions::default()->withExtraHeader('X-Vitals-Audit-Id', 'abc.def');

    expect($options->extraHeaders)->toBe(['X-Vitals-Audit-Id' => 'abc.def']);
});
