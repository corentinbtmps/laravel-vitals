<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Enums\Severity;
use LaravelVitals\Mcp\Tools\LatestAuditTool;
use LaravelVitals\Mcp\Tools\ListRecommendationsTool;
use LaravelVitals\Mcp\Tools\RunAuditTool;
use LaravelVitals\Mcp\VitalsServer;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\BackendTelemetry;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;

function completedAudit(string $label = 'home'): Audit
{
    $url   = Url::create(['label' => $label, 'path' => '/']);
    $audit = Audit::create([
        'id'                   => Str::uuid()->toString(),
        'url_id'               => $url->id,
        'driver'               => 'stub',
        'device'               => 'mobile',
        'status'               => 'completed',
        'score_performance'    => 92,
        'score_accessibility'  => 88,
        'score_best_practices' => 96,
        'score_seo'            => 100,
        'lcp_ms'               => 1500.0,
        'cls'                  => 0.02,
        'completed_at'         => now(),
    ]);

    BackendTelemetry::create([
        'audit_id'           => $audit->id,
        'sampled_request'    => false,
        'http_status'        => 200,
        'duration_ms'        => 100.0,
        'memory_peak_kb'     => 2_000,
        'queries_count'      => 12,
        'queries_time_ms'    => 30.0,
        'queries_unique'     => 8,
        'n_plus_one_suspect' => false,
        'views_rendered'     => 1,
        'views_time_ms'      => 1.0,
        'jobs_dispatched'    => 0,
        'events_fired'       => 0,
        'cache_hits'         => 0,
        'cache_misses'       => 0,
    ]);

    return $audit;
}

it('latest_audit returns the newest completed audit for a URL', function (): void {
    $audit = completedAudit();

    VitalsServer::tool(LatestAuditTool::class, ['url' => 'home'])
        ->assertOk()
        ->assertSee($audit->id);
});

it('latest_audit errors for an unknown URL label', function (): void {
    VitalsServer::tool(LatestAuditTool::class, ['url' => 'does-not-exist'])
        ->assertHasErrors();
});

it('list_recommendations returns the audit findings', function (): void {
    $audit = completedAudit();

    Recommendation::create([
        'audit_id'           => $audit->id,
        'source'             => 'lighthouse',
        'audit_key'          => 'unused-javascript',
        'category'           => 'performance',
        'severity'           => Severity::Warning,
        'title_key'          => 'vitals::vitals.recommendations.unused-javascript.title',
        'description_key'    => 'vitals::vitals.recommendations.unused-javascript.description',
        'translation_params' => [],
        'metrics'            => [],
        'code_references'    => [],
    ]);

    VitalsServer::tool(ListRecommendationsTool::class, ['url' => 'home'])
        ->assertOk()
        ->assertSee('unused-javascript');
});

it('run_audit errors when disabled by config', function (): void {
    config(['vitals.mcp.allow_run_audit' => false]);
    completedAudit();

    VitalsServer::tool(RunAuditTool::class, ['url' => 'home'])
        ->assertHasErrors();
});
