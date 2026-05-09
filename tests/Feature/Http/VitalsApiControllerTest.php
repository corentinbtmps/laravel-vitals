<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use LaravelVitals\Facades\Vitals;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;

// ── helpers ───────────────────────────────────────────────────────────────────

function apiAllowAccess(): void
{
    Vitals::authorize(fn (): true => true);
}

function apiDenyAccess(): void
{
    Vitals::authorize(fn (): false => false);
}

function apiMakeUrl(array $attrs = []): Url
{
    return Url::create(array_merge([
        'label'   => 'Home',
        'path'    => '/',
        'device'  => 'mobile',
        'enabled' => true,
    ], $attrs));
}

function apiMakeAudit(Url $url, array $attrs = []): Audit
{
    return Audit::create(array_merge([
        'url_id'              => $url->id,
        'driver'              => 'stub',
        'device'              => 'mobile',
        'status'              => 'completed',
        'score_performance'   => 88,
        'score_accessibility' => 92,
        'lcp_ms'              => 2200.0,
        'inp_ms'              => 150.0,
        'cls'                 => 0.04,
        'ttfb_ms'             => 300.0,
        'completed_at'        => now(),
    ], $attrs));
}

// ── auth ──────────────────────────────────────────────────────────────────────

it('returns 403 when the gate denies for /api/v1/audits', function (): void {
    apiDenyAccess();
    $this->getJson('/vitals/api/v1/audits')->assertForbidden();
});

it('returns 403 when the gate denies for /api/v1/urls', function (): void {
    apiDenyAccess();
    $this->getJson('/vitals/api/v1/urls')->assertForbidden();
});

it('returns 403 when the gate denies for /api/v1/recommendations', function (): void {
    apiDenyAccess();
    $this->getJson('/vitals/api/v1/recommendations')->assertForbidden();
});

// ── audits index ──────────────────────────────────────────────────────────────

it('returns audits list with correct structure', function (): void {
    apiAllowAccess();

    $url   = apiMakeUrl();
    $audit = apiMakeAudit($url);

    $response = $this->getJson('/vitals/api/v1/audits');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'url', 'device', 'score_performance', 'lcp_ms', '_links']],
            'meta' => ['page', 'per_page', 'total'],
        ])
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $audit->id);
});

it('paginates audits correctly', function (): void {
    apiAllowAccess();
    $url = apiMakeUrl();

    for ($i = 0; $i < 5; $i++) {
        apiMakeAudit($url);
    }

    $response = $this->getJson('/vitals/api/v1/audits?per_page=2&page=1');

    $response->assertOk()
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.total', 5)
        ->assertJsonCount(2, 'data');
});

it('caps per_page at 100', function (): void {
    apiAllowAccess();
    $url = apiMakeUrl();

    $response = $this->getJson('/vitals/api/v1/audits?per_page=999');

    $response->assertOk()
        ->assertJsonPath('meta.per_page', 100);
});

it('filters audits by since date', function (): void {
    apiAllowAccess();
    $url = apiMakeUrl();

    apiMakeAudit($url, ['completed_at' => now()->subDays(10)]);
    $recent = apiMakeAudit($url, ['completed_at' => now()->subDay()]);

    $response = $this->getJson('/vitals/api/v1/audits?since=' . now()->subDays(3)->toDateString());

    $response->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $recent->id);
});

it('filters audits by until date', function (): void {
    apiAllowAccess();
    $url = apiMakeUrl();

    $old = apiMakeAudit($url, ['completed_at' => now()->subDays(10)]);
    apiMakeAudit($url, ['completed_at' => now()]);

    $response = $this->getJson('/vitals/api/v1/audits?until=' . now()->subDays(5)->toDateString());

    $response->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $old->id);
});

// ── audit show ────────────────────────────────────────────────────────────────

it('returns a single audit with _links', function (): void {
    apiAllowAccess();
    $url   = apiMakeUrl();
    $audit = apiMakeAudit($url);

    $this->getJson("/vitals/api/v1/audits/{$audit->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $audit->id)
        ->assertJsonStructure(['data' => ['_links' => ['self', 'html']]]);
});

it('returns 404 for unknown audit', function (): void {
    apiAllowAccess();

    $this->getJson('/vitals/api/v1/audits/non-existent-uuid')
        ->assertNotFound()
        ->assertJsonStructure(['error']);
});

// ── urls index ────────────────────────────────────────────────────────────────

it('returns urls list', function (): void {
    apiAllowAccess();
    apiMakeUrl(['label' => 'Alpha']);
    apiMakeUrl(['label' => 'Beta', 'path' => '/beta']);

    $this->getJson('/vitals/api/v1/urls')
        ->assertOk()
        ->assertJsonPath('meta.total', 2)
        ->assertJsonCount(2, 'data');
});

// ── url latest ────────────────────────────────────────────────────────────────

it('returns latest completed audit for a URL', function (): void {
    apiAllowAccess();
    $url = apiMakeUrl();
    apiMakeAudit($url, ['completed_at' => now()->subHour(), 'score_performance' => 70]);
    $latest = apiMakeAudit($url, ['completed_at' => now(), 'score_performance' => 90]);

    $this->getJson("/vitals/api/v1/urls/{$url->id}/latest")
        ->assertOk()
        ->assertJsonPath('data.id', $latest->id)
        ->assertJsonPath('data.score_performance', 90);
});

it('returns 404 for unknown url on latest', function (): void {
    apiAllowAccess();

    $this->getJson('/vitals/api/v1/urls/99999/latest')
        ->assertNotFound();
});

it('returns 404 when URL has no completed audits', function (): void {
    apiAllowAccess();
    $url = apiMakeUrl();

    $this->getJson("/vitals/api/v1/urls/{$url->id}/latest")
        ->assertNotFound()
        ->assertJsonStructure(['error']);
});

// ── recommendations ───────────────────────────────────────────────────────────

it('returns recommendations list', function (): void {
    apiAllowAccess();
    $url   = apiMakeUrl();
    $audit = apiMakeAudit($url);

    Recommendation::create([
        'audit_id'        => $audit->id,
        'source'          => 'lighthouse',
        'audit_key'       => 'unused-javascript',
        'category'        => 'performance',
        'severity'        => 'warning',
        'title_key'       => 'vitals::vitals.recommendations.unused-javascript.title',
        'description_key' => 'vitals::vitals.recommendations.unused-javascript.description',
    ]);

    $this->getJson('/vitals/api/v1/recommendations')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonStructure([
            'data' => [['id', 'audit_id', 'audit_key', 'category', 'severity', 'title', 'description']],
        ]);
});
