<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

it('creates all four vitals tables', function (): void {
    expect(Schema::hasTable('vitals_urls'))->toBeTrue()
        ->and(Schema::hasTable('vitals_audits'))->toBeTrue()
        ->and(Schema::hasTable('vitals_audit_recommendations'))->toBeTrue()
        ->and(Schema::hasTable('vitals_backend_telemetry'))->toBeTrue();
});

it('creates the expected columns on vitals_audits', function (): void {
    $expected = [
        'id', 'url_id', 'batch_id', 'driver', 'device', 'status',
        'score_performance', 'score_accessibility', 'score_best_practices', 'score_seo',
        'lcp_ms', 'cls', 'inp_ms', 'ttfb_ms', 'fcp_ms', 'si_ms', 'tbt_ms',
        'report_path', 'error', 'started_at', 'completed_at',
        'created_at', 'updated_at',
    ];

    foreach ($expected as $column) {
        expect(Schema::hasColumn('vitals_audits', $column))
            ->toBeTrue("vitals_audits is missing column [$column]");
    }
});

it('creates the expected columns on vitals_audit_recommendations', function (): void {
    $expected = [
        'id', 'audit_id', 'source', 'audit_key', 'category', 'severity',
        'title_key', 'description_key', 'translation_params', 'metrics',
        'code_references', 'created_at', 'updated_at',
    ];

    foreach ($expected as $column) {
        expect(Schema::hasColumn('vitals_audit_recommendations', $column))
            ->toBeTrue("vitals_audit_recommendations is missing column [$column]");
    }
});

it('creates the expected columns on vitals_backend_telemetry', function (): void {
    $expected = [
        'id', 'audit_id', 'sampled_request', 'route_name', 'http_status',
        'duration_ms', 'memory_peak_kb', 'queries_count', 'queries_time_ms',
        'queries_unique', 'n_plus_one_suspect', 'views_rendered', 'views_time_ms',
        'jobs_dispatched', 'events_fired', 'cache_hits', 'cache_misses',
        'slow_queries', 'truncated', 'created_at', 'updated_at',
    ];

    foreach ($expected as $column) {
        expect(Schema::hasColumn('vitals_backend_telemetry', $column))
            ->toBeTrue("vitals_backend_telemetry is missing column [$column]");
    }
});
