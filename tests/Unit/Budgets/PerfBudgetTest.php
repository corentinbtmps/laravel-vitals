<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Budgets\PerfBudget;
use LaravelVitals\Enums\AuditStatus;
use LaravelVitals\Enums\Device;
use LaravelVitals\Enums\Severity;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;

beforeEach(function (): void {
    config()->set('vitals.budgets', [
        'lcp_ms'              => ['warning' => 2500, 'critical' => 4000],
        'cls'                 => ['warning' => 0.1,  'critical' => 0.25],
        'score_performance'   => ['warning' => 90,   'critical' => 70],
        'per_url'             => [],
    ]);
});

function makeAudit(array $overrides = []): Audit
{
    $url = Url::create(['label' => 'home', 'path' => '/']);
    return Audit::create(array_merge([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'stub',
        'device' => Device::Mobile,
        'status' => AuditStatus::Completed,
    ], $overrides));
}

it('returns no violations when scores and metrics are within budget', function (): void {
    $audit = makeAudit([
        'score_performance' => 95,
        'lcp_ms'            => 1800.0,
        'cls'               => 0.05,
    ]);

    $violations = PerfBudget::evaluate($audit);

    expect($violations->all())->toBe([]);
});

it('reports a warning when LCP exceeds the warning threshold but not critical', function (): void {
    $audit = makeAudit(['score_performance' => 95, 'lcp_ms' => 3000.0]);

    $violations = PerfBudget::evaluate($audit);

    $lcp = collect($violations->all())->firstWhere('metric', 'lcp_ms');
    expect($lcp)->not->toBeNull()
        ->and($lcp['severity'])->toBe('warning');
});

it('reports a critical when LCP exceeds the critical threshold', function (): void {
    $audit = makeAudit(['score_performance' => 95, 'lcp_ms' => 5000.0]);

    $violations = PerfBudget::evaluate($audit);

    $lcp = collect($violations->all())->firstWhere('metric', 'lcp_ms');
    expect($lcp['severity'])->toBe('critical');
});

it('treats score_performance as a "below threshold" violation', function (): void {
    $audit = makeAudit(['score_performance' => 60, 'lcp_ms' => 1000.0]);

    $violations = PerfBudget::evaluate($audit);

    $perf = collect($violations->all())->firstWhere('metric', 'score_performance');
    expect($perf['severity'])->toBe('critical');
});

it('honours per-url overrides', function (): void {
    config()->set('vitals.budgets.per_url', [
        'home' => ['lcp_ms' => ['warning' => 4000, 'critical' => 6000]],
    ]);

    $audit = makeAudit(['score_performance' => 95, 'lcp_ms' => 3500.0]);

    $violations = PerfBudget::evaluate($audit);

    expect(collect($violations->all())->firstWhere('metric', 'lcp_ms'))->toBeNull();
});

it('exposes worst severity for CI exit codes', function (): void {
    $audit = makeAudit(['score_performance' => 95, 'lcp_ms' => 5000.0]);

    $violations = PerfBudget::evaluate($audit);

    expect($violations->worstSeverity())->toBe(Severity::Critical);
});
