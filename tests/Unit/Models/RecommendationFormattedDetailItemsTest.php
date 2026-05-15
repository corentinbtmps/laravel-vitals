<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;

function makeRecoWithItems(string $auditKey, array $detailItems): Recommendation
{
    $url   = Url::create(['label' => 'test', 'path' => '/test-' . Str::random(6)]);
    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'stub',
        'device' => 'mobile',
        'status' => 'completed',
    ]);

    return Recommendation::create([
        'audit_id'        => $audit->id,
        'source'          => 'lighthouse',
        'audit_key'       => $auditKey,
        'category'        => 'performance',
        'severity'        => 'warning',
        'title_key'       => "vitals::vitals.recommendations.{$auditKey}.title",
        'description_key' => "vitals::vitals.recommendations.{$auditKey}.description",
        'detail_items'    => $detailItems,
    ]);
}

it('returns formatted detail items with url, wasted_label and hint for uses-optimized-images', function (): void {
    $reco = makeRecoWithItems('uses-optimized-images', [
        ['url' => 'https://app.test/images/hero.jpg', 'wasted_bytes' => 387000, 'total_bytes' => 512000],
        ['url' => 'https://app.test/images/banner.png', 'wasted_bytes' => 220000, 'total_bytes' => 350000],
    ]);

    $formatted = $reco->formatted_detail_items;

    expect($formatted)->toBeArray()
        ->and($formatted)->toHaveCount(2);

    $first = $formatted[0];
    expect($first['url'])->toBe('https://app.test/images/hero.jpg')
        ->and($first['wasted_label'])->toContain('wasted')
        ->and($first['hint'])->toBe('Convert to WebP or AVIF');
});

it('shows wasted bytes in KB for small savings', function (): void {
    $reco = makeRecoWithItems('uses-text-compression', [
        ['url' => 'https://app.test/build/app.js', 'wasted_bytes' => 45000, 'total_bytes' => 120000],
    ]);

    $formatted = $reco->formatted_detail_items;

    expect($formatted[0]['wasted_label'])->toBe('44 KB wasted')
        ->and($formatted[0]['hint'])->toBe('Enable gzip or Brotli compression');
});

it('shows wasted bytes in MB for large savings', function (): void {
    $reco = makeRecoWithItems('uses-optimized-images', [
        ['url' => 'https://app.test/images/huge.jpg', 'wasted_bytes' => 2_200_000, 'total_bytes' => 3_000_000],
    ]);

    $formatted = $reco->formatted_detail_items;

    expect($formatted[0]['wasted_label'])->toContain('MB wasted');
});

it('shows ms blocking label for render-blocking-resources', function (): void {
    $reco = makeRecoWithItems('render-blocking-resources', [
        ['url' => 'https://app.test/build/app.css', 'wasted_ms' => 560.0, 'total_bytes' => 98000],
    ]);

    $formatted = $reco->formatted_detail_items;

    expect($formatted[0]['wasted_label'])->toBe('560ms blocking')
        ->and($formatted[0]['hint'])->toBe('Defer or async load this resource');
});

it('returns empty array when detail_items is null', function (): void {
    $reco = makeRecoWithItems('unused-javascript', []);
    // Manually set to null via direct DB update since create won't set null for empty []
    $reco->detail_items = null;
    $reco->save();

    expect($reco->fresh()->formatted_detail_items)->toBe([]);
});

it('caps formatted items at 10 regardless of stored count', function (): void {
    $items = [];
    for ($i = 0; $i < 15; $i++) {
        $items[] = ['url' => "https://app.test/img/image-{$i}.jpg", 'wasted_bytes' => ($i + 1) * 10000];
    }

    $reco = makeRecoWithItems('uses-optimized-images', $items);

    expect(count($reco->formatted_detail_items))->toBeLessThanOrEqual(10);
});

it('returns null hint for audit keys not in the hint map', function (): void {
    $reco = makeRecoWithItems('dom-size', [
        ['url' => 'https://app.test/', 'wasted_bytes' => 0],
    ]);

    $formatted = $reco->formatted_detail_items;
    expect($formatted[0]['hint'])->toBeNull();
});
