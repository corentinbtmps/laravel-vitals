<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use LaravelVitals\Database\IndexAdvisor;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\BackendTelemetry;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;

/**
 * @param list<array{sql: string, time_ms: float}> $slow
 */
function auditWithSlowQueries(array $slow): Audit
{
    $url   = Url::create(['label' => 'db-' . Str::random(5), 'path' => '/']);
    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'stub',
        'device' => 'mobile',
        'status' => 'completed',
    ]);

    BackendTelemetry::create([
        'audit_id'           => $audit->id,
        'sampled_request'    => false,
        'http_status'        => 200,
        'duration_ms'        => 100.0,
        'memory_peak_kb'     => 1_000,
        'queries_count'      => count($slow),
        'queries_time_ms'    => 50.0,
        'queries_unique'     => count($slow),
        'n_plus_one_suspect' => false,
        'views_rendered'     => 1,
        'views_time_ms'      => 1.0,
        'jobs_dispatched'    => 0,
        'events_fired'       => 0,
        'cache_hits'         => 0,
        'cache_misses'       => 0,
        'slow_queries'       => $slow,
    ]);

    return $audit->refresh();
}

function runAdvisor(Audit $audit): void
{
    app(IndexAdvisor::class)->run($audit, $audit->telemetry);
}

function dbRecommendations(Audit $audit)
{
    return Recommendation::where('audit_id', $audit->id)->where('audit_key', 'missing-index')->get();
}

beforeEach(function (): void {
    Schema::create('shop_orders', function (Blueprint $t): void {
        $t->id();
        $t->string('email');            // not indexed
        $t->string('status')->index();  // indexed
        $t->timestamps();
    });
});

afterEach(function (): void {
    Schema::dropIfExists('shop_orders');
});

it('suggests an index for an unindexed filtered column', function (): void {
    $audit = auditWithSlowQueries([
        ['sql' => 'select * from shop_orders where email = ?', 'time_ms' => 120.0],
    ]);

    runAdvisor($audit);

    $recs = dbRecommendations($audit);
    expect($recs)->toHaveCount(1);

    $rec = $recs->first();
    expect($rec->audit_key)->toBe('missing-index')
        ->and($rec->detail_items)->toHaveCount(1)
        ->and($rec->detail_items[0]['url'])->toContain("index('email')");
});

it('does not suggest an index for an already-indexed column', function (): void {
    $audit = auditWithSlowQueries([
        ['sql' => 'select * from shop_orders where status = ?', 'time_ms' => 120.0],
    ]);

    runAdvisor($audit);

    expect(dbRecommendations($audit))->toHaveCount(0);
});

it('ignores columns that do not exist on the table', function (): void {
    $audit = auditWithSlowQueries([
        ['sql' => 'select * from shop_orders where nope_col = ?', 'time_ms' => 120.0],
    ]);

    runAdvisor($audit);

    expect(dbRecommendations($audit))->toHaveCount(0);
});

it('never suggests indexes for the package own vitals_ tables', function (): void {
    $audit = auditWithSlowQueries([
        ['sql' => 'select * from vitals_audits where url_id = ?', 'time_ms' => 120.0],
    ]);

    runAdvisor($audit);

    expect(dbRecommendations($audit))->toHaveCount(0);
});

it('does nothing when the advisor is disabled', function (): void {
    config(['vitals.database_advisor.enabled' => false]);

    $audit = auditWithSlowQueries([
        ['sql' => 'select * from shop_orders where email = ?', 'time_ms' => 120.0],
    ]);

    runAdvisor($audit);

    expect(dbRecommendations($audit))->toHaveCount(0);
});

it('does nothing when there is no telemetry', function (): void {
    $url   = Url::create(['label' => 'db-none', 'path' => '/']);
    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'stub',
        'device' => 'mobile',
        'status' => 'completed',
    ]);

    app(IndexAdvisor::class)->run($audit, null);

    expect(dbRecommendations($audit))->toHaveCount(0);
});
