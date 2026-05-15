<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Enums\AuditStatus;
use LaravelVitals\Enums\Device;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;

it('returns the correct global_grade letter for a high-scoring audit', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);

    $audit = Audit::create([
        'id'                    => Str::uuid()->toString(),
        'url_id'                => $url->id,
        'driver'                => 'local',
        'device'                => Device::Mobile,
        'status'                => AuditStatus::Completed,
        'score_performance'     => 95,
        'score_accessibility'   => 92,
        'score_best_practices'  => 91,
        'score_seo'             => 93,
    ]);

    // avg = (95+92+91+93)/4 = 92.75 → 93 → A
    expect($audit->global_grade)->toBe('A');
});

it('returns the correct global_grade for mixed scores', function (): void {
    $url = Url::create(['label' => 'page', 'path' => '/page']);

    $audit = Audit::create([
        'id'                    => Str::uuid()->toString(),
        'url_id'                => $url->id,
        'driver'                => 'local',
        'device'                => Device::Desktop,
        'status'                => AuditStatus::Completed,
        'score_performance'     => 72,
        'score_accessibility'   => 80,
        'score_best_practices'  => 75,
        'score_seo'             => 85,
    ]);

    // avg = (72+80+75+85)/4 = 78 → C (>=70)
    expect($audit->global_grade)->toBe('C');
});

it('returns the correct performance_grade letter', function (): void {
    $url = Url::create(['label' => 'about', 'path' => '/about']);

    $audit = Audit::create([
        'id'                => Str::uuid()->toString(),
        'url_id'            => $url->id,
        'driver'            => 'local',
        'device'            => Device::Mobile,
        'status'            => AuditStatus::Completed,
        'score_performance' => 55,
    ]);

    // 55 → F (<60)
    expect($audit->performance_grade)->toBe('F');
});

it('returns null global_grade when all scores are null', function (): void {
    $url = Url::create(['label' => 'contact', 'path' => '/contact']);

    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'local',
        'device' => Device::Mobile,
        'status' => AuditStatus::Pending,
    ]);

    expect($audit->global_grade)->toBeNull();
});

it('returns null performance_grade when score_performance is null', function (): void {
    $url = Url::create(['label' => 'faq', 'path' => '/faq']);

    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'local',
        'device' => Device::Mobile,
        'status' => AuditStatus::Pending,
    ]);

    expect($audit->performance_grade)->toBeNull();
});

it('computes global_grade correctly with only two non-null scores', function (): void {
    $url = Url::create(['label' => 'blog', 'path' => '/blog']);

    $audit = Audit::create([
        'id'                    => Str::uuid()->toString(),
        'url_id'                => $url->id,
        'driver'                => 'local',
        'device'                => Device::Mobile,
        'status'                => AuditStatus::Completed,
        'score_performance'     => 60,
        'score_accessibility'   => null,
        'score_best_practices'  => 70,
        'score_seo'             => null,
    ]);

    // avg of [60, 70] = 65 → D (>=60)
    expect($audit->global_grade)->toBe('D');
});

it('performance_grade returns F for a very low score (< 60)', function (): void {
    $url = Url::create(['label' => 'slow', 'path' => '/slow']);

    $audit = Audit::create([
        'id'                => Str::uuid()->toString(),
        'url_id'            => $url->id,
        'driver'            => 'local',
        'device'            => Device::Mobile,
        'status'            => AuditStatus::Completed,
        'score_performance' => 20,
    ]);

    expect($audit->performance_grade)->toBe('F');
});

it('performance_grade returns A for score >= 90', function (): void {
    $url = Url::create(['label' => 'fast', 'path' => '/fast']);

    $audit = Audit::create([
        'id'                => Str::uuid()->toString(),
        'url_id'            => $url->id,
        'driver'            => 'local',
        'device'            => Device::Desktop,
        'status'            => AuditStatus::Completed,
        'score_performance' => 94,
    ]);

    expect($audit->performance_grade)->toBe('A');
});
