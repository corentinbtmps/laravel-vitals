<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Budgets\BudgetViolations;
use LaravelVitals\Commands\Output\JUnitFormatter;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;

it('produces a JUnit XML document with one testsuite per audit', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);
    $audit = Audit::create([
        'id'                => Str::uuid()->toString(),
        'url_id'            => $url->id,
        'driver'            => 'stub',
        'device'            => 'mobile',
        'status'            => 'completed',
        'score_performance' => 95,
        'lcp_ms'            => 1500.0,
    ]);

    $violations = new BudgetViolations([
        ['metric' => 'lcp_ms', 'severity' => 'warning', 'threshold' => 1000.0, 'actual' => 1500.0],
    ]);

    $xml = JUnitFormatter::format([['audit' => $audit->fresh(), 'violations' => $violations]]);

    expect($xml)->toContain('<testsuites');
    expect($xml)->toContain('home')
        ->and($xml)->toContain('lcp_ms')
        ->and($xml)->toContain('failure');
});
